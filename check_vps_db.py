import paramiko
import sys

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('157.230.93.99', port=22, username='root', password='@Kodoka123ya')

def safe_print(text):
    try:
        print(text)
    except UnicodeEncodeError:
        enc = getattr(sys.stdout, "encoding", "utf-8") or "utf-8"
        print(text.encode(enc, errors="replace").decode(enc, errors="replace"))

def run(cmd, title):
    safe_print(f"=== {title} ===")
    stdin, stdout, stderr = ssh.exec_command(cmd)
    out = stdout.read().decode('utf-8', errors='replace')
    err = stderr.read().decode('utf-8', errors='replace')
    safe_print(out)
    if err:
        safe_print("STDERR: " + err)
    safe_print("\n")

# Show journalctl logs of netlabs-ai service for errors
run("journalctl -u netlabs-ai --no-pager | grep -i -E \"error|fail|exception\" | tail -n 20", "AI Service Error Logs")

# Test calling the AI chat endpoint locally on VPS
run("curl -s -X POST -H 'Content-Type: application/json' -d '{\"message\":\"test\"}' http://localhost:5050/chat", "Test Chat Endpoint Local Curl")

ssh.close()
