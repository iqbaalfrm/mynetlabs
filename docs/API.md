# API Documentation — Netlabs

Dokumen ini menjelaskan seluruh endpoint REST API yang digunakan dalam ekosistem Netlabs, baik yang disediakan oleh **Laravel Web Server** (untuk komunikasi Mobile App ke Database) maupun **Flask AI Backend** (untuk operasi NLP, Vector DB, transkripsi suara, dan pembuatan soal otomatis).

---

## Base URL

*   **Laravel API Server**: `http://127.0.0.1:8000/api` (Lokal) atau `https://netlabs.web.id/api` (Production)
*   **Flask AI Engine**: `http://127.0.0.1:5050` (Lokal/Inter-service) atau `http://localhost/ai-api` (Proxy VPS Nginx)

---

## Autentikasi

Semua endpoint di bawah segmen **Laravel Protected API** membutuhkan Bearer Token menggunakan Laravel Sanctum.
Header wajib disertakan pada setiap request:
```http
Authorization: Bearer {your_sanctum_token}
Content-Type: application/json
Accept: application/json
```

---

## 1. Laravel API Endpoints (Untuk Mobile App)

### A. Public Endpoints (Tanpa Token)

#### 1. Registrasi Akun Siswa
*   **Method & URL**: `POST /register`
*   **Request Body (JSON)**:
    ```json
    {
      "username": "22019283",
      "password": "password123",
      "nama": "Iqbal Firmansyah",
      "role": "siswa",
      "kelas": "XI TKJ 1"
    }
    ```
*   **Response Body (210 Created)**:
    ```json
    {
      "message": "Registrasi akun berhasil!",
      "user": {
        "username": "22019283",
        "nama": "Iqbal Firmansyah",
        "role": "siswa",
        "kelas": "XI TKJ 1",
        "foto_profil_url": null,
        "password_is_default": true,
        "must_change_password": false,
        "password_grace_days_remaining": 7
      },
      "token": "1|sYh821Ja0Sjkas..."
    }
    ```

#### 2. Login Siswa / Guru
*   **Method & URL**: `POST /login`
*   **Catatan**: Dilindungi Rate Limiter (Maksimal 5 kali percobaan dalam 15 menit).
*   **Request Body (JSON)**:
    ```json
    {
      "username": "22019283",
      "password": "password123"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Login Berhasil!",
      "user": {
        "username": "22019283",
        "nama": "Iqbal Firmansyah",
        "role": "siswa",
        "kelas": "XI TKJ 1",
        "foto_profil_url": "http://127.0.0.1:8000/storage/avatars/iqbal.jpg",
        "password_is_default": false,
        "must_change_password": false,
        "password_grace_days_remaining": 0
      },
      "token": "2|uHsjka817Sjadk..."
    }
    ```
*   **Error Response (401 Unauthorized)**:
    ```json
    {
      "message": "Nomor Induk (NIS) atau Kata Sandi salah."
    }
    ```

---

### B. Protected Endpoints (Membutuhkan Bearer Token)

#### 3. Logout Siswa
*   **Method & URL**: `POST /logout`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Logout berhasil."
    }
    ```

#### 4. Ganti Password
*   **Method & URL**: `POST /change-password`
*   **Request Body (JSON)**:
    ```json
    {
      "password_lama": "password123",
      "password_baru": "newpassword123",
      "password_baru_confirmation": "newpassword123"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Password berhasil diperbarui."
    }
    ```

#### 5. Ambil Profil User
*   **Method & URL**: `GET /user-profile`
*   **Response Body (200 OK)**:
    ```json
    {
      "id": 5,
      "username": "22019283",
      "nama": "Iqbal Firmansyah",
      "role": "siswa",
      "kelas": "XI TKJ 1",
      "kelas_id": 1,
      "foto_profil": "avatars/iqbal.jpg",
      "status": "aktif",
      "foto_profil_url": "http://127.0.0.1:8000/storage/avatars/iqbal.jpg"
    }
    ```

#### 6. Ambil Daftar Pertemuan (Modul)
*   **Method & URL**: `GET /pertemuan`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Daftar pertemuan berhasil dimuat.",
      "data": [
        {
          "id": 1,
          "nomor": 1,
          "urutan": 1,
          "judul": "Modul 1: Pengenalan Jaringan Komputer",
          "deskripsi": "Konsep Dasar Jaringan, Tipe Jaringan, dan Topologi.",
          "isi_materi": "Materi lengkap bab 1...",
          "semester": "1",
          "warna_tema": "#3B82F6",
          "progress": 1.0,
          "is_completed": true,
          "status_indexing": "success",
          "pdf_url": "http://127.0.0.1:8000/storage/modul_pdf/Modul-01-Pengenalan-Jaringan.pdf"
        }
      ]
    }
    ```

#### 7. Detail Pertemuan & Progres
*   **Method & URL**: `GET /pertemuan/{id}`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Detail pertemuan berhasil dimuat.",
      "data": {
        "id": 1,
        "nomor": 1,
        "urutan": 1,
        "judul": "Modul 1: Pengenalan Jaringan Komputer",
        "deskripsi": "Konsep Dasar Jaringan, Tipe Jaringan, dan Topologi.",
        "isi_materi": "Materi lengkap bab 1...",
        "semester": "1",
        "warna_tema": "#3B82F6",
        "progress": 0.0,
        "is_completed": false,
        "status_indexing": "success",
        "pdf_url": "http://127.0.0.1:8000/storage/modul_pdf/Modul-01-Pengenalan-Jaringan.pdf"
      }
    }
    ```

#### 8. Tandai Pertemuan Selesai
*   **Method & URL**: `POST /pertemuan/{pertemuanId}/selesai`
*   **Deskripsi**: Mencatat kemajuan belajar siswa pada modul pertemuan tertentu (mengubah progres dari 0.0 menjadi 1.0).
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Pertemuan berhasil ditandai selesai.",
      "progress": 1.0
    }
    ```

#### 9. Ambil Soal Kuis Pertemuan
*   **Method & URL**: `GET /pertemuan/{id}/kuis`
*   **Deskripsi**: Mengambil daftar pertanyaan kuis untuk pertemuan tertentu. Kunci jawaban disembunyikan untuk menjaga kejujuran ujian.
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Soal kuis berhasil dimuat.",
      "data": {
        "pertemuan": {
          "id": 1,
          "nomor": 1,
          "judul": "Modul 1: Pengenalan Jaringan Komputer"
        },
        "total_soal": 1,
        "soal": [
          {
            "id": 12,
            "pertanyaan": "Manakah topologi jaringan yang seluruh node-nya terhubung ke satu kabel pusat?",
            "pilihan_a": "Topologi Bus",
            "pilihan_b": "Topologi Star",
            "pilihan_c": "Topologi Ring",
            "pilihan_d": "Topologi Mesh"
          }
        ]
      }
    }
    ```

#### 10. Kirim Hasil Jawaban Kuis (Submit)
*   **Method & URL**: `POST /kuis/submit`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "jawaban": [
        {
          "soal_id": 12,
          "jawaban": "A"
        }
      ]
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Kuis berhasil disubmit!",
      "data": {
        "hasil_id": 3,
        "nilai": 100,
        "jumlah_benar": 1,
        "total_soal": 1,
        "rekomendasi_ai": "Luar biasa! Pertahankan pemahaman Anda mengenai topologi jaringan.",
        "pembahasan": [
          {
            "soal_id": 12,
            "pertanyaan": "Manakah topologi jaringan yang seluruh node-nya terhubung ke satu kabel pusat?",
            "pilihan_a": "Topologi Bus",
            "pilihan_b": "Topologi Star",
            "pilihan_c": "Topologi Ring",
            "pilihan_d": "Topologi Mesh",
            "kunci_jawaban": "A",
            "jawaban_siswa": "A",
            "is_benar": true,
            "penjelasan": "Topologi Bus menghubungkan semua komputer menggunakan satu kabel backbone dengan terminator di kedua ujungnya."
          }
        ]
      }
    }
    ```

#### 11. Ambil Riwayat Hasil Kuis
*   **Method & URL**: `GET /kuis/riwayat`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Riwayat kuis berhasil dimuat.",
      "data": [
        {
          "id": 3,
          "pertemuan": "Modul 1: Pengenalan Jaringan Komputer",
          "nomor_pertemuan": 1,
          "nilai": 100.0,
          "jumlah_benar": 1,
          "total_soal": 1,
          "rekomendasi_ai": "Luar biasa! Pertahankan pemahaman Anda mengenai topologi jaringan.",
          "tanggal": "2026-07-17 10:15"
        }
      ]
    }
    ```

#### 12. Kirim Pesan Chat AI Tutor (RAG Teks)
*   **Method & URL**: `POST /chat`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "pesan": "Bagaimana cara kerja topologi ring?"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Pesan berhasil diproses.",
      "data": {
        "sender": "ai",
        "pesan": "Cara kerja topologi ring adalah data dikirim secara melingkar melewati tiap node...",
        "sumber": "Netlabs AI Tutor",
        "waktu": "2026-07-17 10:20",
        "sources": ["Modul-01-Pengenalan-Jaringan.pdf"],
        "chunks_used": 1
      }
    }
    ```

#### 13. Kirim Pesan Chat Audio (Voice Query & Speech-to-Text)
*   **Method & URL**: `POST /chat/audio`
*   **Request Body (Multipart Form-Data)**:
    *   `audio` (File binary): File rekaman suara (`.wav` / `.m4a` / `.mp3`).
    *   `pertemuan_id` (Integer): `1`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Pesan berhasil diproses.",
      "data": {
        "sender": "ai",
        "pesan": "Topologi mesh memiliki koneksi point-to-point langsung antar komputer...",
        "sumber": "Netlabs AI Tutor",
        "waktu": "2026-07-17 10:22",
        "sources": ["Modul-01-Pengenalan-Jaringan.pdf"],
        "chunks_used": 2
      }
    }
    ```

#### 14. Ambil Riwayat Chat AI
*   **Method & URL**: `GET /chat/riwayat`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Riwayat chat berhasil dimuat.",
      "data": [
        {
          "id": 14,
          "pertemuan_id": 1,
          "sender": "siswa",
          "pesan": "Bagaimana cara kerja topologi ring?",
          "sumber_referensi": null,
          "waktu": "2026-07-17 10:20"
        },
        {
          "id": 15,
          "pertemuan_id": 1,
          "sender": "ai",
          "pesan": "Cara kerja topologi ring adalah data dikirim secara melingkar melewati tiap node...",
          "sumber_referensi": "Netlabs AI Tutor",
          "waktu": "2026-07-17 10:20"
        }
      ]
    }
    ```

#### 15. Ambil Statistik Belajar Siswa
*   **Method & URL**: `GET /siswa/statistik`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Statistik siswa berhasil dimuat.",
      "data": {
        "profil": {
          "username": "22019283",
          "nama": "Iqbal Firmansyah",
          "role": "siswa",
          "kelas": "XI TKJ 1",
          "foto_profil_url": "http://127.0.0.1:8000/storage/avatars/iqbal.jpg",
          "password_is_default": false,
          "must_change_password": false,
          "password_grace_days_remaining": 0
        },
        "statistik": {
          "total_pertemuan_selesai": 1,
          "total_pertemuan": 12,
          "total_topik_selesai": 3,
          "total_topik": 36,
          "rata_rata_nilai": 100.0,
          "total_chat_ai": 52
        }
      }
    }
    ```

#### 16. Ambil Modul yang Sedang Berjalan (Aktif)
*   **Method & URL**: `GET /siswa/pertemuan-aktif`
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Pertemuan aktif berhasil dimuat.",
      "data": [
        {
          "id": 2,
          "nomor": 2,
          "judul": "Modul 2: Model Referensi OSI & TCP/IP",
          "topik": "3 Topik",
          "progress": 0.33
        }
      ]
    }
    ```

#### 17. Unggah Foto Profil Siswa
*   **Method & URL**: `POST /siswa/foto-profil`
*   **Request Body (Multipart Form-Data)**:
    *   `foto` (File Image): Berkas gambar (`jpeg`/`png`/`jpg`, maks 2MB).
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Foto profil berhasil diperbarui.",
      "foto_profil_url": "http://127.0.0.1:8000/storage/avatars/randomname.jpg"
    }
    ```

---

## 2. Flask AI Endpoints (Inter-Service / Internal)

Endpoint Flask ini dipanggil secara internal dari Laravel Service ke Python AI Service.

### 1. Indexing Dokumen PDF
*   **Method & URL**: `POST /index-pdf`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "file_path": "/var/www/mynetlabs/backend-web/storage/app/public/modul_pdf/Modul-01-Pengenalan-Jaringan.pdf"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Modul berhasil di-index ke Vektor DB.",
      "data": {
        "file_name": "Modul-01-Pengenalan-Jaringan.pdf",
        "pertemuan_id": 1,
        "total_chunks": 18,
        "total_documents_in_db": 18
      }
    }
    ```

### 2. Chat RAG AI Tutor (Utama)
*   **Method & URL**: `POST /chat`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "message": "Apa itu ARPANET?"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "answer": "ARPANET adalah jaringan komputer pertama yang dikembangkan oleh Departemen Pertahanan AS...",
      "sources": ["Modul-01-Pengenalan-Jaringan.pdf"],
      "chunks_used": 2
    }
    ```

### 3. Generate Soal Kuis Otomatis
*   **Method & URL**: `POST /generate-quiz`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "jumlah_soal": 5
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Berhasil menghasilkan 5 soal kuis.",
      "data": {
        "pertemuan_id": 1,
        "jumlah_soal_diminta": 5,
        "jumlah_soal_dihasilkan": 5,
        "soal": [
          {
            "pertanyaan": "Berapakah jumlah bit pada IP Address v4?",
            "pilihan_a": "16 bit",
            "pilihan_b": "32 bit",
            "pilihan_c": "64 bit",
            "pilihan_d": "128 bit",
            "kunci_jawaban": "B",
            "pembahasan": "IP Address v4 terdiri atas 32 bit bilangan biner."
          }
        ]
      }
    }
    ```

### 4. Transkrip Pesan Audio (Speech-to-Text)
*   **Method & URL**: `POST /transcribe`
*   **Request Body (Multipart Form-Data)**:
    *   `audio` (File Audio): Berkas suara biner.
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "text": "Bagaimana cara kerja topologi ring"
    }
    ```

### 5. Debug Similarity Search (Kebutuhan Pengujian Sidang)
*   **Method & URL**: `GET /debug/search`
*   **Query Parameters**:
    *   `query`: Kata kunci pencarian (misal: `ARPANET`).
    *   `pertemuan_id` (Opsional): `1`
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "query": "ARPANET",
      "pertemuan_id": 1,
      "relevance_threshold": 0.5,
      "results": [
        {
          "score": 0.7812,
          "source_file": "Modul-01-Pengenalan-Jaringan.pdf",
          "chunk_index": 1,
          "text": "Sejarah perkembangan jaringan diawali dari proyek ARPANET...",
          "passed_threshold": true
        }
      ]
    }
    ```

### 6. Statistik Qdrant Vector DB
*   **Method & URL**: `GET /stats`
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "total_documents": 142,
      "collection_name": "basis_pengetahuan",
      "vector_size": 384,
      "distance_metric": "Cosine"
    }
    ```

### 7. Hapus Vektor Dokumen Pertemuan
*   **Method & URL**: `DELETE /delete-pertemuan/{pertemuan_id}`
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Berhasil menghapus 18 dokumen untuk pertemuan_id=1.",
      "deleted_count": 18
    }
    ```
