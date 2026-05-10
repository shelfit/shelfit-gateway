#!/usr/bin/env python3
"""One-shot cleaner for var/data/books.csv description column.

Strips librarian/ISBN/alternate-cover preamble junk and recases all-caps
descriptions to sentence case. Writes in place; prints first 10 changes.
"""
import csv
import re
import sys

SRC = 'var/data/books.csv'
DST = 'var/data/books.csv.tmp'

# Patterns that match a junk preamble at the start of a description.
# Order matters: most specific first. Each is applied repeatedly until none fire.
JUNK_PATTERNS = [
    # Librarian's note: ... ending in "here" / "here." (handles smart quote)
    re.compile(r"^\s*Librarian['’]s\s+note[:.].*?\bhere\.?", re.IGNORECASE | re.DOTALL),
    # Librarian's note: ... ISBN: NNN.   (the "previously-published edition of" variant)
    re.compile(r"^\s*Librarian['’]s\s+note[:.].*?ISBN:?\s*[\d\-Xx]+\.?\s*", re.IGNORECASE | re.DOTALL),
    # NOTE: Alternate Cover Edition followed by one or more ISBN/ASIN tokens
    re.compile(r"^\s*NOTE[:.]\s*Alternate\s+Cover\s+Edition\s*(?:(?:ISBN|ASIN)[:\s]*[A-Z0-9\-]+\s*)+", re.IGNORECASE),
    # "Alternate Cover Edition ISBN: NNN (ISBN13: NNN)"
    re.compile(r"^\s*(?:Alternate|Alternative)\s+[Cc]over[^.]*?\(ISBN13:\s*\d+\)?", re.IGNORECASE),
    # "Alternate Cover for ISBN: NNN" / "Alternate cover edition of ISBN NNN / NNN." / etc.
    re.compile(r"^\s*(?:Alternate|Alternative)\s+[Cc]over[^.\n]*?ISBN[:\s]?\s*[\d\-Xx]+(?:\s*/\s*[\d\-Xx]+)*\.?", re.IGNORECASE),
    # "This is the original cover edition of ISBN: NNN (ISBN13: NNN"
    re.compile(r"^\s*This\s+is\s+the\s+original\s+cover\s+edition\s+of\s+ISBN:?\s*[\d\-Xx]+\s*(?:\(ISBN13:\s*\d+\)?)?", re.IGNORECASE),
    # "ISBN(s) NNN moved to (this|the most recent) edition (here)?."
    re.compile(r"^\s*ISBNs?[:\s]?\s*[\d\-Xx]*\s*moved\s+to\s+(?:this|the\s+most\s+recent)\s+edition(?:\s+here)?\.?", re.IGNORECASE),
    # "ISBN: NNN updated version found here"
    re.compile(r"^\s*ISBN[:\s]?\s*[\d\-Xx]+\s+updated\s+version\s+found\s+here\.?", re.IGNORECASE),
    # "ISBN: NNN için alternatif kapak" (Turkish)
    re.compile(r"^\s*ISBN[:\s]?\s*[\d\-Xx]+\s+için\s+alternatif\s+kapak", re.IGNORECASE),
    # "ISBNs for 2003 edition re-used for this one"
    re.compile(r"^\s*ISBNs?\s+for\s+\d+\s+edition\s+re-?used\s+for\s+this\s+one\.?", re.IGNORECASE),
    # "ISBN Shared with X:" + trailing whitespace/newlines
    re.compile(r"^\s*ISBN\s+Shared\s+with[^:]*:\s*", re.IGNORECASE),
]


def strip_junk(text: str) -> str:
    prev = None
    cur = text
    while prev != cur:
        prev = cur
        for p in JUNK_PATTERNS:
            m = p.match(cur)
            if m:
                cur = cur[m.end():]
                break
    return cur.lstrip()


_SENT_SPLIT = re.compile(r"([.!?]\s+|\n+)")


def is_all_caps(text: str) -> bool:
    letters = [c for c in text if c.isalpha()]
    if len(letters) < 30:
        return False
    upper = sum(1 for c in letters if c.isupper())
    return upper / len(letters) > 0.85


def to_sentence_case(text: str) -> str:
    lower = text.lower()
    parts = _SENT_SPLIT.split(lower)
    out = []
    capitalize_next = True
    for part in parts:
        if not part:
            continue
        if _SENT_SPLIT.fullmatch(part):
            out.append(part)
            capitalize_next = True
            continue
        if capitalize_next:
            # find first alpha and uppercase it
            for i, c in enumerate(part):
                if c.isalpha():
                    part = part[:i] + c.upper() + part[i+1:]
                    break
            capitalize_next = False
        out.append(part)
    result = ''.join(out)
    # Restore lone-pronoun "i" -> "I"
    result = re.sub(r"\bi\b", "I", result)
    result = re.sub(r"\bi'", "I'", result)
    return result


def main():
    changes = []  # (rowidx, kind, before_snippet, after_snippet)
    rows_changed = 0

    with open(SRC, newline='', encoding='utf-8') as fin, \
         open(DST, 'w', newline='', encoding='utf-8') as fout:
        reader = csv.DictReader(fin)
        fieldnames = reader.fieldnames
        writer = csv.DictWriter(fout, fieldnames=fieldnames, quoting=csv.QUOTE_ALL)
        writer.writeheader()

        for i, row in enumerate(reader):
            orig = row.get('description') or ''
            new = strip_junk(orig)
            kind = None
            if new != orig:
                kind = 'junk'
            if is_all_caps(new):
                new = to_sentence_case(new)
                kind = 'allcaps' if kind is None else 'junk+allcaps'
            if new != orig:
                rows_changed += 1
                if len(changes) < 10:
                    changes.append((i, kind, orig[:120], new[:120]))
                row['description'] = new
            writer.writerow(row)

    import os
    os.replace(DST, SRC)

    print(f"Rows changed: {rows_changed}\n")
    print("First 10 changes:")
    for idx, kind, before, after in changes:
        print(f"\n--- row {idx} [{kind}] ---")
        print(f"BEFORE: {before!r}")
        print(f"AFTER : {after!r}")


if __name__ == '__main__':
    sys.exit(main())
