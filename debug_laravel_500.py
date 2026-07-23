import paramiko

VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(hostname=VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)

print("=== Laravel Last Log Errors ===")
stdin, stdout, stderr = ssh.exec_command("tail -n 40 /var/www/mynetlabs/backend-web/storage/logs/laravel.log")
print(stdout.read().decode('utf-8', errors='ignore'))

ssh.close()
