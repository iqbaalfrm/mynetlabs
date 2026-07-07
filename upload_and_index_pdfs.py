import os
import paramiko
import json
import time

VPS_IP = "157.230.93.99"
VPS_PORT = 22
VPS_USER = "root"
VPS_PASS = "@Kodoka123ya"

LOCAL_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "storage_modul_pdf")
REMOTE_DIR = "/var/www/mynetlabs/backend-web/storage/app/public/modul_pdf"

# Daftar file PDF dengan pertemuan_id masing-masing
pdf_files = [
    {"pertemuan_id": 1, "file_name": "Modul-01-Pengenalan-Jaringan.pdf"},
    {"pertemuan_id": 2, "file_name": "Modul-02-OSI-dan-TCPIP.pdf"},
    {"pertemuan_id": 3, "file_name": "Modul-03-IP-Address.pdf"},
    {"pertemuan_id": 4, "file_name": "Modul-04-CIDR-Subnetting.pdf"},
    {"pertemuan_id": 5, "file_name": "Modul-05-VLSM.pdf"},
    {"pertemuan_id": 6, "file_name": "Modul-06-Kabel-UTP.pdf"},
    {"pertemuan_id": 7, "file_name": "Modul-07-Static-Routing.pdf"},
    {"pertemuan_id": 8, "file_name": "Modul-08-OSPF.pdf"},
    {"pertemuan_id": 9, "file_name": "Modul-09-DHCP.pdf"},
    {"pertemuan_id": 10, "file_name": "Modul-10-NAT.pdf"},
    {"pertemuan_id": 11, "file_name": "Modul-11-VLAN-Trunking.pdf"},
    {"pertemuan_id": 12, "file_name": "Modul-12-ACL.pdf"},
]

def main():
    # 1. Hubungkan SSH & SFTP
    print("Connecting to VPS SSH...")
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(VPS_IP, port=VPS_PORT, username=VPS_USER, password=VPS_PASS)
    print("[OK] SSH Connected.")

    sftp = ssh.open_sftp()
    print("[OK] SFTP Session Opened.")

    # 2. Pastikan direktori tujuan ada di VPS
    try:
        sftp.mkdir(REMOTE_DIR)
        print(f"Created remote directory: {REMOTE_DIR}")
    except IOError:
        print(f"Remote directory already exists: {REMOTE_DIR}")

    # 3. Upload & Index setiap file
    for item in pdf_files:
        pertemuan_id = item["pertemuan_id"]
        file_name = item["file_name"]
        
        local_path = os.path.join(LOCAL_DIR, file_name)
        remote_path = f"{REMOTE_DIR}/{file_name}"
        
        if not os.path.exists(local_path):
            print(f"[ERROR] File lokal tidak ditemukan: {local_path}")
            continue

        print(f"\n>>> Mengunggah {file_name}...")
        sftp.put(local_path, remote_path)
        print(f"[OK] Berhasil mengunggah ke {remote_path}")

        # Atur ownership & permission agar www-data bisa mengaksesnya
        ssh.exec_command(f"chown www-data:www-data '{remote_path}'")
        ssh.exec_command(f"chmod 664 '{remote_path}'")

        # Jalankan indexing via Curl ke Flask AI Backend di VPS
        payload = {
            "pertemuan_id": pertemuan_id,
            "file_path": remote_path
        }
        payload_str = json.dumps(payload).replace('"', '\\"')
        
        curl_cmd = f"curl -s -X POST -H 'Content-Type: application/json' -d \"{payload_str}\" http://localhost:5050/index-pdf"
        print(f"Jalankan indexing untuk pertemuan_id {pertemuan_id}...")
        
        stdin, stdout, stderr = ssh.exec_command(curl_cmd)
        res_out = stdout.read().decode().strip()
        res_err = stderr.read().decode().strip()
        
        if res_out:
            print(f"Respon: {res_out}")
        if res_err:
            print(f"Stderr: {res_err}")

    # 4. Ambil statistik dokumen di Qdrant pasca indexing
    print("\n" + "=" * 60)
    print("Verifikasi Total Dokumen di Qdrant:")
    stdin, stdout, stderr = ssh.exec_command("curl -s http://localhost:5050/")
    print(stdout.read().decode().strip())
    print("=" * 60)

    sftp.close()
    ssh.close()
    print("\n[DONE] Semua file PDF modul praktikum berhasil diunggah dan di-index ke Qdrant!")

if __name__ == "__main__":
    main()
