import os
import sys

# Matikan backend TensorFlow untuk menghindari crash impor Keras/TensorFlow
os.environ["USE_TF"] = "0"
os.environ["USE_TORCH"] = "1"

# Tambahkan direktori saat ini ke path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from dotenv import load_dotenv
load_dotenv()

from services import rag_service

# Path relatif dari folder backend-ai ke folder storage_modul_pdf
pdf_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "storage_modul_pdf"))
if not os.path.exists(pdf_dir):
    print(f"Error: direktori '{pdf_dir}' tidak ditemukan.")
    sys.exit(1)

print(f"Mulai proses indexing seluruh PDF materi secara offline ke Qdrant dari {pdf_dir}...")
for filename in os.listdir(pdf_dir):
    if filename.endswith(".pdf") and filename.startswith("Modul-"):
        parts = filename.split("-")
        try:
            pertemuan_id = int(parts[1])
            file_path = os.path.abspath(os.path.join(pdf_dir, filename))
            print(f"\n==================================================")
            print(f"Mengindeks: {filename} | Pertemuan ID: {pertemuan_id}")
            print(f"Path: {file_path}")
            
            result = rag_service.index_pdf_chunks(pertemuan_id, file_path)
            print(f"SUKSES: {filename} berhasil di-index!")
        except (IndexError, ValueError) as e:
            print(f"Melewati {filename}: Gagal parsing ID pertemuan.")
        except Exception as e:
            print(f"GAGAL mengindeks {filename}: {e}")
print("\nSemua proses indexing selesai!")
