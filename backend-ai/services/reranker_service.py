import os
os.environ["USE_TF"] = "0"
os.environ["USE_TORCH"] = "1"

import logging
from sentence_transformers import CrossEncoder

logger = logging.getLogger("NetLabsAI.RerankerService")

# ─────────────────────────────────────────────────────────────────────────────
# Inisialisasi Cross-Encoder Re-ranker Model
# Model: ms-marco-MiniLM-L-6-v2 (~80MB, ringan, presisi tinggi)
#
# Perbedaan Bi-Encoder vs Cross-Encoder:
# - Bi-Encoder:  query & doc di-encode TERPISAH → cepat tapi kurang presisi.
# - Cross-Encoder: query & doc di-encode BERSAMAAN → lambat tapi sangat presisi.
#
# Dalam pipeline Hybrid RAG, Cross-Encoder digunakan sebagai tahap akhir
# (re-ranker) untuk menilai ulang kandidat dari Bi-Encoder + BM25.
# ─────────────────────────────────────────────────────────────────────────────
RERANKER_MODEL_NAME = "cross-encoder/ms-marco-MiniLM-L-6-v2"

logger.info(f"Memuat model Cross-Encoder Re-ranker '{RERANKER_MODEL_NAME}'...")
_reranker = CrossEncoder(RERANKER_MODEL_NAME)
logger.info("Model Cross-Encoder Re-ranker berhasil dimuat.")


def rerank_chunks(query: str, chunks: list[dict], top_k: int = 4) -> list[dict]:
    """Menilai ulang (re-rank) kandidat chunks menggunakan Cross-Encoder Neural Network.

    Cross-Encoder mengevaluasi setiap pasangan (query, chunk_text) secara bersamaan
    melalui attention mechanism Transformer, menghasilkan skor relevansi yang
    jauh lebih presisi dibandingkan Bi-Encoder (cosine similarity).

    Arsitektur:
        Input: [CLS] query [SEP] chunk_text [SEP] → Transformer → Skor Relevansi

    Args:
        query (str): Pertanyaan siswa.
        chunks (list[dict]): Daftar kandidat chunks dari Hybrid Search (BM25 + Dense).
            Setiap chunk harus memiliki key 'teks_asli'.
        top_k (int): Jumlah chunk terbaik yang akan dikembalikan setelah re-ranking.

    Returns:
        list[dict]: Daftar chunks yang sudah di-rerank, diurutkan berdasarkan
            skor Cross-Encoder tertinggi. Setiap chunk ditambahkan key 'reranker_score'.
    """
    if not chunks or not query:
        return chunks[:top_k] if chunks else []

    # Buat pasangan (query, chunk_text) untuk Cross-Encoder
    pairs = [(query, chunk["teks_asli"]) for chunk in chunks]

    # Prediksi skor relevansi menggunakan Cross-Encoder
    scores = _reranker.predict(pairs)

    # Tambahkan skor re-ranker ke setiap chunk
    for i, chunk in enumerate(chunks):
        chunk["reranker_score"] = float(scores[i])

    # Urutkan berdasarkan skor Cross-Encoder tertinggi
    chunks_sorted = sorted(chunks, key=lambda x: x["reranker_score"], reverse=True)

    logger.info(
        f"Re-ranking selesai: {len(chunks)} kandidat → Top-{top_k} terbaik | "
        f"Skor tertinggi: {chunks_sorted[0]['reranker_score']:.4f} | "
        f"Skor terendah: {chunks_sorted[min(top_k - 1, len(chunks_sorted) - 1)]['reranker_score']:.4f}"
    )

    return chunks_sorted[:top_k]
