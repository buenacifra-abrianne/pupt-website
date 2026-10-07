"""BM25 retrieval across the complete fetched corpus with source/page references."""
import math
import re
from collections import Counter


def tokens(text):
    return re.findall(r"\w+", text.lower())


class KnowledgeIndex:
    def __init__(self, passages):
        if not passages:
            raise ValueError("Knowledge corpus is empty")
        self.chunks = []
        for passage in passages:
            text = passage["content"]
            for offset in range(0, len(text), 1600):
                self.chunks.append(dict(passage, content=text[offset:offset + 1800]))
        self.counts = [Counter(tokens(p["content"])) for p in self.chunks]
        self.lengths = [sum(c.values()) for c in self.counts]
        self.avg_length = max(1, sum(self.lengths) / len(self.lengths))
        self.frequency = Counter(term for count in self.counts for term in count)

    def retrieve(self, question, answer, max_chars=16000):
        query = Counter({term: count * 3 for term, count in Counter(tokens(question)).items()})
        query += Counter(tokens(answer))
        scores = []
        for i, count in enumerate(self.counts):
            score = 0
            for term, weight in query.items():
                tf = count.get(term, 0)
                if tf:
                    idf = math.log(1 + (len(self.chunks) - self.frequency[term] + .5) / (self.frequency[term] + .5))
                    score += min(weight, 6) * idf * tf * 2.5 / (tf + 1.5 * (.25 + .75 * self.lengths[i] / self.avg_length))
            scores.append((score, i))
        selected, size = [], 0
        for score, i in sorted(scores, reverse=True):
            if score <= 0:
                break
            p = self.chunks[i]
            block = f"[{len(selected) + 1}] Source: {p['source']}; page: {p['page']}; passage: {p['id']}\n{p['content']}"
            block_size = len(block) + (2 if selected else 0)
            if size + block_size > max_chars:
                continue
            selected.append(block)
            size += block_size
        return "\n\n".join(selected), len(selected)
