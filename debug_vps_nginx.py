import paramiko

VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(hostname=VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)

print("=== Nginx Config ===")
stdin, stdout, stderr = ssh.exec_command("cat /etc/nginx/sites-enabled/default /etc/nginx/sites-enabled/* 2>/dev/null")
print(stdout.read().decode('utf-8'))

print("=== AI Service Status ===")
stdin, stdout, stderr = ssh.exec_command("systemctl status netlabs-ai --no-pager -l")
print(stdout.read().decode('utf-8'))

ssh.close()
