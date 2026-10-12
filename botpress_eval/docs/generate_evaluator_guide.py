"""Render the adjacent Markdown guide as a paginated, searchable PDF."""
from pathlib import Path
import re

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
from matplotlib.backends.backend_pdf import PdfPages
from matplotlib.font_manager import FontProperties

ROOT = Path(__file__).resolve().parent
SOURCE = ROOT / 'evaluator_details.md'
OUTPUT = ROOT / 'evaluator_details.pdf'
WIDTH, HEIGHT = 595.28, 841.89
LEFT, RIGHT, BOTTOM = 48, 48, 56


def render():
    plt.rcParams['pdf.fonttype'] = 42
    plt.rcParams['font.family'] = 'DejaVu Sans'
    figures = []
    fig = None
    y = 0
    section = 'Overview'

    def new_page():
        nonlocal fig, y
        fig = plt.figure(figsize=(WIDTH / 72, HEIGHT / 72), dpi=72)
        fig.patch.set_facecolor('white')
        fig.text(LEFT / WIDTH, (HEIGHT - 30) / HEIGHT,
                 'PUP TAGUIG  /  BOTPRESS EVALUATION', fontsize=8,
                 color='#496078', weight='bold')
        fig.add_artist(plt.Line2D([LEFT / WIDTH, 1 - RIGHT / WIDTH],
                                 [(HEIGHT - 40) / HEIGHT] * 2,
                                 transform=fig.transFigure, color='#bfd0df', linewidth=0.7))
        figures.append(fig)
        y = HEIGHT - 64
        fig.canvas.draw()

    def wrap(text, size, family, weight, available):
        renderer = fig.canvas.get_renderer()
        font = FontProperties(family=family, size=size, weight=weight)
        lines, line = [], ''
        for word in text.split():
            candidate = (line + ' ' + word).strip()
            width = renderer.get_text_width_height_descent(candidate, font, False)[0]
            if line and width > available:
                lines.append(line)
                line = word
            else:
                line = candidate
        if line:
            lines.append(line)
        return lines

    def paragraph(text, size=10, weight='normal', color='#253649',
                  family='DejaVu Sans', indent=0, gap=7):
        nonlocal y
        lines = wrap(text, size, family, weight, WIDTH - LEFT - RIGHT - indent)
        leading = size * 1.48
        for line in lines:
            if y - leading < BOTTOM:
                new_page()
                fig.text(LEFT / WIDTH, y / HEIGHT, section + ' (continued)',
                         fontsize=10, weight='bold', color='#496078', va='top')
                y -= 27
            fig.text((LEFT + indent) / WIDTH, y / HEIGHT, line,
                     fontsize=size, weight=weight, family=family, color=color, va='top')
            y -= leading
        y -= gap

    new_page()
    raw = SOURCE.read_text(encoding='utf-8').splitlines()
    paragraph('EVALUATOR GUIDE', size=11, weight='bold', color='#26738b', gap=18)
    in_code = False
    buffer = []

    def flush():
        if buffer:
            paragraph(' '.join(buffer))
            buffer.clear()

    for line in raw:
        if line.startswith('## '):
            flush()
            section = line[3:]
            if section.startswith('1. '):
                y -= 15
            else:
                new_page()
            paragraph(section, size=18, weight='bold', color='#133f60', gap=16)
        elif line.startswith('# '):
            flush()
            paragraph(line[2:], size=26, weight='bold', color='#133f60', gap=23)
        elif line.startswith('```'):
            flush()
            in_code = not in_code
            if not in_code:
                y -= 6
        elif in_code:
            paragraph(line, size=9, family='DejaVu Sans Mono', color='#1b6171', gap=3)
        elif not line.strip():
            flush()
        elif line.startswith('- ') or re.match(r'^\d+(?:-\d+)?\. ', line):
            flush()
            paragraph(line, indent=8, gap=6)
        else:
            buffer.append(line)
    flush()

    with PdfPages(OUTPUT, metadata={
        'Title': 'Botpress Evaluator: Complete Process and Record Guide',
        'Subject': 'Configuration, process, scoring, errors, records, and runtime',
        'Author': 'PUP Taguig chatbot project',
    }) as pdf:
        for number, page in enumerate(figures, 1):
            page.text(LEFT / WIDTH, 29 / HEIGHT,
                      'Implementation snapshot | 7 October 2026 | Asia/Singapore',
                      fontsize=7, color='#64758a')
            page.text((WIDTH - RIGHT) / WIDTH, 29 / HEIGHT,
                      f'{number} / {len(figures)}', ha='right', fontsize=8, color='#64758a')
            pdf.savefig(page)
        figures[0].savefig(ROOT / 'evaluator_cover_preview.png', dpi=110)
    plt.close('all')
    print(f'Created {OUTPUT} ({len(figures)} pages)')


if __name__ == '__main__':
    render()
