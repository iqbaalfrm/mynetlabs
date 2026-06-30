"""Sync local backend-web files to VPS via SFTP + clear Laravel caches."""
import paramiko
import os
import sys

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

# Create remote directories if needed, then upload all files
for root, dirs, files in os.walk(LOCAL_BASE):
    # Skip vendor, node_modules, storage, .git
    dirs[:] = [d for d in dirs if d not in ('vendor', 'node_modules', 'storage', '.git')]
    
    rel_dir = os.path.relpath(root, LOCAL_BASE).replace('\\', '/')
    remote_dir = f"{REMOTE_BASE}/{rel_dir}" if rel_dir != '.' else REMOTE_BASE
    
    # Ensure remote directory exists
    try:
        ftp.stat(remote_dir)
    except FileNotFoundError:
        try:
            ftp.mkdir(remote_dir)
        except Exception:
            pass  # already exists or parent missing — handled by recursion
    
    for f in files:
        local_path = os.path.join(root, f)
        remote_path = f"{remote_dir}/{f}".replace('//', '/')
        try:
            ftp.put(local_path, remote_path)
            print(f"OK: {local_path} -> {remote_path}")
        except Exception as e:
            print(f"ERR: {local_path}: {e}")

ftp.close()

# Clear caches
stdin, stdout, stderr = ssh.exec_command(
    f"cd {REMOTE_BASE} && php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache && php artisan route:cache"
)
print("\n[CACHE CLEAR]")
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print(f"[STDERR] {err}")

ssh.close()
print("\n[DONE] Sync complete.")