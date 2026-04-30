#!/usr/bin/env python3
"""Normalize datatable action buttons after the kitchen table migration."""

from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent / "client" / "src"


def add_button_type(match: re.Match[str]) -> str:
    attrs = match.group(1)
    if "type=" in attrs:
        return match.group(0)

    # Only action-style buttons need this pass; submit buttons should already be explicit.
    if "@click" not in attrs and "@click." not in attrs:
        return match.group(0)

    return f'<button type="button"{attrs}>'


def process(text: str) -> str:
    text = re.sub(r"\bExcell\b", "Excel", text)
    text = re.sub(r"<button(\s[^>]*)>", add_button_type, text)
    return text


def main() -> None:
    changed: list[Path] = []
    for path in sorted(ROOT.rglob("*.vue")):
        raw = path.read_text(encoding="utf-8")
        new = process(raw)
        if new != raw:
            path.write_text(new, encoding="utf-8")
            changed.append(path)
    print(f"Updated {len(changed)} Vue files")


if __name__ == "__main__":
    main()
