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
from utils.text_cleaner import ekstrak_teks_pdf_dengan_halaman, potong_teks_menjadi_chunks_dengan_halaman

logger = logging.getLogger("NetLabsAI.RagService")

# Inisialisasi Qdrant client
logger.info(f"Menginisialisasi Qdrant di: {os.path.abspath(Config.QDRANT_PERSIST_DIR)}")
qdrant_client = QdrantClient(path=Config.QDRANT_PERSIST_DIR)

# Buat collection jika belum ada
if not qdrant_client.collection_exists(Config.QDRANT_COLLECTION_NAME):
    qdrant_client.create_collection(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        vectors_config=VectorParams(size=VECTOR_SIZE, distance=Distance.COSINE),
    )
    logger.info(f"Qdrant collection '{Config.QDRANT_COLLECTION_NAME}' berhasil dibuat.")
else:
    logger.info(f"Qdrant collection '{Config.QDRANT_COLLECTION_NAME}' sudah ada.")

def get_total_documents() -> int:
    """Mengambil total dokumen/points dalam collection Qdrant.
    
    Returns:
        int: Jumlah total dokumen.
    """
    return qdrant_client.count(collection_name=Config.QDRANT_COLLECTION_NAME).count

logger.info(f"Total dokumen di Qdrant saat ini: {get_total_documents()}")

def index_pdf_chunks(pertemuan_id: int, file_path: str) -> dict:
    """Mengekstrak PDF, membuat embedding, dan mengindeks chunks ke Qdrant.
    
    Args:
        pertemuan_id (int): ID pertemuan materi.
        file_path (str): Path absolut file PDF materi.
        
    Returns:
        dict: Hasil operasi indeks.
    """
    halaman_list = ekstrak_teks_pdf_dengan_halaman(file_path)
    if not halaman_list:
        raise ValueError("File PDF tidak mengandung teks yang cukup untuk diproses.")

    chunks = potong_teks_menjadi_chunks_dengan_halaman(halaman_list)
    if not chunks:
        raise ValueError("Gagal memotong teks PDF menjadi chunks.")

    nama_file = os.path.basename(file_path)
    logger.info(f"Menghapus dokumen lama jika ada (pertemuan_id={pertemuan_id}, file={nama_file})...")

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

    logger.info(f"Membuat embedding secara batch untuk {len(chunks)} chunk...")
    teks_list = [c["text"] for c in chunks]
    vektor_list = buat_embedding_batch(teks_list)

    points = []
    for i, (chunk_item, vektor) in enumerate(zip(chunks, vektor_list)):
        doc_id = str(uuid.uuid4())
        points.append(
            PointStruct(
                id=doc_id,
                vector=vektor,
                payload={
                    "teks_asli": chunk_item["text"],
                    "pertemuan_id": pertemuan_id,
                    "source_file": nama_file,
                    "halaman": chunk_item["halaman"],
                    "chunk_index": i,
                    "total_chunks": len(chunks),
                    "indexed_at": datetime.now().isoformat(),
                },
            )
        )

    # Batch upsert ke Qdrant
    qdrant_client.upsert(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        points=points,
    )
    
    total_in_db = get_total_documents()
    logger.info(f"INDEX-PDF selesai | {len(chunks)} chunk disimpan untuk pertemuan_id={pertemuan_id}")
    
    return {
        "pertemuan_id": pertemuan_id,
        "file_name": nama_file,
        "total_chunks": len(chunks),
        "total_documents_in_db": total_in_db,
    }

def search_relevant_chunks(pertemuan_id: int | None, query_vector: list[float], limit: int = 4) -> list[dict]:
    """Mencari chunk dokumen yang paling mirip di Qdrant.
    
    Args:
        pertemuan_id (int | None): ID pertemuan untuk filter (opsional).
        query_vector (list[float]): Vektor embedding kueri.
        limit (int): Jumlah maksimal point hasil pencarian.
        
    Returns:
        list[dict]: Point pencarian Qdrant yang berisi score dan payload.
    """
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
            "halaman": point.payload.get("halaman", "?"),
            "chunk_index": point.payload.get("chunk_index", "?"),
        }
        for point in hasil
    ]

def get_pertemuan_chunks(pertemuan_id: int) -> list:
    """Mengambil seluruh chunks yang terkait dengan pertemuan_id tertentu (untuk generate quiz).
    
    Args:
        pertemuan_id (int): ID pertemuan.
        
    Returns:
        list: Daftar point chunks.
    """
    hasil_scroll, _ = qdrant_client.scroll(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        scroll_filter=Filter(
            must=[
                FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
            ]
        ),
        limit=1000,
        with_payload=True,
    )
    return hasil_scroll

def delete_pertemuan_chunks(pertemuan_id: int) -> int:
    """Menghapus seluruh point chunks yang terkait dengan pertemuan_id tertentu.
    
    Args:
        pertemuan_id (int): ID pertemuan.
        
    Returns:
        int: Jumlah point yang dihapus.
    """
    jumlah = qdrant_client.count(
        collection_name=Config.QDRANT_COLLECTION_NAME,
        count_filter=Filter(
            must=[
                FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
            ]
        ),
    ).count

    if jumlah > 0:
        qdrant_client.delete(
            collection_name=Config.QDRANT_COLLECTION_NAME,
            points_selector=FilterSelector(
                filter=Filter(
                    must=[
                        FieldCondition(key="pertemuan_id", match=MatchValue(value=pertemuan_id)),
                    ]
                )
            ),
        )
    return jumlah

def get_statistics() -> dict:
    """Mendapatkan data statistik isi database vektor Qdrant.
    
    Returns:
        dict: Statistik database.
    """
    total = get_total_documents()
    pertemuan_map = {}
    
    if total > 0:
        semua_data, _ = qdrant_client.scroll(
            collection_name=Config.QDRANT_COLLECTION_NAME,
            limit=min(total, 1000),
            with_payload=True,
        )
        for point in semua_data:
            pid = point.payload.get("pertemuan_id", "unknown")
            pertemuan_map[pid] = pertemuan_map.get(pid, 0) + 1
            
    return {
        "total_documents": total,
        "per_pertemuan": pertemuan_map
    }
