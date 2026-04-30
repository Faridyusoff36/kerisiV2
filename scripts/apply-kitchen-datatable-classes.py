#!/usr/bin/env python3
"""One-off: add admin-table-kitchen to <table> and normalize <thead> to kitchen sink pattern.

If re-run: skips files that already satisfy patterns. Does NOT handle <table v-if="x" class="y"> —
those must use a single class attribute: <table v-if="x" class="admin-table-kitchen y">.
"""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent / "client" / "src"

THEADreplacements = [
    # Longest / most specific first
    ('<thead class="sticky top-0 z-10 bg-violet-600 text-white shadow-sm">', '<thead class="admin-table-thead-sticky z-10 shadow-sm">'),
    ('<thead class="sticky top-0 z-[1] bg-violet-600 text-white">', '<thead class="admin-table-thead-sticky z-[1]">'),
    ('<thead class="sticky top-0 z-[1] bg-violet-500 text-white">', '<thead class="admin-table-thead-sticky z-[1]">'),
    ('<thead class="sticky top-0 z-[1] bg-slate-50">', '<thead class="admin-table-thead-sticky z-[1]">'),
    ('<thead class="sticky top-0 z-10 bg-slate-50 shadow-sm">', '<thead class="admin-table-thead-sticky z-10 shadow-sm">'),
    ('<thead class="sticky top-0 z-10 bg-slate-50">', '<thead class="admin-table-thead-sticky z-10">'),
    ('<thead class="sticky top-0 bg-indigo-100 text-slate-700">', '<thead class="admin-table-thead-sticky">'),
    ('<thead class="sticky top-0 bg-indigo-100 text-slate-800">', '<thead class="admin-table-thead-sticky">'),
    ('<thead class="sticky top-0 bg-slate-50">', '<thead class="admin-table-thead-sticky">'),
    ('<thead class="sticky top-0 bg-slate-50 z-10">', '<thead class="admin-table-thead-sticky z-10">'),
    ('<thead class="sticky top-0 bg-slate-50 z-[1]">', '<thead class="admin-table-thead-sticky z-[1]">'),
    ('<thead class="bg-indigo-100 text-slate-800">', "<thead>"),
    ('<thead class="bg-indigo-100 text-slate-700">', "<thead>"),
    ('<thead class="bg-slate-100 text-xs font-semibold text-slate-700">', "<thead>"),
    ('<thead class="bg-slate-100 text-slate-800">', "<thead>"),
    ('<thead class="bg-slate-100">', "<thead>"),
    ('<thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">', '<thead class="admin-table-thead-sticky">'),
    ('<thead class="bg-slate-50 text-slate-700">', "<thead>"),
    ('<thead class="bg-slate-50">', "<thead>"),
    ('<thead class="bg-violet-600 text-white">', "<thead>"),
    ('<thead class="sticky top-0 bg-slate-50 text-slate-700">', '<thead class="admin-table-thead-sticky">'),
]


def inject_table_classes(attrs: str) -> str:
    if "admin-table-kitchen" in attrs:
        return attrs
    s = attrs or ""
    m = re.match(r"^(\s*)class=\"([^\"]*)\"(.*)$", s, re.DOTALL)
    if m:
        ws, cls, tail = m.groups()
        if cls.startswith("admin-table-kitchen"):
            return s
        return f'{ws}class="admin-table-kitchen {cls}"{tail}'
    stripped = s.strip()
    if not stripped:
        return ' class="admin-table-kitchen"'
    return f' class="admin-table-kitchen"{s}'


def process(text: str) -> str:

    def sub_table(m: re.Match[str]) -> str:
        inner = m.group(1) or ""
        inj = inject_table_classes(inner)
        # normalize: <table + inj + >
        rest = inner
        return "<table" + inj + ">"

    # <table …> possibly multiline attrs (rare) — handle single-line only
    text = re.sub(r"<table(\s[^>]*?)>", sub_table, text)

    for old, new in THEADreplacements:
        text = text.replace(old, new)

    return text


def main() -> None:
    changed: list[Path] = []
    for path in sorted(ROOT.rglob("*.vue")):
        raw = path.read_text(encoding="utf-8")
        if "<table" not in raw:
            continue
        new = process(raw)
        if new != raw:
            path.write_text(new, encoding="utf-8")
            changed.append(path)
    print(f"Updated {len(changed)} files")


if __name__ == "__main__":
    main()
