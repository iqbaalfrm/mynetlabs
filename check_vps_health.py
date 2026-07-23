import paramiko
import sys

sys.stdout.reconfigure(encoding='utf-8')

VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(hostname=VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)

print("=== 1. Cek Status Service AI (netlabs-ai) ===")
stdin, stdout, stderr = ssh.exec_command("systemctl status netlabs-ai --no-pager -l")
print(stdout.read().decode('utf-8'))

print("=== 2. Cek Health Check Endpoint AI (Port 5050) ===")
stdin, stdout, stderr = ssh.exec_command("curl -s http://localhost:5050/")
print(stdout.read().decode('utf-8'))

print("\n=== 3. Cek Status Nginx ===")
stdin, stdout, stderr = ssh.exec_command("systemctl is-active nginx")
print("Nginx status:", stdout.read().decode('utf-8').strip())

print("\n=== 4. Cek Log Error AI Terbaru (15 baris terakhir) ===")
stdin, stdout, stderr = ssh.exec_command("journalctl -u netlabs-ai -n 15 --no-pager")
print(stdout.read().decode('utf-8'))

ssh.close()
