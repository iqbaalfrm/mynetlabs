"""Check if Authenticate middleware exists."""
import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('157.230.93.99', port=22, username='root', password='@Kodoka123ya')

stdin, stdout, stderr = ssh.exec_command(
    'cat /var/www/mynetlabs/backend-web/app/Http/Middleware/Authenticate.php 2>&1'
)
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print("STDERR:", err)
ssh.close()