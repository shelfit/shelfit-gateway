#!/usr/bin/env python3
"""Convert the `genres` column of var/data/books.csv from a Python list
literal (single-quoted) to JSON (double-quoted) so PHP json_decode can read it.
"""
import ast
import csv
import json
import os
import sys

SRC = 'var/data/books.csv'
DST = 'var/data/books.csv.tmp'

converted = 0
empty_or_skipped = 0
parse_failures = []

with open(SRC, newline='', encoding='utf-8') as fin, \
     open(DST, 'w', newline='', encoding='utf-8') as fout:
    reader = csv.DictReader(fin)
    writer = csv.DictWriter(fout, fieldnames=reader.fieldnames, quoting=csv.QUOTE_ALL)
    writer.writeheader()

    for i, row in enumerate(reader):
        raw = row.get('genres') or ''
        if not raw.strip():
            empty_or_skipped += 1
        else:
            try:
                parsed = ast.literal_eval(raw)
                if not isinstance(parsed, list):
                    raise ValueError(f'expected list, got {type(parsed).__name__}')
                row['genres'] = json.dumps(parsed, ensure_ascii=False)
                converted += 1
            except (ValueError, SyntaxError) as e:
                if len(parse_failures) < 5:
                    parse_failures.append((i, raw[:80], str(e)))
        writer.writerow(row)

os.replace(DST, SRC)

print(f'Converted: {converted}')
print(f'Empty/skipped: {empty_or_skipped}')
print(f'Parse failures: {len(parse_failures)}')
for idx, snippet, err in parse_failures:
    print(f'  row {idx}: {err} | {snippet!r}')

sys.exit(0)
