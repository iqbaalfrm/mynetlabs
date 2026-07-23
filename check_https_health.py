import urllib.request
import ssl
import json

ctx = ssl.create_default_context()

print("=== 1. Cek HTTPS Web / API (netlabs.web.id/api/pertemuan) ===")
try:
    req = urllib.request.urlopen("https://netlabs.web.id/api/pertemuan", context=ctx)
    data = json.loads(req.read().decode('utf-8'))
    print("Status:", req.getcode())
    print("Jumlah pertemuan:", len(data.get('data', [])))
except Exception as e:
    print("Error:", e)

print("\n=== 2. Cek HTTPS AI Proxy (netlabs.web.id/ai-api/) ===")
try:
    req = urllib.request.urlopen("https://netlabs.web.id/ai-api/", context=ctx)
    data = json.loads(req.read().decode('utf-8'))
    print("Status:", req.getcode())
    print("Service AI:", data.get('service'))
    print("Status AI:", data.get('status'))
except Exception as e:
    print("Error:", e)
