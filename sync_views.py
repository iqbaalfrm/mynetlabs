"""Upload only views + config + routes to VPS (skip heavy assets)."""
import paramiko
import os

VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"
REMOTE_BASE = "/var/www/mynetlabs/backend-web"
LOCAL_BASE = "backend-web"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)
ftp = ssh.open_sftp()

def ensure_remote_dir(remote_path):
    try:
        ftp.stat(remote_path)
    except FileNotFoundError:
        try:
            ftp.mkdir(remote_path)
        except Exception:
            pass

def upload_dir(local_dir, remote_dir):
    for root, dirs, files in os.walk(local_dir):
        rel_dir = os.path.relpath(root, local_dir).replace('\\', '/')
        remote_sub = f"{remote_dir}/{rel_dir}" if rel_dir != '.' else remote_dir
        ensure_remote_dir(remote_sub)
        for f in files:
            local = os.path.join(root, f)
            remote = f"{remote_sub}/{f}".replace('//', '/')
            try:
                ftp.put(local, remote)
                print(f"  OK: {rel_dir}/{f}")
            except Exception as e:
                print(f"  ERR: {rel_dir}/{f}: {e}")

# Upload views (admin + layouts)
print("[VIEWS]")
upload_dir("backend-web/resources/views", f"{REMOTE_BASE}/resources/views")

# Upload config
print("[CONFIG]")
upload_dir("backend-web/config", f"{REMOTE_BASE}/config")

# Upload routes
print("[ROUTES]")
upload_dir("backend-web/routes", f"{REMOTE_BASE}/routes")

# Upload bootstrap/providers
print("[BOOTSTRAP]")
ftp.put("backend-web/bootstrap/providers.php", f"{REMOTE_BASE}/bootstrap/providers.php")

ftp.close()

# Clear caches
stdin, stdout, stderr = ssh.exec_command(
    f"cd {REMOTE_BASE} && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache && php artisan route:cache"
)
print("\n[CACHE]")
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print(f"[STDERR] {err}")

ssh.close()
print("[DONE]")