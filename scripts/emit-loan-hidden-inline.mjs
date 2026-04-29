import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const srcPath = path.join(__dirname, "../client/src/router/kerisi-loan-hidden-routes.ts");
const src = fs.readFileSync(srcPath, "utf8");

/** Parse one double-quoted TS string starting at index i (must point at opening "). Returns { value, end }. */
function parseQuotedString(s, i) {
  if (s[i] !== "\"") throw new Error("Expected \" at " + i);
  let j = i + 1;
  let out = "";
  while (j < s.length) {
    const ch = s[j];
    if (ch === "\\") {
      const next = s[j + 1];
      if (next === undefined) break;
      out += ch + next;
      j += 2;
      continue;
    }
    if (ch === "\"") return { value: out, end: j + 1 };
    out += ch;
    j++;
  }
  throw new Error("Unterminated string");
}

/** Skip whitespace/comments from i; return new index. */
function skipSpace(s, i) {
  while (i < s.length) {
    const ch = s[i];
    if (ch === " " || ch === "\t" || ch === "\r" || ch === "\n") {
      i++;
      continue;
    }
    if (ch === "/" && s[i + 1] === "/") {
      i += 2;
      while (i < s.length && s[i] !== "\n") i++;
      continue;
    }
    break;
  }
  return i;
}

/** Try to parse loanHidden( ... ) starting at `start` (index of 'l' in loanHidden). */
function parseLoanHiddenCall(s, start) {
  const head = s.slice(start, start + 11);
  if (!head.startsWith("loanHidden")) return null;
  let i = start + "loanHidden".length;
  i = skipSpace(s, i);
  if (s[i] !== "(") return null;
  i++;
  i = skipSpace(s, i);

  let num = "";
  while (i < s.length && /[0-9]/.test(s[i])) {
    num += s[i];
    i++;
  }
  if (!num) return null;
  const menuId = Number(num);
  i = skipSpace(s, i);
  if (s[i] !== ",") return null;
  i++;
  i = skipSpace(s, i);

  const nameQuoted = parseQuotedString(s, i);
  const routeName = nameQuoted.value;
  i = nameQuoted.end;
  i = skipSpace(s, i);
  if (s[i] !== ",") return null;
  i++;
  i = skipSpace(s, i);

  const titleQuoted = parseQuotedString(s, i);
  const title = titleQuoted.value;
  i = titleQuoted.end;
  i = skipSpace(s, i);
  if (s[i] !== ",") return null;
  i++;
  i = skipSpace(s, i);

  const pathQuoted = parseQuotedString(s, i);
  const legacyMenuPath = pathQuoted.value;
  i = pathQuoted.end;
  i = skipSpace(s, i);
  // Prefer TS trailing comma before `)` (`"lastArg",`).
  if (s[i] === ",") {
    i++;
    i = skipSpace(s, i);
  }
  if (s[i] !== ")") return null;
  i++;
  return {
    end: i,
    menuId,
    routeName,
    title,
    legacyMenuPath,
  };
}

const DESC =
  "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.";

const lines = [`    // HIDDEN_PAGE_LEVEL4 Loan (were \`kerisi-loan-hidden-routes\`; inlined).`];

let idx = 0;
let n = 0;
while (idx < src.length) {
  const pos = src.indexOf("loanHidden", idx);
  if (pos === -1) break;
  const parsed = parseLoanHiddenCall(src, pos);
  if (parsed) {
    n++;
    const breadcrumb = parsed.legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
    lines.push(`    {`);
    lines.push(`      path: "/admin/kerisi/m/${parsed.menuId}",`);
    lines.push(`      name: ${JSON.stringify(parsed.routeName)},`);
    lines.push(`      component: LoanLegacyPlaceholderView,`);
    lines.push(`      props: {`);
    lines.push(`        title: ${JSON.stringify(parsed.title)},`);
    lines.push(`        breadcrumb: ${JSON.stringify(breadcrumb)},`);
    lines.push(`        description:`);
    lines.push(`          ${JSON.stringify(DESC)},`);
    lines.push(`        relatedPath: "/admin",`);
    lines.push(`        relatedLabel: "Open main dashboard",`);
    lines.push(`      },`);
    lines.push(`      meta: { requiresAuth: true, title: ${JSON.stringify(parsed.title)} },`);
    lines.push(`    },`);
    idx = parsed.end;
  } else {
    idx = pos + 1;
  }
}
if (n === 0) {
  console.error("No loanHidden() matches.");
  process.exit(1);
}

process.stdout.write(lines.join("\n") + "\n");
console.error(`Emitted ${n} loan routes.`);
