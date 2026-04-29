import fs from "fs";

const hiddenPath = "client/src/router/kerisi-payroll-hidden-routes.ts";
const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const STAFF_PROFILE = {
  path: "/admin/kerisi/m/1914",
  label: "Open portal staff profile",
};

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

function payrollCallEnd(src, from) {
  const open = src.indexOf("payrollHidden(", from);
  if (open === -1) return null;
  let i = open + "payrollHidden(".length;
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
  return { open, argsInnerStart: open + "payrollHidden(".length, argsInnerEnd: i - 1, end: j };
}

const PAYROLL_LEGACY_DESC =
  "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.";

function breadcrumbFromPath(legacyMenuPath) {
  return legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
}

const exportIdx = hidden.indexOf("export const kerisiPayrollHiddenRoutes");
const tail = exportIdx >= 0 ? hidden.slice(exportIdx) : hidden;

const rows = [];
let iter = 0;
while (true) {
  const idx = tail.indexOf("payrollHidden(", iter);
  if (idx === -1) break;
  const r = payrollCallEnd(tail, idx);
  if (!r) break;
  const innerRaw = tail.slice(r.argsInnerStart, r.argsInnerEnd);
  const tok = tokenizeArgs(innerRaw.trim());
  if (tok.length < 4) {
    iter = r.end;
    continue;
  }
  const menuId = String(tok[0]).trim();
  if (!level3Ids.has(menuId)) {
    iter = r.end;
    continue;
  }
  let rel = STAFF_PROFILE;
  const fifth = tok[4]?.trim();
  if (fifth?.startsWith("{")) {
    const pathM = fifth.match(/path:\s*["']([^"']+)["']/);
    const labelM = fifth.match(/label:\s*["']([^"']+)["']/);
    if (pathM && labelM) rel = { path: pathM[1], label: labelM[1] };
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

if (rows.length !== 16) {
  console.error("Expected 16 routes, got", rows.length);
  process.exit(1);
}

let out =
  "    // HIDDEN_PAGE_LEVEL3 Payroll (`HIDDEN_PAGE_LEVEL3.json` ∩ `kerisi-payroll-hidden-routes`; must stay before `...kerisiPayrollHiddenRoutes`).\n";

for (const row of rows) {
  const bc = breadcrumbFromPath(row.legacyPath);
  out += "    {\n";
  out += `      path: "/admin/kerisi/m/${row.menuId}",\n`;
  out += `      name: ${JSON.stringify(row.name)},\n`;
  out += `      component: PayrollLegacyPlaceholderView,\n`;
  out += `      props: {\n`;
  out += `        title: ${JSON.stringify(row.title)},\n`;
  out += `        breadcrumb: ${JSON.stringify(bc)},\n`;
  out += `        description:\n          ${JSON.stringify(PAYROLL_LEGACY_DESC)},\n`;
  out += `        relatedPath: ${JSON.stringify(row.relatedPath)},\n`;
  out += `        relatedLabel: ${JSON.stringify(row.relatedLabel)},\n`;
  out += `      },\n`;
  out += `      meta: { requiresAuth: true, title: ${JSON.stringify(row.title)} },\n`;
  out += `    },\n`;
}

fs.writeFileSync("scripts/gen-payroll-level3-routes-snippet.txt", out);
console.log("Wrote scripts/gen-payroll-level3-routes-snippet.txt", rows.length, "routes");
