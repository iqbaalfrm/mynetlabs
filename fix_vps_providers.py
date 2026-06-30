"""Fix PailServiceProvider in bootstrap/providers.php on VPS."""
import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('157.230.93.99', port=22, username='root', password='@Kodoka123ya')

# Remove PailServiceProvider reference and clear caches
cmds = [
    "cd /var/www/mynetlabs/backend-web",
    # Comment out PailServiceProvider
    "sed -i 's/Laravel\\\\Pail\\\\PailServiceProvider::class,/\\/\\/ PailServiceProvider removed/' bootstrap/providers.php",
    "php artisan config:clear 2>&1",
    "php artisan route:clear 2>&1",
    "php artisan view:clear 2>&1",
    "echo ALLDONE",
]
cmd = " && ".join(cmds)
stdin, stdout, stderr = ssh.exec_command(cmd)
print(stdout.read().decode())
err = stderr.read().decode()
if err:
    print("STDERR:", err)
ssh.close()