"""Extract local PDFs, caching passages by content hash."""
import hashlib
import json
from pathlib import Path

from pypdf import PdfReader

ROOT = Path(__file__).resolve().parent


def load_pdf_knowledge(folder=None, cache_file=None):
    folder = Path(folder) if folder is not None else ROOT / 'knowledge_base'
    cache_file = Path(cache_file) if cache_file is not None else ROOT / '.pdf_cache.json'
    files = sorted(folder.glob('*.pdf'))
    if not files:
        raise ValueError(f'No local PDFs in {folder}; add PDFs before evaluation.')
    signatures = {p.name: hashlib.sha256(p.read_bytes()).hexdigest() for p in files}
    try:
        cached = json.loads(cache_file.read_text(encoding='utf-8'))
        if cached.get('version') == 1 and cached.get('signatures') == signatures and cached.get('passages'):
            return cached['passages']
    except (OSError, ValueError):
        pass
    passages = []
    for file in files:
        reader = PdfReader(file)
        extracted = []
        for number, page in enumerate(reader.pages, 1):
            text = (page.extract_text() or '').strip()
            if text:
                extracted.append({'id': f'{file.name}:page:{number}', 'file_id': file.name,
                                  'source': file.name, 'kb_id': 'local-pdfs',
                                  'page': number, 'content': text})
        if not extracted:
            raise ValueError(f'{file.name} has no readable text; OCR this PDF first.')
        passages.extend(extracted)
    cache_file.write_text(json.dumps({'version': 1, 'signatures': signatures, 'passages': passages},
                                    ensure_ascii=False), encoding='utf-8')
    return passages
