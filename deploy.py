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
    out = stdout.read().decode("utf-8").strip()
    err = stderr.read().decode("utf-8").strip()
    if out:
        print(out)
    if err:
        # Beberapa perintah (nginx, git) menulis progress ke stderr
        print(f"[VPS] (stderr) {err}")
    return out, err


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
        run_remote(ssh, f"cd {PROJECT_PATH} && git pull origin main", "3. Git pull kode terbaru")

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

        # 8. Validasi & restart Nginx + PHP-FPM
        run_remote(ssh, "nginx -t", "8a. Validasi konfigurasi Nginx")
        run_remote(ssh, "systemctl restart nginx", "8b. Restart Nginx")
        run_remote(ssh, f"systemctl restart php{php_ver}-fpm", f"8c. Restart PHP {php_ver}-FPM")

        # 9. Tes endpoint API login
        run_remote(ssh, f"curl -s -o /dev/null -w '%{{http_code}}' http://localhost/api/login -X POST -H 'Content-Type: application/json' -d '{{\"username\":\"test\",\"password\":\"test\"}}'", "9. Tes endpoint /api/login")

        print("\n" + "=" * 60)
        print("[OK] DEPLOY BERHASIL!")
        print("=" * 60)
        print(f"\n[WEB] Backend API tersedia di: http://{VPS_IP}/api")
        print(f"[ADMIN] Panel Filament: http://{VPS_IP}/admin")
        print(f"[MOBILE] Base URL: http://{VPS_IP}/api")

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
