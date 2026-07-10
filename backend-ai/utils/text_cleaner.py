import os
import re
import logging
import fitz  # PyMuPDF
from langchain_text_splitters import RecursiveCharacterTextSplitter

logger = logging.getLogger("NetLabsAI.TextCleaner")

def bersihkan_teks(teks: str) -> str:
    """Membersihkan teks hasil ekstraksi PDF dari karakter-karakter yang tidak perlu.
    
    Args:
        teks (str): Teks mentah dari PDF.
        
    Returns:
        str: Teks bersih.
    """
    if not teks:
        return ""
    # Hapus karakter kontrol kecuali newline dan tab
    teks = re.sub(r'[\x00-\x08\x0b\x0c\x0e-\x1f\x7f]', '', teks)
    # Normalisasi whitespace berlebih
    teks = re.sub(r'[ \t]+', ' ', teks)
    # Normalisasi newline berlebih (lebih dari 2 baris kosong -> 2)
    teks = re.sub(r'\n{3,}', '\n\n', teks)
    # Hapus spasi di awal/akhir setiap baris
    teks = '\n'.join(line.strip() for line in teks.split('\n'))
    return teks.strip()

def ekstrak_teks_pdf(file_path: str) -> str:
    """Mengekstrak seluruh teks dari file PDF menggunakan PyMuPDF (fitz).
    
    Args:
        file_path (str): Path ke file PDF.
        
    Returns:
        str: Hasil ekstraksi teks.
        
    Raises:
        FileNotFoundError: Jika file tidak ditemukan.
    """
    logger.info(f"Membaca PDF: {file_path}")

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
    logger.info(f"Total teks diekstrak: {len(hasil)} karakter dari {len(seluruh_teks)} halaman")
    return hasil

def potong_teks_menjadi_chunks(teks: str, chunk_size: int = 1000, chunk_overlap: int = 200) -> list[str]:
    """Memotong teks panjang menjadi potongan-potongan kecil menggunakan RecursiveCharacterTextSplitter.
    
    Args:
        teks (str): Teks panjang untuk dipotong.
        chunk_size (int): Ukuran target per chunk.
        chunk_overlap (int): Overlap antar chunk.
        
    Returns:
        list[str]: Daftar potongan teks (chunks).
    """
    splitter = RecursiveCharacterTextSplitter(
        chunk_size=chunk_size,
        chunk_overlap=chunk_overlap,
        length_function=len,
        separators=["\n\n", "\n", ". ", " ", ""],
    )
    chunks = splitter.split_text(teks)
    logger.info(f"Teks dipotong menjadi {len(chunks)} chunk (size={chunk_size}, overlap={chunk_overlap})")
    return chunks
