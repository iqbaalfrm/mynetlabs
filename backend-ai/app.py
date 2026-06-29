"""
=============================================================================
  NetLabs AI Backend — Core RAG Engine
  Flask + Qdrant + Sentence Transformers + Google Gemini LLM
=============================================================================
  Deskripsi:
    Menyediakan endpoint utama untuk Laravel Web Admin & Mobile App:
      1. POST /index-pdf      → Membaca PDF, chunking, embedding, simpan ke Qdrant
      2. POST /chat            → Menjawab pertanyaan siswa (RAG) via Gemini LLM
      3. POST /generate-quiz   → Membuat soal kuis otomatis dari materi modul

  Teknologi:
    - Flask (REST API)
    - Qdrant (Vector Database, persistent storage lokal)
    - Sentence Transformers (paraphrase-multilingual-MiniLM-L12-v2, dimensi 384)
    - Google Gemini LLM (gemini-1.5-flash) — untuk generate jawaban & soal
    - PyMuPDF / fitz (Ekstraksi teks PDF)
    - LangChain Text Splitter (Chunking)

  Referensi Arsitektur RAG:
    Mengadopsi pola dari Pertemuan 9 — Chatbot RAG Sederhana,
    menggunakan Qdrant sebagai Vector DB dan Sentence Transformers
    sebagai model penyematan (embedding).

  Catatan Deployment:
    - Jalankan di Ubuntu Server dengan: python app.py
    - Atau gunakan Gunicorn: gunicorn -w 1 -b 0.0.0.0:5050 --timeout 120 app:app
    - PENTING: Gunakan 1 worker (-w 1) karena Qdrant local mode
      tidak mendukung akses multi-proses secara bersamaan.
=============================================================================
"""

import os
import re
import uuid
import logging
from datetime import datetime

from flask import Flask, request, jsonify
from dotenv import load_dotenv

import fitz  # PyMuPDF — pembaca PDF
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
from sentence_transformers import SentenceTransformer
import google.generativeai as genai
from langchain_text_splitters import RecursiveCharacterTextSplitter

# ─────────────────────────────────────────────────────────────────────────────
# Konfigurasi Logging
# ─────────────────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s → %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S",
)
logger = logging.getLogger("NetLabsAI")

# ─────────────────────────────────────────────────────────────────────────────
# Load Environment Variables
# ─────────────────────────────────────────────────────────────────────────────
load_dotenv()

GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
QDRANT_PERSIST_DIR = os.getenv("QDRANT_PERSIST_DIR", "./qdrant_data")
QDRANT_COLLECTION_NAME = os.getenv("QDRANT_COLLECTION_NAME", "basis_pengetahuan")
FLASK_PORT = int(os.getenv("FLASK_PORT", 5050))
FLASK_DEBUG = os.getenv("FLASK_DEBUG", "false").lower() == "true"

if not GEMINI_API_KEY:
    logger.error("❌ GEMINI_API_KEY tidak ditemukan di environment variables!")
    raise SystemExit("GEMINI_API_KEY wajib diset. Buat file .env atau export variabel.")

# Konfigurasi Google Generative AI SDK (untuk LLM, bukan embedding)
genai.configure(api_key=GEMINI_API_KEY)

# ─────────────────────────────────────────────────────────────────────────────
# Inisialisasi Model Penyematan — Sentence Transformers
# (Sesuai Pertemuan 9: paraphrase-multilingual-MiniLM-L12-v2, dimensi 384)
# ─────────────────────────────────────────────────────────────────────────────
logger.info("🔤 Memuat model penyematan Sentence Transformers...")
embedding_model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
VECTOR_SIZE = 384  # Dimensi output dari model MiniLM-L12-v2
logger.info("✅ Model penyematan 'paraphrase-multilingual-MiniLM-L12-v2' berhasil dimuat.")

# ─────────────────────────────────────────────────────────────────────────────
# Inisialisasi Qdrant Client (Persistent Storage Lokal)
# (Sesuai Pertemuan 9: QdrantClient dengan Distance.COSINE)
# ─────────────────────────────────────────────────────────────────────────────
logger.info(f"📂 Menginisialisasi Qdrant di: {os.path.abspath(QDRANT_PERSIST_DIR)}")
qdrant_client = QdrantClient(path=QDRANT_PERSIST_DIR)

# Buat collection jika belum ada (idempotent)
if not qdrant_client.collection_exists(QDRANT_COLLECTION_NAME):
    qdrant_client.create_collection(
        collection_name=QDRANT_COLLECTION_NAME,
        vectors_config=VectorParams(size=VECTOR_SIZE, distance=Distance.COSINE),
    )
    logger.info(f"✅ Qdrant collection '{QDRANT_COLLECTION_NAME}' berhasil dibuat.")
else:
    logger.info(f"✅ Qdrant collection '{QDRANT_COLLECTION_NAME}' sudah ada.")

total_docs = qdrant_client.count(collection_name=QDRANT_COLLECTION_NAME).count
logger.info(f"   Total dokumen saat ini: {total_docs}")

# ─────────────────────────────────────────────────────────────────────────────
# Inisialisasi Gemini LLM Model (untuk generate jawaban chat & soal kuis)
# ─────────────────────────────────────────────────────────────────────────────
gemini_model = genai.GenerativeModel(
    model_name="gemini-1.5-flash",
    generation_config=genai.GenerationConfig(
        temperature=0.3,      # Lebih deterministik agar tidak berhalusinasi
        top_p=0.85,
        top_k=40,
        max_output_tokens=2048,
    ),
)
logger.info("🤖 Model Gemini 'gemini-1.5-flash' berhasil diinisialisasi.")

# ─────────────────────────────────────────────────────────────────────────────
# Flask App
# ─────────────────────────────────────────────────────────────────────────────
app = Flask(__name__)


# ═══════════════════════════════════════════════════════════════════════════════
# HELPER FUNCTIONS
# ═══════════════════════════════════════════════════════════════════════════════

def bersihkan_teks(teks: str) -> str:
    """Membersihkan teks hasil ekstraksi PDF dari karakter-karakter yang tidak perlu."""
    if not teks:
        return ""
    # Hapus karakter kontrol kecuali newline dan tab
    teks = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', teks)
    # Normalisasi whitespace berlebih
    teks = re.sub(r'[ \t]+', ' ', teks)
    # Normalisasi newline berlebih (lebih dari 2 baris kosong → 2)
    teks = re.sub(r'\n{3,}', '\n\n', teks)
    # Hapus spasi di awal/akhir setiap baris
    teks = '\n'.join(line.strip() for line in teks.split('\n'))
    return teks.strip()


def ekstrak_teks_pdf(file_path: str) -> str:
    """Mengekstrak seluruh teks dari file PDF menggunakan PyMuPDF (fitz)."""
    logger.info(f"📖 Membaca PDF: {file_path}")

    if not os.path.exists(file_path):
        raise FileNotFoundError(f"File PDF tidak ditemukan: {file_path}")

    doc = fitz.open(file_path)
    seluruh_teks = []

    for nomor_halaman, halaman in enumerate(doc, start=1):
        teks_halaman = halaman.get_text("text")
        if teks_halaman and teks_halaman.strip():
            teks_bersih = bersihkan_teks(teks_halaman)
            seluruh_teks.append(teks_bersih)
            logger.debug(f"  ✓ Halaman {nomor_halaman}: {len(teks_bersih)} karakter")

    doc.close()

    hasil = "\n\n".join(seluruh_teks)
    logger.info(f"📄 Total teks diekstrak: {len(hasil)} karakter dari {len(seluruh_teks)} halaman")
    return hasil


def potong_teks_menjadi_chunks(teks: str, chunk_size: int = 1000, chunk_overlap: int = 200) -> list[str]:
    """Memotong teks panjang menjadi potongan-potongan kecil menggunakan RecursiveCharacterTextSplitter."""
    splitter = RecursiveCharacterTextSplitter(
        chunk_size=chunk_size,
        chunk_overlap=chunk_overlap,
        length_function=len,
        separators=["\n\n", "\n", ". ", " ", ""],
    )
    chunks = splitter.split_text(teks)
    logger.info(f"✂️  Teks dipotong menjadi {len(chunks)} chunk (size={chunk_size}, overlap={chunk_overlap})")
    return chunks


def buat_embedding(teks: str) -> list[float]:
    """
    Membuat embedding vektor dari teks menggunakan Sentence Transformers.
    Model: paraphrase-multilingual-MiniLM-L12-v2 (dimensi 384)
    Sesuai Pertemuan 9 — Chatbot RAG.
    """
    return embedding_model.encode(teks).tolist()


# ═══════════════════════════════════════════════════════════════════════════════
# ENDPOINT 1: POST /index-pdf
# ═══════════════════════════════════════════════════════════════════════════════

@app.route("/index-pdf", methods=["POST"])
def index_pdf():
    """
    Membaca file PDF, memotong teks, membuat embedding, dan menyimpannya
    ke Qdrant dengan metadata pertemuan_id.

    Request JSON:
    {
        "pertemuan_id": 2,
        "file_path": "/path/ke/file.pdf"
    }
    """
    try:
        data = request.get_json(force=True)

        # ── Validasi input ──────────────────────────────────────────────
        pertemuan_id = data.get("pertemuan_id")
        file_path = data.get("file_path")

        if not pertemuan_id:
            return jsonify({
                "success": False,
                "message": "Parameter 'pertemuan_id' wajib diisi."
            }), 400

        if not file_path:
            return jsonify({
                "success": False,
                "message": "Parameter 'file_path' wajib diisi."
            }), 400

        # Konversi pertemuan_id ke integer
        try:
            pertemuan_id = int(pertemuan_id)
        except (ValueError, TypeError):
            return jsonify({
                "success": False,
                "message": "Parameter 'pertemuan_id' harus berupa angka."
            }), 400

        logger.info(f"{'='*60}")
        logger.info(f"📥 INDEX-PDF dimulai | pertemuan_id={pertemuan_id}")
        logger.info(f"   File: {file_path}")
        logger.info(f"{'='*60}")

        # ── Langkah 1: Ekstrak teks dari PDF ────────────────────────────
        teks_pdf = ekstrak_teks_pdf(file_path)

        if not teks_pdf or len(teks_pdf.strip()) < 50:
            logger.warning("⚠️ Teks PDF terlalu pendek atau kosong.")
            return jsonify({
                "success": False,
                "message": "File PDF tidak mengandung teks yang cukup untuk diproses."
            }), 422

        # ── Langkah 2: Potong teks menjadi chunks ──────────────────────
        chunks = potong_teks_menjadi_chunks(teks_pdf)

        if not chunks:
            return jsonify({
                "success": False,
                "message": "Gagal memotong teks PDF menjadi chunks."
            }), 500

        # ── Langkah 3: Hapus data lama untuk file ini (jika re-index) ──
        #    Menggunakan filter Qdrant untuk menghindari duplikat
        nama_file = os.path.basename(file_path)
        logger.info(f"🗑️  Menghapus dokumen lama (pertemuan_id={pertemuan_id}, file={nama_file})...")

        qdrant_client.delete(
            collection_name=QDRANT_COLLECTION_NAME,
            points_selector=FilterSelector(
                filter=Filter(
                    must=[
                        FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                        FieldCondition(key="source_file", match=MatchValue(value=nama_file)),
                    ]
                )
            ),
        )

        # ── Langkah 4: Buat embedding dan simpan ke Qdrant ─────────────
        points = []

        for i, chunk in enumerate(chunks):
            doc_id = str(uuid.uuid4())  # Qdrant menggunakan UUID string
            logger.info(f"  🔢 Membuat embedding chunk {i+1}/{len(chunks)} ...")

            vektor = buat_embedding(chunk)

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

        # Batch upsert ke Qdrant
        qdrant_client.upsert(
            collection_name=QDRANT_COLLECTION_NAME,
            points=points,
        )

        total_in_db = qdrant_client.count(collection_name=QDRANT_COLLECTION_NAME).count

        logger.info(f"✅ INDEX-PDF selesai | {len(chunks)} chunk berhasil disimpan "
                     f"untuk pertemuan_id={pertemuan_id}")
        logger.info(f"   Total dokumen di collection: {total_in_db}")

        return jsonify({
            "success": True,
            "message": f"Modul berhasil di-index ke Vektor DB. "
                       f"Total {len(chunks)} chunk tersimpan.",
            "data": {
                "pertemuan_id": pertemuan_id,
                "file_name": nama_file,
                "total_chunks": len(chunks),
                "total_documents_in_db": total_in_db,
            }
        }), 200

    except FileNotFoundError as e:
        logger.error(f"❌ File tidak ditemukan: {e}")
        return jsonify({
            "success": False,
            "message": f"File PDF tidak ditemukan di server: {str(e)}"
        }), 404

    except Exception as e:
        logger.exception(f"❌ Error saat indexing PDF: {e}")
        return jsonify({
            "success": False,
            "message": f"Terjadi kesalahan internal saat memproses PDF: {str(e)}"
        }), 500


# ═══════════════════════════════════════════════════════════════════════════════
# ENDPOINT 2: POST /chat
# ═══════════════════════════════════════════════════════════════════════════════

# System Prompt ketat untuk NetLabs AI Tutor
SYSTEM_PROMPT = """Kamu adalah NetLabs AI Tutor, asisten cerdas untuk pembelajaran Jaringan Komputer di tingkat SMK.

ATURAN KETAT YANG WAJIB DIPATUHI:
1. Jawab pertanyaan siswa HANYA berdasarkan konteks dokumen praktikum jaringan komputer yang disediakan di bawah ini.
2. JANGAN pernah berhalusinasi atau memberikan jawaban di luar konteks materi bab pertemuan ini.
3. Jika informasi yang ditanyakan TIDAK ADA dalam konteks dokumen, sampaikan dengan jujur bahwa materi tersebut tidak tersedia di modul bab pertemuan ini.
4. Gunakan bahasa Indonesia yang formal, jelas, dan mudah dipahami oleh siswa SMK.
5. Jika memungkinkan, berikan contoh praktis atau langkah-langkah yang terstruktur.
6. Gunakan format yang rapi: gunakan poin-poin, penomoran, atau tabel jika diperlukan.
7. Sertakan istilah teknis jaringan komputer yang relevan beserta penjelasan singkatnya.

KONTEKS DOKUMEN MODUL PERTEMUAN:
---
{konteks}
---

Berdasarkan HANYA konteks dokumen di atas, jawab pertanyaan siswa berikut dengan lengkap dan akurat."""

# Pesan default jika tidak ada dokumen relevan ditemukan
PESAN_TIDAK_DITEMUKAN = (
    "Maaf, materi tersebut belum di-upload oleh gurumu di bab pertemuan ini. "
    "Silakan tanyakan hal yang berkaitan dengan modul bab ini ya!"
)

# Threshold minimum relevansi (Qdrant cosine similarity score)
# Qdrant cosine: 0 = tidak mirip, 1 = identik. Threshold 0.3 cukup longgar.
RELEVANCE_THRESHOLD = 0.3


@app.route("/chat", methods=["POST"])
def chat():
    """
    Menjawab pertanyaan siswa berdasarkan dokumen modul di pertemuan tertentu.
    Menggunakan Qdrant query_points dengan filter metadata pertemuan_id.

    Request JSON:
    {
        "pertemuan_id": 2,
        "message": "Bagaimana cara menghitung IP Broadcast?"
    }
    """
    try:
        data = request.get_json(force=True)

        # ── Validasi input ──────────────────────────────────────────────
        pertemuan_id = data.get("pertemuan_id")
        message = data.get("message", "").strip()

        if not pertemuan_id:
            return jsonify({
                "success": False,
                "answer": "Parameter 'pertemuan_id' wajib diisi."
            }), 400

        if not message:
            return jsonify({
                "success": False,
                "answer": "Parameter 'message' (pertanyaan) wajib diisi."
            }), 400

        try:
            pertemuan_id = int(pertemuan_id)
        except (ValueError, TypeError):
            return jsonify({
                "success": False,
                "answer": "Parameter 'pertemuan_id' harus berupa angka."
            }), 400

        logger.info(f"{'='*60}")
        logger.info(f"💬 CHAT dimulai | pertemuan_id={pertemuan_id}")
        logger.info(f"   Pertanyaan: {message[:100]}{'...' if len(message) > 100 else ''}")
        logger.info(f"{'='*60}")

        # ── Langkah 1: Buat embedding dari pertanyaan siswa ─────────────
        #    (Sesuai Pertemuan 9: model.encode → vektor query)
        vektor_query = buat_embedding(message)

        # ── Langkah 2: Cari dokumen relevan di Qdrant ───────────────────
        #    Filter berdasarkan pertemuan_id agar hanya modul bab aktif
        #    (Sesuai Pertemuan 9: client.query_points)
        hasil_pencarian = qdrant_client.query_points(
            collection_name=QDRANT_COLLECTION_NAME,
            query=vektor_query,
            query_filter=Filter(
                must=[
                    FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                ]
            ),
            limit=4,  # Ambil top 4 chunks terdekat
        ).points

        logger.info(f"🔍 Hasil pencarian: {len(hasil_pencarian)} dokumen ditemukan")

        # ── Langkah 3: Cek apakah ada dokumen yang relevan ──────────────
        if not hasil_pencarian:
            logger.warning(f"⚠️ Tidak ada dokumen untuk pertemuan_id={pertemuan_id}")
            return jsonify({
                "success": True,
                "answer": PESAN_TIDAK_DITEMUKAN
            }), 200

        # Filter berdasarkan threshold relevansi (cosine similarity score)
        chunks_relevan = []
        for point in hasil_pencarian:
            skor = point.score
            source = point.payload.get("source_file", "?")
            chunk_idx = point.payload.get("chunk_index", "?")
            logger.info(f"  📊 Score: {skor:.4f} | Source: {source} | Chunk: {chunk_idx}")

            if skor >= RELEVANCE_THRESHOLD:
                chunks_relevan.append(point.payload["teks_asli"])

        if not chunks_relevan:
            logger.warning(f"⚠️ Semua dokumen di bawah threshold relevansi "
                           f"(threshold={RELEVANCE_THRESHOLD})")
            return jsonify({
                "success": True,
                "answer": PESAN_TIDAK_DITEMUKAN
            }), 200

        logger.info(f"✅ {len(chunks_relevan)} chunk lolos filter relevansi")

        # ── Langkah 4: Gabungkan konteks dan kirim ke Gemini LLM ────────
        konteks_gabungan = "\n\n---\n\n".join(chunks_relevan)

        # Susun prompt lengkap
        prompt_lengkap = SYSTEM_PROMPT.format(konteks=konteks_gabungan)

        logger.info(f"🤖 Mengirim ke Gemini... (konteks: {len(konteks_gabungan)} karakter)")

        # Kirim ke Gemini menggunakan chat-style
        response = gemini_model.generate_content(
            contents=[
                {"role": "user", "parts": [{"text": prompt_lengkap + "\n\nPertanyaan siswa: " + message}]},
            ],
        )

        jawaban = response.text.strip() if response.text else ""

        if not jawaban:
            logger.warning("⚠️ Gemini mengembalikan jawaban kosong.")
            jawaban = ("Maaf, saya tidak dapat menghasilkan jawaban saat ini. "
                       "Silakan coba lagi dalam beberapa saat.")

        logger.info(f"✅ CHAT selesai | Jawaban: {len(jawaban)} karakter")
        logger.info(f"   Preview: {jawaban[:150]}...")

        return jsonify({
            "success": True,
            "answer": jawaban,
            "metadata": {
                "pertemuan_id": pertemuan_id,
                "chunks_used": len(chunks_relevan),
                "total_chunks_found": len(hasil_pencarian),
            }
        }), 200

    except Exception as e:
        logger.exception(f"❌ Error saat memproses chat: {e}")
        return jsonify({
            "success": False,
            "answer": f"Terjadi kesalahan internal: {str(e)}"
        }), 500


# ═══════════════════════════════════════════════════════════════════════════════
# ENDPOINT 3: POST /generate-quiz
# ═══════════════════════════════════════════════════════════════════════════════

# System Prompt ketat untuk Quiz Generator
QUIZ_SYSTEM_PROMPT = """Kamu adalah Profesor Jaringan Komputer dan Ahli Evaluasi Pendidikan SMK.

TUGAS:
Buatlah {jumlah_soal} butir soal kuis pilihan ganda yang valid, berbobot, dan relevan HANYA berdasarkan konteks materi praktikum yang disediakan di bawah ini.

ATURAN KETAT:
1. JANGAN membuat soal di luar konteks materi yang diberikan.
2. Setiap soal HARUS memiliki 4 pilihan jawaban (A, B, C, D).
3. Hanya SATU jawaban yang benar untuk setiap soal.
4. Sertakan pembahasan/penjelasan logis mengapa jawaban tersebut benar berdasarkan materi.
5. Variasikan tingkat kesulitan soal: mudah, sedang, dan sulit.
6. Gunakan bahasa Indonesia yang formal dan jelas.
7. Pastikan pilihan jawaban pengecoh (distractor) masuk akal dan tidak asal-asalan.
8. Soal harus menguji pemahaman konsep, BUKAN hafalan semata.
9. Kunci jawaban harus berupa huruf kapital: A, B, C, atau D.

KONTEKS MATERI PRAKTIKUM:
---
{konteks}
---

Buatlah tepat {jumlah_soal} soal kuis pilihan ganda berdasarkan HANYA materi di atas."""

# Schema JSON untuk Structured Output Gemini
QUIZ_RESPONSE_SCHEMA = {
    "type": "array",
    "items": {
        "type": "object",
        "properties": {
            "pertanyaan": {
                "type": "string",
                "description": "Teks pertanyaan soal kuis"
            },
            "pilihan_a": {
                "type": "string",
                "description": "Teks pilihan jawaban A"
            },
            "pilihan_b": {
                "type": "string",
                "description": "Teks pilihan jawaban B"
            },
            "pilihan_c": {
                "type": "string",
                "description": "Teks pilihan jawaban C"
            },
            "pilihan_d": {
                "type": "string",
                "description": "Teks pilihan jawaban D"
            },
            "kunci_jawaban": {
                "type": "string",
                "description": "Huruf jawaban yang benar (A/B/C/D)",
                "enum": ["A", "B", "C", "D"]
            },
            "pembahasan": {
                "type": "string",
                "description": "Penjelasan mengapa jawaban tersebut benar berdasarkan modul"
            }
        },
        "required": [
            "pertanyaan", "pilihan_a", "pilihan_b",
            "pilihan_c", "pilihan_d", "kunci_jawaban", "pembahasan"
        ]
    }
}

# Model Gemini khusus untuk quiz generation (dengan structured output)
gemini_quiz_model = genai.GenerativeModel(
    model_name="gemini-1.5-flash",
    generation_config=genai.GenerationConfig(
        temperature=0.7,       # Sedikit lebih kreatif untuk variasi soal
        top_p=0.9,
        top_k=40,
        max_output_tokens=4096,
        response_mime_type="application/json",
        response_schema=QUIZ_RESPONSE_SCHEMA,
    ),
)


@app.route("/generate-quiz", methods=["POST"])
def generate_quiz():
    """
    Membuat soal kuis pilihan ganda dari materi modul di pertemuan tertentu.
    Menggunakan Gemini Structured Output agar hasil selalu JSON valid.

    Request JSON:
    {
        "pertemuan_id": 2,
        "jumlah_soal": 5
    }
    """
    try:
        data = request.get_json(force=True)

        # ── Validasi input ──────────────────────────────────────────────
        pertemuan_id = data.get("pertemuan_id")
        jumlah_soal = data.get("jumlah_soal", 5)

        if not pertemuan_id:
            return jsonify({
                "success": False,
                "message": "Parameter 'pertemuan_id' wajib diisi."
            }), 400

        try:
            pertemuan_id = int(pertemuan_id)
        except (ValueError, TypeError):
            return jsonify({
                "success": False,
                "message": "Parameter 'pertemuan_id' harus berupa angka."
            }), 400

        try:
            jumlah_soal = int(jumlah_soal)
            if jumlah_soal < 1 or jumlah_soal > 20:
                raise ValueError
        except (ValueError, TypeError):
            return jsonify({
                "success": False,
                "message": "Parameter 'jumlah_soal' harus berupa angka antara 1-20."
            }), 400

        logger.info(f"{'='*60}")
        logger.info(f"📝 GENERATE-QUIZ dimulai | pertemuan_id={pertemuan_id}, "
                     f"jumlah_soal={jumlah_soal}")
        logger.info(f"{'='*60}")

        # ── Langkah 1: Ambil seluruh dokumen dari Qdrant ────────────────
        #    Menggunakan scroll dengan filter pertemuan_id
        hasil_scroll, _ = qdrant_client.scroll(
            collection_name=QDRANT_COLLECTION_NAME,
            scroll_filter=Filter(
                must=[
                    FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                ]
            ),
            limit=1000,
            with_payload=True,
        )

        if not hasil_scroll:
            logger.warning(f"⚠️ Tidak ada dokumen untuk pertemuan_id={pertemuan_id}")
            return jsonify({
                "success": False,
                "message": f"Belum ada modul yang di-index untuk pertemuan_id={pertemuan_id}. "
                           f"Silakan upload dan index modul PDF terlebih dahulu."
            }), 404

        logger.info(f"📚 Ditemukan {len(hasil_scroll)} chunk dokumen "
                     f"untuk pertemuan_id={pertemuan_id}")

        # ── Langkah 2: Gabungkan semua chunks menjadi konteks ───────────
        #    Urutkan berdasarkan chunk_index agar konteks berurutan
        hasil_scroll.sort(key=lambda p: p.payload.get("chunk_index", 0))
        konteks_gabungan = "\n\n".join([p.payload["teks_asli"] for p in hasil_scroll])

        # Batasi konteks agar tidak terlalu panjang (max ~15000 karakter)
        MAX_KONTEKS = 15000
        if len(konteks_gabungan) > MAX_KONTEKS:
            konteks_gabungan = konteks_gabungan[:MAX_KONTEKS]
            logger.info(f"✂️ Konteks dipotong ke {MAX_KONTEKS} karakter")

        logger.info(f"📄 Total konteks: {len(konteks_gabungan)} karakter")

        # ── Langkah 3: Kirim ke Gemini dengan Structured Output ─────────
        prompt = QUIZ_SYSTEM_PROMPT.format(
            jumlah_soal=jumlah_soal,
            konteks=konteks_gabungan,
        )

        logger.info(f"🤖 Mengirim ke Gemini untuk generate {jumlah_soal} soal quiz...")

        response = gemini_quiz_model.generate_content(
            contents=[
                {"role": "user", "parts": [{"text": prompt}]},
            ],
        )

        # ── Langkah 4: Parse dan validasi output JSON ───────────────────
        raw_text = response.text.strip() if response.text else ""

        if not raw_text:
            logger.warning("⚠️ Gemini mengembalikan respons kosong.")
            return jsonify({
                "success": False,
                "message": "Gemini tidak menghasilkan soal. Silakan coba lagi."
            }), 500

        logger.info(f"📥 Raw response diterima: {len(raw_text)} karakter")

        # Parse JSON — Structured Output seharusnya sudah valid JSON
        import json as _json

        try:
            soal_list = _json.loads(raw_text)
        except _json.JSONDecodeError:
            # Fallback: coba ekstrak JSON dari markdown code block
            logger.warning("⚠️ Response bukan JSON murni, mencoba ekstrak...")
            json_match = re.search(r'\[.*\]', raw_text, re.DOTALL)
            if json_match:
                soal_list = _json.loads(json_match.group())
            else:
                logger.error(f"❌ Gagal parse JSON dari Gemini: {raw_text[:500]}")
                return jsonify({
                    "success": False,
                    "message": "Gagal memproses output dari AI. Silakan coba lagi."
                }), 500

        # Validasi struktur setiap soal
        REQUIRED_KEYS = {"pertanyaan", "pilihan_a", "pilihan_b",
                         "pilihan_c", "pilihan_d", "kunci_jawaban", "pembahasan"}
        VALID_JAWABAN = {"A", "B", "C", "D"}

        soal_valid = []
        for i, soal in enumerate(soal_list):
            if not isinstance(soal, dict):
                logger.warning(f"⚠️ Soal #{i+1} bukan dict, dilewati")
                continue

            # Cek semua key wajib ada
            missing = REQUIRED_KEYS - set(soal.keys())
            if missing:
                logger.warning(f"⚠️ Soal #{i+1} kekurangan key: {missing}, dilewati")
                continue

            # Normalisasi kunci jawaban ke huruf kapital
            soal["kunci_jawaban"] = soal["kunci_jawaban"].strip().upper()
            if soal["kunci_jawaban"] not in VALID_JAWABAN:
                logger.warning(f"⚠️ Soal #{i+1} kunci jawaban invalid: "
                               f"'{soal['kunci_jawaban']}', dilewati")
                continue

            # Pastikan semua value adalah string non-kosong
            all_filled = all(
                isinstance(soal[k], str) and soal[k].strip()
                for k in REQUIRED_KEYS
            )
            if not all_filled:
                logger.warning(f"⚠️ Soal #{i+1} memiliki field kosong, dilewati")
                continue

            # Bersihkan whitespace berlebih
            for key in REQUIRED_KEYS:
                soal[key] = soal[key].strip()

            soal_valid.append(soal)

        if not soal_valid:
            logger.error("❌ Tidak ada soal valid yang dihasilkan.")
            return jsonify({
                "success": False,
                "message": "AI tidak menghasilkan soal yang valid. Silakan coba lagi."
            }), 500

        logger.info(f"✅ GENERATE-QUIZ selesai | {len(soal_valid)} soal valid dihasilkan "
                     f"dari {len(soal_list)} total output")

        return jsonify({
            "success": True,
            "message": f"Berhasil menghasilkan {len(soal_valid)} soal kuis.",
            "data": {
                "pertemuan_id": pertemuan_id,
                "jumlah_soal_diminta": jumlah_soal,
                "jumlah_soal_dihasilkan": len(soal_valid),
                "soal": soal_valid,
            }
        }), 200

    except Exception as e:
        logger.exception(f"❌ Error saat generate quiz: {e}")
        return jsonify({
            "success": False,
            "message": f"Terjadi kesalahan internal saat membuat soal: {str(e)}"
        }), 500


# ═══════════════════════════════════════════════════════════════════════════════
# ENDPOINT TAMBAHAN: Health Check & Status
# ═══════════════════════════════════════════════════════════════════════════════

@app.route("/", methods=["GET"])
def health_check():
    """Health check endpoint untuk monitoring."""
    total = qdrant_client.count(collection_name=QDRANT_COLLECTION_NAME).count
    return jsonify({
        "status": "running",
        "service": "NetLabs AI Backend",
        "version": "2.0.0",
        "vector_db": "Qdrant",
        "embedding_model": "paraphrase-multilingual-MiniLM-L12-v2",
        "qdrant_collection": QDRANT_COLLECTION_NAME,
        "total_documents": total,
        "timestamp": datetime.now().isoformat(),
    }), 200


@app.route("/stats", methods=["GET"])
def stats():
    """Statistik collection Qdrant."""
    try:
        total = qdrant_client.count(collection_name=QDRANT_COLLECTION_NAME).count

        # Hitung dokumen per pertemuan_id (sample dari 1000 dokumen)
        if total > 0:
            semua_data, _ = qdrant_client.scroll(
                collection_name=QDRANT_COLLECTION_NAME,
                limit=min(total, 1000),
                with_payload=True,
            )
            pertemuan_map = {}
            for point in semua_data:
                pid = point.payload.get("pertemuan_id", "unknown")
                if pid not in pertemuan_map:
                    pertemuan_map[pid] = 0
                pertemuan_map[pid] += 1
        else:
            pertemuan_map = {}

        return jsonify({
            "success": True,
            "total_documents": total,
            "per_pertemuan": pertemuan_map,
        }), 200

    except Exception as e:
        logger.exception(f"❌ Error saat mengambil statistik: {e}")
        return jsonify({
            "success": False,
            "message": str(e)
        }), 500


@app.route("/delete-pertemuan/<int:pertemuan_id>", methods=["DELETE"])
def delete_pertemuan(pertemuan_id: int):
    """
    Menghapus seluruh dokumen yang terkait dengan pertemuan_id tertentu
    dari Qdrant. Berguna saat guru menghapus atau mengupdate modul.
    """
    try:
        logger.info(f"🗑️ Menghapus semua dokumen untuk pertemuan_id={pertemuan_id}")

        # Hitung dulu berapa yang akan dihapus
        jumlah_sebelum = qdrant_client.count(
            collection_name=QDRANT_COLLECTION_NAME,
            count_filter=Filter(
                must=[
                    FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                ]
            ),
        ).count

        if jumlah_sebelum == 0:
            return jsonify({
                "success": True,
                "message": f"Tidak ada dokumen untuk pertemuan_id={pertemuan_id}.",
                "deleted_count": 0,
            }), 200

        # Hapus berdasarkan filter
        qdrant_client.delete(
            collection_name=QDRANT_COLLECTION_NAME,
            points_selector=FilterSelector(
                filter=Filter(
                    must=[
                        FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                    ]
                )
            ),
        )

        logger.info(f"✅ Berhasil menghapus {jumlah_sebelum} dokumen untuk pertemuan_id={pertemuan_id}")

        return jsonify({
            "success": True,
            "message": f"Berhasil menghapus {jumlah_sebelum} dokumen untuk pertemuan_id={pertemuan_id}.",
            "deleted_count": jumlah_sebelum,
        }), 200

    except Exception as e:
        logger.exception(f"❌ Error saat menghapus dokumen: {e}")
        return jsonify({
            "success": False,
            "message": str(e)
        }), 500


# ═══════════════════════════════════════════════════════════════════════════════
# MAIN ENTRY POINT
# ═══════════════════════════════════════════════════════════════════════════════

if __name__ == "__main__":
    logger.info("=" * 60)
    logger.info("🚀 NetLabs AI Backend dimulai!")
    logger.info(f"   📡 Port           : {FLASK_PORT}")
    logger.info(f"   🐛 Debug Mode     : {FLASK_DEBUG}")
    logger.info(f"   📂 Qdrant Dir     : {os.path.abspath(QDRANT_PERSIST_DIR)}")
    logger.info(f"   📦 Collection     : {QDRANT_COLLECTION_NAME}")
    logger.info(f"   🔤 Embedding      : paraphrase-multilingual-MiniLM-L12-v2")
    logger.info(f"   📄 Total Docs     : {qdrant_client.count(collection_name=QDRANT_COLLECTION_NAME).count}")
    logger.info("=" * 60)

    app.run(
        host="0.0.0.0",
        port=FLASK_PORT,
        debug=FLASK_DEBUG,
    )
