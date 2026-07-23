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

stdin, stdout, stderr = ssh.exec_command("curl -s http://localhost:5050/")
print("AI Service Health Check Output:")
print(stdout.read().decode('utf-8'))

ssh.close()
