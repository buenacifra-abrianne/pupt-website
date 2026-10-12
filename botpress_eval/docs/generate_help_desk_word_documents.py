"""Create four editable Word documents from the revised Markdown masters.

Run from the project root with .venv/Scripts/python.exe. Requires python-docx.
All deliverables are placed in AI_HELP_DESK_WORD_DOCUMENTS beside this script.
"""
from pathlib import Path
import re
from zipfile import ZipFile, ZIP_DEFLATED

from docx import Document
from docx.enum.table import WD_CELL_VERTICAL_ALIGNMENT, WD_TABLE_ALIGNMENT
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.opc.constants import RELATIONSHIP_TYPE
from docx.shared import Inches, Mm, Pt, RGBColor

ROOT = Path(__file__).resolve().parent
OUTPUT = ROOT / "AI_HELP_DESK_WORD_DOCUMENTS"
NAMES = [
    "AI_HELP_DESK_ARCHITECTURE_AND_AI_CAPABILITIES",
    "AI_HELP_DESK_KNOWLEDGE_BASE_AND_CONTENT_MANAGEMENT",
    "AI_HELP_DESK_EVALUATION",
    "AI_HELP_DESK_PRIVACY_AND_SECURITY_ASSESSMENT",
]
NAVY, TEAL, INK, LIGHT = "17354D", "167477", "243746", "EEF4F7"
PAGE_WIDTH = 6.85


def shade(cell, color):
    element = OxmlElement("w:shd")
    element.set(qn("w:fill"), color)
    cell._tc.get_or_add_tcPr().append(element)


def hyperlink(paragraph, label, url):
    relationship = paragraph.part.relate_to(url, RELATIONSHIP_TYPE.HYPERLINK, is_external=True)
    link = OxmlElement("w:hyperlink")
    link.set(qn("r:id"), relationship)
    run = OxmlElement("w:r")
    properties = OxmlElement("w:rPr")
    color = OxmlElement("w:color")
    color.set(qn("w:val"), TEAL)
    properties.append(color)
    underline = OxmlElement("w:u")
    underline.set(qn("w:val"), "single")
    properties.append(underline)
    run.append(properties)
    text = OxmlElement("w:t")
    text.text = label
    run.append(text)
    link.append(run)
    paragraph._p.append(link)


def inline(paragraph, text):
    pattern = r"(\[[^\]]+\]\(https?://[^\s)]+\)|\*\*[^*]+\*\*|`[^`]+`)"
    for part in re.split(pattern, text):
        if not part:
            continue
        match = re.fullmatch(r"\[([^\]]+)\]\((https?://[^\s)]+)\)", part)
        if match:
            hyperlink(paragraph, match[1], match[2])
        else:
            run = paragraph.add_run(part[2:-2] if part.startswith("**") else part.strip("`"))
            if part.startswith("**"):
                run.bold = True
            if part.startswith("`"):
                run.font.name = "Consolas"
                run.font.size = Pt(9)


def field(paragraph, instruction):
    element = OxmlElement("w:fldSimple")
    element.set(qn("w:instr"), instruction)
    paragraph._p.append(element)


def configure(doc, title):
    doc.core_properties.title = title
    doc.core_properties.author = "PUP Taguig AI Help Desk study"
    doc.core_properties.subject = "Revised system and evaluator documentation"
    doc.core_properties.comments = "Editable Word version of the revised document masters."
    section = doc.sections[0]
    section.page_width, section.page_height = Mm(210), Mm(297)
    section.top_margin = section.bottom_margin = Mm(18)
    section.left_margin = section.right_margin = Mm(18)
    section.header_distance = section.footer_distance = Mm(8)
    normal = doc.styles["Normal"]
    normal.font.name, normal.font.size = "Arial", Pt(10.5)
    normal.font.color.rgb = RGBColor.from_string(INK)
    normal.paragraph_format.space_after = Pt(8)
    normal.paragraph_format.line_spacing = 1.15
    normal.paragraph_format.widow_control = True
    for name, size, color in [("Title", 24, NAVY), ("Heading 1", 14, NAVY), ("Heading 2", 11.5, TEAL)]:
        style = doc.styles[name]
        style.font.name, style.font.size = "Arial", Pt(size)
        style.font.color.rgb = RGBColor.from_string(color)
        style.font.bold = True
        style.paragraph_format.space_before = Pt(14 if name != "Title" else 0)
        style.paragraph_format.space_after = Pt(8)
        style.paragraph_format.keep_with_next = True
    for name in ["List Bullet", "List Number"]:
        style = doc.styles[name]
        style.font.name, style.font.size = "Arial", Pt(10.5)
        style.paragraph_format.space_after = Pt(5)
    header = section.header.paragraphs[0]
    run = header.add_run("PUP TAGUIG  /  AI HELP DESK")
    run.font.name, run.font.size = "Arial", Pt(8)
    run.font.color.rgb, run.bold = RGBColor.from_string(TEAL), True
    footer = section.footer.paragraphs[0]
    footer.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    run = footer.add_run("Revised 12 October 2026  |  Page ")
    run.font.name, run.font.size = "Arial", Pt(8)
    field(footer, "PAGE")
    footer.add_run(" of ").font.size = Pt(8)
    field(footer, "NUMPAGES")
    settings = OxmlElement("w:updateFields")
    settings.set(qn("w:val"), "true")
    doc.settings.element.append(settings)


def make_table(doc, raw_rows, widths=None):
    column_count = len(raw_rows[0])
    if widths is None:
        weights = {2: [0.45, 0.55], 3: [0.29, 0.25, 0.46]}.get(column_count, [1 / column_count] * column_count)
        widths = [PAGE_WIDTH * value for value in weights]
    result = doc.add_table(rows=0, cols=column_count)
    result.alignment = WD_TABLE_ALIGNMENT.CENTER
    result.autofit = False
    for column, width in zip(result.columns, widths):
        column.width = Inches(width)
    for index, cells in enumerate(raw_rows):
        row = result.add_row()
        no_split = OxmlElement("w:cantSplit")
        row._tr.get_or_add_trPr().append(no_split)
        if index == 0:
            repeat = OxmlElement("w:tblHeader")
            row._tr.get_or_add_trPr().append(repeat)
        for cell, text, width in zip(row.cells, cells, widths):
            cell.width = Inches(width)
            cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.TOP
            shade(cell, NAVY if index == 0 else LIGHT if index % 2 == 0 else "FFFFFF")
            paragraph = cell.paragraphs[0]
            paragraph.paragraph_format.space_before = Pt(4)
            paragraph.paragraph_format.space_after = Pt(5)
            paragraph.paragraph_format.line_spacing = 1.08
            inline(paragraph, text)
            for run in paragraph.runs:
                run.font.size = Pt(9)
                if index == 0:
                    run.font.color.rgb, run.bold = RGBColor(255, 255, 255), True
    doc.add_paragraph().paragraph_format.space_after = Pt(2)
    return result


def architecture_diagram(doc):
    # Native Word table: every label and arrow remains editable.
    make_table(doc, [
        ["Flow", "Sequence"],
        ["Visitor interaction", "Laravel pages → Open chat → Botpress iframe → Hosted replies"],
        ["Content transport", "CMS / links → Discover / extract → File upload → Indexing verified"],
        ["Offline evaluation", "Historical logs → Local PDF retrieval → Ollama judge → CSV metrics"],
    ], [1.65, PAGE_WIDTH - 1.65])


def render(name):
    lines = (ROOT / (name + ".md")).read_text(encoding="utf-8-sig").splitlines()
    doc = Document()
    configure(doc, lines[0].lstrip("# "))
    buffer, in_code, index = [], False, 0

    def flush():
        if buffer:
            inline(doc.add_paragraph(), " ".join(buffer))
            buffer.clear()

    while index < len(lines):
        line = lines[index]
        if line.startswith("```"):
            flush()
            in_code = not in_code
        elif in_code:
            paragraph = doc.add_paragraph()
            run = paragraph.add_run(line)
            run.font.name, run.font.size = "Consolas", Pt(9)
            paragraph.paragraph_format.space_after = Pt(3)
        elif line.startswith("|"):
            flush()
            rows = []
            while index < len(lines) and lines[index].startswith("|"):
                cells = [cell.strip() for cell in lines[index].strip().strip("|").split("|")]
                if not all(re.fullmatch(r"[:\- ]+", cell) for cell in cells):
                    rows.append(cells)
                index += 1
            make_table(doc, rows)
            continue
        elif line.startswith("### "):
            flush()
            inline(doc.add_paragraph(style="Heading 2"), line[4:])
        elif line.startswith("## "):
            flush()
            inline(doc.add_paragraph(style="Heading 1"), line[3:])
            if name == NAMES[0] and line.startswith("## 2."):
                architecture_diagram(doc)
        elif line.startswith("# "):
            flush()
            inline(doc.add_paragraph(style="Title"), line[2:])
        elif line.startswith("- "):
            flush()
            inline(doc.add_paragraph(style="List Bullet"), line[2:])
        elif not line.strip():
            flush()
        else:
            buffer.append(line.strip())
        index += 1
    flush()
    destination = OUTPUT / (name + ".docx")
    doc.save(destination)
    print(f"Created {destination.name}")


def main():
    OUTPUT.mkdir(exist_ok=True)
    for name in NAMES:
        render(name)
    package = ROOT / "AI_HELP_DESK_WORD_DOCUMENTS.zip"
    with ZipFile(package, "w", ZIP_DEFLATED) as archive:
        for name in NAMES:
            file = OUTPUT / (name + ".docx")
            archive.write(file, OUTPUT.name + "/" + file.name)
    print(f"Folder: {OUTPUT}")
    print(f"ZIP: {package.name}")


if __name__ == "__main__":
    main()
