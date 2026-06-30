"""
Deploy Backend AI only — SSH ke VPS, pull code, restart service.
"""
import paramiko
import time

VPS_IP = "157.230.93.99"
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"
AI_PATH = "/var/www/mynetlabs/backend-ai"
REPO_PATH = "/var/www/mynetlabs"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
print("SSH connecting...")
ssh.connect(hostname=VPS_IP, port=22, username=VPS_USER, password=VPS_PASS)
print("[OK] SSH connected\n")

def run(cmd, label=""):
    if label:
        print(f"\n>>> {label}")
    print(f"[VPS] {cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode("utf-8", errors="replace").strip()
    err = stderr.read().decode("utf-8", errors="replace").strip()
    if out:
        print(out)
    if err:
        print(f"[stderr] {err}")
    return out, err

# 1. Stop service
run("systemctl stop netlabs-ai", "1. Stop netlabs-ai")

# 2. Git pull
run(f"cd {REPO_PATH} && git pull origin main", "2. Git pull")

# 3. Reinstall pip deps
run(f"cd {AI_PATH} && venv/bin/pip install --upgrade pip", "3a. Upgrade pip")
run(f"cd {AI_PATH} && venv/bin/pip install -r requirements.txt", "3b. Install requirements")

# 4. Write .env
import os as _os
from dotenv import load_dotenv as _load_dotenv
_load_dotenv(_os.path.join(_os.path.dirname(_os.path.abspath(__file__)), "backend-ai", ".env"))
gemini_key = _os.getenv("GEMINI_API_KEY", "")

env_content = (
    f"GEMINI_API_KEY={gemini_key}\n"
    "QDRANT_PERSIST_DIR=./qdrant_data\n"
    "QDRANT_COLLECTION_NAME=basis_pengetahuan\n"
    "FLASK_PORT=5050\n"
    "FLASK_DEBUG=false\n"
)
run(f"printf '%s' '{env_content}' > {AI_PATH}/.env", "4. Write .env")

# 5. Chown
run(f"chown -R www-data:www-data {AI_PATH}", "5. Chown")

# 6. Restart service
run("systemctl daemon-reload", "6a. Daemon reload")
run("systemctl enable netlabs-ai", "6b. Enable")
run("systemctl restart netlabs-ai", "6c. Restart")

# 7. Wait & verify
print("\nWaiting 15s for service startup (model loading)...")
time.sleep(15)
run("systemctl is-active netlabs-ai", "7a. Service status")
run("curl -s http://localhost:5050/", "7b. Health check")

ssh.close()
print("\n[DONE] Deploy AI selesai!")