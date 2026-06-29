# -*- coding: utf-8 -*-
"""
Script untuk membuat PDF Materi Jaringan Komputer dan Subnetting.
"""

from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY
import os

OUTPUT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_FILE = os.path.join(OUTPUT_DIR, "materi_jaringan_komputer.pdf")


def buat_pdf():
    doc = SimpleDocTemplate(
        OUTPUT_FILE,
        pagesize=A4,
        rightMargin=2 * cm,
        leftMargin=2 * cm,
        topMargin=2 * cm,
        bottomMargin=2 * cm,
    )

    styles = getSampleStyleSheet()

    # Custom styles
    style_judul = ParagraphStyle(
        "Judul", parent=styles["Title"],
        fontSize=20, spaceAfter=20, textColor=HexColor("#1a237e"),
        alignment=TA_CENTER,
    )
    style_subjudul = ParagraphStyle(
        "SubJudul", parent=styles["Heading2"],
        fontSize=14, spaceAfter=10, spaceBefore=16,
        textColor=HexColor("#283593"),
    )
    style_heading3 = ParagraphStyle(
        "Heading3Custom", parent=styles["Heading3"],
        fontSize=12, spaceAfter=8, spaceBefore=12,
        textColor=HexColor("#3949ab"),
    )
    style_body = ParagraphStyle(
        "BodyCustom", parent=styles["Normal"],
        fontSize=11, leading=16, spaceAfter=8,
        alignment=TA_JUSTIFY,
    )
    style_code = ParagraphStyle(
        "Code", parent=styles["Code"],
        fontSize=10, leading=14, spaceAfter=8,
        backColor=HexColor("#f5f5f5"),
        borderColor=HexColor("#e0e0e0"),
        borderWidth=1,
        borderPadding=8,
    )
    style_catatan = ParagraphStyle(
        "Catatan", parent=styles["Normal"],
        fontSize=10, leading=14, spaceAfter=8,
        leftIndent=20, textColor=HexColor("#e65100"),
        fontName="Helvetica-Oblique",
    )

    elements = []

    # ===== HALAMAN COVER =====
    elements.append(Spacer(1, 3 * cm))
    elements.append(Paragraph("MODUL PEMBELAJARAN", style_judul))
    elements.append(Paragraph("JARINGAN KOMPUTER & SUBNETTING", style_judul))
    elements.append(Spacer(1, 1 * cm))
    elements.append(Paragraph(
        "Konsep Dasar IP Address, Pengenalan OSI Layer, dan Perhitungan Subnetting IPv4",
        ParagraphStyle("SubCover", parent=styles["Heading2"], alignment=TA_CENTER,
                        textColor=HexColor("#37474f"), fontSize=13)
    ))
    elements.append(Spacer(1, 2 * cm))
    elements.append(Paragraph(
        "Materi Praktikum dan Edukasi Mandiri untuk Jurusan Teknik Komputer dan Jaringan",
        ParagraphStyle("InfoCover", parent=styles["Normal"], alignment=TA_CENTER, fontSize=11, leading=16)
    ))
    elements.append(Spacer(1, 1 * cm))
    elements.append(Paragraph(
        "NetLabs — Platform Pembelajaran Jaringan Komputer",
        ParagraphStyle("Brand", parent=styles["Normal"], alignment=TA_CENTER,
                        fontSize=10, textColor=HexColor("#757575"))
    ))
    elements.append(PageBreak())

    # ===== BAB 1: PENDAHULUAN =====
    elements.append(Paragraph("1. Pendahuluan Jaringan Komputer", style_subjudul))
    elements.append(Paragraph(
        "Jaringan komputer adalah sistem yang terdiri dari dua atau lebih komputer serta perangkat jaringan lainnya "
        "yang saling terhubung menggunakan media transmisi (kabel atau nirkabel) untuk berbagi data, informasi, "
        "dan perangkat keras seperti printer.",
        style_body
    ))
    elements.append(Paragraph(
        "Dalam komunikasi jaringan, terdapat model referensi yang sangat terkenal bernama <b>OSI Layer (Open Systems Interconnection)</b> "
        "yang membagi proses komunikasi data menjadi 7 lapisan:",
        style_body
    ))
    elements.append(Paragraph(
        "1. <b>Physical Layer</b>: Menangani transmisi bit data secara fisik (kabel, konektor).<br/>"
        "2. <b>Data Link Layer</b>: Mengatur pengalamatan fisik (MAC Address) dan mendeteksi kesalahan.<br/>"
        "3. <b>Network Layer</b>: Menangani rute pengiriman data dan pengalamatan logis (IP Address).<br/>"
        "4. <b>Transport Layer</b>: Mengatur transfer data yang andal (TCP) atau cepat (UDP).<br/>"
        "5. <b>Session Layer</b>: Membuka, memelihara, dan menutup sesi komunikasi.<br/>"
        "6. <b>Presentation Layer</b>: Melakukan enkripsi, kompresi, dan format data.<br/>"
        "7. <b>Application Layer</b>: Lapisan yang berinteraksi langsung dengan pengguna (HTTP, FTP, SMTP).",
        style_body
    ))

    # ===== BAB 2: KELAS IP ADDRESS =====
    elements.append(Paragraph("2. Alamat IP (IP Address) IPv4", style_subjudul))
    elements.append(Paragraph(
        "Alamat IP (Internet Protocol Address) adalah pengenal numerik unik yang diberikan kepada setiap perangkat yang "
        "terhubung ke jaringan komputer berbasis TCP/IP. IPv4 menggunakan alamat sepanjang 32 bit yang dibagi menjadi 4 oktet.",
        style_body
    ))

    data_kelas = [
        ["Kelas", "Range Oktet 1", "Default Subnet Mask", "Jumlah Host Maksimum"],
        ["Kelas A", "1 - 126", "255.0.0.0 (/8)", "16.777.214 host"],
        ["Kelas B", "128 - 191", "255.255.0.0 (/16)", "65.534 host"],
        ["Kelas C", "192 - 223", "255.255.255.0 (/24)", "254 host"],
    ]
    tabel_kelas = Table(data_kelas, colWidths=[2.5 * cm, 3.5 * cm, 4.5 * cm, 5 * cm])
    tabel_kelas.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), HexColor("#1a237e")),
        ("TEXTCOLOR", (0, 0), (-1, 0), HexColor("#ffffff")),
        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
        ("FONTSIZE", (0, 0), (-1, -1), 9),
        ("ALIGN", (0, 0), (-1, -1), "CENTER"),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("GRID", (0, 0), (-1, -1), 0.5, HexColor("#bdbdbd")),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [HexColor("#f5f5f5"), HexColor("#ffffff")]),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
    ]))
    elements.append(tabel_kelas)
    elements.append(Spacer(1, 0.5 * cm))

    # ===== BAB 3: KONSEP SUBNETTING =====
    elements.append(PageBreak())
    elements.append(Paragraph("3. Konsep Subnetting", style_subjudul))
    elements.append(Paragraph(
        "Subnetting adalah teknik membagi satu jaringan besar (Network) menjadi beberapa jaringan kecil yang lebih efisien "
        "(Subnet) dengan cara meminjam beberapa bit dari porsi Host ID untuk dijadikan porsi Network ID tambahan.",
        style_body
    ))
    elements.append(Paragraph(
        "<b>Tujuan Subnetting:</b><br/>"
        "• Meminimalkan pemborosan alamat IP.<br/>"
        "• Mengurangi lalu lintas data berlebih (mengurangi ukuran broadcast domain).<br/>"
        "• Meningkatkan keamanan jaringan melalui segmentasi jaringan.",
        style_body
    ))

    elements.append(Paragraph("3.1 Notasi CIDR (Classless Inter-Domain Routing)", style_heading3))
    elements.append(Paragraph(
        "CIDR adalah metode pengalamatan IP yang menggantikan pembagian kelas IP tradisional. CIDR menggunakan notasi "
        "garis miring (/) diikuti oleh jumlah bit bernilai 1 pada subnet mask.<br/>"
        "Contoh: <b>192.168.1.0/26</b> berarti 26 bit pertama adalah Network ID dan 6 bit sisanya adalah Host ID.",
        style_body
    ))

    # ===== BAB 4: PERHITUNGAN SUBNETTING =====
    elements.append(Paragraph("4. Cara Menghitung Subnetting (Studi Kasus /26)", style_subjudul))
    elements.append(Paragraph(
        "Mari kita hitung parameter jaringan untuk IP: <b>192.168.1.10/26</b>.",
        style_body
    ))

    elements.append(Paragraph("Langkah 1: Cari Subnet Mask", style_heading3))
    elements.append(Paragraph(
        "Prefix /26 berarti ada 26 bit angka 1 dalam representasi biner:<br/>"
        "11111111.11111111.11111111.11000000<br/>"
        "Dalam desimal, nilainya adalah: <b>255.255.255.192</b>.",
        style_code
    ))

    elements.append(Paragraph("Langkah 2: Tentukan Jumlah Host per Subnet", style_heading3))
    elements.append(Paragraph(
        "Jumlah bit host sisa = 32 - 26 = 6 bit.<br/>"
        "Jumlah alamat total per subnet = 2^6 = 64 alamat.<br/>"
        "Jumlah host yang dapat digunakan = 64 - 2 = <b>62 host valid</b> (dikurangi 2 untuk Network IP dan Broadcast IP).",
        style_body
    ))

    elements.append(Paragraph("Langkah 3: Tentukan Block Size (Kelipatan Subnet)", style_heading3))
    elements.append(Paragraph(
        "Block Size = 256 - 192 (oktet terakhir subnet mask) = <b>64</b>.<br/>"
        "Rentang kelipatan subnet adalah: 0, 64, 128, 192.",
        style_body
    ))

    elements.append(Paragraph("Langkah 4: Tentukan Network Address, Broadcast, dan Range IP Valid", style_heading3))
    elements.append(Paragraph(
        "Karena IP yang dicari adalah 192.168.1.10, angka 10 berada di rentang 0 sampai 63. "
        "Maka parameter jaringannya adalah:",
        style_body
    ))

    data_hasil = [
        ["Parameter", "Nilai", "Penjelasan"],
        ["Network Address", "192.168.1.0", "Alamat awal dari subnet (identitas jaringan)"],
        ["IP Host Pertama", "192.168.1.1", "Network Address + 1"],
        ["IP Host Terakhir", "192.168.1.62", "Broadcast Address - 1"],
        ["Broadcast Address", "192.168.1.63", "Alamat pengiriman pesan massal dalam subnet"],
        ["Subnet Mask", "255.255.255.192", "Representasi desimal dari /26"],
    ]
    tabel_hasil = Table(data_hasil, colWidths=[4 * cm, 4 * cm, 8 * cm])
    tabel_hasil.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), HexColor("#1b5e20")),
        ("TEXTCOLOR", (0, 0), (-1, 0), HexColor("#ffffff")),
        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
        ("FONTSIZE", (0, 0), (-1, -1), 9),
        ("ALIGN", (0, 0), (1, -1), "CENTER"),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("GRID", (0, 0), (-1, -1), 0.5, HexColor("#bdbdbd")),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [HexColor("#e8f5e9"), HexColor("#ffffff")]),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
    ]))
    elements.append(tabel_hasil)
    elements.append(Spacer(1, 0.5 * cm))

    # ===== BAB 5: LATIHAN SOAL KUIS =====
    elements.append(Paragraph("5. Pertanyaan Evaluasi Jaringan", style_subjudul))
    elements.append(Paragraph(
        "1. Apa fungsi dari Network Layer pada model OSI?<br/>"
        "2. Jika sebuah subnet memiliki CIDR /28, berapakah jumlah host maksimal yang bisa digunakan?<br/>"
        "3. Tentukan Network Address dan Broadcast Address untuk IP 192.168.10.45/27!<br/>"
        "4. Jelaskan perbedaan antara IP Address Kelas A, B, dan C berdasarkan oktet pertamanya!<br/>"
        "5. Mengapa alamat IP pertama dan terakhir dalam sebuah kelipatan subnet tidak dapat dikonfigurasi pada komputer klien?",
        style_body
    ))

    elements.append(Spacer(1, 1 * cm))
    elements.append(Paragraph(
        "<i>— Akhir Modul Pembelajaran Jaringan Komputer —</i>",
        ParagraphStyle("Footer", parent=styles["Normal"], alignment=TA_CENTER,
                        fontSize=10, textColor=HexColor("#9e9e9e"))
    ))

    # Build PDF
    doc.build(elements)
    print(f"SUCCESS: PDF berhasil dibuat: {OUTPUT_FILE}")


if __name__ == "__main__":
    buat_pdf()
