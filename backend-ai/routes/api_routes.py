import os
import re
import logging
import tempfile
import json as _json
from datetime import datetime
from flask import Blueprint, request, jsonify, Response

from services.embedding_service import buat_embedding
from services import gemini_service
from services import rag_service

logger = logging.getLogger("NetLabsAI.Routes")
api_blueprint = Blueprint("api", __name__)

# Constants
PESAN_MODUL_BELUM_TERSEDIA = (
    "Maaf, materi tersebut belum di-upload oleh gurumu di bab pertemuan ini. "
    "Silakan tanyakan hal yang berkaitan dengan modul bab ini ya!"
)
PESAN_PERTANYAAN_TIDAK_RELEVAN = (
    "Maaf, pertanyaan tersebut tidak ditemukan dalam modul praktikum yang tersedia. "
    "Silakan tanyakan materi yang berkaitan dengan praktikum Dasar-Dasar Kejuruan."
)

# System Prompt ketat untuk NetLabs AI Tutor
SYSTEM_PROMPT = """Kamu adalah NetLabs AI Tutor, asisten cerdas untuk pembelajaran Jaringan Komputer di tingkat SMK.

ATURAN KETAT:
1. Jawab pertanyaan siswa berdasarkan konteks dokumen praktikum jaringan komputer yang disediakan di bawah.
2. JANGAN PERNAH memberikan langkah konfigurasi menggunakan CLI Cisco IOS (seperti configure terminal, ip route, router ospf, dll). Jika pertanyaan meminta langkah konfigurasi atau jika konteks dokumen berisi konfigurasi Cisco CLI, terjemahkan langkah tersebut ke konfigurasi Sistem Operasi Windows yang umum (baik GUI melalui Control Panel/Settings, maupun CLI Command Prompt/PowerShell seperti ipconfig, netsh, route add, dll). Anda diperbolehkan menggunakan pengetahuan bawaan Anda HANYA untuk melakukan penerjemahan konfigurasi Cisco ke Windows ini secara akurat.
3. Jika informasi yang ditanyakan tidak tercantum secara eksplisit atau tidak berhubungan dengan modul, Anda harus menolak menjawab dan sampaikan bahwa informasi tersebut tidak ada di modul.
4. Gunakan bahasa Indonesia yang formal, jelas, dan mudah dipahami oleh siswa SMK.
5. Wajib cantumkan format sumber di akhir jawaban Anda pada baris baru dengan format persis seperti ini:
Sumber: [Nama File Modul] (Halaman [Nomor Halaman])
Contoh:
Sumber: Modul-07-Static-Routing.pdf (Halaman 3)
Jika menggunakan beberapa sumber atau halaman berbeda, sebutkan semuanya dipisahkan dengan koma.
6. JANGAN gunakan markdown bold (**) atau formatting khusus dalam jawaban. Gunakan teks biasa saja.
7. JANGAN gunakan asterisk (*) untuk bullet points. Gunakan format: "1. " atau "- " saja.

KONTEKS DOKUMEN MODUL RESMI:
---
{konteks}
---

Berdasarkan HANYA konteks dokumen di atas, jawab pertanyaan siswa berikut dengan lengkap dan akurat."""

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

@api_blueprint.route("/", methods=["GET"])
def health_check() -> tuple[Response, int]:
    """Health check endpoint untuk monitoring."""
    total = rag_service.get_total_documents()
    return jsonify({
        "status": "running",
        "service": "NetLabs AI Backend",
        "version": "3.0.0-rag",
        "retrieval_method": "Dense Vector Search (Qdrant Cosine Similarity) + Gemini LLM",
        "vector_db": "Qdrant",
        "embedding_model": "paraphrase-multilingual-MiniLM-L12-v2",
        "qdrant_collection": "basis_pengetahuan",
        "total_documents": total,
        "timestamp": datetime.now().isoformat(),
    }), 200

@api_blueprint.route("/index-pdf", methods=["POST"])
def index_pdf() -> tuple[Response, int]:
    """Membaca file PDF, memotong teks, membuat embedding, dan menyimpannya ke Qdrant."""
    try:
        data = request.get_json(force=True) or {}
        pertemuan_id = data.get("pertemuan_id")
        file_path = data.get("file_path")

        if not pertemuan_id:
            return jsonify({"success": False, "message": "Parameter 'pertemuan_id' wajib diisi."}), 400

        if not file_path:
            return jsonify({"success": False, "message": "Parameter 'file_path' wajib diisi."}), 400

        try:
            pertemuan_id = int(pertemuan_id)
        except (ValueError, TypeError):
            return jsonify({"success": False, "message": "Parameter 'pertemuan_id' harus berupa angka."}), 400

        logger.info(f"INDEX-PDF dimulai | pertemuan_id={pertemuan_id} | File: {file_path}")
        
        result = rag_service.index_pdf_chunks(pertemuan_id, file_path)
        
        return jsonify({
            "success": True,
            "message": "Modul berhasil di-index ke Vektor DB.",
            "data": result
        }), 200

    except FileNotFoundError as e:
        logger.error(f"File PDF tidak ditemukan: {e}")
        return jsonify({"success": False, "message": f"File PDF tidak ditemukan di server: {str(e)}"}), 404
    except ValueError as e:
        logger.warning(f"Validasi gagal saat memproses PDF: {e}")
        return jsonify({"success": False, "message": str(e)}), 422
    except Exception as e:
        logger.exception(f"Error saat indexing PDF: {e}")
        return jsonify({"success": False, "message": f"Terjadi kesalahan internal saat memproses PDF: {str(e)}"}), 500

@api_blueprint.route("/chat", methods=["POST"])
def chat() -> tuple[Response, int]:
    """Menjawab pertanyaan siswa menggunakan pipeline Vector RAG (Qdrant + Cosine Similarity + Gemini)."""
    try:
        data = request.get_json(force=True) or {}
        pertemuan_id = data.get("pertemuan_id")
        message = (data.get("message") or "").strip()

        if not message:
            return jsonify({"success": False, "answer": "Parameter 'message' (pertanyaan) wajib diisi."}), 400

        if pertemuan_id is not None and pertemuan_id != "" and pertemuan_id != 0:
            try:
                pertemuan_id = int(pertemuan_id)
            except (ValueError, TypeError):
                return jsonify({"success": False, "answer": "Parameter 'pertemuan_id' harus berupa angka."}), 400
        else:
            pertemuan_id = None

        logger.info(f"CHAT [Vector RAG] dimulai | pertemuan_id={pertemuan_id} | Pertanyaan: {message[:100]}")
        
        # 1. Cek apakah ada dokumen modul ter-index untuk pertemuan_id ini
        from qdrant_client.models import Filter, FieldCondition, MatchValue
        from config import Config
        
        query_filter = None
        if pertemuan_id is not None:
            query_filter = Filter(
                must=[
                    FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                ]
            )
            
        total_pertemuan_docs = rag_service.qdrant_client.count(
            collection_name=Config.QDRANT_COLLECTION_NAME,
            count_filter=query_filter
        ).count
        
        if total_pertemuan_docs == 0:
            logger.warning(f"Tidak ada dokumen untuk pertemuan_id={pertemuan_id} di Vector DB.")
            return jsonify({
                "success": False,
                "answer": PESAN_MODUL_BELUM_TERSEDIA,
                "sources": [],
                "chunks_used": 0
            }), 200

        # 2. Jalankan Pencarian Vektor (Vector Search) menggunakan Qdrant Cosine Similarity
        query_vector = buat_embedding(message)
        hasil_pencarian = rag_service.search_relevant_chunks(pertemuan_id, query_vector, limit=4)
        
        # 3. Filter berdasarkan threshold relevansi (Cosine Similarity >= 0.30)
        SIMILARITY_THRESHOLD = 0.30
        chunks_relevan = []
        sources = set()
        retrieval_details = []
        
        for res in hasil_pencarian:
            score = res.get("score", 0.0)
            if score >= SIMILARITY_THRESHOLD:
                chunk_teks = f"[Sumber: {res['source_file']}, Halaman: {res.get('halaman', '?')}]\n{res['teks_asli']}"
                chunks_relevan.append(chunk_teks)
                sources.add(f"{res['source_file']} (Halaman {res.get('halaman', '?')})")
                retrieval_details.append({
                    "source_file": res["source_file"],
                    "halaman": res.get("halaman", "?"),
                    "chunk_index": res.get("chunk_index", "?"),
                    "similarity_score": round(score, 4),
                })

        # 4. Jika tidak ada chunk yang relevan, tolak secara spesifik
        if not chunks_relevan:
            logger.warning("Vector Search: Semua kandidat di bawah threshold. Menolak menjawab.")
            return jsonify({
                "success": False,
                "answer": PESAN_PERTANYAAN_TIDAK_RELEVAN,
                "sources": [],
                "chunks_used": 0
            }), 200

        # 5. Gabungkan konteks dan kirim ke Gemini
        konteks_gabungan = "\n\n---\n\n".join(chunks_relevan)
        prompt_lengkap = SYSTEM_PROMPT.format(konteks=konteks_gabungan)
        
        logger.info(f"Mengirim ke Gemini... (konteks: {len(konteks_gabungan)} karakter, sources: {list(sources)})")
        jawaban = gemini_service.generate_chat_response(prompt_lengkap, message)
        
        if not jawaban:
            jawaban = "Maaf, saya tidak dapat menghasilkan jawaban saat ini. Silakan coba lagi."

        return jsonify({
            "success": True,
            "answer": jawaban,
            "sources": list(sources),
            "chunks_used": len(chunks_relevan),
            "retrieval_method": "vector_qdrant_cosine_similarity",
            "retrieval_details": retrieval_details,
        }), 200

    except Exception as e:
        logger.exception(f"Error saat memproses chat: {e}")
        return jsonify({"success": False, "answer": f"Terjadi kesalahan internal: {str(e)}", "sources": [], "chunks_used": 0}), 500

@api_blueprint.route("/debug/search", methods=["GET"])
def debug_search() -> tuple[Response, int]:
    """Endpoint untuk visualisasi lengkap pipeline Hybrid RAG untuk sidang/skripsi.
    
    Menampilkan skor detail dari setiap tahap retrieval:
    - Dense Retrieval (Qdrant Cosine Similarity)
    - Sparse Retrieval (BM25 Okapi)
    - Reciprocal Rank Fusion (RRF)
    - Cross-Encoder Re-ranking
    """
    try:
        query = request.args.get("query", "").strip()
        pertemuan_id = request.args.get("pertemuan_id")

        if not query:
            return jsonify({"success": False, "message": "Query parameter 'query' wajib diisi."}), 400

        if pertemuan_id is not None and pertemuan_id != "" and pertemuan_id != "0":
            try:
                pertemuan_id = int(pertemuan_id)
            except (ValueError, TypeError):
                return jsonify({"success": False, "message": "Parameter 'pertemuan_id' harus berupa angka."}), 400
        else:
            pertemuan_id = None

        logger.info(f"DEBUG SEARCH [Hybrid] | query={query} | pertemuan_id={pertemuan_id}")

        # Jalankan pipeline Hybrid Search lengkap
        hasil_hybrid = hybrid_search(pertemuan_id, query, top_k=5)

        results = []
        for rank, res in enumerate(hasil_hybrid, start=1):
            results.append({
                "final_rank": rank,
                "dense_score": round(res.get("dense_score", 0), 4),
                "dense_rank": res.get("dense_rank"),
                "bm25_score": round(res.get("bm25_score", 0), 4),
                "bm25_rank": res.get("bm25_rank"),
                "rrf_score": round(res.get("rrf_score", 0), 6),
                "reranker_score": round(res.get("reranker_score", 0), 4),
                "source_file": res["source_file"],
                "chunk_index": int(res.get("chunk_index", 0)),
                "text": res["teks_asli"],
            })

        return jsonify({
            "success": True,
            "query": query,
            "pertemuan_id": pertemuan_id,
            "retrieval_method": "hybrid_bm25_dense_rrf_reranker",
            "pipeline_stages": [
                "1. Dense Retrieval (Qdrant Cosine Similarity, Top-10)",
                "2. Sparse Retrieval (BM25 Okapi, Top-10)",
                "3. Reciprocal Rank Fusion (RRF, k=60)",
                "4. Cross-Encoder Re-ranking (ms-marco-MiniLM-L-6-v2)",
            ],
            "results": results
        }), 200

    except Exception as e:
        logger.exception(f"Error saat debug search: {e}")
        return jsonify({"success": False, "message": str(e)}), 500

@api_blueprint.route("/generate-quiz", methods=["POST"])
def generate_quiz() -> tuple[Response, int]:
    """Membuat soal kuis pilihan ganda dari materi modul di pertemuan tertentu."""
    try:
        data = request.get_json(force=True) or {}
        pertemuan_id = data.get("pertemuan_id")
        jumlah_soal = data.get("jumlah_soal", 5)

        if not pertemuan_id:
            return jsonify({"success": False, "message": "Parameter 'pertemuan_id' wajib diisi."}), 400

        try:
            pertemuan_id = int(pertemuan_id)
        except (ValueError, TypeError):
            return jsonify({"success": False, "message": "Parameter 'pertemuan_id' harus berupa angka."}), 400

        try:
            jumlah_soal = int(jumlah_soal)
            if jumlah_soal < 1 or jumlah_soal > 20:
                raise ValueError
        except (ValueError, TypeError):
            return jsonify({"success": False, "message": "Parameter 'jumlah_soal' harus berupa angka antara 1-20."}), 400

        logger.info(f"GENERATE-QUIZ dimulai | pertemuan_id={pertemuan_id} | jumlah_soal={jumlah_soal}")

        # 1. Ambil dokumen dari Qdrant
        hasil_scroll = rag_service.get_pertemuan_chunks(pertemuan_id)
        if not hasil_scroll:
            return jsonify({
                "success": False,
                "message": f"Belum ada modul yang di-index untuk pertemuan_id={pertemuan_id}. Silakan upload modul PDF terlebih dahulu."
            }), 404

        # Urutkan berdasarkan chunk_index dan gabungkan
        hasil_scroll.sort(key=lambda p: p.payload.get("chunk_index", 0))
        konteks_gabungan = "\n\n".join([p.payload["teks_asli"] for p in hasil_scroll])

        # Batasi panjang konteks agar respon Gemini cepat dan tidak timeout
        MAX_KONTEKS = 8000
        if len(konteks_gabungan) > MAX_KONTEKS:
            konteks_gabungan = konteks_gabungan[:MAX_KONTEKS]

        # 2. Kirim ke Gemini
        prompt = QUIZ_SYSTEM_PROMPT.format(jumlah_soal=jumlah_soal, konteks=konteks_gabungan)
        raw_text = gemini_service.generate_quiz_json(prompt)

        if not raw_text:
            return jsonify({"success": False, "message": "Gemini tidak menghasilkan soal kuis."}), 500

        # 3. Parse dan validasi JSON
        clean_text = raw_text.strip()
        if clean_text.startswith("```"):
            clean_text = re.sub(r'^```(?:json)?\s*', '', clean_text, flags=re.IGNORECASE)
            clean_text = re.sub(r'\s*```$', '', clean_text).strip()

        soal_list = None
        try:
            soal_list = _json.loads(clean_text)
        except _json.JSONDecodeError:
            # Fallback regex extraction jika ada teks tambahan di luar array JSON
            json_match = re.search(r'\[.*\]', clean_text, re.DOTALL)
            if json_match:
                try:
                    soal_list = _json.loads(json_match.group())
                except _json.JSONDecodeError:
                    pass

        if not soal_list or not isinstance(soal_list, list):
            logger.error(f"Gagal parse JSON dari Gemini: {raw_text[:200]}")
            return jsonify({"success": False, "message": "Gagal memproses output JSON dari AI. Silakan coba klik tombol Generate lagi."}), 500

        REQUIRED_KEYS = {"pertanyaan", "pilihan_a", "pilihan_b", "pilihan_c", "pilihan_d", "kunci_jawaban", "pembahasan"}
        VALID_JAWABAN = {"A", "B", "C", "D"}

        soal_valid = []
        for i, soal in enumerate(soal_list):
            if not isinstance(soal, dict) or (REQUIRED_KEYS - set(soal.keys())):
                continue

            soal["kunci_jawaban"] = soal["kunci_jawaban"].strip().upper()
            if soal["kunci_jawaban"] not in VALID_JAWABAN:
                continue

            all_filled = all(isinstance(soal[k], str) and soal[k].strip() for k in REQUIRED_KEYS)
            if not all_filled:
                continue

            for key in REQUIRED_KEYS:
                soal[key] = soal[key].strip()

            soal_valid.append(soal)

        if not soal_valid:
            return jsonify({"success": False, "message": "AI tidak menghasilkan soal kuis yang valid."}), 500

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
        logger.exception(f"Error saat generate quiz: {e}")
        return jsonify({"success": False, "message": f"Terjadi kesalahan internal saat membuat soal: {str(e)}"}), 500

@api_blueprint.route("/stats", methods=["GET"])
def stats() -> tuple[Response, int]:
    """Statistik collection Qdrant."""
    try:
        stat_data = rag_service.get_statistics()
        return jsonify(dict(success=True, **stat_data)), 200
    except Exception as e:
        logger.exception(f"Error saat mengambil statistik: {e}")
        return jsonify({"success": False, "message": str(e)}), 500

@api_blueprint.route("/delete-pertemuan/<int:pertemuan_id>", methods=["DELETE"])
def delete_pertemuan(pertemuan_id: int) -> tuple[Response, int]:
    """Menghapus seluruh dokumen yang terkait dengan pertemuan_id tertentu dari Qdrant."""
    try:
        logger.info(f"Menghapus semua dokumen untuk pertemuan_id={pertemuan_id}")
        count = rag_service.delete_pertemuan_chunks(pertemuan_id)
        
        # Invalidasi cache BM25 karena dokumen dihapus
        bm25_service.invalidate_cache(pertemuan_id)
        
        return jsonify({
            "success": True,
            "message": f"Berhasil menghapus {count} dokumen untuk pertemuan_id={pertemuan_id}.",
            "deleted_count": count
        }), 200
    except Exception as e:
        logger.exception(f"Error saat menghapus dokumen: {e}")
        return jsonify({"success": False, "message": str(e)}), 500

@api_blueprint.route("/transcribe", methods=["POST"])
def transcribe_audio() -> tuple[Response, int]:
    """Mentranskripsi file audio menjadi teks menggunakan Google Gemini."""
    try:
        if "audio" not in request.files:
            return jsonify({"success": False, "message": "Field 'audio' wajib dikirim."}), 400

        audio_file = request.files["audio"]
        if audio_file.filename == "":
            return jsonify({"success": False, "message": "File audio kosong."}), 400

        mime_map = {
            ".wav": "audio/wav",
            ".mp3": "audio/mp3",
            ".m4a": "audio/mp4",
            ".ogg": "audio/ogg",
            ".webm": "audio/webm",
            ".aac": "audio/aac",
        }

        ext = os.path.splitext(audio_file.filename)[1].lower()
        mime_type = mime_map.get(ext, "audio/wav")

        logger.info(f"Transkripsi audio: {audio_file.filename} ({mime_type})")

        with tempfile.NamedTemporaryFile(delete=False, suffix=ext) as tmp:
            audio_file.save(tmp)
            tmp_path = tmp.name

        try:
            teks = gemini_service.transcribe_audio_file(tmp_path, mime_type)
            return jsonify({"success": True, "text": teks}), 200
        finally:
            if os.path.exists(tmp_path):
                os.unlink(tmp_path)

    except Exception as e:
        logger.exception(f"Error transkripsi audio: {e}")
        return jsonify({"success": False, "message": f"Gagal mentranskripsi audio: {str(e)}"}), 500
