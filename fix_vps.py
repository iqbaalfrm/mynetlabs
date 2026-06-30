import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect("157.230.93.99", username="root", password="@Kodoka123ya")

commands = [
    # Fix Nginx config: replace literal {php_ver} with 8.4
    "sed -i 's/{php_ver}/8.4/g' /etc/nginx/sites-available/netlabs",
    "nginx -t",
    "systemctl restart nginx",
    # Fix Filament: langsung edit file di VPS (hapus readOnlyRelationManagersOnResourceViewPages)
    "sed -i '/readOnlyRelationManagersOnResourceViewPages/d' /var/www/mynetlabs/backend-web/app/Providers/Filament/AdminPanelProvider.php",
    # Run Laravel artisan commands
    "cd /var/www/mynetlabs/backend-web && php artisan config:clear",
    "cd /var/www/mynetlabs/backend-web && php artisan cache:clear",
    "cd /var/www/mynetlabs/backend-web && php artisan route:clear",
    "cd /var/www/mynetlabs/backend-web && php artisan view:clear",
    "cd /var/www/mynetlabs/backend-web && php artisan config:cache",
    "cd /var/www/mynetlabs/backend-web && php artisan route:cache",
    # Test API endpoint
    "curl -s -o /dev/null -w '%{http_code}' http://localhost/api/login -X POST -H 'Content-Type: application/json' -d '{\"username\":\"test\",\"password\":\"test\"}'",
    # Test AI backend
    "curl -s http://localhost:5050/",
]

for cmd in commands:
    print(f"\n>>> $ {cmd}")
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode()
    err = stderr.read().decode()
    if out.strip():
        print(out.strip())
    if err.strip():
        print(f"[stderr] {err.strip()}")

ssh.close()
print("\nDone!")