# API Documentation — Netlabs

Dokumen ini menjelaskan seluruh endpoint REST API yang digunakan dalam ekosistem Netlabs, baik yang disediakan oleh **Laravel Web Server** (untuk komunikasi Mobile App ke Database) maupun **Flask AI Backend** (untuk operasi NLP dan Vector DB).

---

## Base URL

*   **Laravel API Server**: `http://127.0.0.1:8000/api` (Local) atau `https://netlabs-web.net/api` (Production)
*   **Flask AI Engine**: `http://127.0.0.1:5050` (Local/Inter-service) atau `http://vps-ip:5050` (Production)

---

## Autentikasi

Semua endpoint di bawah segmen **Laravel Protected API** membutuhkan Bearer Token menggunakan Laravel Sanctum.
Header wajib disertakan pada request:
```http
Authorization: Bearer {your_sanctum_token}
Content-Type: application/json
Accept: application/json
```

---

## 1. Laravel API Endpoints

### A. Public Endpoints (Tanpa Token)

#### 1. Register Siswa
*   **Method & URL**: `POST /register`
*   **Deskripsi**: Mendaftarkan akun siswa baru ke database Netlabs.
*   **Request Body (JSON)**:
    ```json
    {
      "nis": "22019283",
      "name": "Iqbal Firmansyah",
      "username": "iqbalfrm",
      "kelas": "XI TKJ 1",
      "password": "password123",
      "password_confirmation": "password123"
    }
    ```
*   **Response Body (201 Created)**:
    ```json
    {
      "message": "Pendaftaran berhasil.",
      "data": {
        "token": "1|sYh821Ja0Sjkas...",
        "user": {
          "id": 5,
          "nis": "22019283",
          "name": "Iqbal Firmansyah",
          "username": "iqbalfrm",
          "kelas": "XI TKJ 1",
          "foto_profil": null
        }
      }
    }
    ```
*   **Error Response (422 Unprocessable Entity)**:
    ```json
    {
      "message": "NIS sudah terdaftar.",
      "errors": {
        "nis": ["The nis has already been taken."]
      }
    }
    ```

#### 2. Login Siswa
*   **Method & URL**: `POST /login`
*   **Deskripsi**: Melakukan autentikasi akun siswa dan menerbitkan Bearer Token.
*   **Request Body (JSON)**:
    ```json
    {
      "username": "iqbalfrm",
      "password": "password123"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "message": "Login berhasil.",
      "data": {
        "token": "2|uHsjka817Sjadk...",
        "user": {
          "id": 5,
          "nis": "22019283",
          "name": "Iqbal Firmansyah",
          "username": "iqbalfrm",
          "kelas": "XI TKJ 1",
          "foto_profil": "http://127.0.0.1:8000/storage/foto_profil/iqbal.jpg"
        }
      }
    }
    ```

---

### B. Protected Endpoints (Membutuhkan Bearer Token)

#### 3. Logout Siswa
*   **Method & URL**: `POST /logout`
*   **Deskripsi**: Menghapus token autentikasi yang sedang digunakan saat ini.
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
      "current_password": "password123",
      "new_password": "newpassword123",
      "new_password_confirmation": "newpassword123"
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
      "nis": "22019283",
      "name": "Iqbal Firmansyah",
      "username": "iqbalfrm",
      "kelas": "XI TKJ 1",
      "foto_profil": "http://127.0.0.1:8000/storage/foto-profil/5.jpg"
    }
    ```

#### 6. Ambil Daftar Pertemuan
*   **Method & URL**: `GET /pertemuan`
*   **Response Body (200 OK)**:
    ```json
    [
      {
        "id": 1,
        "nama_pertemuan": "Pertemuan 1: Pengenalan IP Address",
        "deskripsi": "Mempelajari konsep dasar IP Address v4, Subnet Mask, dan pengalamatan host.",
        "file_modul": "http://127.0.0.1:8000/storage/modul/pertemuan_1.pdf",
        "is_completed": true,
        "progress_persen": 100
      },
      {
        "id": 2,
        "nama_pertemuan": "Pertemuan 2: Routing Statis",
        "deskripsi": "Mengkonfigurasi rute statis pada router Cisco CLI.",
        "file_modul": "http://127.0.0.1:8000/storage/modul/pertemuan_2.pdf",
        "is_completed": false,
        "progress_persen": 0
      }
    ]
    ```

#### 7. Detail Pertemuan & Topik
*   **Method & URL**: `GET /pertemuan/{id}`
*   **Response Body (200 OK)**:
    ```json
    {
      "id": 1,
      "nama_pertemuan": "Pertemuan 1: Pengenalan IP Address",
      "deskripsi": "Mempelajari konsep dasar IP Address v4, Subnet Mask, dan pengalamatan host.",
      "file_modul": "http://127.0.0.1:8000/storage/modul/pertemuan_1.pdf",
      "topik_list": [
        {
          "id": 1,
          "judul_topik": "Konsep Desimal ke Biner",
          "konten": "IP Address v4 terdiri atas 32 bit bilangan biner...",
          "is_done": true
        },
        {
          "id": 2,
          "judul_topik": "Kelas IP Address v4",
          "konten": "Pembagian kelas IP Address terdiri dari kelas A, B, C...",
          "is_done": false
        }
      ]
    }
    ```

#### 8. Tandai Topik Selesai
*   **Method & URL**: `POST /pertemuan/{pertemuanId}/topik/{topikId}/selesai`
*   **Deskripsi**: Mencatat progress belajar siswa pada topik tertentu.
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Topik berhasil ditandai selesai.",
      "progress_persen": 50
    }
    ```

#### 9. Ambil Soal Kuis
*   **Method & URL**: `GET /pertemuan/{id}/kuis`
*   **Deskripsi**: Mengambil daftar pertanyaan kuis untuk pertemuan tertentu. Jika guru belum men-generate kuis, Laravel akan meminta Flask AI Backend untuk men-generate soal kuis dari modul Qdrant secara real-time.
*   **Response Body (200 OK)**:
    ```json
    [
      {
        "id": 12,
        "pertanyaan": "Berapakah jumlah bit pada IP Address v4?",
        "pilihan_a": "16 bit",
        "pilihan_b": "32 bit",
        "pilihan_c": "64 bit",
        "pilihan_d": "128 bit"
      }
    ]
    ```

#### 10. Submit Jawaban Kuis
*   **Method & URL**: `POST /kuis/submit`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "jawaban": [
        {"soal_id": 12, "pilihan": "B"},
        {"soal_id": 13, "pilihan": "A"}
      ]
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Kuis berhasil dikirim.",
      "data": {
        "nilai": 100,
        "total_soal": 2,
        "jawaban_benar": 2,
        "status_lulus": true,
        "rekomendasi_belajar": "Selamat! Pemahaman Anda sudah sangat baik pada materi ini.",
        "pembahasan": [
          {
            "soal_id": 12,
            "pertanyaan": "Berapakah jumlah bit pada IP Address v4?",
            "kunci": "B",
            "jawaban_siswa": "B",
            "is_benar": true,
            "penjelasan": "IP Address v4 terdiri atas 32 bit bilangan biner yang terbagi menjadi 4 oktet."
          }
        ]
      }
    }
    ```

#### 11. Kirim Pesan Chat (RAG)
*   **Method & URL**: `POST /chat`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "message": "Bagaimana cara menghitung range IP Host?"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "answer": "Untuk menghitung range IP Host, Anda dapat melihat block size subnet...",
      "sources": ["pertemuan_1.pdf"],
      "chunks_used": 2
    }
    ```

#### 12. Kirim Pesan Chat Audio (Voice Query)
*   **Method & URL**: `POST /chat/audio`
*   **Request Body (Multipart Form-Data)**:
    - `audio`: File `.wav` atau `.m4a` rekaman suara siswa.
    - `pertemuan_id`: `1` (Integer)
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "query_transcription": "Apa itu subnet mask?",
      "answer": "Subnet mask adalah bitmask 32-bit yang digunakan untuk membedakan Network ID dan Host ID...",
      "sources": ["pertemuan_1.pdf"],
      "chunks_used": 1
    }
    ```

---

## 2. Flask AI Endpoints (Inter-Service)

Endpoint Flask dipanggil secara internal oleh server Laravel, tetapi dapat dipanggil secara langsung untuk kebutuhan testing/debugging.

### 1. Indexing Modul PDF
*   **Method & URL**: `POST /index-pdf`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "file_path": "c:/SKRIPSI2026/netlabs/backend-web/storage/app/public/modul/pertemuan_1.pdf"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Modul berhasil di-index ke Vektor DB.",
      "data": {
        "file_name": "pertemuan_1.pdf",
        "pertemuan_id": 1,
        "total_chunks": 12,
        "total_documents_in_db": 12
      }
    }
    ```

### 2. Chat RAG (Engine Utama)
*   **Method & URL**: `POST /chat`
*   **Request Body (JSON)**:
    ```json
    {
      "pertemuan_id": 1,
      "message": "Bagaimana cara melakukan konfigurasi IP Address?"
    }
    ```
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "answer": "Untuk melakukan konfigurasi IP Address pada interface Cisco router, gunakan command: \n- interface gigabitethernet 0/0\n- ip address 192.168.1.1 255.255.255.0",
      "sources": ["pertemuan_1.pdf"],
      "chunks_used": 3
    }
    ```
*   **Response Body - Pertanyaan tidak relevan (200 OK)**:
    ```json
    {
      "success": false,
      "answer": "Maaf, pertanyaan tersebut tidak ditemukan dalam modul praktikum yang tersedia. Silakan tanyakan materi yang berkaitan dengan praktikum Dasar-Dasar Kejuruan.",
      "sources": [],
      "chunks_used": 0
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
            "pertanyaan": "Berapakah CIDR untuk subnet mask 255.255.255.0?",
            "pilihan_a": "/24",
            "pilihan_b": "/25",
            "pilihan_c": "/26",
            "pilihan_d": "/30",
            "kunci_jawaban": "A",
            "pembahasan": "Subnet mask 255.255.255.0 memiliki 24 bit bernilai 1, yang direpresentasikan dengan CIDR /24."
          }
        ]
      }
    }
    ```

### 4. Transkripsi Audio
*   **Method & URL**: `POST /transcribe`
*   **Request Body (Multipart Form-Data)**:
    - `audio`: File file audio binary (misal: format `.wav`)
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "text": "Bagaimana cara melakukan konfigurasi DHCP server"
    }
    ```

### 5. Debug Pencarian Vektor (Sidang Sidang Ready)
*   **Method & URL**: `GET /debug/search`
*   **Query Parameters**:
    - `query`: `IP Address` (String - Wajib)
    - `pertemuan_id`: `1` (Integer - Opsional)
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "query": "IP Address",
      "pertemuan_id": 1,
      "relevance_threshold": 0.5,
      "results": [
        {
          "score": 0.7682,
          "source_file": "pertemuan_1.pdf",
          "chunk_index": 3,
          "text": "IP Address v4 terdiri atas 32 bit bilangan biner yang terbagi...",
          "passed_threshold": true
        },
        {
          "score": 0.4512,
          "source_file": "pertemuan_1.pdf",
          "chunk_index": 7,
          "text": "Kelas C memiliki default subnet mask berupa 255.255.255.0...",
          "passed_threshold": false
        }
      ]
    }
    ```

### 6. Statistik Vektor Database
*   **Method & URL**: `GET /stats`
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "total_documents": 24,
      "collection_name": "basis_pengetahuan",
      "vector_size": 384,
      "distance_metric": "Cosine"
    }
    ```

### 7. Hapus Seluruh Dokumen Pertemuan
*   **Method & URL**: `DELETE /delete-pertemuan/{pertemuan_id}`
*   **Response Body (200 OK)**:
    ```json
    {
      "success": true,
      "message": "Berhasil menghapus 12 dokumen untuk pertemuan_id=1.",
      "deleted_count": 12
    }
    ```
