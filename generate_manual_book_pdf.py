# -*- coding: utf-8 -*-
"""
Script untuk mengonversi MANUAL_BOOK.md menjadi MANUAL_BOOK.pdf secara otomatis.
Menggunakan ReportLab dengan styling minimalis, hitam-putih (monokrom),
nomor halaman dinamis, dan penyematan gambar screenshots asli.
Cocok untuk format cetak akademis formal.
"""

import os
import re
import sys
from PIL import Image as PILImage
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, Image
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT
from reportlab.pdfgen import canvas

# Tentukan path file input dan output
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
INPUT_FILE = os.path.join(BASE_DIR, "MANUAL_BOOK.md")
OUTPUT_FILE = os.path.join(BASE_DIR, "MANUAL_BOOK.pdf")

class NumberedCanvas(canvas.Canvas):
    """Canvas monokrom untuk menggambar header, footer, dan nomor halaman otomatis."""
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        # Halaman 1 adalah cover, tidak diberi header/footer
        if self._pageNumber == 1:
            return
            
        self.saveState()
        
        # Header (Monokrom)
        self.setFont("Helvetica-Bold", 8)
        self.setFillColor(HexColor("#333333"))
        self.drawString(2 * cm, A4[1] - 1.5 * cm, "NETLABS — BUKU PANDUAN PENGGUNAAN (MANUAL BOOK)")
        
        self.setStrokeColor(HexColor("#333333"))
        self.setLineWidth(0.5)
        self.line(2 * cm, A4[1] - 1.7 * cm, A4[0] - 2 * cm, A4[1] - 1.7 * cm)
        
        # Footer (Monokrom)
        self.setFont("Helvetica-Oblique", 8)
        self.setFillColor(HexColor("#555555"))
        self.drawString(2 * cm, 1.2 * cm, "Sistem ITS Jaringan Komputer berbasis AI RAG")
        
        self.setFont("Helvetica", 8)
        page_text = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(A4[0] - 2 * cm, 1.2 * cm, page_text)
        
        self.setStrokeColor(HexColor("#999999"))
        self.setLineWidth(0.5)
        self.line(2 * cm, 1.5 * cm, A4[0] - 2 * cm, 1.5 * cm)
        
        self.restoreState()


def parse_markdown_to_flowables(filepath, my_styles):
    """Membaca MD dan menerjemahkannya ke ReportLab Flowables."""
    flowables = []
    
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    # Pisahkan dokumen menjadi paragraf/bagian
    lines = content.split("\n")
    
    in_code_block = False
    code_lines = []
    in_mermaid = False
    
    # Pre-process text to ReportLab HTML format
    def format_text(text):
        # Format Bold: **text** -> <b>text</b>
        text = re.sub(r"\*\*(.*?)\*\*", r"<b>\1</b>", text)
        # Format Italic: *text* -> <i>text</i>
        text = re.sub(r"\*(.*?)\*", r"<i>\1</i>", text)
        # Format inline code: `code` -> <font face="Courier">%s</font>
        text = re.sub(r"`(.*?)`", r'<font face="Courier" color="#111111" size="9.5"><b>\1</b></font>', text)
        # Format Links: [name](url) -> <u>name</u>
        text = re.sub(r"\[(.*?)\]\((.*?)\)", r"<u>\1</u>", text)
        return text

    i = 0
    while i < len(lines):
        line = lines[i]
        stripped = line.strip()
        
        # 1. Skip empty lines
        if not stripped:
            if in_code_block:
                code_lines.append("")
            else:
                flowables.append(Spacer(1, 0.25 * cm))
            i += 1
            continue

        # 2. Handle Code Block / Mermaid
        if stripped.startswith("```"):
            if in_code_block:
                # End of code block
                in_code_block = False
                if not in_mermaid:
                    code_text = "\n".join(code_lines)
                    flowables.append(Paragraph(code_text.replace("\n", "<br/>").replace(" ", "&nbsp;"), my_styles["CodeBlock"]))
                    flowables.append(Spacer(1, 0.3 * cm))
                code_lines = []
                in_mermaid = False
            else:
                # Start of code block
                in_code_block = True
                if "mermaid" in stripped:
                    in_mermaid = True
                    # Kita buat Tabel Komponen Arsitektur sebagai pengganti Mermaid diagram (Monokrom)
                    data_arsitektur = [
                        [Paragraph("<b>Komponen Sistem</b>", my_styles["TableHead"]), 
                         Paragraph("<b>Teknologi/Stack</b>", my_styles["TableHead"]), 
                         Paragraph("<b>Peran Utama</b>", my_styles["TableHead"])],
                        [Paragraph("Web Admin", my_styles["TableBody"]), 
                         Paragraph("Laravel 12 + Bootstrap 5 (Skydash)", my_styles["TableBody"]), 
                         Paragraph("Mengelola data siswa, kelas, upload PDF materi, dan monitoring log chat.", my_styles["TableBody"])],
                        [Paragraph("AI Backend", my_styles["TableBody"]), 
                         Paragraph("Python Flask + Qdrant DB + Gemini API", my_styles["TableBody"]), 
                         Paragraph("Ekstraksi PDF modul, similarity search vektor, chatbot RAG, & auto-generate kuis.", my_styles["TableBody"])],
                        [Paragraph("Mobile Client", my_styles["TableBody"]), 
                         Paragraph("Flutter 3.12 + GetX Framework", my_styles["TableBody"]), 
                         Paragraph("Antarmuka interaktif siswa untuk belajar, mengerjakan kuis, dan konsultasi AI.", my_styles["TableBody"])]
                    ]
                    t_arsitektur = Table(data_arsitektur, colWidths=[3.5*cm, 5*cm, 8.5*cm])
                    t_arsitektur.setStyle(TableStyle([
                        ('BACKGROUND', (0,0), (-1,0), HexColor("#eaeaea")), # Abu-abu terang untuk header
                        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
                        ('VALIGN', (0,0), (-1,-1), 'TOP'),
                        ('GRID', (0,0), (-1,-1), 0.5, HexColor("#aaaaaa")),
                        ('BOTTOMPADDING', (0,0), (-1,-1), 8),
                        ('TOPPADDING', (0,0), (-1,-1), 8),
                    ]))
                    flowables.append(t_arsitektur)
                    flowables.append(Spacer(1, 0.4 * cm))
            i += 1
            continue

        if in_code_block:
            if not in_mermaid:
                code_lines.append(stripped)
            i += 1
            continue

        # 3. Handle Separator Line (---)
        if stripped == "---":
            flowables.append(Spacer(1, 0.3 * cm))
            flowables.append(Table([[""]], colWidths=[17 * cm], rowHeights=[1], style=TableStyle([
                ('LINEABOVE', (0,0), (-1,-1), 0.5, HexColor("#cccccc")),
            ])))
            flowables.append(Spacer(1, 0.4 * cm))
            i += 1
            continue

        # 4. Handle Images: ![caption](path)
        img_match = re.match(r"!\[(.*?)\]\((.*?)\)", stripped)
        if img_match:
            caption = img_match.group(1)
            img_path = img_match.group(2)
            
            # Sesuaikan relative path jika perlu
            full_img_path = os.path.join(BASE_DIR, img_path)
            
            if os.path.exists(full_img_path):
                # Load image size menggunakan PIL
                try:
                    pil_img = PILImage.open(full_img_path)
                    w, h = pil_img.size
                    # Batasi lebar maksimal gambar di PDF agar pas di A4
                    max_width = 15.5 * cm
                    scaled_w = max_width
                    scaled_h = (h / w) * scaled_w
                    
                    # Tambahkan flowable Image dan Caption
                    flowables.append(Spacer(1, 0.2 * cm))
                    flowables.append(Image(full_img_path, width=scaled_w, height=scaled_h))
                    flowables.append(Spacer(1, 0.15 * cm))
                    flowables.append(Paragraph(f"Gambar: {caption}", my_styles["ImageCaption"]))
                    flowables.append(Spacer(1, 0.3 * cm))
                except Exception as e:
                    print(f"[WARNING] Gagal memuat gambar {img_path}: {e}")
            else:
                print(f"[WARNING] Gambar tidak ditemukan: {full_img_path}")
            i += 1
            continue

        # 5. Handle Alerts (GitHub-style blocks) - Diubah menjadi gaya monokrom
        if stripped.startswith(">"):
            alert_lines = []
            alert_type = "NOTE"
            
            while i < len(lines) and lines[i].strip().startswith(">"):
                l_text = lines[i].strip().lstrip(">").strip()
                if l_text.startswith("[!IMPORTANT]"):
                    alert_type = "PENTING"
                elif l_text.startswith("[!WARNING]"):
                    alert_type = "PERINGATAN"
                elif l_text.startswith("[!TIP]"):
                    alert_type = "PETUNJUK"
                elif l_text.startswith("[!NOTE]"):
                    alert_type = "CATATAN"
                else:
                    alert_lines.append(l_text)
                i += 1
            
            # Gabungkan teks alert
            alert_content = " ".join(alert_lines)
            alert_content = format_text(alert_content)
            
            # Garis tepi monokrom (hitam/abu-abu gelap)
            border_color = "#333333"
                
            alert_table = Table([[Paragraph(f"<b>{alert_type}:</b> {alert_content}", my_styles["AlertText"])]], colWidths=[17*cm])
            alert_table.setStyle(TableStyle([
                ('BACKGROUND', (0,0), (-1,-1), HexColor("#fafafa")),
                ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
                ('LINEBEFORE', (0,0), (0,-1), 2.5, HexColor(border_color)),
                ('BOTTOMPADDING', (0,0), (-1,-1), 8),
                ('TOPPADDING', (0,0), (-1,-1), 8),
                ('LEFTPADDING', (0,0), (-1,-1), 10),
                ('RIGHTPADDING', (0,0), (-1,-1), 10),
            ]))
            flowables.append(alert_table)
            flowables.append(Spacer(1, 0.4 * cm))
            continue

        # 6. Handle Headings (Hitam Penuh / Monokrom)
        if stripped.startswith("#"):
            # Hitung jumlah '#'
            level = len(stripped) - len(stripped.lstrip("#"))
            title_text = stripped.lstrip("#").strip()
            title_text = format_text(title_text)
            
            if level == 1:
                # Kita gunakan Cover page jika H1 berada di baris pertama
                if i == 0 or len(flowables) < 3:
                    # Halaman Cover Akademis Polos
                    flowables.append(Spacer(1, 3 * cm))
                    flowables.append(Paragraph(title_text, my_styles["CoverTitle"]))
                    
                    # Cek baris berikutnya untuk H2
                    i += 1
                    if i < len(lines) and lines[i].strip().startswith("##"):
                        subtitle_text = lines[i].strip().lstrip("#").strip()
                        flowables.append(Spacer(1, 0.5 * cm))
                        flowables.append(Paragraph(subtitle_text, my_styles["CoverSubtitle"]))
                        i += 1
                    
                    # Tambahkan deskripsi cover
                    flowables.append(Spacer(1, 2 * cm))
                    flowables.append(Paragraph(
                        "<b>NetLabs</b> adalah platform <i>Intelligent Tutoring System</i> (ITS) untuk "
                        "praktikum mandiri jaringan komputer SMK TKJ berbasis RAG (Retrieval-Augmented Generation).",
                        my_styles["CoverDesc"]
                    ))
                    flowables.append(Spacer(1, 5 * cm))
                    flowables.append(Paragraph(
                        "<b>PENGEMBANG / GURU / ADMINISTRATOR</b><br/>"
                        "Tahun Rilis: 2026<br/>"
                        "Kementerian Hukum dan HAM RI — HKI: EC002026102929",
                        my_styles["CoverMeta"]
                    ))
                    flowables.append(PageBreak())
                    continue
                else:
                    flowables.append(Paragraph(title_text, my_styles["Heading1"]))
            elif level == 2:
                flowables.append(Paragraph(title_text, my_styles["Heading2"]))
            elif level == 3:
                flowables.append(Paragraph(title_text, my_styles["Heading3"]))
            
            flowables.append(Spacer(1, 0.2 * cm))
            i += 1
            continue

        # 7. Handle Bullet & List Items
        if stripped.startswith("*") or stripped.startswith("-") or re.match(r"^\d+\.", stripped):
            is_ordered = re.match(r"^\d+\.", stripped)
            list_text = ""
            
            if is_ordered:
                prefix = is_ordered.group(0) + " "
                list_text = stripped[len(is_ordered.group(0)):].strip()
            else:
                prefix = "&bull; "
                list_text = stripped[1:].strip()
                
            list_text = format_text(list_text)
            
            # Untuk Ordered/Unordered list sub-items indentasi
            indent = 12
            if line.startswith("    ") or line.startswith("\t"):
                indent = 24
                if not is_ordered:
                    prefix = "&ndash; "
            
            p_style = ParagraphStyle(
                "ListText", parent=my_styles["NormalText"],
                leftIndent=indent, firstLineIndent=-10,
                spaceAfter=5, leading=15, fontSize=10.5
            )
            
            flowables.append(Paragraph(f"{prefix}{list_text}", p_style))
            i += 1
            continue

        # 8. Normal Paragraph Text
        p_text = format_text(stripped)
        flowables.append(Paragraph(p_text, my_styles["NormalText"]))
        i += 1
        
    return flowables


def buat_pdf():
    print("Memulai konversi MANUAL_BOOK.md ke PDF...")
    
    # 1. Validasi file input
    if not os.path.exists(INPUT_FILE):
        print(f"[ERROR] Berkas input {INPUT_FILE} tidak ditemukan!")
        sys.exit(1)
        
    # 2. Setup document template
    doc = SimpleDocTemplate(
        OUTPUT_FILE,
        pagesize=A4,
        rightMargin=2 * cm,
        leftMargin=2 * cm,
        topMargin=2 * cm,
        bottomMargin=2 * cm,
    )
    
    # 3. Setup premium styles
    styles = getSampleStyleSheet()
    
    # Custom styles dictionary (Hitam & Putih / Monokrom Polos)
    my_styles = {
        "CoverTitle": ParagraphStyle(
            "CoverTitle", parent=styles["Title"],
            fontName="Helvetica-Bold", fontSize=22, leading=28,
            textColor=HexColor("#000000"), alignment=TA_CENTER,
            spaceAfter=15
        ),
        "CoverSubtitle": ParagraphStyle(
            "CoverSubtitle", parent=styles["Normal"],
            fontName="Helvetica", fontSize=13, leading=17,
            textColor=HexColor("#333333"), alignment=TA_CENTER,
            spaceAfter=10
        ),
        "CoverDesc": ParagraphStyle(
            "CoverDesc", parent=styles["Normal"],
            fontName="Helvetica", fontSize=11, leading=16,
            textColor=HexColor("#333333"), alignment=TA_CENTER
        ),
        "CoverMeta": ParagraphStyle(
            "CoverMeta", parent=styles["Normal"],
            fontName="Helvetica", fontSize=9.5, leading=14,
            textColor=HexColor("#555555"), alignment=TA_CENTER
        ),
        "Heading1": ParagraphStyle(
            "H1Custom", parent=styles["Heading1"],
            fontName="Helvetica-Bold", fontSize=15, leading=19,
            textColor=HexColor("#000000"), spaceBefore=18, spaceAfter=8,
            keepWithNext=True
        ),
        "Heading2": ParagraphStyle(
            "H2Custom", parent=styles["Heading2"],
            fontName="Helvetica-Bold", fontSize=12.5, leading=16,
            textColor=HexColor("#111111"), spaceBefore=14, spaceAfter=6,
            keepWithNext=True
        ),
        "Heading3": ParagraphStyle(
            "H3Custom", parent=styles["Heading3"],
            fontName="Helvetica-Bold", fontSize=11, leading=14,
            textColor=HexColor("#333333"), spaceBefore=10, spaceAfter=5,
            keepWithNext=True
        ),
        "NormalText": ParagraphStyle(
            "NormalCustom", parent=styles["Normal"],
            fontName="Helvetica", fontSize=10.5, leading=15.5,
            textColor=HexColor("#000000"), spaceAfter=8, alignment=TA_JUSTIFY
        ),
        "ImageCaption": ParagraphStyle(
            "ImgCaption", parent=styles["Italic"],
            fontName="Helvetica-Oblique", fontSize=8.5, leading=12,
            textColor=HexColor("#444444"), alignment=TA_CENTER,
            spaceAfter=10
        ),
        "CodeBlock": ParagraphStyle(
            "CodeBlockCustom", parent=styles["Code"],
            fontName="Courier", fontSize=9, leading=12,
            textColor=HexColor("#111111"), backColor=HexColor("#f8f8f8"),
            borderColor=HexColor("#cccccc"), borderWidth=0.5,
            borderPadding=6, spaceAfter=8
        ),
        "TableHead": ParagraphStyle(
            "THead", parent=styles["Normal"],
            fontName="Helvetica-Bold", fontSize=9.5, leading=12,
            textColor=HexColor("#000000")
        ),
        "TableBody": ParagraphStyle(
            "TBody", parent=styles["Normal"],
            fontName="Helvetica", fontSize=9, leading=12,
            textColor=HexColor("#111111")
        ),
        "AlertText": ParagraphStyle(
            "AlertText", parent=styles["Normal"],
            fontName="Helvetica", fontSize=9.5, leading=14,
            textColor=HexColor("#111111")
        )
    }
    
    # 4. Parse markdown ke flowables
    flowables = parse_markdown_to_flowables(INPUT_FILE, my_styles)
    
    # 5. Bangun berkas PDF
    doc.build(flowables, canvasmaker=NumberedCanvas)
    print(f"[OK] Berkas PDF berhasil dibuat di: {OUTPUT_FILE}")


if __name__ == "__main__":
    buat_pdf()
