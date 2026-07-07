# -*- coding: utf-8 -*-
"""
Script untuk membuat 12 PDF Modul Praktikum Jaringan Komputer secara otomatis.
Menggunakan Google Gemini API untuk menghasilkan konten pembelajaran yang sangat padat,
natural, mendalam (seperti modul buatan guru asli), dan berformat tepat 10 halaman.
"""

from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY
from reportlab.pdfgen import canvas
from dotenv import load_dotenv
import google.generativeai as genai
import os
import json
import re
import sys

# Load env dari backend-ai/.env
load_dotenv("backend-ai/.env")

GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
if not GEMINI_API_KEY:
    print("[ERROR] GEMINI_API_KEY tidak ditemukan di backend-ai/.env")
    sys.exit(1)

genai.configure(api_key=GEMINI_API_KEY)

OUTPUT_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), "storage_modul_pdf")
CACHE_DIR = os.path.join(OUTPUT_DIR, "cache_ai")
os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(CACHE_DIR, exist_ok=True)

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        canvas.Canvas.__init__(self, *args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            canvas.Canvas.showPage(self)
        canvas.Canvas.save(self)

    def draw_page_decorations(self, page_count):
        if self._pageNumber == 1:
            return
            
        self.saveState()
        
        # Header
        self.setFont("Helvetica-Bold", 8)
        self.setFillColor(HexColor("#1a237e"))
        self.drawString(2 * cm, A4[1] - 1.5 * cm, "NETLABS — PLATFORM PRAKTIKUM JARINGAN KOMPUTER SMK")
        
        self.setStrokeColor(HexColor("#1a237e"))
        self.setLineWidth(0.75)
        self.line(2 * cm, A4[1] - 1.7 * cm, A4[0] - 2 * cm, A4[1] - 1.7 * cm)
        
        # Footer
        self.setFont("Helvetica-Oblique", 8)
        self.setFillColor(HexColor("#757575"))
        self.drawString(2 * cm, 1.2 * cm, "Modul Resmi Praktikum NetLabs AI Tutor")
        
        self.setFont("Helvetica", 8)
        page_text = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(A4[0] - 2 * cm, 1.2 * cm, page_text)
        
        self.setStrokeColor(HexColor("#bdbdbd"))
        self.setLineWidth(0.5)
        self.line(2 * cm, 1.5 * cm, A4[0] - 2 * cm, 1.5 * cm)
        
        self.restoreState()


# Daftar Outline Modul Jaringan Komputer SMK TKJ
modul_outlines = [
    {
        "file_name": "Modul-01-Pengenalan-Jaringan.pdf",
        "pertemuan_id": 1,
        "judul": "Modul 1: Pengenalan Jaringan Komputer",
        "topik": "Konsep Dasar Jaringan, Sejarah ARPANET, Tipe Jaringan (PAN, LAN, MAN, WAN), dan Topologi Jaringan (Bus, Star, Ring, Mesh)",
    },
    {
        "file_name": "Modul-02-OSI-dan-TCPIP.pdf",
        "pertemuan_id": 2,
        "judul": "Modul 2: Model Referensi OSI & TCP/IP",
        "topik": "Pemahaman 7 Lapisan OSI Layer, 4 Lapisan TCP/IP, Perbandingan Protokol, dan Enkapsulasi / Dekapsulasi Data",
    },
    {
        "file_name": "Modul-03-IP-Address.pdf",
        "pertemuan_id": 3,
        "judul": "Modul 3: Pengalamatan IPv4",
        "topik": "Konsep IP Address, Struktur Bit IPv4, Pembagian Kelas A, B, C, serta IP Privat vs IP Publik menurut RFC 1918",
    },
    {
        "file_name": "Modul-04-CIDR-Subnetting.pdf",
        "pertemuan_id": 4,
        "judul": "Modul 4: Subnetting Dasar (CIDR)",
        "topik": "Perhitungan Subnetting Menggunakan Notasi CIDR dari prefix /24 hingga /30, Penentuan Network, Broadcast, dan Host Range",
    },
    {
        "file_name": "Modul-05-VLSM.pdf",
        "pertemuan_id": 5,
        "judul": "Modul 5: VLSM (Variable Length Subnet Mask)",
        "topik": "Teknik Subnetting VLSM Efisien Berdasarkan Kebutuhan Host Riil, Pengurutan Divisi, dan Pencegahan Overlapping IP",
    },
    {
        "file_name": "Modul-06-Kabel-UTP.pdf",
        "pertemuan_id": 6,
        "judul": "Modul 6: Media Transmisi Kabel UTP",
        "topik": "Standar EIA/TIA 568A dan 568B, Crimping Kabel RJ-45, Pembuatan Kabel Straight-Through dan Crossover, serta Pengujian LAN Tester",
    },
    {
        "file_name": "Modul-07-Static-Routing.pdf",
        "pertemuan_id": 7,
        "judul": "Modul 7: Perutean Statis (Static Routing)",
        "topik": "Konsep Perutean Manual, Tabel Routing Router, Perintah ip route Cisco, Administrative Distance, dan Rute Balik",
    },
    {
        "file_name": "Modul-08-OSPF.pdf",
        "pertemuan_id": 8,
        "judul": "Modul 8: Routing Dinamis OSPF",
        "topik": "Konsep Link-State OSPF, Algoritma Dijkstra, Pembagian Area (Area 0), Adjacency Neighbor, Wildcard Mask, dan Konfigurasi Router",
    },
    {
        "file_name": "Modul-09-DHCP.pdf",
        "pertemuan_id": 9,
        "judul": "Modul 9: DHCP (Dynamic Host Configuration Protocol)",
        "topik": "Konsep Alokasi IP Dinamis, Tahapan Proses DORA, Konfigurasi DHCP Server Router Cisco, dan DHCP IP Exclusion",
    },
    {
        "file_name": "Modul-10-NAT.pdf",
        "pertemuan_id": 10,
        "judul": "Modul 10: NAT (Network Address Translation)",
        "topik": "Teknologi Translasi IP Privat ke Publik, Konsep Static NAT, Dynamic NAT, PAT (NAT Overload), dan inside/outside interface",
    },
    {
        "file_name": "Modul-11-VLAN-Trunking.pdf",
        "pertemuan_id": 11,
        "judul": "Modul 11: VLAN dan Trunking",
        "topik": "Segmentasi Logis Switch Layer 2, Access vs Trunk Port, Enkapsulasi IEEE 802.1Q, dan Inter-VLAN Routing (Router-on-a-Stick)",
    },
    {
        "file_name": "Modul-12-ACL.pdf",
        "pertemuan_id": 12,
        "judul": "Modul 12: ACL (Access Control List)",
        "topik": "Aturan Penyaringan Paket Jaringan, Perbedaan Standard vs Extended ACL, Arah Traffic (IN/OUT), dan Implicit Deny All",
    }
]


def dapatkan_konten_ai(outline):
    cache_path = os.path.join(CACHE_DIR, outline["file_name"].replace(".pdf", ".json"))
    
    # Gunakan cache jika sudah ada untuk menghemat kuota token API
    if os.path.exists(cache_path):
        print(f"   [CACHE] Memuat konten dari cache untuk {outline['file_name']}")
        with open(cache_path, "r", encoding="utf-8") as f:
            return json.load(f)

    print(f"   [API] Meminta Google Gemini AI menyusun modul: {outline['judul']} ...")
    
    prompt = f"""
Anda adalah seorang Guru SMK Jurusan TKJ (Teknik Komputer & Jaringan) senior dan Praktisi Network Engineer bersertifikasi CCNA.
Tugas Anda adalah menulis konten modul praktikum yang sangat lengkap, padat materi, akademis, dan bernada mendidik untuk:
Topik Modul: {outline["topik"]}
Judul Modul: {outline["judul"]}
File Name: {outline["file_name"]}

Format keluaran harus berupa objek JSON dengan struktur kunci berikut:
{{
  "judul": "Modul X: ...",
  "subjudul": "Subjudul yang menggambarkan isi materi...",
  "tujuan": [
    "Tujuan 1 (jelaskan secara detail, formal, dan akademis)",
    "Tujuan 2...",
    "Tujuan 3...",
    "Tujuan 4...",
    "Tujuan 5..."
  ],
  "kompetensi_dasar": [
    "KD Pengetahuan (misal: KD 3.X Menganalisis...)",
    "KD Keterampilan (misal: KD 4.X Mengonfigurasikan...)"
  ],
  "alat_bahan": [
    "Alat 1 (PC/Laptop dengan spesifikasi tertentu)",
    "Alat 2 (Software Simulator Cisco Packet Tracer versi terbaru)",
    "Alat 3 (Modul/Internet)",
    "Alat 4..."
  ],
  "prasyarat": "Penjelasan prasyarat materi/pemahaman dasar yang harus dimiliki siswa sebelum mempelajari modul ini...",
  "teori_1": "Penjelasan teori dasar bagian 1 yang sangat mendalam dan panjang (minimal 3-4 paragraf padat). Gunakan tag HTML seperti <br/> dan <b> untuk penekanan/formatting penting. Harus berisi latar belakang sejarah, definisi terminologi secara fiqh muamalah jika ada hubungan (atau formal akademis jaringan), konsep utama, dan klasifikasinya. Panjang teks antara 250 - 300 kata.",
  "teori_2_text": "Penjelasan teori dasar bagian 2 (minimal 2 paragraf padat, panjang sekitar 150-200 kata) sebagai pengantar tabel teknis di bawahnya.",
  "teori_2_table": [
    ["Kolom 1 (Header)", "Kolom 2 (Header)", "Kolom 3 (Header)", "Kolom 4 (Header)"],
    ["Baris 1 Kolom 1", "Baris 1 Kolom 2", "Baris 1 Kolom 3", "Baris 1 Kolom 4"],
    ["Baris 2 Kolom 1", "Baris 2 Kolom 2", "Baris 2 Kolom 3", "Baris 2 Kolom 4"],
    ["Baris 3 ...", "...", "...", "..."],
    ["Baris 4 ...", "...", "...", "..."]
  ],
  "teori_2_extra": "Penjelasan teknis tambahan/catatan penting di bawah tabel teori (minimal 1 paragraf panjang sekitar 100 kata).",
  "topologi_desc": "Deskripsi terperinci mengenai topologi jaringan yang digunakan, koneksi fisik port switch/router (misalnya Fa0/1, Gig0/0), dan jenis kabel (Straight/Cross/Serial).",
  "addressing_table": [
    ["Perangkat", "Interface", "IP Address", "Subnet Mask", "Default Gateway"],
    ["Host1", "...", "...", "...", "..."],
    ["Host2", "...", "...", "...", "..."],
    ["Router1", "...", "...", "...", "..."],
    ["Switch1", "...", "...", "...", "..."]
  ],
  "skenario_desc": "Skenario lengkap dan cerita/studi kasus di lapangan mengapa topologi ini dibangun, dan apa tujuan/kasus operasional nyata yang ingin dicapai oleh praktikan (minimal 2 paragraf padat, sekitar 150 kata).",
  "langkah_kerja": [
    "Langkah 1 (Jelaskan secara detail tindakan pengerjaan dan verifikasi visual pada simulator)",
    "Langkah 2...",
    "Langkah 3...",
    "Langkah 4...",
    "Langkah 5...",
    "Langkah 6...",
    "Langkah 7...",
    "Langkah 8...",
    "Langkah 9..."
  ],
  "commands": "Kumpulan baris perintah CLI Cisco IOS atau perintah sistem operasi terkait yang lengkap dengan komentar penjelasan (gunakan tanda # di awal baris komentar). Buat panjang dan detail.",
  "uji_coba_text": "Penjelasan rinci mengenai tata cara pengujian konektivitas (seperti utilitas ping, traceroute, atau show commands) dan bagaimana menganalisis respon paket data.",
  "uji_coba_cmd": "Simulasi output terminal ketika pengujian sukses dilakukan (misal output utility ping atau show ip route).",
  "troubleshooting_table": [
    ["Gejala Masalah (Header)", "Kemungkinan Penyebab (Header)", "Tindakan Korektif (Header)"],
    ["Gejala 1", "Penyebab 1", "Solusi/Tindakan 1"],
    ["Gejala 2", "Penyebab 2", "Solusi/Tindakan 2"],
    ["Gejala 3", "Penyebab 3", "Solusi/Tindakan 3"]
  ],
  "tugas_mandiri": "Tantangan tugas mandiri individu berbobot untuk memperluas topologi praktikum (misal menambah 2 client dengan aturan VLAN tertentu atau konfigurasi ACL baru) yang wajib dilaporkan dalam bentuk laporan praktikum.",
  "evaluasi_questions": [
    "Pertanyaan analisis kritis 1 tentang konsep praktikum",
    "Pertanyaan analisis kritis 2...",
    "Pertanyaan analisis kritis 3...",
    "Pertanyaan analisis kritis 4...",
    "Pertanyaan analisis kritis 5..."
  ]
}}

Catatan penting:
- Gunakan Bahasa Indonesia formal yang baku (sesuai PUEBI/EYD) dan terminologi jaringan komputer yang tepat.
- Pastikan semua penjelasan diisi dengan detail teknis nyata (misal port FastEthernet0/1, GigabitEthernet0/0, dll.), bukan penjelasan generik atau kosong.
- Konten harus sangat padat dan mendalam agar modul terlihat bernilai akademis tinggi dan menyerupai modul buatan guru/instruktur profesional.
- Batasi jumlah baris pada tabel teori dan tabel pengalamatan agar tidak melebihi batas tinggi halaman A4 (masing-masing tabel maksimal 5-6 baris).
- Kembalikan HANYA JSON objek yang valid sesuai dengan spesifikasi di atas. Jangan tambahkan penjelasan pembuka atau penutup di luar objek JSON tersebut. Jangan dibungkus dengan markdown ```json.
"""

    # Coba beberapa model jika terjadi rate limit (bypassing pool quota)
    models_to_try = ["gemini-2.5-flash", "gemini-1.5-flash"]
    current_model_idx = 0

    # Coba hingga 6 kali jika ada kegagalan parsing JSON atau kuota terlampaui
    for attempt in range(6):
        try:
            model_name = models_to_try[current_model_idx]
            model = genai.GenerativeModel(model_name)
            response = model.generate_content(
                prompt,
                generation_config={"response_mime_type": "application/json"}
            )
            raw_text = response.text.strip()
            
            # Parsing ke dictionary
            data = json.loads(raw_text)
            
            # Simpan ke cache
            with open(cache_path, "w", encoding="utf-8") as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
                
            return data
        except Exception as e:
            error_msg = str(e)
            print(f"      [WARNING] Gagal pada percobaan {attempt+1} dengan model {models_to_try[current_model_idx]}: {error_msg}")
            
            if "429" in error_msg or "quota" in error_msg.lower() or "limit" in error_msg.lower():
                current_model_idx = (current_model_idx + 1) % len(models_to_try)
                print(f"      [RATE LIMIT] Kuota terlampaui. Beralih ke model {models_to_try[current_model_idx]} dan menunggu 35 detik...")
                import time
                time.sleep(35)
            else:
                print("      Menunggu 5 detik sebelum mencoba kembali...")
                import time
                time.sleep(5)
            
    print(f"   [ERROR] Gagal mendapatkan konten berkualitas untuk {outline['file_name']} setelah 6 kali mencoba.")
    sys.exit(1)




def buat_modul_pdf(data, file_name):
    file_path = os.path.join(OUTPUT_DIR, file_name)
    
    # Setup document template with 2.5cm margin top/bottom to clear headers/footers
    doc = SimpleDocTemplate(
        file_path,
        pagesize=A4,
        rightMargin=2 * cm,
        leftMargin=2 * cm,
        topMargin=2.5 * cm,
        bottomMargin=2.5 * cm,
    )

    styles = getSampleStyleSheet()

    # Cover Page Styles
    style_cover_kategori = ParagraphStyle(
        "CoverKategori", parent=styles["Normal"],
        fontSize=12, leading=16, spaceAfter=20, textColor=HexColor("#3949ab"),
        alignment=TA_CENTER, fontName="Helvetica-Bold",
    )
    style_cover_judul = ParagraphStyle(
        "CoverJudul", parent=styles["Normal"],
        fontSize=18, leading=24, textColor=HexColor("#ffffff"),
        alignment=TA_CENTER, fontName="Helvetica-Bold",
    )
    style_cover_subjudul = ParagraphStyle(
        "CoverSubJudul", parent=styles["Normal"],
        fontSize=11, leading=16, spaceAfter=20, textColor=HexColor("#424242"),
        alignment=TA_CENTER, fontName="Helvetica",
    )
    style_cover_meta = ParagraphStyle(
        "CoverMeta", parent=styles["Normal"],
        fontSize=10, leading=14, spaceAfter=8, textColor=HexColor("#212121"),
        alignment=TA_CENTER,
    )
    style_cover_footer = ParagraphStyle(
        "CoverFooter", parent=styles["Normal"],
        fontSize=10, leading=14, textColor=HexColor("#0d47a1"),
        alignment=TA_CENTER, fontName="Helvetica-Bold",
    )

    # General page styles
    style_judul_halaman = ParagraphStyle(
        "JudulHalaman", parent=styles["Heading2"],
        fontSize=13, leading=16, spaceAfter=12, spaceBefore=0,
        textColor=HexColor("#1a237e"), fontName="Helvetica-Bold",
    )
    style_heading = ParagraphStyle(
        "Heading", parent=styles["Heading3"],
        fontSize=10.5, leading=14, spaceAfter=6, spaceBefore=8,
        textColor=HexColor("#283593"), fontName="Helvetica-Bold",
    )
    style_body = ParagraphStyle(
        "BodyCustom", parent=styles["Normal"],
        fontSize=9.5, leading=13.5, spaceAfter=6,
        alignment=TA_JUSTIFY,
    )
    style_code = ParagraphStyle(
        "CodeCustom", parent=styles["Code"],
        fontSize=8.5, leading=11, spaceAfter=8,
        backColor=HexColor("#f5f5f5"),
        borderColor=HexColor("#e0e0e0"),
        borderWidth=0.5,
        borderPadding=5,
        fontName="Courier",
    )
    style_catatan = ParagraphStyle(
        "CatatanCustom", parent=styles["Normal"],
        fontSize=8.5, leading=12, spaceAfter=8,
        leftIndent=15, textColor=HexColor("#e65100"),
        fontName="Helvetica-Oblique",
    )

    elements = []

    # ================= HALAMAN 1: COVER =================
    elements.append(Spacer(1, 3 * cm))
    elements.append(Paragraph("MODUL PRAKTIKUM JARINGAN KOMPUTER", style_cover_kategori))
    elements.append(Spacer(1, 0.5 * cm))
    
    # Title table with dark blue background
    title_data = [[Paragraph(data["judul"].upper(), style_cover_judul)]]
    title_table = Table(title_data, colWidths=[A4[0] - 4 * cm])
    title_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), HexColor("#1a237e")),
        ('TOPPADDING', (0,0), (-1,-1), 22),
        ('BOTTOMPADDING', (0,0), (-1,-1), 22),
        ('LEFTPADDING', (0,0), (-1,-1), 15),
        ('RIGHTPADDING', (0,0), (-1,-1), 15),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
    ]))
    elements.append(title_table)
    elements.append(Spacer(1, 1.2 * cm))
    elements.append(Paragraph(data["subjudul"], style_cover_subjudul))
    elements.append(Spacer(1, 4 * cm))
    
    elements.append(Paragraph("<b>Disusun Oleh:</b> Tim Kurikulum NetLabs AI", style_cover_meta))
    elements.append(Paragraph("<b>Target Pengguna:</b> Siswa SMK Jurusan TKJ (Teknik Komputer & Jaringan)", style_cover_meta))
    elements.append(Paragraph("<b>Versi Dokumen:</b> 2026.1 (Edisi Lengkap)", style_cover_meta))
    elements.append(Spacer(1, 2.5 * cm))
    elements.append(Paragraph("<b>LABORATORIUM JARINGAN KOMPUTER NETLABS</b>", style_cover_footer))
    elements.append(PageBreak())

    # ================= HALAMAN 2: TATA TERTIB & K3 =================
    elements.append(Paragraph("TATA TERTIB & KESELAMATAN KERJA LABORATORIUM JARINGAN", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    elements.append(Paragraph(
        "Demi kenyamanan, keamanan, dan keselamatan seluruh praktikan di Laboratorium Jaringan Komputer NetLabs, "
        "berikut adalah tata tertib dan aturan keselamatan kerja yang wajib ditaati:",
        style_body
    ))
    elements.append(Paragraph("<b>1. Perilaku Umum Praktikan:</b>", style_heading))
    elements.append(Paragraph(
        "• Praktikan wajib hadir 10 menit sebelum waktu praktikum dimulai.<br/>"
        "• Dilarang keras membawa makanan, minuman, atau senjata tajam ke dalam ruang laboratorium.<br/>"
        "• Menjaga kebersihan meja kerja dan merapikan kembali kursi setelah selesai praktikum.<br/>"
        "• Dilarang memindahkan atau mengambil komponen hardware tanpa izin tertulis dari instruktur lab.",
        style_body
    ))
    elements.append(Paragraph("<b>2. Keselamatan Kerja & Kelistrikan:</b>", style_heading))
    elements.append(Paragraph(
        "• Periksa semua kabel daya dan kabel jaringan sebelum menyalakan PC atau perangkat Router/Switch.<br/>"
        "• Jangan menyentuh soket listrik dengan tangan basah atau menggunakan kabel daya yang terkelupas.<br/>"
        "• Jika terjadi percikan api atau tercium bau terbakar, segera matikan tombol power pusat dan laporkan kepada pengawas.<br/>"
        "• Pastikan grounding pada instalasi listrik laboratorium berfungsi dengan baik guna menghindari sengatan listrik statis.",
        style_body
    ))
    elements.append(Paragraph("<b>3. Etika Penggunaan Software & Lisensi:</b>", style_heading))
    elements.append(Paragraph(
        "• Dilarang menginstal software bajakan atau program yang tidak relevan dengan praktikum (seperti game).<br/>"
        "• Gunakan emulator atau simulator (Cisco Packet Tracer/GNS3) sesuai dengan petunjuk pengerjaan langkah praktis.<br/>"
        "• Dilarang mengubah alamat IP atau konfigurasi jaringan komputer utama laboratorium tanpa persetujuan instruktur.",
        style_body
    ))
    elements.append(Spacer(1, 0.3 * cm))
    elements.append(Paragraph("<i>Pelanggaran terhadap aturan di atas akan dikenai sanksi berupa pengurangan nilai praktikum hingga larangan mengikuti praktikum selanjutnya.</i>", style_catatan))
    elements.append(PageBreak())

    # ================= HALAMAN 3: TUJUAN & ALAT BAHAN =================
    elements.append(Paragraph("TUJUAN PRAKTIKUM & PERSIAPAN ALAT / BAHAN", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    
    elements.append(Paragraph("<b>1. Tujuan Instruksional Khusus (TIK):</b>", style_heading))
    tujuan_p = "<br/>".join([f"• {t}" for t in data["tujuan"]])
    elements.append(Paragraph(tujuan_p, style_body))
    
    elements.append(Paragraph("<b>2. Kompetensi Dasar (KD) Terkait:</b>", style_heading))
    kd_p = "<br/>".join([f"• {k}" for k in data["kompetensi_dasar"]])
    elements.append(Paragraph(kd_p, style_body))
    
    elements.append(Paragraph("<b>3. Alat dan Bahan Praktikum:</b>", style_heading))
    alat_p = "<br/>".join([f"• [ ] {a}" for a in data["alat_bahan"]])
    elements.append(Paragraph(alat_p, style_body))
    
    elements.append(Spacer(1, 0.3 * cm))
    elements.append(Paragraph("<b>Prasyarat Teori:</b>", style_heading))
    elements.append(Paragraph(data["prasyarat"], style_body))
    elements.append(PageBreak())

    # ================= HALAMAN 4: TEORI DASAR I =================
    elements.append(Paragraph("TEORI DASAR I — KONSEP DAN MODEL TEKNOLOGI", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    elements.append(Paragraph(data["teori_1"], style_body))
    elements.append(PageBreak())

    # ================= HALAMAN 5: TEORI DASAR II =================
    elements.append(Paragraph("TEORI DASAR II — ARSITEKTUR TEKNIS DAN DATA", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    elements.append(Paragraph(data["teori_2_text"], style_body))
    elements.append(Spacer(1, 0.2 * cm))
    
    # Display data table
    formatted_table = []
    for row_idx, row in enumerate(data["teori_2_table"]):
        formatted_row = []
        for col_idx, col in enumerate(row):
            if row_idx == 0:
                p_style = ParagraphStyle("TableHeader", parent=style_body, fontName="Helvetica-Bold", alignment=TA_CENTER, textColor=HexColor("#ffffff"), fontSize=8.5)
            else:
                p_style = ParagraphStyle("TableCell", parent=style_body, fontSize=8.5, leading=11)
            formatted_row.append(Paragraph(col, p_style))
        formatted_table.append(formatted_row)
      
    num_cols = len(data["teori_2_table"][0])
    col_width = (A4[0] - 4 * cm) / num_cols
    
    t_teori = Table(formatted_table, colWidths=[col_width]*num_cols)
    t_teori.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), HexColor("#1a237e")),
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('GRID', (0,0), (-1,-1), 0.5, HexColor("#bdbdbd")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [HexColor("#f5f5f5"), HexColor("#ffffff")]),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
    ]))
    elements.append(t_teori)
    
    if "teori_2_extra" in data:
        elements.append(Spacer(1, 0.4 * cm))
        elements.append(Paragraph(data["teori_2_extra"], style_body))
      
    elements.append(PageBreak())

    # ================= HALAMAN 6: TOPOLOGI & SKENARIO =================
    elements.append(Paragraph("TOPOLOGI JARINGAN DAN SKENARIO PRAKTIKUM", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    elements.append(Paragraph("<b>1. Deskripsi Topologi Jaringan:</b>", style_heading))
    elements.append(Paragraph(data["topologi_desc"], style_body))
    elements.append(Spacer(1, 0.2 * cm))
    
    elements.append(Paragraph("<b>2. Tabel Pengalamatan IP (Addressing Table):</b>", style_heading))
    
    formatted_addr = []
    for row_idx, row in enumerate(data["addressing_table"]):
        formatted_row = []
        for col_idx, col in enumerate(row):
            if row_idx == 0:
                p_style = ParagraphStyle("AddrHeader", parent=style_body, fontName="Helvetica-Bold", alignment=TA_CENTER, textColor=HexColor("#ffffff"), fontSize=8.5)
            else:
                p_style = ParagraphStyle("AddrCell", parent=style_body, fontSize=8.5, leading=11, alignment=TA_CENTER)
            formatted_row.append(Paragraph(col, p_style))
        formatted_addr.append(formatted_row)
      
    num_cols_addr = len(data["addressing_table"][0])
    col_width_addr = (A4[0] - 4 * cm) / num_cols_addr
    
    t_addr = Table(formatted_addr, colWidths=[col_width_addr]*num_cols_addr)
    t_addr.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), HexColor("#311b92")),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('GRID', (0,0), (-1,-1), 0.5, HexColor("#bdbdbd")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [HexColor("#ede7f6"), HexColor("#ffffff")]),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
    ]))
    elements.append(t_addr)
    
    elements.append(Spacer(1, 0.2 * cm))
    elements.append(Paragraph("<b>3. Skenario Pekerjaan:</b>", style_heading))
    elements.append(Paragraph(data["skenario_desc"], style_body))
    elements.append(PageBreak())

    # ================= HALAMAN 7: PROSEDUR KONFIGURASI =================
    elements.append(Paragraph("PROSEDUR KONFIGURASI LANGKAH DEMI LANGKAH", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    
    # Steps loop
    for idx, step in enumerate(data["langkah_kerja"]):
        elements.append(Paragraph(f"<b>Langkah {idx+1}:</b> {step}", style_body))
        elements.append(Spacer(1, 0.05 * cm))
      
    # Show commands codeblock if present
    if "commands" in data and data["commands"]:
        elements.append(Spacer(1, 0.2 * cm))
        elements.append(Paragraph("<b>Baris Kode / Konfigurasi CLI Cisco IOS:</b>", style_heading))
        elements.append(Paragraph(data["commands"].replace("\n", "<br/>"), style_code))
      
    elements.append(PageBreak())

    # ================= HALAMAN 8: UJI COBA & TROUBLESHOOTING =================
    elements.append(Paragraph("PROSEDUR UJI COBA DAN TROUBLESHOOTING", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    
    elements.append(Paragraph("<b>1. Pengujian Konektivitas (Verification):</b>", style_heading))
    elements.append(Paragraph(data["uji_coba_text"], style_body))
    
    if "uji_coba_cmd" in data and data["uji_coba_cmd"]:
        elements.append(Paragraph(data["uji_coba_cmd"].replace("\n", "<br/>"), style_code))
      
    elements.append(Paragraph("<b>2. Panduan Pemecahan Masalah (Troubleshooting Guide):</b>", style_heading))
    
    formatted_tb = []
    for row_idx, row in enumerate(data["troubleshooting_table"]):
        formatted_row = []
        for col_idx, col in enumerate(row):
            if row_idx == 0:
                p_style = ParagraphStyle("TbHeader", parent=style_body, fontName="Helvetica-Bold", alignment=TA_CENTER, textColor=HexColor("#ffffff"), fontSize=8)
            else:
                p_style = ParagraphStyle("TbCell", parent=style_body, fontSize=8, leading=10.5)
            formatted_row.append(Paragraph(col, p_style))
        formatted_tb.append(formatted_row)
      
    num_cols_tb = len(data["troubleshooting_table"][0])
    col_width_tb = (A4[0] - 4 * cm) / num_cols_tb
    
    t_tb = Table(formatted_tb, colWidths=[col_width_tb]*num_cols_tb)
    t_tb.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), HexColor("#b71c1c")),
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('GRID', (0,0), (-1,-1), 0.5, HexColor("#bdbdbd")),
        ('ROWBACKGROUNDS', (0,1), (-1,-1), [HexColor("#ffebee"), HexColor("#ffffff")]),
        ('TOPPADDING', (0,0), (-1,-1), 4),
        ('BOTTOMPADDING', (0,0), (-1,-1), 4),
    ]))
    elements.append(t_tb)
    elements.append(PageBreak())

    # ================= HALAMAN 9: LEMBAR EVALUASI =================
    elements.append(Paragraph("TUGAS MANDIRI DAN PERTANYAAN EVALUASI", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    
    elements.append(Paragraph("<b>1. Tugas Mandiri Praktikan:</b>", style_heading))
    elements.append(Paragraph(data["tugas_mandiri"], style_body))
    
    elements.append(Paragraph("<b>2. Pertanyaan Evaluasi Jaringan:</b>", style_heading))
    for idx, q in enumerate(data["evaluasi_questions"]):
        elements.append(Paragraph(f"{idx+1}. {q}", style_body))
        elements.append(Spacer(1, 0.05 * cm))
      
    elements.append(Spacer(1, 0.3 * cm))
    elements.append(Paragraph("<i>Petunjuk Laporan: Kerjakan Tugas Mandiri di atas secara individu. Ambil screenshot topologi dan hasil ping, kemudian lampirkan pada Laporan Praktikum yang dikumpulkan minggu depan.</i>", style_catatan))
    elements.append(PageBreak())

    # ================= HALAMAN 10: LEMBAR PENILAIAN =================
    elements.append(Paragraph("LEMBAR PENILAIAN PRAKTIKUM GURU/INSTRUKTUR", style_judul_halaman))
    elements.append(Spacer(1, 0.2 * cm))
    elements.append(Paragraph(
        "Halaman ini digunakan oleh instruktur atau guru pengampu untuk mencatat skor pencapaian "
        "praktikan berdasarkan kriteria penilaian kinerja yang telah ditentukan di bawah ini.",
        style_body
    ))
    
    penilaian_data = [
        ["No", "Aspek Penilaian", "Bobot", "Skor (0-100)", "Nilai Akhir (Skor x Bobot)"],
        ["1", "Sikap & Kehadiran di Laboratorium", "10%", "", ""],
        ["2", "Pemahaman Teori & Evaluasi Mandiri", "20%", "", ""],
        ["3", "Penyusunan & Konfigurasi Topologi", "40%", "", ""],
        ["4", "Troubleshooting & Pengujian Jaringan", "20%", "", ""],
        ["5", "Kerapian Laporan Praktikum", "10%", "", ""],
        ["", "<b>TOTAL NILAI AKHIR</b>", "100%", "", ""]
    ]
    
    formatted_pn = []
    for row_idx, row in enumerate(penilaian_data):
        formatted_row = []
        for col_idx, col in enumerate(row):
            if row_idx == 0:
                p_style = ParagraphStyle("PnHeader", parent=style_body, fontName="Helvetica-Bold", alignment=TA_CENTER, textColor=HexColor("#ffffff"), fontSize=8.5)
            else:
                p_style = ParagraphStyle("PnCell", parent=style_body, fontSize=8.5, leading=11, fontName="Helvetica-Bold" if row_idx == len(penilaian_data)-1 else "Helvetica")
            formatted_row.append(Paragraph(col, p_style))
        formatted_pn.append(formatted_row)
      
    t_pn = Table(formatted_pn, colWidths=[1*cm, 7.5*cm, 2*cm, 2.5*cm, 3*cm])
    t_pn.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), HexColor("#0d47a1")),
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
        ('GRID', (0,0), (-1,-1), 0.5, HexColor("#bdbdbd")),
        ('BACKGROUND', (0,-1), (-1,-1), HexColor("#e3f2fd")),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
    ]))
    elements.append(t_pn)
  
    elements.append(Spacer(1, 1.2 * cm))
  
    # Signature Blocks
    sig_data = [
        ["Praktikan,", "Guru / Instruktur Lab,"],
        ["\n\n\n...................................................", "\n\n\n..................................................."],
        ["NIS:", "NIP / NUPTK:"]
    ]
    t_sig = Table(sig_data, colWidths=[8*cm, 8*cm])
    t_sig.setStyle(TableStyle([
        ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ('VALIGN', (0,0), (-1,-1), 'BOTTOM'),
        ('BOTTOMPADDING', (0,1), (-1,1), 8),
    ]))
    elements.append(t_sig)

    doc.build(elements, canvasmaker=NumberedCanvas)
    print(f"SUCCESS: Modul PDF berhasil dibuat: {file_name}")


if __name__ == "__main__":
    print("[START] Mulai mengunduh konten dari Gemini AI & membuat 12 PDF Modul...")
    for outline in modul_outlines:
        # 1. Dapatkan konten berkualitas tinggi dari Gemini API
        data_konten = dapatkan_konten_ai(outline)
        
        # 2. Buat PDF dari konten tersebut
        buat_modul_pdf(data_konten, outline["file_name"])
        
    print("\n[OK] Sukses membuat 12 file PDF modul praktikum hasil scraping AI dengan format 10 halaman!")
