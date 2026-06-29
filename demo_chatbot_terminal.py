# -*- coding: utf-8 -*-
"""
Script Demo Chatbot RAG di Terminal.
Membaca file PDF materi_jaringan_komputer.pdf, melakukan embedding dengan Gemini API,
menyimpannya ke Vector DB Qdrant in-memory, dan berinteraksi secara
interaktif dengan Google Gemini LLM menggunakan RAG.
"""

import os
import sys
import fitz  # PyMuPDF
from dotenv import load_dotenv
from qdrant_client import QdrantClient
from qdrant_client.models import Distance, VectorParams, PointStruct
import google.generativeai as genai

# Load environment variables dari backend-ai/.env
load_dotenv("backend-ai/.env")

# Lokasi file PDF materi
PDF_PATH = "materi_jaringan_komputer.pdf"

# Cek ketersediaan file PDF
if not os.path.exists(PDF_PATH):
    print(f"Error: File '{PDF_PATH}' tidak ditemukan. Silakan jalankan 'python generate_pdf_materi.py' terlebih dahulu.")
    sys.exit(1)

# Cek API Key Gemini
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
if not GEMINI_API_KEY:
    print("Error: GEMINI_API_KEY tidak ditemukan di file backend-ai/.env atau environment variables.")
    print("Silakan atur GEMINI_API_KEY di file backend-ai/.env Anda.")
    sys.exit(1)

# Konfigurasi Google Gemini
genai.configure(api_key=GEMINI_API_KEY)
llm_model = genai.GenerativeModel("gemini-2.5-flash")

print("=" * 60)
print("             DEMO CHATBOT RAG TERMINAL - NETLABS AI            ")
print("=" * 60)

# Fungsi pembantu untuk membuat embedding menggunakan Gemini API
def dapatkan_embedding(teks, task_type="retrieval_document"):
    response = genai.embed_content(
        model="models/gemini-embedding-001",
        content=teks,
        task_type=task_type
    )
    return response["embedding"]

# 1. Mengekstrak Teks dari PDF
print(f"1. Membaca berkas materi: {PDF_PATH} ...")
doc = fitz.open(PDF_PATH)
teks_penuh = ""
for halaman in doc:
    teks_penuh += halaman.get_text()

# 2. Pemotongan Teks (Chunking) Sederhana
print("2. Melakukan chunking teks...")
panjang_chunk = 600
overlap = 100
chunks = []

i = 0
while i < len(teks_penuh):
    chunk = teks_penuh[i : i + panjang_chunk]
    chunks.append(chunk.strip())
    i += panjang_chunk - overlap

print(f"   Berhasil membuat {len(chunks)} dokumen chunk.")

# 3. Inisialisasi Qdrant in-memory
print("3. Menginisialisasi Vector Database Qdrant (In-Memory)...")
client = QdrantClient(":memory:")
collection_name = "demo_jaringan_komputer"

# models/gemini-embedding-001 menghasilkan vektor berdimensi 3072
client.create_collection(
    collection_name=collection_name,
    vectors_config=VectorParams(size=3072, distance=Distance.COSINE),
)

# 4. Memasukkan Vektor dan Metadata ke Qdrant
print("4. Melakukan indexing dokumen ke Vector DB via Gemini Embedding API...")
points = []
for idx, chunk in enumerate(chunks):
    if not chunk:
        continue
    # Generate embedding via API
    vektor = dapatkan_embedding(chunk, task_type="retrieval_document")
    points.append(
        PointStruct(id=idx, vector=vektor, payload={"teks_asli": chunk})
    )

client.upsert(
    collection_name=collection_name,
    points=points
)
print("   Data materi berhasil disimpan di Vector Database!")
print("=" * 60)
print("RAG ENGINE SIAP! Silakan ajukan pertanyaan seputar materi Jaringan & Subnetting.")
print("Ketik 'keluar' atau 'exit' untuk mengakhiri demo.")
print("=" * 60)

# 5. Loop Interaktif Chatbot
while True:
    try:
        pertanyaan_user = input("\nSiswa (Tanya): ").strip()
        if not pertanyaan_user:
            continue
        
        if pertanyaan_user.lower() in ["keluar", "exit", "quit"]:
            print("\nTerima kasih! Demo chatbot diakhiri.")
            break

        # Ubah pertanyaan siswa menjadi vektor
        vektor_query = dapatkan_embedding(pertanyaan_user, task_type="retrieval_query")

        # Cari 2 chunk teratas yang paling relevan
        hasil_pencarian = client.query_points(
            collection_name=collection_name,
            query=vektor_query,
            limit=2
        ).points

        # Gabungkan konteks yang ditemukan
        konteks = ""
        if hasil_pencarian:
            for idx, res in enumerate(hasil_pencarian):
                konteks += f"[Konteks {idx+1}]\n{res.payload['teks_asli']}\n\n"
        else:
            konteks = "Tidak ditemukan dokumen referensi yang relevan."

        # Buat prompt terstruktur
        prompt_akhir = f"""Anda adalah AI Tutor dari NetLabs. Anda bertugas menjawab pertanyaan siswa berdasarkan Konteks materi Jaringan Komputer di bawah ini.

PANDUAN JAWABAN:
1. Jawablah menggunakan Bahasa Indonesia yang formal, terstruktur, dan mudah dipahami oleh siswa SMK/Mahasiswa.
2. Gunakan istilah-istilah teknis jaringan komputer secara tepat (seperti Subnet Mask, IP Address, Network ID, Broadcast ID, Host ID, OSI Layer, dll.).
3. Jika siswa meminta bantuan perhitungan subnetting, tunjukkan langkah-langkah perhitungannya secara berurutan dan jelas.
4. Jika pertanyaan tidak ada relevansinya dengan Konteks yang diberikan, jawablah dengan jujur bahwa Anda tidak tahu atau informasi tersebut tidak tersedia di dalam modul materi.
5. Sebutkan bahwa jawaban Anda didasarkan pada dokumen "Materi Jaringan Komputer dan Subnetting".

Konteks Materi:
{konteks}

Pertanyaan Siswa:
{pertanyaan_user}

Jawaban AI Tutor:"""

        print("AI Tutor sedang menyusun jawaban...")
        
        # Panggil Gemini LLM
        response = llm_model.generate_content(prompt_akhir)
        
        print("\n" + "-" * 50)
        print("AI Tutor (Respons):")
        print(response.text.strip())
        print("-" * 50)
        
    except KeyboardInterrupt:
        print("\n\nDemo dihentikan paksa (KeyboardInterrupt).")
        break
    except Exception as e:
        print(f"\nTerjadi kesalahan: {e}")
