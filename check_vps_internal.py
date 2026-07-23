import paramiko

VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(hostname=VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)

print("=== 1. AI Health Internal ===")
stdin, stdout, stderr = ssh.exec_command("curl -s http://127.0.0.1:5050/")
print(stdout.read().decode('utf-8'))

print("=== 2. Nginx Status ===")
stdin, stdout, stderr = ssh.exec_command("systemctl status nginx --no-pager")
print(stdout.read().decode('utf-8')[:300])

print("=== 3. AI Service Active Status ===")
stdin, stdout, stderr = ssh.exec_command("systemctl is-active netlabs-ai")
print(stdout.read().decode('utf-8').strip())

ssh.close()
