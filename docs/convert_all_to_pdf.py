import os
import subprocess
import markdown
import shutil
from pygments.formatters import HtmlFormatter

EDGE_PATH = r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DOCS_DIR = BASE_DIR
ROOT_DIR = os.path.dirname(BASE_DIR)
ARTIFACTS_DIR = r"C:\Users\iqbal\.gemini\antigravity-ide\brain\99306097-1e3f-4822-a1e5-59dca265b836"

# Carbon.now.sh Pygments CSS theme (OneDark / Dracula style)
PYGMENTS_CSS = HtmlFormatter(style="one-dark").get_style_defs('.codehilite')

CSS_STYLE = f"""
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');
    
    @page {{
        size: A4;
        margin: 10mm 10mm 10mm 10mm;
    }}

    * {{
        box-sizing: border-box;
    }}

    body {{
        font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif;
        color: #000000;
        line-height: 1.35;
        font-size: 9.5pt;
        background: #ffffff;
        padding: 0;
        margin: 0;
    }}

    /* Ringkas & Padat - Bebas Space Kosong */
    h1 {{
        font-size: 16pt;
        color: #000000;
        border-bottom: 2px solid #000000;
        padding-bottom: 4px;
        margin-top: 12px;
        margin-bottom: 8px;
        page-break-before: avoid !important;
        page-break-after: avoid !important;
    }}

    h2 {{
        font-size: 12.5pt;
        color: #000000;
        border-bottom: 1px solid #666666;
        padding-bottom: 2px;
        margin-top: 10px;
        margin-bottom: 6px;
        page-break-after: avoid !important;
    }}

    h3 {{
        font-size: 10.5pt;
        color: #000000;
        margin-top: 8px;
        margin-bottom: 4px;
        font-weight: 700;
        page-break-after: avoid !important;
    }}

    h4 {{
        font-size: 9.5pt;
        color: #000000;
        margin-top: 6px;
        margin-bottom: 3px;
        font-weight: 700;
        page-break-after: avoid !important;
    }}

    p {{
        margin-top: 0;
        margin-bottom: 5px;
        text-align: justify;
        color: #000000;
    }}

    strong {{
        color: #000000;
    }}

    ul, ol {{
        margin-top: 2px;
        margin-bottom: 5px;
        padding-left: 18px;
    }}

    li {{
        margin-bottom: 2px;
        color: #000000;
    }}

    /* Tabel Padat Hitam Tegas */
    table {{
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0;
        font-size: 9pt;
        page-break-inside: avoid;
    }}

    th {{
        background-color: #111111;
        color: #ffffff;
        font-weight: 700;
        text-align: left;
        padding: 5px 8px;
        border: 1px solid #000000;
    }}

    td {{
        padding: 4px 8px;
        border: 1px solid #000000;
        color: #000000;
    }}

    tr:nth-child(even) {{
        background-color: #f4f4f4;
    }}

    /* Carbon.now.sh Style Code Block (Warna-Warni) */
    .codehilite, pre {{
        background-color: #1e1e2e !important;
        color: #cdd6f4 !important;
        border-radius: 8px;
        padding: 10px 14px 12px 14px;
        margin: 6px 0 !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        font-family: 'JetBrains Mono', 'Consolas', monospace;
        font-size: 8.5pt;
        line-height: 1.4;
        overflow-x: auto;
        position: relative;
        border: 1px solid #313244;
        page-break-inside: avoid;
    }}

    /* Carbon Window Control Buttons (Mac Style: Red, Yellow, Green) */
    .codehilite::before, pre::before {{
        content: "•••";
        display: block;
        color: #ff5f56;
        letter-spacing: 3px;
        font-size: 14px;
        margin-bottom: 6px;
        border-bottom: 1px solid #313244;
        padding-bottom: 3px;
        text-shadow: 14px 0 0 #ffbd2e, 28px 0 0 #27c93f;
    }}

    code {{
        font-family: 'JetBrains Mono', 'Consolas', monospace;
        background-color: #e2e8f0;
        color: #000000;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 8.5pt;
        font-weight: 600;
    }}

    pre code {{
        background: none !important;
        color: inherit !important;
        padding: 0 !important;
        font-weight: normal;
    }}

    {PYGMENTS_CSS}

    /* Additional Carbon Color Overrides for Python & PHP */
    .codehilite .k, .codehilite .kd, .codehilite .kn, .codehilite .kr {{ color: #ff79c6 !important; font-weight: bold; }} /* Pink Keywords */
    .codehilite .s, .codehilite .sa, .codehilite .sb, .codehilite .sc, .codehilite .s2, .codehilite .sh, .codehilite .s1 {{ color: #f1fa8c !important; }} /* Yellow Strings */
    .codehilite .nf, .codehilite .fm, .codehilite .nc, .codehilite .na {{ color: #50fa7b !important; font-weight: bold; }} /* Green Functions */
    .codehilite .nb, .codehilite .bp, .codehilite .nv, .codehilite .vc, .codehilite .vg, .codehilite .vi {{ color: #8be9fd !important; }} /* Cyan Variables/Builtins */
    .codehilite .mi, .codehilite .mf, .codehilite .mh, .codehilite .mo {{ color: #bd93f9 !important; }} /* Purple Numbers */
    .codehilite .c, .codehilite .ch, .codehilite .cm, .codehilite .c1, .codehilite .cs {{ color: #6272a4 !important; font-style: italic; }} /* Gray Comments */
    .codehilite .o, .codehilite .ow {{ color: #ff79c6 !important; }} /* Operators */

    blockquote {{
        background-color: #f8fafc;
        border-left: 3px solid #000000;
        margin: 6px 0;
        padding: 6px 10px;
        color: #000000;
    }}

    blockquote p {{
        margin: 0;
        color: #000000;
    }}

    hr {{
        border: none;
        border-top: 1px solid #000000;
        margin: 10px 0;
    }}
</style>
"""

files_to_convert = [
    ("dokumen_teknis_lengkap_netlabs.md", "Dokumen_Teknis_Lengkap_NetLabs.pdf"),
    ("MANUAL_BOOK.md", "Manual_Book_NetLabs.pdf"),
]

def convert_md_to_pdf(md_filename, pdf_filename):
    md_path = os.path.join(DOCS_DIR, md_filename)
    if not os.path.exists(md_path):
        md_path = os.path.join(ROOT_DIR, md_filename)
    if not os.path.exists(md_path):
        md_path = os.path.join(ROOT_DIR, md_filename.upper())
    if not os.path.exists(md_path):
        md_path = os.path.join(ARTIFACTS_DIR, md_filename)
    if not os.path.exists(md_path):
        print(f"Error: {md_filename} does not exist in DOCS_DIR, ROOT_DIR, or ARTIFACTS_DIR.")
        return

    with open(md_path, 'r', encoding='utf-8') as f:
        md_content = f.read()

    # Convert markdown to html with codehilite and tables
    html_body = markdown.markdown(
        md_content, 
        extensions=['tables', 'fenced_code', 'codehilite', 'nl2br', 'toc']
    )
    
    full_html = f"""<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{pdf_filename}</title>
    {CSS_STYLE}
</head>
<body>
    {html_body}
</body>
</html>
"""

    temp_html_path = os.path.join(DOCS_DIR, md_filename.replace('.md', '.html'))
    with open(temp_html_path, 'w', encoding='utf-8') as f:
        f.write(full_html)

    out_pdf_docs = os.path.join(DOCS_DIR, pdf_filename)
    out_pdf_artifacts = os.path.join(ARTIFACTS_DIR, pdf_filename)

    cmd = [
        EDGE_PATH,
        "--headless",
        "--disable-gpu",
        "--no-pdf-header-footer",
        f"--print-to-pdf={out_pdf_docs}",
        temp_html_path
    ]

    print(f"Converting {md_filename} -> {pdf_filename}...")
    res = subprocess.run(cmd, capture_output=True, text=True)
    
    if os.path.exists(out_pdf_docs):
        if os.path.exists(os.path.dirname(out_pdf_artifacts)):
            shutil.copy(out_pdf_docs, out_pdf_artifacts)
            print(f"[SUCCESS] Created PDF:\n   1. {out_pdf_docs}\n   2. {out_pdf_artifacts}")
        else:
            print(f"[SUCCESS] Created PDF:\n   1. {out_pdf_docs}")
    else:
        print(f"[ERROR] Failed to create PDF for {md_filename}. Edge stderr: {res.stderr}")

if __name__ == "__main__":
    os.makedirs(DOCS_DIR, exist_ok=True)
    for md_file, pdf_file in files_to_convert:
        convert_md_to_pdf(md_file, pdf_file)
