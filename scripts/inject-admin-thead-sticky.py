#!/usr/bin/env python3
"""
Ensure tables using admin-table-kitchen include <thead class="admin-table-thead-sticky">.
"""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent / "client" / "src"


def inject_thead(contents: str) -> tuple[str, bool]:
    if "admin-table-kitchen" not in contents:
        return contents, False
    original = contents

    def rebuild_thead(inner: str) -> str:
        inner = inner.strip()
        if not inner:
            return '<thead class="admin-table-thead-sticky">'
        if inner.startswith('class="'):
            m = re.match(r'^class="([^\"]*)"(.*)$', inner, re.DOTALL)
            if not m:
                return f'<thead class="admin-table-thead-sticky" {inner}>'
            cls, tail = (m.group(1) or "").strip(), (m.group(2) or "").strip()
            if "admin-table-thead-sticky" in cls:
                return "<thead " + inner + ">"
            merged_cls = (f"admin-table-thead-sticky {cls}" if cls else "admin-table-thead-sticky").strip()
            return f'<thead class="{merged_cls}"{(" " + tail) if tail else ""}>'.replace(" >", ">")
        return f'<thead class="admin-table-thead-sticky" {inner}>'

    contents = re.sub(r"<thead\s([^>]*?)>", lambda m: rebuild_thead(m.group(1)), contents)
    # Plain <thead> with no attrs (after above, none remain unless broken)
    contents = contents.replace("<thead>", '<thead class="admin-table-thead-sticky">')

    return contents, contents != original


def main() -> None:
    changed = 0
    for path in sorted(ROOT.rglob("*.vue")):
        raw = path.read_text(encoding="utf-8")
        new, ok = inject_thead(raw)
        if ok:
            path.write_text(new, encoding="utf-8")
            changed += 1
    print(f"Patched thead in {changed} Vue files")


if __name__ == "__main__":
    main()
