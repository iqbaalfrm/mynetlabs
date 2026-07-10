# Panduan Instalasi dan Deployment Netlabs

Dokumen ini memandu Anda dalam melakukan setup aplikasi Netlabs di lingkungan lokal (*development*) maupun di lingkungan server VPS (*production*).

---

## Kebutuhan Sistem

| Komponen | Spesifikasi Minimum | Rekomendasi |
|----------|---------------------|-------------|
| **OS** | Ubuntu 22.04 LTS (VPS) / Windows 10 | Ubuntu 24.04 LTS / Windows 11 |
| **RAM** | 4 GB (Lokal) / 2 GB (VPS) | 8 GB (Lokal) / 4 GB (VPS) |
| **Storage** | 10 GB ruang kosong | 20 GB SSD |
| **PHP** | v8.2.0 | v8.3.0 |
| **Python** | v3.11.0 | v3.11.9 |
| **Composer**| v2.5.0 | v2.7.0 |
| **MySQL** | v8.0.0 (atau MariaDB 10.4) | MySQL v8.0.36 |
| **Node.js** | v18.0.0 (npm v9.0.0) | v20.0.0 (npm v10.0.0) |
| **Flutter** | v3.12.0 (Dart 3.0) | v3.16.0 (Dart 3.2) |

---

## A. Instalasi Lokal (Lingkungan Development)

### Langkah 1 — Clone Repository
```bash
git clone https://github.com/username/netlabs.git
cd netlabs
```

### Langkah 2 — Setup Database & API Server (Laravel)
1. Pindah ke direktori project:
   ```bash
   cd backend-web
   ```
2. Pasang semua dependensi PHP:
   ```bash
   composer install
   ```
3. Buat file konfigurasi `.env` dari template:
   ```bash
   cp .env.example .env
   ```
4. Buka file `.env` baru Anda dan sesuaikan konfigurasi database Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=netlabs
   DB_USERNAME=root
   DB_PASSWORD=yourpassword
   
   AI_SERVICE_URL=http://127.0.0.1:5050
   ```
5. Buat kunci enkripsi Laravel:
   ```bash
   php artisan key:generate
   ```
6. Jalankan migrasi tabel beserta *seeder* data materi awal TKJ:
   ```bash
   php artisan migrate --seed
   ```
7. Jalankan build asset front-end Vite:
   ```bash
   npm install
   npm run build
   ```
8. Jalankan server Laravel:
   ```bash
   php artisan serve --port=8000
   ```
   *Laravel API sekarang dapat diakses melalui `http://127.0.0.1:8000`.*

### Langkah 3 — Setup Python AI Engine (Flask)
1. Buka terminal baru dan masuk ke folder `backend-ai`:
   ```bash
   cd backend-ai
   ```
2. Buat lingkungan virtual (*virtual environment*):
   ```bash
   python -m venv venv
   ```
3. Aktifkan *virtual environment*:
   - **Windows (CMD/PowerShell)**:
     ```cmd
     venv\Scripts\activate
     ```
   - **Linux / MacOS**:
     ```bash
     source venv/bin/activate
     ```
4. Pasang library Python yang terdaftar di requirements:
   ```bash
   pip install -r requirements.txt
   ```
5. Salin file template `.env` ke `.env`:
   ```bash
   cp .env.example .env
   ```
6. Buka file `.env` dan masukkan API Key Google Gemini Anda:
   ```env
   GEMINI_API_KEY=AIzaSyD-your-api-key-here
   QDRANT_PERSIST_DIR=./qdrant_data
   QDRANT_COLLECTION_NAME=basis_pengetahuan
   FLASK_PORT=5050
   FLASK_DEBUG=true
   ```
7. Jalankan aplikasi Flask:
   ```bash
   python app.py
   ```
   *Flask AI Backend sekarang menyala di `http://127.0.0.1:5050`.*

### Langkah 4 — Setup Mobile Client (Flutter)
1. Buka folder `netlabs_mobile`:
   ```bash
   cd netlabs_mobile
   ```
2. Unduh semua paket dependensi pub:
   ```bash
   flutter pub get
   ```
3. Pastikan konfigurasi alamat Base URL API pada `lib/core/constants/app_constants.dart` sudah mengarah ke IP Server Laravel lokal Anda (gunakan IP lokal jaringan Wifi Anda, bukan localhost `127.0.0.1` jika dijalankan di hp fisik).
   ```dart
   class AppConstants {
     static const String baseUrl = "http://192.168.1.100:8000/api";
   }
   ```
4. Hubungkan handphone Android via kabel USB dengan opsi *USB Debugging* aktif, atau jalankan Emulator Android.
5. Jalankan aplikasi:
   ```bash
   flutter run
   ```

---

## B. Deployment ke VPS (Lingkungan Production)

Bagian ini memandu Anda melakukan deployment backend Laravel dan Flask AI ke server VPS berbasis **Ubuntu 22.04 LTS**.

### 1. Persiapan Awal Server VPS
1. Update package manager server:
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```
2. Install LAMP Stack (Linux, Apache/Nginx, MySQL, PHP):
   ```bash
   sudo apt install nginx mysql-server php8.2 php8.2-fpm php8.2-mysql php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip -y
   ```
3. Install Python 3.11 & pip:
   ```bash
   sudo apt install python3.11 python3.11-venv python3-pip -y
   ```

### 2. Deployment Laravel (backend-web)
1. Pindahkan folder `backend-web` ke `/var/www/netlabs-web`.
2. Ubah hak kepemilikan folder ke user Nginx (`www-data`):
   ```bash
   sudo chown -R www-data:www-data /var/www/netlabs-web
   sudo chmod -R 775 /var/www/netlabs-web/storage
   sudo chmod -R 775 /var/www/netlabs-web/bootstrap/cache
   ```
3. Install package & migrasi database di folder tersebut dengan opsi production:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### 3. Deployment Flask (backend-ai) & Gunicorn
1. Pindahkan folder `backend-ai` ke `/var/www/netlabs-ai`.
2. Buat venv dan pasang dependensi:
   ```bash
   cd /var/www/netlabs-ai
   python3 -m venv venv
   source venv/bin/activate
   pip install -r requirements.txt
   ```
3. Buat systemd service file untuk Gunicorn agar Flask AI berjalan otomatis di latar belakang (*background service*).
   ```bash
   sudo nano /etc/systemd/system/netlabs-ai.service
   ```
4. Tulis konfigurasi berikut (gunakan 1 worker `-w 1` karena keterbatasan database lokal Qdrant):
   ```ini
   [Unit]
   Description=Gunicorn instance to serve Netlabs AI Backend
   After=network.target

   [Service]
   User=www-data
   WorkingDirectory=/var/www/netlabs-ai
   Environment="PATH=/var/www/netlabs-ai/venv/bin"
   ExecStart=/var/www/netlabs-ai/venv/bin/gunicorn -w 1 -b 127.0.0.1:5050 --timeout 120 app:app

   [Install]
   WantedBy=multi-user.target
   ```
5. Nyalakan dan aktifkan service:
   ```bash
   sudo systemctl daemon-reload
   sudo systemctl start netlabs-ai
   sudo systemctl enable netlabs-ai
   ```

### 4. Konfigurasi Nginx Virtual Host
1. Buat file konfigurasi virtual host Nginx:
   ```bash
   sudo nano /etc/nginx/sites-available/netlabs
   ```
2. Masukkan konfigurasi server block berikut:
   ```nginx
   server {
       listen 80;
       server_name netlabs-web.net;

       # Laravel Front-end & API
       root /var/www/netlabs-web/public;
       index index.php index.html;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
       }

       # Proxy request AI ke Gunicorn Flask (khusus jika diakses eksternal)
       location /ai/ {
           proxy_pass http://127.0.0.1:5050/;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       }
   }
   ```
3. Aktifkan konfigurasi dan restart Nginx:
   ```bash
   sudo ln -s /etc/nginx/sites-available/netlabs /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

### 5. Pemasangan SSL (HTTPS Certbot)
Untuk mengamankan API transport, pasang sertifikat SSL gratis dari Let's Encrypt:
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d netlabs-web.net
```
Certbot akan memperbarui file konfigurasi Nginx secara otomatis dan mengaktifkan HTTPS port 443.
Setup selesai!
