"""
=============================================================================
  NetLabs AI Backend — Core RAG Engine
  Flask + ChromaDB + Google Gemini (embedding-001 & gemini-1.5-flash)
=============================================================================
  Deskripsi:
    Menyediakan dua endpoint utama untuk Laravel Web Admin:
      1. POST /index-pdf   → Membaca PDF, chunking, embedding, simpan ke ChromaDB
      2. POST /chat         → Menjawab pertanyaan siswa berdasarkan konteks pertemuan

  Teknologi:
    - Flask (REST API)
    - ChromaDB (Vector Database, persistent storage)
    - Google GenAI Embedding (embedding-001)
    - Google Gemini LLM (gemini-1.5-flash)
    - PyMuPDF / fitz (Ekstraksi teks PDF)
    - LangChain Text Splitter (Chunking)

  Catatan Deployment:
    - Jalankan di Ubuntu Server dengan: python app.py
    - Atau gunakan Gunicorn: gunicorn -w 2 -b 0.0.0.0:5050 app:app
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
import chromadb
from chromadb.config import Settings as ChromaSettings
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
CHROMA_PERSIST_DIR = os.getenv("CHROMA_PERSIST_DIR", "./chroma_data")
CHROMA_COLLECTION_NAME = os.getenv("CHROMA_COLLECTION_NAME", "netlabs_modul")
FLASK_PORT = int(os.getenv("FLASK_PORT", 5050))
FLASK_DEBUG = os.getenv("FLASK_DEBUG", "false").lower() == "true"

if not GEMINI_API_KEY:
    logger.error("❌ GEMINI_API_KEY tidak ditemukan di environment variables!")
    raise SystemExit("GEMINI_API_KEY wajib diset. Buat file .env atau export variabel.")

# Konfigurasi Google Generative AI SDK
genai.configure(api_key=GEMINI_API_KEY)

# ─────────────────────────────────────────────────────────────────────────────
# Inisialisasi ChromaDB Client (Persistent)
# ─────────────────────────────────────────────────────────────────────────────
logger.info(f"📂 Menginisialisasi ChromaDB di: {os.path.abspath(CHROMA_PERSIST_DIR)}")
chroma_client = chromadb.PersistentClient(path=CHROMA_PERSIST_DIR)

# Buat atau ambil collection
collection = chroma_client.get_or_create_collection(
    name=CHROMA_COLLECTION_NAME,
    metadata={"hnsw:space": "cosine"},  # Menggunakan cosine similarity
)
logger.info(f"✅ ChromaDB collection '{CHROMA_COLLECTION_NAME}' siap. "
            f"Total dokumen saat ini: {collection.count()}")

# ─────────────────────────────────────────────────────────────────────────────
# Inisialisasi Gemini LLM Model
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
    """Membuat embedding vektor dari teks menggunakan Google GenAI embedding-001."""
    result = genai.embed_content(
        model="models/embedding-001",
        content=teks,
        task_type="retrieval_document",
    )
    return result['embedding']


def buat_embedding_query(teks: str) -> list[float]:
    """Membuat embedding vektor dari query siswa (task_type=retrieval_query)."""
    result = genai.embed_content(
        model="models/embedding-001",
        content=teks,
        task_type="retrieval_query",
    )
    return result['embedding']


# ═══════════════════════════════════════════════════════════════════════════════
# ENDPOINT 1: POST /index-pdf
# ═══════════════════════════════════════════════════════════════════════════════

@app.route("/index-pdf", methods=["POST"])
def index_pdf():
    """
    Membaca file PDF, memotong teks, membuat embedding, dan menyimpannya
    ke ChromaDB dengan metadata pertemuan_id.

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
        #    Kita cari dokumen dengan source yang sama untuk menghindari duplikat
        nama_file = os.path.basename(file_path)
        existing = collection.get(
            where={
                "$and": [
                    {"pertemuan_id": {"$eq": pertemuan_id}},
                    {"source_file": {"$eq": nama_file}},
                ]
            }
        )
        if existing and existing["ids"]:
            logger.info(f"🗑️  Menghapus {len(existing['ids'])} dokumen lama "
                        f"(pertemuan_id={pertemuan_id}, file={nama_file})")
            collection.delete(ids=existing["ids"])

        # ── Langkah 4: Buat embedding dan simpan ke ChromaDB ───────────
        ids = []
        embeddings = []
        documents = []
        metadatas = []

        for i, chunk in enumerate(chunks):
            doc_id = f"pertemuan_{pertemuan_id}_{nama_file}_{i}_{uuid.uuid4().hex[:8]}"
            logger.info(f"  🔢 Membuat embedding chunk {i+1}/{len(chunks)} ...")

            embedding = buat_embedding(chunk)

            ids.append(doc_id)
            embeddings.append(embedding)
            documents.append(chunk)
            metadatas.append({
                "pertemuan_id": pertemuan_id,
                "source_file": nama_file,
                "chunk_index": i,
                "total_chunks": len(chunks),
                "indexed_at": datetime.now().isoformat(),
            })

        # Batch upsert ke ChromaDB
        collection.upsert(
            ids=ids,
            embeddings=embeddings,
            documents=documents,
            metadatas=metadatas,
        )

        logger.info(f"✅ INDEX-PDF selesai | {len(chunks)} chunk berhasil disimpan "
                     f"untuk pertemuan_id={pertemuan_id}")
        logger.info(f"   Total dokumen di collection: {collection.count()}")

        return jsonify({
            "success": True,
            "message": f"Modul berhasil di-index ke Vektor DB. "
                       f"Total {len(chunks)} chunk tersimpan.",
            "data": {
                "pertemuan_id": pertemuan_id,
                "file_name": nama_file,
                "total_chunks": len(chunks),
                "total_documents_in_db": collection.count(),
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

# Threshold minimum relevansi (ChromaDB distance; cosine distance — semakin kecil semakin mirip)
# Untuk cosine distance: 0 = identik, 2 = berlawanan. Threshold 1.2 cukup longgar.
RELEVANCE_THRESHOLD = 1.2


@app.route("/chat", methods=["POST"])
def chat():
    """
    Menjawab pertanyaan siswa berdasarkan dokumen modul di pertemuan tertentu.
    Menggunakan metadata-based filtering pada ChromaDB.

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
        query_embedding = buat_embedding_query(message)

        # ── Langkah 2: Cari dokumen relevan di ChromaDB ─────────────────
        #    Filter berdasarkan pertemuan_id agar hanya modul bab aktif
        hasil_pencarian = collection.query(
            query_embeddings=[query_embedding],
            n_results=4,  # Ambil top 4 chunks terdekat
            where={"pertemuan_id": {"$eq": pertemuan_id}},
            include=["documents", "distances", "metadatas"],
        )

        dokumen_ditemukan = hasil_pencarian.get("documents", [[]])[0]
        jarak = hasil_pencarian.get("distances", [[]])[0]
        metadatas_hasil = hasil_pencarian.get("metadatas", [[]])[0]

        logger.info(f"🔍 Hasil pencarian: {len(dokumen_ditemukan)} dokumen ditemukan")

        # ── Langkah 3: Cek apakah ada dokumen yang relevan ──────────────
        if not dokumen_ditemukan:
            logger.warning(f"⚠️ Tidak ada dokumen untuk pertemuan_id={pertemuan_id}")
            return jsonify({
                "success": True,
                "answer": PESAN_TIDAK_DITEMUKAN
            }), 200

        # Filter berdasarkan threshold relevansi
        chunks_relevan = []
        for doc, dist, meta in zip(dokumen_ditemukan, jarak, metadatas_hasil):
            logger.info(f"  📊 Distance: {dist:.4f} | Source: {meta.get('source_file', '?')} "
                        f"| Chunk: {meta.get('chunk_index', '?')}")
            if dist <= RELEVANCE_THRESHOLD:
                chunks_relevan.append(doc)

        if not chunks_relevan:
            logger.warning(f"⚠️ Semua dokumen di bawah threshold relevansi "
                           f"(threshold={RELEVANCE_THRESHOLD})")
            return jsonify({
                "success": True,
                "answer": PESAN_TIDAK_DITEMUKAN
            }), 200

        logger.info(f"✅ {len(chunks_relevan)} chunk lolos filter relevansi")

        # ── Langkah 4: Gabungkan konteks dan kirim ke Gemini ────────────
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
                "total_chunks_found": len(dokumen_ditemukan),
            }
        }), 200

    except Exception as e:
        logger.exception(f"❌ Error saat memproses chat: {e}")
        return jsonify({
            "success": False,
            "answer": f"Terjadi kesalahan internal: {str(e)}"
        }), 500


# ═══════════════════════════════════════════════════════════════════════════════
# ENDPOINT TAMBAHAN: Health Check & Status
# ═══════════════════════════════════════════════════════════════════════════════

@app.route("/", methods=["GET"])
def health_check():
    """Health check endpoint untuk monitoring."""
    return jsonify({
        "status": "running",
        "service": "NetLabs AI Backend",
        "version": "1.0.0",
        "chroma_collection": CHROMA_COLLECTION_NAME,
        "total_documents": collection.count(),
        "timestamp": datetime.now().isoformat(),
    }), 200


@app.route("/stats", methods=["GET"])
def stats():
    """Statistik collection ChromaDB."""
    try:
        total = collection.count()

        # Hitung dokumen per pertemuan_id (sample dari 1000 dokumen)
        if total > 0:
            semua_data = collection.get(
                limit=min(total, 1000),
                include=["metadatas"],
            )
            pertemuan_map = {}
            for meta in semua_data.get("metadatas", []):
                pid = meta.get("pertemuan_id", "unknown")
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
    dari ChromaDB. Berguna saat guru menghapus atau mengupdate modul.
    """
    try:
        logger.info(f"🗑️ Menghapus semua dokumen untuk pertemuan_id={pertemuan_id}")

        existing = collection.get(
            where={"pertemuan_id": {"$eq": pertemuan_id}},
        )

        if not existing or not existing["ids"]:
            return jsonify({
                "success": True,
                "message": f"Tidak ada dokumen untuk pertemuan_id={pertemuan_id}.",
                "deleted_count": 0,
            }), 200

        jumlah = len(existing["ids"])
        collection.delete(ids=existing["ids"])

        logger.info(f"✅ Berhasil menghapus {jumlah} dokumen untuk pertemuan_id={pertemuan_id}")

        return jsonify({
            "success": True,
            "message": f"Berhasil menghapus {jumlah} dokumen untuk pertemuan_id={pertemuan_id}.",
            "deleted_count": jumlah,
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
    logger.info(f"   📡 Port         : {FLASK_PORT}")
    logger.info(f"   🐛 Debug Mode   : {FLASK_DEBUG}")
    logger.info(f"   📂 ChromaDB Dir : {os.path.abspath(CHROMA_PERSIST_DIR)}")
    logger.info(f"   📦 Collection   : {CHROMA_COLLECTION_NAME}")
    logger.info(f"   📄 Total Docs   : {collection.count()}")
    logger.info("=" * 60)

    app.run(
        host="0.0.0.0",
        port=FLASK_PORT,
        debug=FLASK_DEBUG,
    )
