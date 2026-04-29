import fs from "fs";

const hiddenPath = "client/src/router/kerisi-investment-hidden-routes.ts";
const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";

const level3Ids = new Set();
for (const r of JSON.parse(fs.readFileSync(level3Path, "utf8"))) {
  if (/^investment/i.test(String(r.Menu ?? ""))) level3Ids.add(String(r.MENUID));
}

const hidden = fs.readFileSync(hiddenPath, "utf8");

const DEFAULT_REL = { path: "/admin/kerisi/m/1448", label: "Open list of investments" };

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
    if (inner[i] === "{") {
      let depth = 1;
      let j = i + 1;
      let sb = "{";
      for (; j < inner.length && depth > 0; j++) {
        const c = inner[j];
        sb += c;
        if (c === "{") depth++;
        else if (c === "}") depth--;
      }
      tokens.push(sb);
      i = j;
      while (i < inner.length && /[,\s]/.test(inner[i])) i++;
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

function invCallEnd(src, from) {
  const open = src.indexOf("investmentHidden(", from);
  if (open === -1) return null;
  let i = open + "investmentHidden(".length;
  let depth = 1;
  let inStr = false;
  let quote = "";
  for (; i < src.length && depth > 0; i++) {
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
    if (c === "(") depth++;
    else if (c === ")") depth--;
  }
  let j = i;
  while (j < src.length && /\s/.test(src[j])) j++;
  if (src[j] === ",") j++;
  return { open, end: j };
}

function parseRelatedObject(objStr) {
  const pathM = objStr.match(/path:\s*["']([^"']+)["']/);
  const labelM = objStr.match(/label:\s*["']([^"']+)["']/);
  if (pathM && labelM) return { path: pathM[1], label: labelM[1].startsWith("Open ") ? labelM[1] : "Open " + labelM[1].toLowerCase() };
  return null;
}

const INVESTMENT_LEGACY_DESC =
  "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.";

function breadcrumbFromPath(legacyMenuPath) {
  return legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
}

const exportIdx = hidden.indexOf("export const kerisiInvestmentHiddenRoutes");
const sliceFrom = exportIdx >= 0 ? exportIdx : 0;
const tail = hidden.slice(sliceFrom);

const rows = [];
let iter = 0;
while (true) {
  const relIdx = tail.indexOf("investmentHidden(", iter);
  if (relIdx === -1) break;
  const r = invCallEnd(tail, relIdx);
  if (!r) break;
  const inner = tail.slice(r.open + "investmentHidden(".length, r.end - (tail[r.end - 1] === "," ? 1 : 0));
  const cleanEnd = inner.replace(/,\s*$/, "").trim();
  const tok = tokenizeArgs(cleanEnd);
  if (tok.length < 4) {
    iter = r.end;
    continue;
  }
  const menuId = String(tok[0]).trim();
  if (!level3Ids.has(menuId)) {
    iter = r.end;
    continue;
  }
  let rel = DEFAULT_REL;
  if (tok[4]?.trim().startsWith("{")) {
    const p = parseRelatedObject(tok[4]);
    if (p) rel = { path: p.path, label: p.label };
  }
  rows.push({
    menuId,
    name: tok[1],
    title: tok[2],
    legacyPath: tok[3],
    relatedPath: rel.path,
    relatedLabel: rel.label,
  });
  iter = r.end;
}
rows.sort((a, b) => Number(a.menuId) - Number(b.menuId));

if (rows.length !== level3Ids.size) {
  console.error("Expected", level3Ids.size, "routes, got", rows.length);
  process.exit(1);
}

let out =
  "    // HIDDEN_PAGE_LEVEL3 Investment (`HIDDEN_PAGE_LEVEL3.json`; must stay before `...kerisiInvestmentHiddenRoutes`).\n";

for (const row of rows) {
  const bc = breadcrumbFromPath(row.legacyPath);
  out += "    {\n";
  out += `      path: "/admin/kerisi/m/${row.menuId}",\n`;
  out += `      name: ${JSON.stringify(row.name)},\n`;
  out += `      component: InvestmentLegacyPlaceholderView,\n`;
  out += `      props: {\n`;
  out += `        title: ${JSON.stringify(row.title)},\n`;
  out += `        breadcrumb: ${JSON.stringify(bc)},\n`;
  out += `        description:\n          ${JSON.stringify(INVESTMENT_LEGACY_DESC)},\n`;
  out += `        relatedPath: ${JSON.stringify(row.relatedPath)},\n`;
  out += `        relatedLabel: ${JSON.stringify(row.relatedLabel)},\n`;
  out += `      },\n`;
  out += `      meta: { requiresAuth: true, title: ${JSON.stringify(row.title)} },\n`;
  out += `    },\n`;
}

fs.writeFileSync("scripts/gen-investment-level3-routes-snippet.txt", out);
console.log("Wrote snippet", rows.length, "routes");
