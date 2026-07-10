# Panduan Kontribusi — Netlabs

Terima kasih atas ketertarikan Anda untuk berkontribusi pada pengembangan platform **Netlabs**! Dokumen ini membantu Anda memahami alur kerja, konvensi penulisan kode, dan cara mengirimkan kontribusi secara efisien.

---

## 1. Setup Development Environment

Ikuti langkah-langkah di [SETUP.md](file:///c:/SKRIPSI2026/netlabs/docs/SETUP.md) untuk menyiapkan repositori dan dependensi lokal Anda.
1. Buat branch baru dari branch `main` untuk pengerjaan fitur/perbaikan Anda:
   ```bash
   git checkout -b feature/nama-fitur-anda
   # atau
   git checkout -b bugfix/nama-bug-anda
   ```
2. Pastikan virtual environment Python (`backend-ai/venv`) dan server database lokal (`MySQL`) menyala saat Anda mengembangkan fitur.

---

## 2. Konvensi Kode (Code Conventions)

Untuk menjaga kualitas dan keterbacaan kode (*clean code*), pastikan kode Anda mematuhi standar bahasa pemrograman masing-masing komponen:

### A. Flutter / Dart
*   **Standard Linting**: Patuhi aturan standard `flutter_lints` yang dikonfigurasi di `analysis_options.yaml`.
*   **Arsitektur GetX**:
    *   Letakkan UI View hanya di dalam folder `lib/app/views/` (kelompokkan berdasarkan fitur).
    *   Letakkan logic controller di dalam `lib/app/controllers/` dan deklarasikan instansiasinya di kelas binding `lib/app/bindings/`.
    *   JANGAN melakukan panggilan state management atau pembaruan DOM secara ad-hoc tanpa menggunakan `GetBuilder` / `Obx`.
*   **Pembersihan**: Hapus komentar yang tidak relevan, kode mati (*dead code*), dan seluruh perintah `print()` debug sebelum melakukan commit. Gunakan `debugPrint()` jika benar-benar dibutuhkan.

### B. Laravel / PHP
*   **PSR Standards**: Wajib mengikuti standar penulisan kode **PSR-12** (atau gunakan `laravel/pint` untuk format otomatis):
    ```bash
    vendor/bin/pint
    ```
*   **Separation of Concerns**:
    *   Controller API (`app/Http/Controllers/Api`) tidak boleh menampung logika query database mentah atau manipulasi model yang rumit. Pisahkan ke kelas Service (`app/Services`).
    *   Gunakan Laravel Form Request (`app/Http/Requests`) untuk validasi input request.
    *   Gunakan Laravel API Resource (`app/Http/Resources`) untuk memformat respons JSON API.
*   **Logging**: Selalu bungkus query database eksternal dan interaksi API Flask dengan blok `try-catch` dan catat error ke log menggunakan facade `Log::error()`.

### C. Flask / Python
*   **PEP 8 Standards**: Wajib mematuhi standar penulisan kode Python **PEP 8**. Gunakan *linter* seperti `flake8` atau formatter `black` untuk merapikan berkas `.py`.
*   **Modularitas**: JANGAN menambahkan fungsionalitas besar langsung di `app.py`. Gunakan Blueprint (`routes/`) untuk merancang endpoint baru, dan simpan logika komputasi di folder `services/` atau `utils/`.
*   **Dokumentasi**: Setiap fungsi baru wajib dilengkapi dengan *docstring* berformat Google Style serta *static type hints* lengkap (misal: `def buat_embedding(teks: str) -> list[float]`).

---

## 3. Alur Pengiriman Kontribusi (Pull Request Guidelines)

1. Lakukan pengujian unit secara mandiri setelah memodifikasi kode:
   - **Laravel Tests**:
     ```bash
     php artisan test
     ```
   - **Flutter Tests**:
     ```bash
     flutter test
     ```
2. Lakukan commit dengan pesan yang jelas dan deskriptif:
   ```bash
   git commit -m "feat(rag): add custom debug search endpoint to backend AI"
   ```
3. Push branch Anda ke repository remote:
   ```bash
   git push origin feature/nama-fitur-anda
   ```
4. Buka Pull Request (PR) ke branch `main`. Tulis deskripsi lengkap mengenai:
   - Apa masalah yang diselesaikan?
   - Bagaimana cara menguji perubahan tersebut?
   - Apakah ada dependensi baru yang ditambahkan?
5. Tunggu proses review dan feedback dari pengembang utama.

---

## 4. Melaporkan Bug (Reporting Bugs)

Jika Anda menemukan bug, laporkan melalui menu **Issues** di GitHub dengan menyertakan informasi berikut:
*   Deskripsi langkah-langkah reproduksi bug secara berurutan.
*   Hasil yang diharapkan vs hasil aktual yang terjadi.
*   Screenshot/rekaman layar (jika terkait UI) atau potongan log error dari backend.
*   Versi OS, PHP/Python, dan simulator yang Anda gunakan saat bug terpicu.
