# -*- coding: utf-8 -*-
"""
Script untuk menggabungkan dan mengonversi berkas dokumentasi teknis:
docs/SETUP.md, docs/API.md, dan docs/TEST_REPORT.md
menjadi sebuah berkas PDF monokrom tunggal DOKUMEN_TEKNIS.pdf.
"""

import os
import re
import sys
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY, TA_LEFT
from reportlab.pdfgen import canvas

# Tentukan file input dan output
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DOCS_DIR = os.path.join(BASE_DIR, "docs")
OUTPUT_FILE = os.path.join(BASE_DIR, "DOKUMEN_TEKNIS.pdf")

# Urutan penggabungan berkas
FILES_TO_MERGE = [
    {"name": "Panduan Setup & Deployment", "path": os.path.join(DOCS_DIR, "SETUP.md")},
    {"name": "Dokumentasi API Endpoints", "path": os.path.join(DOCS_DIR, "API.md")},
    {"name": "Lampiran Implementasi Kode Sumber Utama", "path": os.path.join(DOCS_DIR, "IMPLEMENTATION.md")},
    {"name": "Laporan Hasil Pengujian Sistem", "path": os.path.join(DOCS_DIR, "TEST_REPORT.md")}
]

class NumberedCanvas(canvas.Canvas):
    """Canvas monokrom untuk dokumen teknis."""
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
        if self._pageNumber == 1:
            return
            
        self.saveState()
        
        # Header (Monokrom)
        self.setFont("Helvetica-Bold", 8)
        self.setFillColor(HexColor("#333333"))
        self.drawString(2 * cm, A4[1] - 1.5 * cm, "NETLABS — DOKUMEN TEKNIS & SPESIFIKASI SISTEM")
        
        self.setStrokeColor(HexColor("#333333"))
        self.setLineWidth(0.5)
        self.line(2 * cm, A4[1] - 1.7 * cm, A4[0] - 2 * cm, A4[1] - 1.7 * cm)
        
        # Footer
        self.setFont("Helvetica-Oblique", 8)
        self.setFillColor(HexColor("#555555"))
        self.drawString(2 * cm, 1.2 * cm, "Setup Panduan, Spesifikasi API, dan Hasil Pengujian")
        
        self.setFont("Helvetica", 8)
        page_text = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(A4[0] - 2 * cm, 1.2 * cm, page_text)
        
        self.setStrokeColor(HexColor("#999999"))
        self.setLineWidth(0.5)
        self.line(2 * cm, 1.5 * cm, A4[0] - 2 * cm, 1.5 * cm)
        
        self.restoreState()


def parse_markdown_to_flowables(filepath, my_styles, section_title):
    """Membaca berkas MD dan menerjemahkannya ke flowables."""
    flowables = []
    
    # Judul Bab Baru
    flowables.append(Spacer(1, 0.5 * cm))
    flowables.append(Paragraph(section_title, my_styles["Heading1"]))
    flowables.append(Table([[""]], colWidths=[17 * cm], rowHeights=[2], style=TableStyle([
        ('LINEABOVE', (0,0), (-1,-1), 1.5, HexColor("#000000")),
    ])))
    flowables.append(Spacer(1, 0.4 * cm))
    
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    lines = content.split("\n")
    
    in_code_block = False
    code_lines = []
    
    def format_text(text):
        # Format Bold: **text** -> <b>text</b>
        text = re.sub(r"\*\*(.*?)\*\*", r"<b>\1</b>", text)
        # Format Italic: *text* -> <i>text</i>
        text = re.sub(r"\*(.*?)\*", r"<i>\1</i>", text)
        # Format inline code: `code` -> Courier
        text = re.sub(r"`(.*?)`", r'<font face="Courier" color="#222222" size="9.0"><b>\1</b></font>', text)
        # Format Links: [name](url) -> <u>name</u>
        text = re.sub(r"\[(.*?)\]\((.*?)\)", r"<u>\1</u>", text)
        return text

    i = 0
    # Abaikan baris judul H1 pertama pada berkas MD (karena sudah kita buat section_title)
    has_skipped_first_h1 = False
    
    while i < len(lines):
        line = lines[i]
        stripped = line.strip()
        
        # Skip empty lines
        if not stripped:
            if in_code_block:
                code_lines.append("")
            else:
                flowables.append(Spacer(1, 0.22 * cm))
            i += 1
            continue

        # Handle Code Blocks (Nginx, JSON, Systemd, Bash)
        if stripped.startswith("```"):
            if in_code_block:
                in_code_block = False
                code_text = "\n".join(code_lines)
                # Escaping text untuk XML/ReportLab Paragraph
                code_text = code_text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
                code_text = code_text.replace(" ", "&nbsp;").replace("\n", "<br/>")
                flowables.append(Paragraph(code_text, my_styles["CodeBlock"]))
                flowables.append(Spacer(1, 0.25 * cm))
                code_lines = []
            else:
                in_code_block = True
            i += 1
            continue

        if in_code_block:
            code_lines.append(stripped)
            i += 1
            continue

        # Handle Separator
        if stripped == "---":
            flowables.append(Spacer(1, 0.25 * cm))
            flowables.append(Table([[""]], colWidths=[17 * cm], rowHeights=[1], style=TableStyle([
                ('LINEABOVE', (0,0), (-1,-1), 0.5, HexColor("#cccccc")),
            ])))
            flowables.append(Spacer(1, 0.3 * cm))
            i += 1
            continue

        # Handle Alerts
        if stripped.startswith(">"):
            alert_lines = []
            alert_type = "CATATAN"
            
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
            
            alert_content = " ".join(alert_lines)
            alert_content = format_text(alert_content)
            
            alert_table = Table([[Paragraph(f"<b>{alert_type}:</b> {alert_content}", my_styles["AlertText"])]], colWidths=[17*cm])
            alert_table.setStyle(TableStyle([
                ('BACKGROUND', (0,0), (-1,-1), HexColor("#fafafa")),
                ('VALIGN', (0,0), (-1,-1), 'MIDDLE'),
                ('LINEBEFORE', (0,0), (0,-1), 2.5, HexColor("#333333")),
                ('BOTTOMPADDING', (0,0), (-1,-1), 8),
                ('TOPPADDING', (0,0), (-1,-1), 8),
                ('LEFTPADDING', (0,0), (-1,-1), 10),
                ('RIGHTPADDING', (0,0), (-1,-1), 10),
            ]))
            flowables.append(alert_table)
            flowables.append(Spacer(1, 0.3 * cm))
            continue

        # Handle Tables (Contoh: Kebutuhan Sistem, Ringkasan Pengujian)
        if stripped.startswith("|"):
            table_rows = []
            # Tarik semua baris tabel
            while i < len(lines) and lines[i].strip().startswith("|"):
                r_text = lines[i].strip()
                # Skip header separator line (|---|---|...)
                if not re.match(r"^\|[\s\-\|]+$", r_text):
                    # Bersihkan sel dan parsing
                    cells = [c.strip() for c in r_text.split("|")[1:-1]]
                    table_rows.append(cells)
                i += 1
                
            if len(table_rows) > 0:
                # Bungkus sel ke dalam Paragraph agar terbungkus otomatis (auto wrap)
                formatted_table_data = []
                # Hitung jumlah kolom
                num_cols = len(table_rows[0])
                
                for r_idx, row in enumerate(table_rows):
                    formatted_row = []
                    for c_idx, cell in enumerate(row):
                        cell_fmt = format_text(cell)
                        if r_idx == 0:
                            formatted_row.append(Paragraph(f"<b>{cell_fmt}</b>", my_styles["TableHead"]))
                        else:
                            formatted_row.append(Paragraph(cell_fmt, my_styles["TableBody"]))
                    # Jika jumlah sel baris ini kurang, tambahkan sel kosong
                    while len(formatted_row) < num_cols:
                        formatted_row.append(Paragraph("", my_styles["TableBody"]))
                    formatted_table_data.append(formatted_row)
                
                # Definisikan lebar kolom proporsional berdasarkan jumlah kolom
                col_width = (17.0 / num_cols) * cm
                widths = [col_width] * num_cols
                
                # Khusus tabel 3 kolom (Contoh Kebutuhan Sistem)
                if num_cols == 3:
                    widths = [3.5*cm, 5.5*cm, 8.0*cm]
                elif num_cols == 4:
                    widths = [1.0*cm, 4.5*cm, 3.5*cm, 8.0*cm] # Tabel Test Issue
                
                t = Table(formatted_table_data, colWidths=widths)
                t.setStyle(TableStyle([
                    ('BACKGROUND', (0,0), (-1,0), HexColor("#eaeaea")),
                    ('VALIGN', (0,0), (-1,-1), 'TOP'),
                    ('GRID', (0,0), (-1,-1), 0.5, HexColor("#aaaaaa")),
                    ('BOTTOMPADDING', (0,0), (-1,-1), 6),
                    ('TOPPADDING', (0,0), (-1,-1), 6),
                ]))
                flowables.append(t)
                flowables.append(Spacer(1, 0.4 * cm))
            continue

        # Handle Headings
        if stripped.startswith("#"):
            level = len(stripped) - len(stripped.lstrip("#"))
            title_text = stripped.lstrip("#").strip()
            title_text = format_text(title_text)
            
            if level == 1:
                # Abaikan H1 pertama pada berkas MD karena sudah dicantumkan sebagai Bab Utama
                if not has_skipped_first_h1:
                    has_skipped_first_h1 = True
                    i += 1
                    continue
                flowables.append(Paragraph(title_text, my_styles["Heading1_Inner"]))
            elif level == 2:
                flowables.append(Paragraph(title_text, my_styles["Heading2"]))
            elif level == 3:
                flowables.append(Paragraph(title_text, my_styles["Heading3"]))
            
            flowables.append(Spacer(1, 0.18 * cm))
            i += 1
            continue

        # Handle Bullet & List Items
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
            
            indent = 12
            if line.startswith("    ") or line.startswith("\t"):
                indent = 24
                if not is_ordered:
                    prefix = "&ndash; "
            
            p_style = ParagraphStyle(
                "ListText", parent=my_styles["NormalText"],
                leftIndent=indent, firstLineIndent=-10,
                spaceAfter=4, leading=14, fontSize=9.5
            )
            
            flowables.append(Paragraph(f"{prefix}{list_text}", p_style))
            i += 1
            continue

        # Normal Paragraph Text
        p_text = format_text(stripped)
        flowables.append(Paragraph(p_text, my_styles["NormalText"]))
        i += 1
        
    return flowables


def buat_pdf():
    print("Membuat DOKUMEN_TEKNIS.pdf...")
    
    doc = SimpleDocTemplate(
        OUTPUT_FILE,
        pagesize=A4,
        rightMargin=2 * cm,
        leftMargin=2 * cm,
        topMargin=2 * cm,
        bottomMargin=2 * cm,
    )
    
    styles = getSampleStyleSheet()
    
    # Custom styles dictionary (Monokrom Akademis)
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
            fontName="Helvetica-Bold", fontSize=16, leading=20,
            textColor=HexColor("#000000"), spaceBefore=22, spaceAfter=10,
            keepWithNext=True
        ),
        "Heading1_Inner": ParagraphStyle(
            "H1InnerCustom", parent=styles["Heading1"],
            fontName="Helvetica-Bold", fontSize=14, leading=18,
            textColor=HexColor("#000000"), spaceBefore=18, spaceAfter=8,
            keepWithNext=True
        ),
        "Heading2": ParagraphStyle(
            "H2Custom", parent=styles["Heading2"],
            fontName="Helvetica-Bold", fontSize=12, leading=16,
            textColor=HexColor("#111111"), spaceBefore=14, spaceAfter=6,
            keepWithNext=True
        ),
        "Heading3": ParagraphStyle(
            "H3Custom", parent=styles["Heading3"],
            fontName="Helvetica-Bold", fontSize=10.5, leading=14,
            textColor=HexColor("#333333"), spaceBefore=10, spaceAfter=5,
            keepWithNext=True
        ),
        "NormalText": ParagraphStyle(
            "NormalCustom", parent=styles["Normal"],
            fontName="Helvetica", fontSize=9.5, leading=14.5,
            textColor=HexColor("#000000"), spaceAfter=6, alignment=TA_JUSTIFY
        ),
        "CodeBlock": ParagraphStyle(
            "CodeBlockCustom", parent=styles["Code"],
            fontName="Courier", fontSize=8, leading=11,
            textColor=HexColor("#111111"), backColor=HexColor("#f8f8f8"),
            borderColor=HexColor("#cccccc"), borderWidth=0.5,
            borderPadding=6, spaceAfter=8
        ),
        "TableHead": ParagraphStyle(
            "THead", parent=styles["Normal"],
            fontName="Helvetica-Bold", fontSize=9, leading=11,
            textColor=HexColor("#000000")
        ),
        "TableBody": ParagraphStyle(
            "TBody", parent=styles["Normal"],
            fontName="Helvetica", fontSize=8.5, leading=11,
            textColor=HexColor("#111111")
        ),
        "AlertText": ParagraphStyle(
            "AlertText", parent=styles["Normal"],
            fontName="Helvetica", fontSize=9, leading=13,
            textColor=HexColor("#111111")
        )
    }
    
    flowables = []
    
    # 1. HALAMAN COVER DOKUMEN TEKNIS
    flowables.append(Spacer(1, 3 * cm))
    flowables.append(Paragraph("DOKUMEN SPESIFIKASI TEKNIS", my_styles["CoverTitle"]))
    flowables.append(Paragraph("NetLabs — Intelligent Tutoring System", my_styles["CoverSubtitle"]))
    flowables.append(Spacer(1, 1.5 * cm))
    flowables.append(Paragraph(
        "Dokumen ini memuat detail arsitektur integrasi sistem, petunjuk komprehensif setup "
        "dan deployment server (SETUP.md), spesifikasi lengkap API Endpoints Laravel & Flask AI (API.md), "
        "lampiran kode sumber utama sistem (IMPLEMENTATION.md), serta laporan evaluasi pengujian "
        "fungsionalitas sistem (TEST_REPORT.md).",
        my_styles["CoverDesc"]
    ))
    flowables.append(Spacer(1, 5 * cm))
    flowables.append(Paragraph(
        "<b>DIREKTORAT TEKNOLOGI & PENGEMBANGAN SISTEM</b><br/>"
        "NetLabs Academy &copy; 2026<br/>"
        "Klasifikasi: Dokumen Teknis Internal / Lampiran Skripsi",
        my_styles["CoverMeta"]
    ))
    flowables.append(PageBreak())
    
    # 2. GABUNGKAN BERKAS MD SATU PER SATU
    for idx, doc_info in enumerate(FILES_TO_MERGE):
        print(f"Menggabungkan {doc_info['name']}...")
        if not os.path.exists(doc_info['path']):
            print(f"[WARNING] Berkas {doc_info['path']} tidak ditemukan! Melewati.")
            continue
            
        doc_flowables = parse_markdown_to_flowables(doc_info['path'], my_styles, f"{idx+1}. {doc_info['name']}")
        flowables.extend(doc_flowables)
        
        # Tambahkan page break kecuali untuk file terakhir
        if idx < len(FILES_TO_MERGE) - 1:
            flowables.append(PageBreak())
            
    # 3. BANGUN PDF
    doc.build(flowables, canvasmaker=NumberedCanvas)
    print(f"[OK] Berkas DOKUMEN_TEKNIS.pdf berhasil dibuat di: {OUTPUT_FILE}")


if __name__ == "__main__":
    buat_pdf()
