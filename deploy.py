"""
============================================================
 NETLABS AUTO-DEPLOY SCRIPT (Paramiko)
 Backend Laravel 12 + Mobile Flutter Integration
============================================================
Skrip ini melakukan:
  1. Git commit & push kode terbaru (backend-web) dari lokal
  2. SSH ke VPS via Paramiko
  3. Git pull di server
  4. Install dependency (composer install --no-dev)
  5. Migrasi database (php artisan migrate --force)
  6. Optimasi & clear cache Laravel
  7. Set permission folder storage & bootstrap/cache
  8. Deteksi & restart PHP-FPM + Nginx

Cara pakai:  python deploy.py
"""

import subprocess
import sys

import time
import paramiko

# ============================================================
# KONFIGURASI VPS & GIT (Sesuaikan jika berubah)
# ============================================================
VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"
PROJECT_PATH = "/var/www/mynetlabs/backend-web"
AI_PROJECT_PATH = "/var/www/mynetlabs/backend-ai"

# API Key Gemini untuk backend AI (baca dari environment / .env)
import os as _os
from dotenv import load_dotenv as _load_dotenv
_load_dotenv(_os.path.join(_os.path.dirname(_os.path.abspath(__file__)), "backend-ai", ".env"))
GEMINI_API_KEY = _os.getenv("GEMINI_API_KEY", "")

# Konfigurasi Git lokal (path folder backend-web relatif terhadap root repo)
LOCAL_BACKEND_PATH = "backend-web"


# ============================================================
# FUNGSI HELPER
# ============================================================
def run_local(cmd, cwd=None):
    """Jalankan perintah di mesin lokal (Windows)."""
    print(f"[LOKAL] $ {cmd}")
    try:
        result = subprocess.run(
            cmd, shell=True, cwd=cwd,
            capture_output=True, text=True
        )
        if result.stdout.strip():
            print(result.stdout.strip())
        if result.stderr.strip():
            print(result.stderr.strip())
        return result.returncode == 0
    except Exception as e:
        print(f"[LOKAL] ERROR: {e}")
        return False


def run_remote(ssh, cmd, label=""):
    """Jalankan perintah di VPS via SSH dan tampilkan outputnya."""
    if label:
        print(f"\n>>> {label}")
    print(f"[VPS] $ {cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode("utf-8", errors="replace").strip()
    err = stderr.read().decode("utf-8", errors="replace").strip()
    if out:
        safe_print(out)
    if err:
        # Beberapa perintah (nginx, git) menulis progress ke stderr
        safe_print(f"[VPS] (stderr) {err}")
    return out, err


def safe_print(text):
    """Print yang aman terhadap karakter Unicode di Windows console."""
    try:
        print(text)
    except UnicodeEncodeError:
        # Hapus karakter yang tidak bisa di-encode console Windows
        enc = getattr(sys.stdout, "encoding", "utf-8") or "utf-8"
        print(text.encode(enc, errors="replace").decode(enc, errors="replace"))


# ============================================================
# TAHAP 1: GIT COMMIT & PUSH DARI LOKAL
# ============================================================
def git_commit_and_push():
    print("\n" + "=" * 60)
    print("TAHAP 1: Git Commit & Push dari Lokal")
    print("=" * 60)

    # Tambahkan semua perubahan
    run_local("git add -A")
    # Commit dengan timestamp
    commit_msg = f"feat: lengkapi API backend & integrasi mobile (materi, kuis, chat, statistik) - {time.strftime('%Y-%m-%d %H:%M')}"
    # Gunakan commit yang aman (abaikan jika tidak ada perubahan)
    run_local(f'git commit -m "{commit_msg}"')
    # Push ke origin/main
    ok = run_local("git push origin main")
    if ok:
        print("[OK] Push ke GitHub berhasil!")
    else:
        print("[!] Push gagal atau tidak ada perubahan baru. Lanjut ke deploy server...")
    return ok


# ============================================================
# TAHAP 2: SSH & DEPLOY KE VPS
# ============================================================
def deploy_to_vps():
    print("\n" + "=" * 60)
    print("TAHAP 2: SSH & Deploy ke VPS")
    print("=" * 60)

    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

    try:
        print(f"\nMenyambungkan SSH ke VPS {VPS_IP}...")
        ssh.connect(hostname=VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)
        print("[OK] Login SSH berhasil!\n")

        # 1. Deteksi versi PHP-FPM yang aktif di VPS
        run_remote(ssh, "php -r 'echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;'", "1. Deteksi versi PHP")
        stdin, stdout, stderr = ssh.exec_command("php -r 'echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;'")
        php_ver = stdout.read().decode("utf-8").strip()
        print(f"Versi PHP aktif: {php_ver}")

        if not php_ver:
            print("[X] Gagal mendeteksi versi PHP. Deploy dibatalkan.")
            return

        # 2. Daftarkan safe.directory untuk git
        run_remote(ssh, "git config --global --add safe.directory /var/www/mynetlabs", "2. Set safe.directory git")

        # 3. Git pull kode terbaru
        run_remote(ssh, f"cd /var/www/mynetlabs && git reset --hard && git clean -fd && cd {PROJECT_PATH} && git pull origin main", "3. Git reset & pull kode terbaru")

        # 4. Install dependency Composer (production, tanpa dev)
        run_remote(ssh, f"cd {PROJECT_PATH} && composer install --no-dev --optimize-autoloader", "4. Composer install")

        # 5. Migrasi database
        run_remote(ssh, f"cd {PROJECT_PATH} && php artisan migrate --force", "5. Migrate database")

        # 6. Clear & optimize cache Laravel
        run_remote(ssh, f"cd {PROJECT_PATH} && php artisan config:clear", "6a. Config clear")
        run_remote(ssh, f"cd {PROJECT_PATH} && php artisan cache:clear", "6b. Cache clear")
        run_remote(ssh, f"cd {PROJECT_PATH} && php artisan route:clear", "6c. Route clear")
        run_remote(ssh, f"cd {PROJECT_PATH} && php artisan view:clear", "6d. View clear")
        run_remote(ssh, f"cd {PROJECT_PATH} && php artisan config:cache", "6e. Config cache")
        run_remote(ssh, f"cd {PROJECT_PATH} && php artisan route:cache", "6f. Route cache")

        # 7. Set permission folder storage & bootstrap/cache
        run_remote(ssh, "chown -R www-data:www-data /var/www/mynetlabs", "7a. Set ownership www-data")
        run_remote(ssh, f"chmod -R 775 {PROJECT_PATH}/storage {PROJECT_PATH}/bootstrap/cache", "7b. Set permission 775")

        # 8. Tulis ulang config Nginx yang BENAR jika belum ada SSL
        # Kita cek apakah file config nginx di vps sudah punya ssl
        _, stdout, _ = ssh.exec_command("cat /etc/nginx/sites-available/netlabs 2>/dev/null")
        current_conf = stdout.read().decode('utf-8', errors='ignore')
        if "listen 443" not in current_conf:
            nginx_conf = (
                "server {\n"
                "    listen 80;\n"
                "    server_name netlabs.web.id www.netlabs.web.id;\n"
                "    root /var/www/mynetlabs/backend-web/public;\n"
                "    index index.php index.html;\n"
                "    client_max_body_size 64M;\n"
                "    location / {\n"
                "        try_files $uri $uri/ /index.php?$query_string;\n"
                "    }\n"
                "    location ~ \\.php$ {\n"
                "        include snippets/fastcgi-php.conf;\n"
                f"        fastcgi_pass unix:/var/run/php/php{php_ver}-fpm.sock;\n"
                "        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n"
                "        include fastcgi_params;\n"
                "    }\n"
                "    location /ai-api/ {\n"
                "        proxy_pass http://127.0.0.1:5050/;\n"
                "        proxy_set_header Host $host;\n"
                "        proxy_set_header X-Real-IP $remote_addr;\n"
                "    }\n"
                "}\n"
            )
            write_cmd = "cat > /etc/nginx/sites-available/netlabs << 'NGINXEOF'" + chr(10) + nginx_conf + "NGINXEOF"
            run_remote(ssh, write_cmd, "8a. Tulis config Nginx baru")
            run_remote(ssh, "ln -sf /etc/nginx/sites-available/netlabs /etc/nginx/sites-enabled/", "8b. Symlink sites-enabled")
            run_remote(ssh, "rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-enabled/default.conf", "8c. Hapus default nginx")
            run_remote(ssh, "nginx -t", "8d. Validasi konfigurasi Nginx")
            run_remote(ssh, "systemctl restart nginx", "8e. Restart Nginx")
        else:
            print(">>> [Nginx] Konfigurasi memiliki SSL aktif (port 443). Mengatur timeout Nginx & PHP-FPM...")
            run_remote(ssh, "sed -i 's/fastcgi_read_timeout [0-9]*;/fastcgi_read_timeout 300;/g' /etc/nginx/sites-available/netlabs", "8f1. Update fastcgi_read_timeout Nginx")
            run_remote(ssh, "grep -q 'fastcgi_read_timeout' /etc/nginx/sites-available/netlabs || sed -i '/fastcgi_params;/i \\        fastcgi_read_timeout 300;' /etc/nginx/sites-available/netlabs", "8f2. Sisipkan fastcgi_read_timeout Nginx jika belum ada")
            run_remote(ssh, "sed -i 's/proxy_read_timeout [0-9]*;/proxy_read_timeout 300;/g' /etc/nginx/sites-available/netlabs", "8f3. Update proxy_read_timeout Nginx")
            run_remote(ssh, "grep -q 'proxy_read_timeout' /etc/nginx/sites-available/netlabs || sed -i '/proxy_pass http:\\/\\/127.0.0.1:5050\\/;/a \\        proxy_read_timeout 300;' /etc/nginx/sites-available/netlabs", "8f4. Sisipkan proxy_read_timeout Nginx jika belum ada")
            run_remote(ssh, "nginx -t && systemctl reload nginx", "8f5. Reload Nginx dengan timeout baru")

        # Update PHP max_execution_time = 180 di VPS
        run_remote(ssh, "sed -i 's/max_execution_time = [0-9]*/max_execution_time = 180/g' /etc/php/*/fpm/php.ini 2>/dev/null || true", "8g. Set PHP max_execution_time = 180")
        run_remote(ssh, f"systemctl restart php{php_ver}-fpm", f"8h. Restart PHP {php_ver}-FPM")

        # 9. Tes endpoint API login (harusnya 401/422, BUKAN 500)
        run_remote(ssh, "curl -s -o /dev/null -w '%{http_code}' http://localhost/api/login -X POST -H 'Content-Type: application/json' -d '{\"username\":\"test\",\"password\":\"test\"}'", "9. Tes endpoint /api/login (401 = sukses)")

        # ============================================================
        # TAHAP BACKEND AI: Setup Python Flask + ChromaDB + Gemini
        # ============================================================
        print("\n" + "=" * 60)
        print("TAHAP 3: Deploy Backend AI (Flask + ChromaDB)")
        print("=" * 60)

        # 10. Install Python3, pip, venv jika belum ada
        run_remote(ssh, "apt-get update -qq && apt-get install -y -qq python3 python3-pip python3-venv", "10. Install Python3 + pip + venv")

        # 11. Buat virtual environment
        run_remote(ssh, f"cd {AI_PROJECT_PATH} && python3 -m venv venv", "11. Buat Python virtual environment")

        # 12. Install dependensi Python
        run_remote(ssh, f"cd {AI_PROJECT_PATH} && venv/bin/pip install --upgrade pip && venv/bin/pip install -r requirements.txt", "12. Install Python dependencies")

        # 13. Buat file .env untuk backend AI
        env_content = (
            f"GEMINI_API_KEY={GEMINI_API_KEY}\n"
            f"QDRANT_PERSIST_DIR=./qdrant_data\n"
            f"QDRANT_COLLECTION_NAME=basis_pengetahuan\n"
            f"FLASK_PORT=5050\n"
            f"FLASK_DEBUG=false\n"
        )
        env_cmd = f"cat > {AI_PROJECT_PATH}/.env << 'ENVEOF'\n{env_content}ENVEOF"
        run_remote(ssh, env_cmd, "13. Tulis .env backend AI")

        # 14. Buat systemd service untuk backend AI
        service_content = (
            "[Unit]\n"
            "Description=NetLabs AI Backend (Flask + ChromaDB + Gemini)\n"
            "After=network.target\n\n"
            "[Service]\n"
            "User=www-data\n"
            f"WorkingDirectory={AI_PROJECT_PATH}\n"
            f"Environment=PATH={AI_PROJECT_PATH}/venv/bin:/usr/bin\n"
            f"Environment=HF_HOME={AI_PROJECT_PATH}/hf_cache\n"
            f"ExecStart={AI_PROJECT_PATH}/venv/bin/gunicorn -w 1 -b 0.0.0.0:5050 --timeout 120 app:app\n"
            "Restart=always\n"
            "RestartSec=5\n\n"
            "[Install]\n"
            "WantedBy=multi-user.target\n"
        )
        svc_cmd = f"cat > /etc/systemd/system/netlabs-ai.service << 'SVCEOF'\n{service_content}SVCEOF"
        run_remote(ssh, svc_cmd, "14a. Buat systemd service netlabs-ai")

        # Set ownership
        run_remote(ssh, f"chown -R www-data:www-data {AI_PROJECT_PATH}", "14b. Set ownership backend-ai")

        # Reload dan start service
        run_remote(ssh, "systemctl daemon-reload", "14c. Daemon reload")
        run_remote(ssh, "systemctl enable netlabs-ai", "14d. Enable service netlabs-ai")
        run_remote(ssh, "systemctl restart netlabs-ai", "14e. Restart service netlabs-ai")

        # 14f. Jalankan re-indexing seluruh PDF ke Qdrant di VPS
        run_remote(ssh, f"cd {AI_PROJECT_PATH} && venv/bin/python index_all_pdfs.py", "14f. Jalankan re-indexing offline di VPS")

        # 14g. Kembalikan kepemilikan ke www-data dan restart service AI agar tidak terjadi Permission Error pada file lock Qdrant
        run_remote(ssh, f"chown -R www-data:www-data {AI_PROJECT_PATH}", "14g. Set ownership backend-ai post-indexing")
        run_remote(ssh, "systemctl restart netlabs-ai", "14h. Restart service netlabs-ai post-indexing")

        # 15. Tunggu sebentar lalu cek status
        import time as _time
        _time.sleep(3)
        run_remote(ssh, "systemctl status netlabs-ai --no-pager -l", "15a. Cek status service AI")
        run_remote(ssh, "curl -s http://localhost:5050/ 2>/dev/null || echo 'AI backend belum siap'", "15b. Tes health check AI")

        print("\n" + "=" * 60)
        print("[OK] DEPLOY BERHASIL! (Laravel + AI Backend)")
        print("=" * 60)
        print(f"\n[WEB]    Backend API   : http://{VPS_IP}/api")
        print(f"[ADMIN]  Panel Filament: http://{VPS_IP}/admin")
        print(f"[MOBILE] Base URL      : http://{VPS_IP}/api")
        print(f"[AI]     Flask Backend : http://{VPS_IP}:5050")
        print(f"[AI]     Via Nginx     : http://{VPS_IP}/ai-api/")

    except paramiko.AuthenticationException:
        print("[X] Autentikasi SSH gagal. Periksa user/password VPS.")
    except paramiko.SSHException as e:
        print(f"[X] Error SSH: {e}")
    except Exception as e:
        print(f"[X] Terjadi error fatal: {e}")
    finally:
        ssh.close()
        print("\n[SSH] Sesi SSH diputus dengan aman.")


# ============================================================
# MAIN
# ============================================================
if __name__ == "__main__":
    print("=" * 60)
    print("  NETLABS AUTO-DEPLOY (Paramiko)")
    print("=" * 60)

    # Tahap 1: Push kode dari lokal
    git_commit_and_push()

    # Tahap 2: Deploy ke VPS
    deploy_to_vps()

    print("\nSelesai.")
