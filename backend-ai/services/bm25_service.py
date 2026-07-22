import re
import logging
from rank_bm25 import BM25Okapi
from services import rag_service

logger = logging.getLogger("NetLabsAI.BM25Service")

# ─────────────────────────────────────────────────────────────────────────────
# Cache indeks BM25 per pertemuan_id agar tidak rebuild setiap request.
# Format: { pertemuan_id (int|None) : (BM25Okapi instance, list[dict]) }
# ─────────────────────────────────────────────────────────────────────────────
_bm25_cache: dict[int | None, tuple[BM25Okapi, list[dict]]] = {}


def _tokenize(teks: str) -> list[str]:
    """Tokenisasi sederhana: lowercase, split berdasarkan kata alfanumerik.

    Args:
        teks (str): Teks yang akan di-tokenisasi.

    Returns:
        list[str]: List token hasil tokenisasi.
    """
    return re.findall(r'\w+', teks.lower())


def _build_bm25_index(pertemuan_id: int | None) -> tuple[BM25Okapi, list[dict]]:
    """Membangun indeks BM25 dari seluruh chunk di Qdrant untuk pertemuan_id tertentu.

    Mengambil seluruh dokumen yang terkait dengan pertemuan_id dari Qdrant,
    lalu membangun indeks BM25Okapi untuk pencarian leksikal.

    Args:
        pertemuan_id (int | None): ID pertemuan untuk filter. None = seluruh dokumen.

    Returns:
        tuple[BM25Okapi, list[dict]]: Tuple berisi instance BM25 dan list metadata dokumen.
    """
    from qdrant_client.models import Filter, FieldCondition, MatchValue
    from config import Config

    scroll_filter = None
    if pertemuan_id is not None:
        scroll_filter = Filter(
            must=[
                FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
            ]
        )

    semua_data, _ = rag_service.qdrant_client.scroll(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        scroll_filter=scroll_filter,
        limit=10000,
        with_payload=True,
    )

    if not semua_data:
        logger.warning(f"BM25: Tidak ada dokumen untuk pertemuan_id={pertemuan_id}")
        return BM25Okapi([[""]]), []

    # Bangun corpus dan metadata
    corpus_tokens = []
    docs_metadata = []
    for point in semua_data:
        teks = point.payload.get("teks_asli", "")
        corpus_tokens.append(_tokenize(teks))
        docs_metadata.append({
            "teks_asli": teks,
            "source_file": point.payload.get("source_file", "?"),
            "halaman": point.payload.get("halaman", "?"),
            "chunk_index": point.payload.get("chunk_index", 0),
            "pertemuan_id": point.payload.get("pertemuan_id", 0),
        })

    bm25 = BM25Okapi(corpus_tokens)
    logger.info(f"BM25: Indeks dibangun untuk pertemuan_id={pertemuan_id} | {len(docs_metadata)} dokumen")
    return bm25, docs_metadata


def search_bm25(pertemuan_id: int | None, query: str, limit: int = 10) -> list[dict]:
    """Mencari dokumen menggunakan algoritma BM25 (Okapi BM25) untuk pencarian leksikal/kata kunci.

    Algoritma BM25 menghitung skor relevansi berdasarkan Term Frequency (TF),
    Inverse Document Frequency (IDF), dan normalisasi panjang dokumen.

    Formula Okapi BM25:
        BM25(q, d) = Σ IDF(t) × [tf(t,d) × (k₁+1)] / [tf(t,d) + k₁ × (1 - b + b × |d|/avgdl)]
    dimana k₁ = 1.5 (default), b = 0.75 (default).

    Args:
        pertemuan_id (int | None): ID pertemuan untuk filter. None = seluruh dokumen.
        query (str): Kueri pencarian dari siswa.
        limit (int): Jumlah maksimal hasil pencarian.

    Returns:
        list[dict]: Daftar dokumen relevan beserta skor BM25.
    """
    # Ambil atau bangun indeks BM25 dari cache
    if pertemuan_id not in _bm25_cache:
        _bm25_cache[pertemuan_id] = _build_bm25_index(pertemuan_id)

    bm25, docs_metadata = _bm25_cache[pertemuan_id]

    if not docs_metadata:
        return []

    query_tokens = _tokenize(query)
    if not query_tokens:
        return []

    # Hitung skor BM25 untuk setiap dokumen
    scores = bm25.get_scores(query_tokens)

    # Buat pasangan (index, score) dan urutkan berdasarkan skor tertinggi
    scored_docs = [(i, float(scores[i])) for i in range(len(scores)) if scores[i] > 0]
    scored_docs.sort(key=lambda x: x[1], reverse=True)

    # Ambil top-N hasil
    results = []
    for idx, score in scored_docs[:limit]:
        doc = docs_metadata[idx]
        results.append({
            "score": score,
            "teks_asli": doc["teks_asli"],
            "source_file": doc["source_file"],
            "halaman": doc.get("halaman", "?"),
            "chunk_index": doc["chunk_index"],
        })

    logger.info(f"BM25 Search: query='{query[:50]}...' | {len(results)} hasil ditemukan")
    return results


def invalidate_cache(pertemuan_id: int | None = None) -> None:
    """Menghapus cache indeks BM25 saat dokumen di-update (re-index atau delete).

    Args:
        pertemuan_id (int | None): ID pertemuan yang cache-nya akan dihapus.
            Jika None, seluruh cache akan dihapus.
    """
    if pertemuan_id is None:
        _bm25_cache.clear()
        logger.info("BM25: Seluruh cache indeks dihapus.")
    else:
        _bm25_cache.pop(pertemuan_id, None)
        _bm25_cache.pop(None, None)  # Hapus juga cache global
        logger.info(f"BM25: Cache indeks untuk pertemuan_id={pertemuan_id} dihapus.")
