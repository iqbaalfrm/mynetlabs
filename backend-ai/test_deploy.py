"""Quick script to pull latest code on VPS, restart AI service, and test endpoints."""
import paramiko
import time
import json

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("157.230.93.99", 22, "root", "@Kodoka123ya")

def run(cmd, label=""):
    if label:
        print(f"\n>>> {label}")
    print(f"[VPS] $ {cmd}")
    _, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode("utf-8", errors="replace").strip()
    err = stderr.read().decode("utf-8", errors="replace").strip()
    if out:
        print(out)
    if err:
        print(f"(stderr) {err}")
    return out

# 1. Pull kode terbaru
run("cd /var/www/mynetlabs && git pull origin main", "1. Git pull")

# 2. Restart service
run("systemctl restart netlabs-ai", "2. Restart service")
time.sleep(4)

# 3. Health check
run("curl -s http://localhost:5050/", "3. Health check")

# 4. Test index-pdf
payload_index = json.dumps({
    "pertemuan_id": 2,
    "file_path": "/var/www/mynetlabs/backend-ai/modul_test_subnetting_ipv4.pdf"
})
result = run(
    f"curl -s -X POST http://localhost:5050/index-pdf "
    f"-H 'Content-Type: application/json' "
    f"-d '{payload_index}'",
    "4. Test index-pdf"
)

# 5. Test chat
payload_chat = json.dumps({
    "pertemuan_id": 2,
    "message": "Bagaimana cara menghitung IP Broadcast jika IP-nya 192.168.1.10/26?"
})
result = run(
    f"curl -s -X POST http://localhost:5050/chat "
    f"-H 'Content-Type: application/json' "
    f"-d '{payload_chat}'",
    "5. Test chat"
)

ssh.close()
print("\nSelesai!")
