import logging
from services.embedding_service import buat_embedding
from services.rag_service import search_relevant_chunks
from services.bm25_service import search_bm25
from services.reranker_service import rerank_chunks

logger = logging.getLogger("NetLabsAI.HybridSearch")

# ─────────────────────────────────────────────────────────────────────────────
# Konstanta Reciprocal Rank Fusion (RRF)
# k = 60 adalah konstanta smoothing standar dari paper asli RRF (Cormack et al., 2009)
# ─────────────────────────────────────────────────────────────────────────────
RRF_K = 60

# Jumlah kandidat dari masing-masing metode retrieval sebelum fusion
DENSE_RETRIEVAL_LIMIT = 10
SPARSE_RETRIEVAL_LIMIT = 10

# Jumlah kandidat setelah RRF fusion yang akan dikirim ke Cross-Encoder Re-ranker
RRF_CANDIDATES_LIMIT = 15

# Jumlah chunk final setelah re-ranking yang dikirim ke Gemini LLM
FINAL_TOP_K = 4


def _reciprocal_rank_fusion(
    dense_results: list[dict],
    sparse_results: list[dict],
    k: int = RRF_K,
) -> list[dict]:
    """Menggabungkan hasil Dense Retrieval dan Sparse Retrieval menggunakan Reciprocal Rank Fusion (RRF).

    Algoritma RRF menghitung skor gabungan untuk setiap dokumen berdasarkan
    peringkatnya di masing-masing metode retrieval:

        RRF_Score(d) = Σ_{m ∈ M} 1 / (k + r_m(d))

    dimana:
        - M = himpunan metode retrieval (Dense, Sparse)
        - k = konstanta smoothing (default: 60, dari paper Cormack et al. 2009)
        - r_m(d) = peringkat dokumen d dari metode m (1-indexed)

    Keunggulan RRF:
        - Rank-based: tidak bergantung pada skala skor yang berbeda antara Dense dan Sparse.
        - Non-parametric: tidak memerlukan pelatihan/kalibrasi bobot.
        - Terbukti efektif di berbagai benchmark IR (Information Retrieval).

    Args:
        dense_results (list[dict]): Hasil pencarian Dense (Qdrant Cosine Similarity).
        sparse_results (list[dict]): Hasil pencarian Sparse (BM25).
        k (int): Konstanta smoothing RRF.

    Returns:
        list[dict]: Daftar dokumen yang sudah digabungkan dan diurutkan berdasarkan skor RRF.
    """
    # Dictionary untuk menyimpan skor RRF dan metadata per dokumen
    # Key: (source_file, chunk_index) sebagai unique identifier
    rrf_scores: dict[tuple, dict] = {}

    # Hitung skor RRF dari Dense Retrieval (Qdrant Vector Search)
    for rank, doc in enumerate(dense_results, start=1):
        doc_key = (doc["source_file"], doc["chunk_index"])
        if doc_key not in rrf_scores:
            rrf_scores[doc_key] = {
                "teks_asli": doc["teks_asli"],
                "source_file": doc["source_file"],
                "chunk_index": doc["chunk_index"],
                "halaman": doc.get("halaman", "?"),
                "rrf_score": 0.0,
                "dense_score": 0.0,
                "dense_rank": None,
                "bm25_score": 0.0,
                "bm25_rank": None,
            }
        rrf_scores[doc_key]["rrf_score"] += 1.0 / (k + rank)
        rrf_scores[doc_key]["dense_score"] = doc.get("score", 0.0)
        rrf_scores[doc_key]["dense_rank"] = rank

    # Hitung skor RRF dari Sparse Retrieval (BM25)
    for rank, doc in enumerate(sparse_results, start=1):
        doc_key = (doc["source_file"], doc["chunk_index"])
        if doc_key not in rrf_scores:
            rrf_scores[doc_key] = {
                "teks_asli": doc["teks_asli"],
                "source_file": doc["source_file"],
                "chunk_index": doc["chunk_index"],
                "halaman": doc.get("halaman", "?"),
                "rrf_score": 0.0,
                "dense_score": 0.0,
                "dense_rank": None,
                "bm25_score": 0.0,
                "bm25_rank": None,
            }
        rrf_scores[doc_key]["rrf_score"] += 1.0 / (k + rank)
        rrf_scores[doc_key]["bm25_score"] = doc.get("score", 0.0)
        rrf_scores[doc_key]["bm25_rank"] = rank

    # Urutkan berdasarkan skor RRF tertinggi
    fused = sorted(rrf_scores.values(), key=lambda x: x["rrf_score"], reverse=True)

    logger.info(
        f"RRF Fusion: {len(dense_results)} dense + {len(sparse_results)} sparse "
        f"→ {len(fused)} dokumen unik (setelah deduplikasi)"
    )
    return fused


def hybrid_search(
    pertemuan_id: int | None,
    query: str,
    top_k: int = FINAL_TOP_K,
) -> list[dict]:
    """Pipeline utama Advanced Hybrid RAG Search.

    Alur lengkap:
        1. Dense Retrieval  → Qdrant Cosine Similarity (Bi-Encoder) Top-10
        2. Sparse Retrieval → BM25 Okapi (Lexical/Keyword) Top-10
        3. Reciprocal Rank Fusion (RRF) → Gabungkan & deduplikasi → Top-15
        4. Cross-Encoder Re-ranking → ms-marco-MiniLM-L-6-v2 → Top-4

    Args:
        pertemuan_id (int | None): ID pertemuan untuk filter dokumen.
        query (str): Pertanyaan siswa.
        top_k (int): Jumlah chunk final yang dikembalikan.

    Returns:
        list[dict]: Daftar chunks terbaik dengan metadata skor lengkap:
            - dense_score: Skor Cosine Similarity (Qdrant)
            - dense_rank: Peringkat dari Dense Retrieval
            - bm25_score: Skor BM25 (Okapi)
            - bm25_rank: Peringkat dari Sparse Retrieval
            - rrf_score: Skor Reciprocal Rank Fusion
            - reranker_score: Skor Cross-Encoder Re-ranker (presisi tertinggi)
    """
    logger.info(f"═══ HYBRID SEARCH dimulai | pertemuan_id={pertemuan_id} | query='{query[:80]}' ═══")

    # ── Tahap 1: Dense Retrieval (Qdrant Vector Search / Bi-Encoder) ──
    logger.info(f"[1/4] Dense Retrieval (Qdrant Cosine Similarity) → Top-{DENSE_RETRIEVAL_LIMIT}")
    query_vector = buat_embedding(query)
    dense_results = search_relevant_chunks(pertemuan_id, query_vector, limit=DENSE_RETRIEVAL_LIMIT)
    logger.info(f"      → {len(dense_results)} hasil ditemukan")

    # ── Tahap 2: Sparse Retrieval (BM25 Okapi) ──
    logger.info(f"[2/4] Sparse Retrieval (BM25 Okapi) → Top-{SPARSE_RETRIEVAL_LIMIT}")
    sparse_results = search_bm25(pertemuan_id, query, limit=SPARSE_RETRIEVAL_LIMIT)
    logger.info(f"      → {len(sparse_results)} hasil ditemukan")

    # ── Tahap 3: Reciprocal Rank Fusion (RRF) ──
    logger.info(f"[3/4] Reciprocal Rank Fusion (k={RRF_K}) → Top-{RRF_CANDIDATES_LIMIT}")
    rrf_results = _reciprocal_rank_fusion(dense_results, sparse_results)
    rrf_candidates = rrf_results[:RRF_CANDIDATES_LIMIT]
    logger.info(f"      → {len(rrf_candidates)} kandidat untuk re-ranking")

    # ── Tahap 4: Cross-Encoder Re-ranking ──
    logger.info(f"[4/4] Cross-Encoder Re-ranking → Top-{top_k}")
    final_results = rerank_chunks(query, rrf_candidates, top_k=top_k)

    # Log ringkasan hasil akhir
    for i, res in enumerate(final_results, start=1):
        logger.info(
            f"  #{i} | RerankerScore={res.get('reranker_score', 0):.4f} | "
            f"RRF={res.get('rrf_score', 0):.4f} | "
            f"Dense={res.get('dense_score', 0):.4f}(R{res.get('dense_rank', '-')}) | "
            f"BM25={res.get('bm25_score', 0):.4f}(R{res.get('bm25_rank', '-')}) | "
            f"Source={res.get('source_file', '?')}"
        )

    logger.info(f"═══ HYBRID SEARCH selesai | {len(final_results)} chunk final ═══")
    return final_results
