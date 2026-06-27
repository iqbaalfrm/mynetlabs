import paramiko
import time

# === KREDENSIAL VPS (SESUAI REQUEST KAMU) ===
VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"
PROJECT_PATH = "/var/www/mynetlabs/backend-web"

def run_remote_commands():
    # Inisialisasi Robot SSH
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        print(f"Menyambungkan SSH ke VPS {VPS_IP}...")
        ssh.connect(hostname=VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)
        print("Login Berhasil! Mulai menganalisis dan memperbaiki server...\n")
        
        # 1. Deteksi versi PHP FPM yang aktif di sistem VPS secara otomatis
        stdin, stdout, stderr = ssh.exec_command("php -r 'echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;'")
        php_ver = stdout.read().decode('utf-8').strip()
        print(f"LOG: [INFO] Versi PHP aktif di VPS: {php_ver}")
        print("-" * 60)
        
        if not php_ver:
            print("Gagal mendeteksi versi PHP di VPS.")
            return

        # --- STRATEGI PERBAIKAN & AUTO DEPLOY ---
        commands = [
            # 1. Hapus paksa setelan default Nginx yang memicu error 404
            "rm -f /etc/nginx/sites-enabled/default",
            "rm -f /etc/nginx/sites-enabled/default.conf",
            
            # 2. Tulis ulang file konfigurasi Nginx secara dinamis dan presisi
            f"""cat << 'EOF' > /etc/nginx/sites-available/netlabs
server {{
    listen 80;
    server_name _;
    root {PROJECT_PATH}/public;
    index index.php index.html;

    location / {{
        try_files \$uri \$uri/ /index.php?\$query_string;
    }}

    location ~ \\.php\$ {{
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php{php_ver}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }}

    location /ai-api/ {{
        proxy_pass http://127.0.0.1:5000/;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
    }}
}}
EOF""",
            
            # 3. Aktifkan konfigurasi baru ke folder link aktif Nginx
            "ln -sf /etc/nginx/sites-available/netlabs /etc/nginx/sites-enabled/",
            
            # 4. Daftarkan safe.directory untuk menghindari error dubious ownership Git
            "git config --global --add safe.directory /var/www/mynetlabs",
            
            # 5. Tarik kodingan terbaru dari Github repo 'mynetlabs'
            f"cd {PROJECT_PATH} && git pull origin main",
            
            # 6. Jalankan pembersihan internal & migrasi database Laravel 11/12
            f"cd {PROJECT_PATH} && php artisan config:clear",
            f"cd {PROJECT_PATH} && php artisan cache:clear",
            f"cd {PROJECT_PATH} && php artisan migrate --force",
            
            # 7. Setel hak izin akses (Permission) folder agar Nginx tidak terhadang error 500/403
            "chown -R www-data:www-data /var/www/mynetlabs",
            f"chmod -R 775 {PROJECT_PATH}/storage {PROJECT_PATH}/bootstrap/cache",
            
            # 8. Validasi konfigurasi Nginx & Restart service server
            "nginx -t",
            "systemctl restart nginx",
            f"systemctl restart php{php_ver}-fpm"
        ]
        
        # Eksekusi rentetan perintah di atas
        for i, cmd in enumerate(commands, 1):
            print(f"Mengeksekusi perintah [{i}/{len(commands)}]...")
            stdin, stdout, stderr = ssh.exec_command(cmd)
            
            out = stdout.read().decode('utf-8').strip()
            err = stderr.read().decode('utf-8').strip()
            
            if out:
                print(f"LOG: {out}")
            if err and "warning" in err.lower() or "successful" in err.lower() or "updating" in err.lower() or "nothing to migrate" in err.lower():
                print(f"INFO: {err}")
            elif err:
                print(f"LOG/ERR: {err}")
            print("-" * 60)
            time.sleep(1)
            
        print("\nSYSTEM SUCCESS: Perbaikan Nginx & Auto-Deploy sukses dijalankan!")
        print(f"Silakan buka kembali browser kamu di: http://{VPS_IP}/admin")
        
    except Exception as e:
        print(f"Terjadi Eror Fatal: {e}")
    finally:
        ssh.close()
        print("Sesi SSH diputus aman.")

if __name__ == "__main__":
    run_remote_commands()