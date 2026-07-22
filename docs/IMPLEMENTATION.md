# Lampiran Implementasi Kode Sumber Utama — Netlabs

Dokumen ini memuat detail arsitektur implementasi teknis dan salinan kode sumber (*source code*) utama dari masing-masing lapisan komponen Netlabs, yaitu:
1.  **AI Backend (Python Flask & Qdrant)**: Logika *Retrieval-Augmented Generation* (RAG) dan interaksi Gemini API.
2.  **Web Backend (Laravel 12)**: Komunikasi inter-service dan autentikasi REST API.
3.  **Mobile Client (Flutter & GetX)**: Logika otentikasi dan *session cache* pada aplikasi siswa.

---

## 1. Implementasi AI Backend (Python Flask & Qdrant)

Mesin kecerdasan buatan (*AI Engine*) Netlabs bertanggung jawab atas ekstraksi materi PDF modul praktikum, penyimpanan representasi vektor ke database Qdrant, pencarian dokumen relevan (*similarity search*), serta penjadwalan kueri LLM ke Google Gemini API.

### A. Layanan RAG & Vector Database (`backend-ai/services/rag_service.py`)

Kode ini mengimplementasikan pemisahan dokumen menjadi fragmen-fragmen (*chunks*), pembuatan *embedding* secara batch, penyimpanan ke dalam Qdrant Vector DB, serta kueri pencarian kemiripan berbasis *Cosine Similarity*.

```python
import os
import uuid
import logging
from datetime import datetime
from qdrant_client import QdrantClient
from qdrant_client.models import (
    Distance,
    VectorParams,
    PointStruct,
    Filter,
    FieldCondition,
    MatchValue,
    FilterSelector,
)
from config import Config
from services.embedding_service import buat_embedding, buat_embedding_batch, VECTOR_SIZE
from utils.text_cleaner import ekstrak_teks_pdf, potong_teks_menjadi_chunks

logger = logging.getLogger("NetLabsAI.RagService")

# Inisialisasi Qdrant client
qdrant_client = QdrantClient(path=Config.QDRANT_PERSIST_DIR)

# Buat collection jika belum ada
if not qdrant_client.collection_exists(Config.QDRANT_COLLECTION_NAME):
    qdrant_client.create_collection(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        vectors_config=VectorParams(size=VECTOR_SIZE, distance=Distance.COSINE),
    )

def index_pdf_chunks(pertemuan_id: int, file_path: str) -> dict:
    """Mengekstrak PDF, membuat embedding, dan mengindeks chunks ke Qdrant."""
    teks_pdf = ekstrak_teks_pdf(file_path)
    if not teks_pdf or len(teks_pdf.strip()) < 50:
        raise ValueError("File PDF tidak mengandung teks yang cukup untuk diproses.")

    chunks = potong_teks_menjadi_chunks(teks_pdf)
    if not chunks:
        raise ValueError("Gagal memotong teks PDF menjadi chunks.")

    nama_file = os.path.basename(file_path)

    # Hapus file lama jika re-index
    qdrant_client.delete(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        points_selector=FilterSelector(
            filter=Filter(
                must=[
                    FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                    FieldCondition(key="source_file", match=MatchValue(value=nama_file)),
                ]
            )
        ),
    )

    # Pembuatan embedding batch untuk efisiensi
    vektor_list = buat_embedding_batch(chunks)

    points = []
    for i, (chunk, vektor) in enumerate(zip(chunks, vektor_list)):
        doc_id = str(uuid.uuid4())
        points.append(
            PointStruct(
                id=doc_id,
                vector=vektor,
                payload={
                    "teks_asli": chunk,
                    "pertemuan_id": pertemuan_id,
                    "source_file": nama_file,
                    "chunk_index": i,
                    "total_chunks": len(chunks),
                    "indexed_at": datetime.now().isoformat(),
                },
            )
        )

    # Batch upsert ke Qdrant Database
    qdrant_client.upsert(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        points=points,
    )
    
    return {
        "pertemuan_id": pertemuan_id,
        "file_name": nama_file,
        "total_chunks": len(chunks),
        "total_documents_in_db": qdrant_client.count(collection_name=Config.QDRANT_COLLECTION_NAME).count,
    }

def search_relevant_chunks(pertemuan_id: int | None, query_vector: list[float], limit: int = 4) -> list[dict]:
    """Mencari chunk dokumen yang paling mirip menggunakan Cosine Similarity."""
    query_filter = None
    if pertemuan_id is not None:
        query_filter = Filter(
            must=[
                FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
            ]
        )

    hasil = qdrant_client.query_points(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        query=query_vector,
        query_filter=query_filter,
        limit=limit,
    ).points
    
    return [
        {
            "score": point.score,
            "teks_asli": point.payload.get("teks_asli", ""),
            "source_file": point.payload.get("source_file", "?"),
            "chunk_index": point.payload.get("chunk_index", "?"),
        }
        for point in hasil
    ]
```

### B. Integrasi Model LLM Google Gemini (`backend-ai/services/gemini_service.py`)

Kode ini membungkus interaksi SDK Google Generative AI untuk menghasilkan jawaban chatbot dengan pembatasan konteks (*grounding*), generator kuis terstruktur berbasis skema JSON, serta transkripsi kueri audio.

```python
import logging
import google.generativeai as genai
from config import Config

logger = logging.getLogger("NetLabsAI.GeminiService")

Config.validate()
genai.configure(api_key=Config.GEMINI_API_KEY)

# Schema JSON untuk validasi terstruktur kuis harian
QUIZ_RESPONSE_SCHEMA = {
    "type": "array",
    "items": {
        "type": "object",
        "properties": {
            "pertanyaan": {"type": "string", "description": "Teks pertanyaan soal kuis"},
            "pilihan_a": {"type": "string", "description": "Teks pilihan jawaban A"},
            "pilihan_b": {"type": "string", "description": "Teks pilihan jawaban B"},
            "pilihan_c": {"type": "string", "description": "Teks pilihan jawaban C"},
            "pilihan_d": {"type": "string", "description": "Teks pilihan jawaban D"},
            "kunci_jawaban": {
                "type": "string",
                "description": "Huruf jawaban yang benar (A/B/C/D)",
                "enum": ["A", "B", "C", "D"]
            },
            "pembahasan": {"type": "string", "description": "Penjelasan detail kunci jawaban"}
        },
        "required": [
            "pertanyaan", "pilihan_a", "pilihan_b",
            "pilihan_c", "pilihan_d", "kunci_jawaban", "pembahasan"
        ]
    }
}

# Model untuk percakapan RAG (suhu rendah untuk menekan halusinasi)
_chat_model = genai.GenerativeModel(
    model_name="gemini-2.5-flash",
    generation_config=genai.GenerationConfig(
        temperature=0.3,
        top_p=0.85,
        max_output_tokens=2048,
    ),
)

# Model untuk pembuatan kuis (suhu tinggi untuk variansi soal, wajib JSON)
_quiz_model = genai.GenerativeModel(
    model_name="gemini-2.5-flash",
    generation_config=genai.GenerationConfig(
        temperature=0.7,
        top_p=0.9,
        max_output_tokens=4096,
        response_mime_type="application/json",
        response_schema=QUIZ_RESPONSE_SCHEMA,
    ),
)

def generate_chat_response(prompt: str, user_message: str) -> str:
    """Mengirim prompt grounding dan kueri user ke model Gemini."""
    response = _chat_model.generate_content(
        contents=[
            {"role": "user", "parts": [{"text": prompt + "\n\nPertanyaan siswa: " + user_message}]},
        ],
        request_options={"timeout": 30.0}
    )
    return response.text.strip() if response.text else ""

def transcribe_audio_file(file_path: str, mime_type: str) -> str:
    """Mengunggah berkas audio ke API Gemini untuk transkripsi ke teks."""
    gemini_file = genai.upload_file(file_path, mime_type=mime_type)
    response = _chat_model.generate_content([
        gemini_file,
        "Transkripsikan audio ini menjadi teks Bahasa Indonesia. "
        "Berikan HANYA teks hasil transkripsi tanpa penjelasan tambahan. "
        "Jika audio tidak jelas atau kosong, tulis '[audio tidak jelas]'."
    ], request_options={"timeout": 60.0})
    return response.text.strip() if response.text else "[audio tidak jelas]"
```

---

## 2. Implementasi Web Backend (Laravel 12)

Web Backend bertindak sebagai *middleware* utama yang menyimpan data relasional, mengamankan komunikasi API melalui token, dan menjadi jembatan antara aplikasi Flutter dan AI Backend Flask.

### A. Jembatan Komunikasi Chat AI (`backend-web/app/Services/ChatService.php`)

Kelas ini menangani logging percakapan ke database lokal MySQL, mengirim payload kueri ke endpoint AI Backend Flask, dan menangani kegagalan koneksi (*error resilience*).

```php
<?php

namespace App\Services;

use App\Models\ChatHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ChatService
{
    protected string $aiUrl;

    public function __construct()
    {
        $this->aiUrl = config('services.ai_service.url', 'http://127.0.0.1:5050');
    }

    /**
     * Kirim pesan teks siswa ke AI Tutor dan simpan ke database.
     */
    public function kirimPesanTeks($siswa, ?int $pertemuanId, string $pesan): array
    {
        // 1. Simpan pesan input siswa
        ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $pertemuanId,
            'sender' => 'siswa',
            'pesan' => $pesan,
            'sumber_referensi' => null,
        ]);

        $sumber = 'Netlabs AI Tutor';
        $balasan = 'Maaf, terjadi kesalahan saat menghubungi AI Tutor.';
        $sources = [];
        $chunksUsed = 0;

        try {
            // Mengirim request HTTP POST ke Flask AI Backend
            $response = Http::timeout(60)->post("{$this->aiUrl}/chat", [
                'pertemuan_id' => $pertemuanId,
                'message' => $pesan,
            ]);

            if ($response->successful()) {
                $balasan = $response->json('answer') ?? 'Maaf, gagal memproses jawaban dari AI.';
                $sources = $response->json('sources') ?? [];
                $chunksUsed = $response->json('chunks_used') ?? 0;
            }
        } catch (Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            $balasan = 'Maaf, koneksi ke mesin AI sedang bermasalah. Coba lagi nanti.';
        }

        // 2. Simpan respon jawaban dari AI ke database
        $chatAi = ChatHistory::create([
            'siswa_id' => $siswa->id,
            'pertemuan_id' => $pertemuanId,
            'sender' => 'ai',
            'pesan' => $balasan,
            'sumber_referensi' => $sumber,
        ]);

        return [
            'sender' => 'ai',
            'pesan' => $balasan,
            'sumber' => $sumber,
            'waktu' => $chatAi->created_at->format('Y-m-d H:i'),
            'sources' => $sources,
            'chunks_used' => $chunksUsed,
        ];
    }
}
```

### B. Otentikasi REST API (`backend-web/app/Http/Controllers/Api/AuthController.php`)

Controller ini mengatur proses login, pembuatan token otentikasi berbasis Laravel Sanctum, penggantian password, dan invalidasi token (*logout*).

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class AuthController extends Controller
{
    // API Registrasi Siswa Baru
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'role' => $request->role,
                'kelas' => $request->kelas,
            ]);

            $token = $user->createToken('netlabs_token')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi akun berhasil!',
                'user' => new UserResource($user),
                'token' => $token
            ], 201);
        } catch (Exception $e) {
            Log::error('Error saat registrasi user: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mendaftarkan akun baru.'], 500);
        }
    }

    // API Login Siswa / Guru (Single Active Session)
    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Nomor Induk (NIS) atau Kata Sandi salah.'
                ], 401);
            }

            if ($user->status === 'nonaktif') {
                return response()->json([
                    'message' => 'Akun Anda telah dinonaktifkan oleh administrator.'
                ], 403);
            }

            // Hapus token lama untuk membatasi multi-device login
            $user->tokens()->delete();
            $token = $user->createToken('netlabs_token')->plainTextToken;

            return response()->json([
                'message' => 'Login Berhasil!',
                'user' => new UserResource($user),
                'token' => $token
            ], 200);
        } catch (Exception $e) {
            Log::error('Error saat login: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal melakukan login server.'], 500);
        }
    }
}
```

---

## 3. Implementasi Mobile Client (Flutter & GetX)

Aplikasi mobile dirancang menggunakan framework Flutter dengan manajemen status terpusat GetX untuk memastikan fungsionalitas reaktif.

### A. Pengendali Otentikasi & Penyimpanan Lokal (`netlabs_mobile/lib/app/controllers/login_controller.dart`)

Controller ini menangani logika form login, interaksi dengan server melalui request HTTP client Dio, penyimpanan token akses secara lokal memakai GetStorage, serta manajemen navigasi halaman.

```dart
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../data/providers/api_provider.dart';
import '../data/services/auth_service.dart';
import '../routes/app_pages.dart';

class LoginController extends GetxController {
  final loginFormKey = GlobalKey<FormState>();
  final _auth = Get.find<AuthService>();
  final ApiProvider _api = Get.find<ApiProvider>();

  late TextEditingController nisController;
  late TextEditingController passwordController;

  var isLoading = false.obs;
  var isPasswordObscured = true.obs;

  @override
  void onInit() {
    super.onInit();
    nisController = TextEditingController();
    passwordController = TextEditingController();

    if (_auth.isLoggedIn) {
      Future.delayed(Duration.zero, () => Get.offAllNamed(Routes.HOME));
    }
  }

  @override
  void onClose() {
    nisController.dispose();
    passwordController.dispose();
    super.onClose();
  }

  void togglePasswordVisibility() {
    isPasswordObscured.value = !isPasswordObscured.value;
  }

  void login() async {
    if (loginFormKey.currentState!.validate()) {
      isLoading.value = true;

      try {
        final response = await _api.login(
          nisController.text.trim(),
          passwordController.text,
        );

        final data = response.data;
        final user = data['user'] as Map<String, dynamic>;

        // Simpan data otentikasi dan token ke local storage
        _auth.saveLoginData({
          'token': data['token'] as String,
          'nis': user['username'] ?? '',
          'nama': user['nama'] ?? '',
          'kelas': user['kelas'] ?? '',
          'role': user['role'] ?? 'siswa',
          'password_is_default': user['password_is_default'] ?? false,
          'must_change_password': user['must_change_password'] ?? false,
          'password_grace_days_remaining': user['password_grace_days_remaining'] ?? 0,
        });

        isLoading.value = false;
        Get.offAllNamed(Routes.HOME);
      } on DioException catch (e) {
        isLoading.value = false;
        String pesanError = 'NIS atau Kata Sandi salah.';
        if (e.response?.data != null && e.response!.data['message'] != null) {
          pesanError = e.response!.data['message'];
        }
        Get.snackbar(
          'Gagal Login',
          pesanError,
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.red,
          colorText: Colors.white,
        );
      } catch (e) {
        isLoading.value = false;
        Get.snackbar(
          'Gagal Login',
          'Terjadi kesalahan: ${e.toString()}',
          snackPosition: SnackPosition.BOTTOM,
          backgroundColor: Colors.red,
          colorText: Colors.white,
        );
      }
    }
  }
}
```
