import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('157.230.93.99', port=22, username='root', password='@Kodoka123ya')

# Get last error entry from laravel log
stdin, stdout, stderr = ssh.exec_command('grep -A3 "admin/dashboard" /var/www/mynetlabs/backend-web/storage/logs/laravel.log 2>/dev/null | tail -20')
print("=== DASHBOARD ERRORS ===")
print(stdout.read().decode())
print(stderr.read().decode())

# Also check PHP-FPM error log
stdin, stdout, stderr = ssh.exec_command('tail -20 /var/log/php8.4-fpm.log 2>/dev/null || echo NO_FPM_LOG')
print("=== PHP-FPM LOG ===")
print(stdout.read().decode())
print(stderr.read().decode())

# Try to get the actual error by triggering it
stdin, stdout, stderr = ssh.exec_command('curl -s -o /dev/null -w "%{http_code}" http://localhost/admin/dashboard')
print("=== LOCAL CURL STATUS ===")
print(stdout.read().decode())
print(stderr.read().decode())

ssh.close()