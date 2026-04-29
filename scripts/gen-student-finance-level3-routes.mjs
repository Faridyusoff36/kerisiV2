import fs from "fs";

const hiddenPath = "client/src/router/kerisi-student-finance-hidden-routes.ts";
const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";

const level3Json = JSON.parse(fs.readFileSync(level3Path, "utf8"));
const level3Ids = new Set();
for (const r of level3Json) {
  if (/^student finance/i.test(String(r.Menu ?? ""))) level3Ids.add(String(r.MENUID));
}

const hidden = fs.readFileSync(hiddenPath, "utf8");

const RELATED_MAP = {
  SF_INVOICE: { path: "/admin/kerisi/m/1023", label: "Open invoice listing" },
  SF_ADVANCE: { path: "/admin/kerisi/m/2020", label: "Open advance payment" },
  SF_JOURNAL_APPROVAL: { path: "/admin/kerisi/m/2390", label: "Open student journal approval" },
  SF_SPONSOR: { path: "/admin/kerisi/m/1025", label: "Open sponsor profile" },
  SF_OFFERED: { path: "/admin/kerisi/m/2636", label: "Open list of offered" },
};

const SF_LEGACY_DESC =
  "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.";

function breadcrumbFromPath(legacyMenuPath) {
  return legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
}

/** Extract comma-separated tokens from sfHidden argument list string (handles quoted strings). */
function tokenizeArgs(inner) {
  const tokens = [];
  let i = 0;
  while (i < inner.length) {
    while (i < inner.length && /\s/.test(inner[i])) i++;
    if (i >= inner.length) break;
    if (inner[i] === '"' || inner[i] === "'") {
      const q = inner[i];
      let j = i + 1;
      let sb = "";
      while (j < inner.length) {
        if (inner[j] === "\\") {
          sb += inner[j + 1] ?? "";
          j += 2;
          continue;
        }
        if (inner[j] === q) break;
        sb += inner[j];
        j++;
      }
      tokens.push(sb);
      i = j + 1;
      continue;
    }
    let j = i;
    while (j < inner.length && inner[j] !== ",") j++;
    const raw = inner.slice(i, j).trim();
    if (raw) tokens.push(raw);
    i = j + 1;
  }
  return tokens;
}

/** Find matching `)` for `sfHidden(` at startIdx. Returns end index inclusive of closing paren, or -1. */
function findCallEnd(src, startIdx) {
  const open = src.indexOf("sfHidden(", startIdx);
  if (open === -1) return [-1, -1];
  let i = open + "sfHidden(".length;
  let depthParen = 1;
  let depthBracket = 0;
  let inStr = false;
  let quote = "";
  for (; i < src.length && depthParen > 0; i++) {
    const c = src[i];
    if (inStr) {
      if (c === "\\" && quote) {
        i++;
        continue;
      }
      if (c === quote) inStr = false;
      continue;
    }
    if (c === '"' || c === "'") {
      inStr = true;
      quote = c;
      continue;
    }
    if (c === "(") depthParen++;
    else if (c === ")") depthParen--;
  }
  if (depthParen !== 0) return [-1, -1];
  return [open, i - 1];
}

const rows = [];
let searchFrom = 0;
while (true) {
  const callStart = hidden.indexOf("sfHidden(", searchFrom);
  if (callStart === -1) break;
  const [open, close] = findCallEnd(hidden, callStart);
  if (close < 0) break;
  const inner = hidden.slice(open + "sfHidden(".length, close);
  const tok = tokenizeArgs(inner);
  if (tok.length < 4) {
    searchFrom = close + 1;
    continue;
  }
  const menuId = String(tok[0]).trim();
  const name = tok[1];
  const title = tok[2];
  const legacyPath = tok[3];
  let relToken = tok[4];
  let relatedPath = RELATED_MAP.SF_INVOICE.path;
  let relatedLabel = RELATED_MAP.SF_INVOICE.label;
  if (relToken?.startsWith("SF_")) {
    const m = RELATED_MAP[relToken];
    if (m) {
      relatedPath = m.path;
      relatedLabel = m.label;
    }
  }
  if (!level3Ids.has(menuId)) {
    searchFrom = close + 1;
    continue;
  }
  rows.push({
    menuId,
    name,
    title,
    legacyPath,
    relatedPath,
    relatedLabel,
  });
  searchFrom = close + 1;
}
rows.sort((a, b) => Number(a.menuId) - Number(b.menuId));

if (rows.length !== level3Ids.size) {
  console.error("Parse mismatch: LEVEL3 set size", level3Ids.size, "parsed", rows.length);
  const parsedIds = new Set(rows.map((r) => r.menuId));
  for (const id of level3Ids) {
    if (!parsedIds.has(id)) console.error("Missing parse for MENUID", id);
  }
  process.exit(1);
}

let out = `    // HIDDEN_PAGE_LEVEL3 Student Finance (\`HIDDEN_PAGE_LEVEL3.json\`; must stay before \`...kerisiStudentFinanceHiddenRoutes\`).\n`;

for (const r of rows) {
  const bc = breadcrumbFromPath(r.legacyPath);
  out += `    {\n`;
  out += `      path: "/admin/kerisi/m/${r.menuId}",\n`;
  out += `      name: ${JSON.stringify(r.name)},\n`;
  out += `      component: StudentFinanceLegacyPlaceholderView,\n`;
  out += `      props: {\n`;
  out += `        title: ${JSON.stringify(r.title)},\n`;
  out += `        breadcrumb: ${JSON.stringify(bc)},\n`;
  out += `        description:\n          ${JSON.stringify(SF_LEGACY_DESC)},\n`;
  out += `        relatedPath: ${JSON.stringify(r.relatedPath)},\n`;
  out += `        relatedLabel: ${JSON.stringify(r.relatedLabel)},\n`;
  out += `      },\n`;
  out += `      meta: { requiresAuth: true, title: ${JSON.stringify(r.title)} },\n`;
  out += `    },\n`;
}

fs.writeFileSync("scripts/gen-student-finance-level3-routes-snippet.txt", out);
console.log("Wrote scripts/gen-student-finance-level3-routes-snippet.txt", rows.length, "routes");
