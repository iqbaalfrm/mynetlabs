# Changelog

Semua pembaruan terdokumentasi dan terperinci untuk platform **Netlabs — Intelligent Tutoring System** dirangkum di bawah ini.

---

## [1.1.0] - 2026-07-10

### Added
- **Debug Vektor Search Endpoint**: Menambahkan endpoint `GET /debug/search` di backend AI untuk menvisualisasikan skor cosine similarity, kecocokan threshold, dan letak chunk teks modul guna mempermudah transparansi perhitungan saat demo sidang.
- **Citation Metadata di Chat**: JSON response pada `/chat` sekarang mengembalikan data list `sources` (nama file PDF) dan `chunks_used` (jumlah chunk) agar pengguna mengetahui sumber kutipan resmi AI Tutor.
- **Batch Processing Embedding**: Menambahkan fungsi `buat_embedding_batch` pada model Sentence Transformers di `embedding_service.py` untuk mempercepat proses indexing PDF materi pertemuan.

### Fixed
- **Pembersihan File Sampah**: Menghapus total file temporer di server seperti log JVM Java (`hs_err_pid*.log`), compiled byte-code Python (`__pycache__`), cache build Flutter, cache `.dart_tool`, dan log harian server Laravel, membebaskan ruang penyimpanan sebesar **1.32 GB**.
- **Pemberantasan Celah Halusinasi**: Menghapus total fungsi *fallback* pengetahuan umum Gemini API. AI Tutor sekarang secara konsisten dan sopan menolak menjawab jika materi tidak tercantum dalam modul resmi praktikum.
- **Restrukturisasi Folder Flutter**: Memindahkan layout dan file modular yang tersebar di Flutter ke struktur terpusat di `lib/app/bindings`, `lib/app/controllers`, dan `lib/app/views` guna mempermudah pemeliharaan kode.
- **Separation of Concerns (Laravel)**: Mengekstrak seluruh logika kuis dan logika chatbot dari controllers Laravel ke dalam service classes (`ChatService.php`, `KuisService.php`).

### Improved
- **Peningkatan Relevance Threshold**: Menaikkan threshold kemiripan semantik Cosine Similarity dari `0.3` ke `0.5` di `api_routes.py` untuk menyaring dokumen yang tidak relevan secara presisi.
- **Aturan System Prompt**: Memperketat prompt sistem AI Tutor dengan aturan penolakan yang tegas, instruksi sitasi otomatis, dan pencegahan formatting markdown bold `**` agar tampilan di aplikasi mobile rapi dan seragam.

---

## [1.0.0] - 2026-06-15

### Added
- **Aplikasi Mobile Flutter (Siswa)**:
  - Antarmuka chatbot interaktif untuk berdiskusi dengan AI Tutor.
  - Fitur perekaman suara (*voice query*) dan Text-to-Speech (TTS) suara jawaban AI.
  - Modul pengerjaan kuis materi jaringan komputer pilihan ganda.
  - Halaman dashboard perkembangan progress materi dan statistik nilai rata-rata kuis.
- **Admin Panel Laravel (Guru)**:
  - Dashboard monitoring performa belajar siswa.
  - Fitur unggah modul PDF praktikum dengan integrasi REST API inter-service ke Flask AI.
  - Manajemen master data pertemuan, bab materi, dan pembuatan topik secara dinamis.
- **Flask AI Engine (RAG Backend)**:
  - Integrasi Qdrant Vector Database local persistent mode.
  - Parser PDF menggunakan library PyMuPDF.
  - Integrasi model embedding lokal Sentence Transformers `paraphrase-multilingual-MiniLM-L12-v2`.
  - Integrasi API Google Gemini 2.5 Flash untuk penalaran dan pembentukan soal kuis structured JSON.
- **Metrik Validasi & HKI**:
  - Pelaksanaan Black Box Testing pada 14 skenario penting dengan hasil kelulusan **100% Pass**.
  - Hasil evaluasi kenyamanan pengguna menggunakan metrik System Usability Scale (SUS) dengan skor **84.50** (*Grade A, Excellent*).
  - Hak Kekayaan Intelektual (HKI) terdaftar secara resmi dengan Nomor Pencatatan: **EC002026102929**.
