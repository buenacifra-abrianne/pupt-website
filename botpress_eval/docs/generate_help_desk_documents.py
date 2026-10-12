"""Generate the four revised, searchable study PDFs from adjacent Markdown sources.

Run with the project virtual environment; requires reportlab.
Markdown sources are the editable document masters. No secrets or CSV rows are embedded.
"""
from html import escape
from pathlib import Path
import re
from zipfile import ZipFile, ZIP_DEFLATED

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, LongTable, TableStyle, Flowable,
)

ROOT = Path(__file__).resolve().parent
NAMES = [
    "AI_HELP_DESK_ARCHITECTURE_AND_AI_CAPABILITIES",
    "AI_HELP_DESK_KNOWLEDGE_BASE_AND_CONTENT_MANAGEMENT",
    "AI_HELP_DESK_EVALUATION",
    "AI_HELP_DESK_PRIVACY_AND_SECURITY_ASSESSMENT",
]
NAVY = colors.HexColor("#17354d")
TEAL = colors.HexColor("#167477")
INK = colors.HexColor("#243746")
LIGHT = colors.HexColor("#eef4f7")
FONT, BOLD = "Helvetica", "Helvetica-Bold"
font_path = Path("C:/Windows/Fonts/arial.ttf")
bold_path = Path("C:/Windows/Fonts/arialbd.ttf")
if font_path.exists() and bold_path.exists():
    pdfmetrics.registerFont(TTFont("StudyArial", str(font_path)))
    pdfmetrics.registerFont(TTFont("StudyArialBold", str(bold_path)))
    pdfmetrics.registerFontFamily("StudyArial", normal="StudyArial", bold="StudyArialBold",
                                italic="StudyArial", boldItalic="StudyArialBold")
    FONT, BOLD = "StudyArial", "StudyArialBold"

styles = getSampleStyleSheet()
styles.add(ParagraphStyle("StudyBody", fontName=FONT, fontSize=10, leading=14.6,
                          textColor=INK, spaceAfter=8, splitLongWords=True,
                          allowWidows=0, allowOrphans=0))
styles.add(ParagraphStyle("StudyTitle", fontName=BOLD, fontSize=25, leading=30,
                          textColor=NAVY, spaceAfter=16, keepWithNext=True))
styles.add(ParagraphStyle("StudyH2", fontName=BOLD, fontSize=14, leading=19,
                          textColor=NAVY, spaceBefore=16, spaceAfter=9, keepWithNext=True))
styles.add(ParagraphStyle("StudyH3", fontName=BOLD, fontSize=11, leading=16,
                          textColor=TEAL, spaceBefore=9, spaceAfter=7, keepWithNext=True))
styles.add(ParagraphStyle("StudyBullet", parent=styles["StudyBody"], leftIndent=11,
                          firstLineIndent=-8, spaceAfter=5))
styles.add(ParagraphStyle("StudyCell", parent=styles["StudyBody"], fontSize=9,
                          leading=12.5, spaceAfter=0, allowWidows=1, allowOrphans=1))
styles.add(ParagraphStyle("StudyHeadCell", parent=styles["StudyCell"], fontName=BOLD,
                          textColor=colors.white))
styles.add(ParagraphStyle("StudyCode", fontName="Courier", fontSize=8.6, leading=13,
                          spaceAfter=4, leftIndent=10, backColor=LIGHT))


def inline(text):
    text = escape(text)
    text = re.sub(r"\[([^\]]+)\]\((https?://[^\s)]+)\)",
                  r'<link href="\2" color="#167477"><u>\1</u></link>', text)
    text = re.sub(r"\*\*(.+?)\*\*", r"<b>\1</b>", text)
    text = re.sub(r"`([^`]+)`", r'<font name="Courier">\1</font>', text)
    return text


class ArchitectureDiagram(Flowable):
    """Vector diagram showing distinct flows and owner-confirmed production indexing."""
    def __init__(self, width):
        super().__init__()
        self.width, self.height = width, 172

    def draw(self):
        canvas = self.canv
        lanes = [
            ("Visitor interaction", ["Laravel pages", "Open chat", "Botpress iframe", "Hosted replies"]),
            ("Content transport", ["CMS / links", "Discover / extract", "File upload", "Indexing verified"]),
            ("Offline evaluation", ["Historical logs", "Local PDF retrieval", "Ollama judge", "CSV metrics"]),
        ]
        cell_width = (self.width - 36) / 4
        for lane, (label, boxes) in enumerate(lanes):
            top = self.height - lane * 55
            canvas.setFont(BOLD, 8.8)
            canvas.setFillColor(TEAL)
            canvas.drawString(0, top - 10, label)
            for index, text in enumerate(boxes):
                x = index * (cell_width + 12)
                canvas.setFillColor(LIGHT)
                canvas.setStrokeColor(colors.HexColor("#c9d8e2"))
                canvas.roundRect(x, top - 43, cell_width, 25, 4, fill=1, stroke=1)
                canvas.setFillColor(INK)
                canvas.setFont(FONT, 8)
                canvas.drawCentredString(x + cell_width / 2, top - 33, text)
                if index < 3:
                    canvas.setStrokeColor(TEAL)
                    end = x + cell_width
                    canvas.line(end + 2, top - 30, end + 10, top - 30)
                    canvas.line(end + 7, top - 27, end + 10, top - 30)
                    canvas.line(end + 7, top - 33, end + 10, top - 30)


def table(lines, width):
    raw_rows = [[c.strip() for c in line.strip().strip("|").split("|")] for line in lines]
    raw_rows = [r for r in raw_rows if not all(re.fullmatch(r"[:\- ]+", c) for c in r)]
    column_count = len(raw_rows[0])
    weights = {2: [0.45, 0.55], 3: [0.29, 0.25, 0.46]}.get(column_count, [1/column_count]*column_count)
    rows = [[Paragraph(inline(cell), styles["StudyHeadCell" if i == 0 else "StudyCell"])
             for cell in row] for i, row in enumerate(raw_rows)]
    result = LongTable(rows, colWidths=[width*w for w in weights], repeatRows=1,
                       hAlign="LEFT", spaceBefore=4, spaceAfter=12)
    result.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), NAVY),
        ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, LIGHT]),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 8),
        ("RIGHTPADDING", (0, 0), (-1, -1), 8),
        ("TOPPADDING", (0, 0), (-1, -1), 8),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 8),
        ("LINEBELOW", (0, -1), (-1, -1), 0.5, colors.HexColor("#cad7df")),
    ]))
    return result


class StudyDoc(SimpleDocTemplate):
    def afterFlowable(self, flowable):
        if isinstance(flowable, Paragraph) and flowable.style.name in ("StudyH2", "StudyH3"):
            title = flowable.getPlainText()
            key = f"section-{self.page}-{self.seq.nextf('bookmark')}"
            self.canv.bookmarkPage(key)
            self.canv.addOutlineEntry(title, key, 0 if flowable.style.name == "StudyH2" else 1)


def render(name):
    source = ROOT / (name + ".md")
    lines = source.read_text(encoding="utf-8-sig").splitlines()
    title = lines[0].lstrip("# ")
    doc = StudyDoc(str(ROOT / (name + ".pdf")), pagesize=A4,
                   leftMargin=46, rightMargin=46, topMargin=62, bottomMargin=53,
                   title=title, author="PUP Taguig AI Help Desk study",
                   subject="Repository-based revision, 12 October 2026")
    story, buffer, in_code = [], [], False

    def flush():
        if buffer:
            story.append(Paragraph(inline(" ".join(buffer)), styles["StudyBody"]))
            buffer.clear()

    index = 0
    while index < len(lines):
        line = lines[index]
        if line.startswith("```"):
            flush()
            in_code = not in_code
            story.append(Spacer(1, 5))
        elif in_code:
            story.append(Paragraph(escape(line) or " ", styles["StudyCode"]))
        elif line.startswith("|"):
            flush()
            group = []
            while index < len(lines) and lines[index].startswith("|"):
                group.append(lines[index])
                index += 1
            story.append(table(group, doc.width))
            continue
        elif line.startswith("### "):
            flush()
            story.append(Paragraph(inline(line[4:]), styles["StudyH3"]))
        elif line.startswith("## "):
            flush()
            story.append(Paragraph(inline(line[3:]), styles["StudyH2"]))
            if name == NAMES[0] and line.startswith("## 2."):
                story.append(ArchitectureDiagram(doc.width))
        elif line.startswith("# "):
            flush()
            story.append(Paragraph(inline(line[2:]), styles["StudyTitle"]))
        elif line.startswith("- "):
            flush()
            story.append(Paragraph("&#8226; " + inline(line[2:]), styles["StudyBullet"]))
        elif not line.strip():
            flush()
        else:
            buffer.append(line.strip())
        index += 1
    flush()

    def chrome(canvas, document):
        canvas.saveState()
        width, height = A4
        canvas.setFillColor(TEAL)
        canvas.setFont(BOLD, 8.2)
        canvas.drawString(46, height-32, "PUP TAGUIG  /  AI HELP DESK")
        canvas.setStrokeColor(colors.HexColor("#c9d8e2"))
        canvas.line(46, height-42, width-46, height-42)
        canvas.line(46, 39, width-46, 39)
        canvas.setFont(FONT, 7.7)
        canvas.setFillColor(INK)
        canvas.drawString(46, 25, "Revised 12 October 2026  |  Repository and evaluator evidence")
        canvas.drawRightString(width-46, 25, str(document.page))
        canvas.restoreState()

    doc.build(story, onFirstPage=chrome, onLaterPages=chrome)
    print(f"Created {name}.pdf")


def main():
    for name in NAMES:
        render(name)
    package = ROOT / "AI_HELP_DESK_REVISED_DOCUMENTS.zip"
    with ZipFile(package, "w", ZIP_DEFLATED) as archive:
        for name in NAMES:
            archive.write(ROOT / (name + ".pdf"), name + ".pdf")
    print(f"Created {package.name} (four separate PDFs)")


if __name__ == "__main__":
    main()
