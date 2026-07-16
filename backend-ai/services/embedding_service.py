import os
os.environ["USE_TF"] = "0"
os.environ["USE_TORCH"] = "1"

import logging
from sentence_transformers import SentenceTransformer

logger = logging.getLogger("NetLabsAI.EmbeddingService")

# Inisialisasi model Sentence Transformers secara global
logger.info("Memuat model penyematan Sentence Transformers 'paraphrase-multilingual-MiniLM-L12-v2'...")
_model = SentenceTransformer('paraphrase-multilingual-MiniLM-L12-v2')
VECTOR_SIZE: int = 384
logger.info("Model penyematan berhasil dimuat.")

def buat_embedding(teks: str) -> list[float]:
    """Membuat embedding vektor dari teks menggunakan Sentence Transformers.
    
    Args:
        teks (str): Teks yang akan dibuat embedding-nya.
        
    Returns:
        list[float]: Vektor representasi teks.
    """
    if not teks:
        return [0.0] * VECTOR_SIZE
    return _model.encode(teks).tolist()

def buat_embedding_batch(chunks: list[str]) -> list[list[float]]:
    """Membuat embedding vektor dari sekumpulan teks secara batch.
    
    Args:
        chunks (list[str]): List berisi potongan teks.
        
    Returns:
        list[list[float]]: List berisi vektor representasi masing-masing teks.
    """
    if not chunks:
        return []
    return _model.encode(chunks).tolist()

