"""
Script untuk membuat PDF modul test materi Jaringan Komputer.
Digunakan untuk menguji endpoint /index-pdf dan /chat.
"""

from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY
import os

OUTPUT_DIR = os.path.dirname(os.path.abspath(__file__))
OUTPUT_FILE = os.path.join(OUTPUT_DIR, "modul_test_subnetting_ipv4.pdf")


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
    elements.append(Paragraph("MODUL PRAKTIKUM", style_judul))
    elements.append(Paragraph("JARINGAN KOMPUTER", style_judul))
    elements.append(Spacer(1, 1 * cm))
    elements.append(Paragraph(
        "Pertemuan 2: Subnetting IPv4 dan Perhitungan Alamat Jaringan",
        ParagraphStyle("SubCover", parent=styles["Heading2"], alignment=TA_CENTER,
                        textColor=HexColor("#37474f"), fontSize=13)
    ))
    elements.append(Spacer(1, 2 * cm))
    elements.append(Paragraph(
        "Disusun untuk Keperluan Pembelajaran<br/>SMK Jurusan Teknik Komputer dan Jaringan",
        ParagraphStyle("InfoCover", parent=styles["Normal"], alignment=TA_CENTER, fontSize=11, leading=16)
    ))
    elements.append(Spacer(1, 1 * cm))
    elements.append(Paragraph(
        "NetLabs — Platform Pembelajaran Jaringan Komputer",
        ParagraphStyle("Brand", parent=styles["Normal"], alignment=TA_CENTER,
                        fontSize=10, textColor=HexColor("#757575"))
    ))
    elements.append(PageBreak())

    # ===== BAB 1: PENDAHULUAN IP ADDRESS =====
    elements.append(Paragraph("1. Pendahuluan: Alamat IP (IP Address)", style_subjudul))
    elements.append(Paragraph(
        "Alamat IP (Internet Protocol Address) adalah identitas numerik yang diberikan kepada "
        "setiap perangkat yang terhubung ke jaringan komputer yang menggunakan protokol TCP/IP. "
        "Alamat IP berfungsi sebagai identitas pengirim dan penerima data dalam komunikasi jaringan.",
        style_body
    ))
    elements.append(Paragraph(
        "Dalam jaringan komputer, terdapat dua versi alamat IP yang umum digunakan:",
        style_body
    ))
    elements.append(Paragraph(
        "• <b>IPv4 (Internet Protocol version 4)</b>: Menggunakan 32 bit yang ditulis dalam format "
        "desimal bertitik (dotted decimal), terdiri dari 4 oktet. Contoh: 192.168.1.10<br/>"
        "• <b>IPv6 (Internet Protocol version 6)</b>: Menggunakan 128 bit yang ditulis dalam format "
        "heksadesimal. Contoh: 2001:0db8:85a3::8a2e:0370:7334",
        style_body
    ))
    elements.append(Paragraph(
        "Pada modul pertemuan ini, kita akan fokus membahas IPv4 dan teknik Subnetting.",
        style_catatan
    ))

    # ===== BAB 2: KELAS IP ADDRESS =====
    elements.append(Paragraph("2. Kelas-Kelas Alamat IPv4", style_subjudul))
    elements.append(Paragraph(
        "Alamat IPv4 dibagi menjadi beberapa kelas berdasarkan oktet pertamanya. "
        "Pembagian kelas ini menentukan porsi mana yang menjadi Network ID dan Host ID.",
        style_body
    ))

    # Tabel kelas IP
    data_kelas = [
        ["Kelas", "Range Oktet 1", "Default Subnet Mask", "Jumlah Host/Network"],
        ["A", "1 - 126", "255.0.0.0 (/8)", "16.777.214 host"],
        ["B", "128 - 191", "255.255.0.0 (/16)", "65.534 host"],
        ["C", "192 - 223", "255.255.255.0 (/24)", "254 host"],
        ["D", "224 - 239", "Multicast", "N/A"],
        ["E", "240 - 255", "Experimental", "N/A"],
    ]
    tabel_kelas = Table(data_kelas, colWidths=[2 * cm, 3.5 * cm, 4.5 * cm, 4.5 * cm])
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

    elements.append(Paragraph(
        "<b>Catatan Penting:</b> Alamat 127.0.0.0 sampai 127.255.255.255 dicadangkan untuk "
        "loopback (localhost). Alamat 0.0.0.0 digunakan sebagai default route.",
        style_catatan
    ))

    # ===== BAB 3: SUBNETTING =====
    elements.append(Paragraph("3. Konsep Subnetting", style_subjudul))
    elements.append(Paragraph(
        "Subnetting adalah teknik membagi satu jaringan besar menjadi beberapa jaringan kecil "
        "(subnet) dengan cara meminjam bit dari bagian Host ID untuk dijadikan bagian Network ID. "
        "Tujuan subnetting antara lain:",
        style_body
    ))
    elements.append(Paragraph(
        "• Efisiensi penggunaan alamat IP<br/>"
        "• Mengurangi ukuran broadcast domain<br/>"
        "• Meningkatkan keamanan jaringan dengan segmentasi<br/>"
        "• Mempermudah manajemen dan troubleshooting jaringan",
        style_body
    ))

    elements.append(Paragraph("3.1 Notasi CIDR (Classless Inter-Domain Routing)", style_heading3))
    elements.append(Paragraph(
        "CIDR menggunakan notasi slash (/) diikuti jumlah bit yang digunakan untuk Network ID. "
        "Contoh: 192.168.1.0/26 berarti 26 bit pertama adalah Network ID dan 6 bit sisanya "
        "adalah Host ID.",
        style_body
    ))

    # Tabel CIDR umum
    data_cidr = [
        ["CIDR", "Subnet Mask", "Jumlah Host", "Jumlah Subnet (dari /24)"],
        ["/24", "255.255.255.0", "254", "1"],
        ["/25", "255.255.255.128", "126", "2"],
        ["/26", "255.255.255.192", "62", "4"],
        ["/27", "255.255.255.224", "30", "8"],
        ["/28", "255.255.255.240", "14", "16"],
        ["/29", "255.255.255.248", "6", "32"],
        ["/30", "255.255.255.252", "2", "64"],
    ]
    tabel_cidr = Table(data_cidr, colWidths=[2 * cm, 4 * cm, 3 * cm, 5 * cm])
    tabel_cidr.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), HexColor("#283593")),
        ("TEXTCOLOR", (0, 0), (-1, 0), HexColor("#ffffff")),
        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
        ("FONTSIZE", (0, 0), (-1, -1), 9),
        ("ALIGN", (0, 0), (-1, -1), "CENTER"),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("GRID", (0, 0), (-1, -1), 0.5, HexColor("#bdbdbd")),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [HexColor("#e8eaf6"), HexColor("#ffffff")]),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
    ]))
    elements.append(tabel_cidr)
    elements.append(Spacer(1, 0.5 * cm))

    # ===== BAB 4: CARA MENGHITUNG SUBNETTING =====
    elements.append(PageBreak())
    elements.append(Paragraph("4. Cara Menghitung Subnetting IPv4", style_subjudul))
    elements.append(Paragraph(
        "Berikut adalah langkah-langkah sistematis untuk menghitung subnetting. "
        "Kita akan menggunakan contoh soal: <b>192.168.1.10/26</b>",
        style_body
    ))

    elements.append(Paragraph("Langkah 1: Tentukan Subnet Mask", style_heading3))
    elements.append(Paragraph(
        "Prefix /26 berarti 26 bit pertama bernilai 1 (Network) dan 6 bit terakhir bernilai 0 (Host).",
        style_body
    ))
    elements.append(Paragraph(
        "Subnet Mask dalam biner: 11111111.11111111.11111111.11000000<br/>"
        "Subnet Mask dalam desimal: 255.255.255.192",
        style_code
    ))

    elements.append(Paragraph("Langkah 2: Hitung Jumlah Host per Subnet", style_heading3))
    elements.append(Paragraph(
        "Jumlah bit host = 32 - 26 = 6 bit<br/>"
        "Jumlah alamat total = 2^6 = 64 alamat<br/>"
        "Jumlah host yang dapat digunakan = 64 - 2 = 62 host<br/><br/>"
        "<b>Catatan:</b> Dikurangi 2 karena 1 alamat untuk Network Address dan 1 alamat "
        "untuk Broadcast Address.",
        style_body
    ))

    elements.append(Paragraph("Langkah 3: Tentukan Block Size (Kelipatan Subnet)", style_heading3))
    elements.append(Paragraph(
        "Block Size = 256 - nilai oktet terakhir subnet mask<br/>"
        "Block Size = 256 - 192 = 64<br/><br/>"
        "Artinya, setiap subnet memiliki rentang 64 alamat IP. "
        "Subnet dimulai dari kelipatan 64 pada oktet terakhir: 0, 64, 128, 192.",
        style_body
    ))

    elements.append(Paragraph("Langkah 4: Tentukan Network Address, Broadcast, dan Range Host", style_heading3))
    elements.append(Paragraph(
        "IP yang diberikan: 192.168.1.10. Karena 10 berada di antara 0 dan 63 "
        "(subnet pertama, kelipatan 64), maka:",
        style_body
    ))

    data_hasil = [
        ["Komponen", "Nilai", "Penjelasan"],
        ["Network Address", "192.168.1.0", "Alamat pertama dalam subnet (kelipatan block size)"],
        ["Host Pertama", "192.168.1.1", "Network Address + 1"],
        ["Host Terakhir", "192.168.1.62", "Broadcast Address - 1"],
        ["Broadcast Address", "192.168.1.63", "Network Address + Block Size - 1 = 0 + 64 - 1"],
        ["Subnet Mask", "255.255.255.192", "Prefix /26"],
    ]
    tabel_hasil = Table(data_hasil, colWidths=[3.5 * cm, 3.5 * cm, 8 * cm])
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

    # ===== BAB 5: CONTOH SOAL TAMBAHAN =====
    elements.append(Paragraph("5. Contoh Soal dan Pembahasan Tambahan", style_subjudul))

    elements.append(Paragraph("Soal 1: 10.0.0.50/28", style_heading3))
    elements.append(Paragraph(
        "Prefix /28 → Subnet Mask: 255.255.255.240<br/>"
        "Bit host = 32 - 28 = 4 bit → Jumlah alamat = 2^4 = 16 → Host valid = 14<br/>"
        "Block Size = 256 - 240 = 16<br/>"
        "IP 50 berada di antara 48 dan 63 (kelipatan 16: 0, 16, 32, <b>48</b>, 64...)<br/><br/>"
        "• Network Address: 10.0.0.48<br/>"
        "• Host Pertama: 10.0.0.49<br/>"
        "• Host Terakhir: 10.0.0.62<br/>"
        "• Broadcast Address: 10.0.0.63<br/>"
        "• Subnet Mask: 255.255.255.240",
        style_body
    ))

    elements.append(Paragraph("Soal 2: 172.16.5.130/25", style_heading3))
    elements.append(Paragraph(
        "Prefix /25 → Subnet Mask: 255.255.255.128<br/>"
        "Bit host = 32 - 25 = 7 bit → Jumlah alamat = 2^7 = 128 → Host valid = 126<br/>"
        "Block Size = 256 - 128 = 128<br/>"
        "IP 130 berada di antara 128 dan 255 (kelipatan 128: 0, <b>128</b>)<br/><br/>"
        "• Network Address: 172.16.5.128<br/>"
        "• Host Pertama: 172.16.5.129<br/>"
        "• Host Terakhir: 172.16.5.254<br/>"
        "• Broadcast Address: 172.16.5.255<br/>"
        "• Subnet Mask: 255.255.255.128",
        style_body
    ))

    # ===== BAB 6: PRIVATE & PUBLIC IP =====
    elements.append(PageBreak())
    elements.append(Paragraph("6. Alamat IP Privat dan Publik", style_subjudul))
    elements.append(Paragraph(
        "Alamat IP dibedakan menjadi dua jenis berdasarkan penggunaannya:",
        style_body
    ))

    data_private = [
        ["Kelas", "Range IP Privat", "CIDR"],
        ["A", "10.0.0.0 - 10.255.255.255", "10.0.0.0/8"],
        ["B", "172.16.0.0 - 172.31.255.255", "172.16.0.0/12"],
        ["C", "192.168.0.0 - 192.168.255.255", "192.168.0.0/16"],
    ]
    tabel_private = Table(data_private, colWidths=[2 * cm, 6 * cm, 4 * cm])
    tabel_private.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), HexColor("#e65100")),
        ("TEXTCOLOR", (0, 0), (-1, 0), HexColor("#ffffff")),
        ("FONTNAME", (0, 0), (-1, 0), "Helvetica-Bold"),
        ("FONTSIZE", (0, 0), (-1, -1), 9),
        ("ALIGN", (0, 0), (-1, -1), "CENTER"),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("GRID", (0, 0), (-1, -1), 0.5, HexColor("#bdbdbd")),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [HexColor("#fff3e0"), HexColor("#ffffff")]),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 6),
        ("TOPPADDING", (0, 0), (-1, -1), 6),
    ]))
    elements.append(tabel_private)
    elements.append(Spacer(1, 0.3 * cm))

    elements.append(Paragraph(
        "<b>IP Publik</b> adalah alamat IP yang dapat diakses dari internet secara langsung, "
        "sedangkan <b>IP Privat</b> hanya dapat digunakan dalam jaringan lokal (LAN) dan "
        "memerlukan NAT (Network Address Translation) untuk mengakses internet.",
        style_body
    ))

    # ===== BAB 7: LATIHAN MANDIRI =====
    elements.append(Paragraph("7. Latihan Mandiri", style_subjudul))
    elements.append(Paragraph(
        "Kerjakan soal-soal berikut untuk mengasah kemampuan subnetting Anda. "
        "Tentukan Network Address, Broadcast Address, Range Host, dan Subnet Mask untuk "
        "masing-masing alamat IP berikut:",
        style_body
    ))
    elements.append(Paragraph(
        "1. 192.168.10.75/27<br/>"
        "2. 10.10.10.200/29<br/>"
        "3. 172.16.100.33/22<br/>"
        "4. 192.168.50.100/30<br/>"
        "5. Sebuah perusahaan memiliki 500 komputer. Tentukan prefix CIDR minimum yang dibutuhkan "
        "jika menggunakan satu subnet dari kelas C privat!",
        style_body
    ))

    elements.append(Spacer(1, 1 * cm))
    elements.append(Paragraph(
        "<i>— Akhir Modul Pertemuan 2 —</i>",
        ParagraphStyle("Footer", parent=styles["Normal"], alignment=TA_CENTER,
                        fontSize=10, textColor=HexColor("#9e9e9e"))
    ))

    # Build PDF
    doc.build(elements)
    print(f"✅ PDF berhasil dibuat: {OUTPUT_FILE}")
    print(f"   Ukuran: {os.path.getsize(OUTPUT_FILE) / 1024:.1f} KB")


if __name__ == "__main__":
    buat_pdf()
