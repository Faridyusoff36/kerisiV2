import fs from "fs";

const hiddenPath = "client/src/router/kerisi-loan-hidden-routes.ts";
const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";

const level3Json = JSON.parse(fs.readFileSync(level3Path, "utf8"));
const level3Ids = new Set();
for (const r of level3Json) {
  level3Ids.add(String(r.MENUID));
}

const hidden = fs.readFileSync(hiddenPath, "utf8");

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

function findLoanCallEnd(src, startIdx) {
  const open = src.indexOf("loanHidden(", startIdx);
  if (open === -1) return [-1, -1];
  let i = open + "loanHidden(".length;
  let depthParen = 1;
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

const LOAN_LEGACY_DESC =
  "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.";

function breadcrumbFromPath(legacyMenuPath) {
  return legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
}

const rows = [];
let searchFrom = 0;
while (true) {
  const callStart = hidden.indexOf("loanHidden(", searchFrom);
  if (callStart === -1) break;
  const [open, close] = findLoanCallEnd(hidden, callStart);
  if (close < 0) break;
  const inner = hidden.slice(open + "loanHidden(".length, close);
  const tok = tokenizeArgs(inner);
  if (tok.length < 4) {
    searchFrom = close + 1;
    continue;
  }
  const menuId = String(tok[0]).trim();
  if (!level3Ids.has(menuId)) {
    searchFrom = close + 1;
    continue;
  }
  rows.push({
    menuId,
    name: tok[1],
    title: tok[2],
    legacyPath: tok[3],
  });
  searchFrom = close + 1;
}
rows.sort((a, b) => Number(a.menuId) - Number(b.menuId));

const hidIds = [];
let re = /loanHidden\(\s*(\d+)/g;
let m;
while ((m = re.exec(hidden))) hidIds.push(m[1]);
const expectedMigrate = hidIds.filter((id) => level3Ids.has(id)).sort((a, b) => Number(a) - Number(b));

if (rows.length !== expectedMigrate.length || rows.map((r) => r.menuId).join() !== expectedMigrate.join()) {
  console.error("Parse vs expected mismatch", rows.length, expectedMigrate.length);
  process.exit(1);
}

let out =
  "    // HIDDEN_PAGE_LEVEL3 Loan (`HIDDEN_PAGE_LEVEL3.json` overlaps; must stay before `...kerisiLoanHiddenRoutes`).\n";

for (const r of rows) {
  const bc = breadcrumbFromPath(r.legacyPath);
  out += "    {\n";
  out += `      path: "/admin/kerisi/m/${r.menuId}",\n`;
  out += `      name: ${JSON.stringify(r.name)},\n`;
  out += `      component: LoanLegacyPlaceholderView,\n`;
  out += `      props: {\n`;
  out += `        title: ${JSON.stringify(r.title)},\n`;
  out += `        breadcrumb: ${JSON.stringify(bc)},\n`;
  out += `        description:\n          ${JSON.stringify(LOAN_LEGACY_DESC)},\n`;
  out += `        relatedPath: "/admin",\n`;
  out += `        relatedLabel: "Open main dashboard",\n`;
  out += `      },\n`;
  out += `      meta: { requiresAuth: true, title: ${JSON.stringify(r.title)} },\n`;
  out += `    },\n`;
}

fs.writeFileSync("scripts/gen-loan-level3-routes-snippet.txt", out);
console.log(
  "Wrote scripts/gen-loan-level3-routes-snippet.txt",
  rows.length,
  "routes:",
  rows.map((x) => x.menuId).join(","),
);
