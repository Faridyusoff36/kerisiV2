import fs from "fs";

const hiddenPath = "client/src/router/kerisi-credit-control-hidden-routes.ts";
const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

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

function ccCallEnd(src, from) {
  const open = src.indexOf("ccHidden(", from);
  if (open === -1) return null;
  let i = open + "ccHidden(".length;
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
  return { open, argsInnerStart: open + "ccHidden(".length, argsInnerEnd: i - 1, end: j };
}

function labelForPath(path) {
  if (path.includes("/1809")) return "Open deposit";
  if (path.includes("/3066")) return "Open list of deposit";
  return "Open related screen";
}

const CC_LEGACY_DESC =
  "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.";

function breadcrumbFromPath(legacyMenuPath) {
  return legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
}

const DEFAULT_REL = { path: "/admin/kerisi/m/3066", label: "Open list of deposit" };

const exportIdx = hidden.indexOf("export const kerisiCreditControlHiddenRoutes");
const tail = exportIdx >= 0 ? hidden.slice(exportIdx) : hidden;

const rows = [];
let iter = 0;
while (true) {
  const idx = tail.indexOf("ccHidden(", iter);
  if (idx === -1) break;
  const r = ccCallEnd(tail, idx);
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
  let rel = DEFAULT_REL;
  const fifth = tok[4]?.trim();
  if (fifth?.startsWith("{")) {
    const pathM = fifth.match(/path:\s*["']([^"']+)["']/);
    if (pathM) rel = { path: pathM[1], label: labelForPath(pathM[1]) };
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

const expected =
  "1766,1910,1921,2150,2162,2178,2352,2353,2358,2359,2518,2521,2607,2632,2634,2662,2668,2803,2806,2862,2873,2969".split(",");
if (rows.length !== expected.length) {
  console.error("Expected", expected.length, "routes, got", rows.length);
  process.exit(1);
}

let out =
  "    // HIDDEN_PAGE_LEVEL3 Credit Control (`HIDDEN_PAGE_LEVEL3.json` ∩ `kerisi-credit-control-hidden-routes`; must stay before `...kerisiCreditControlHiddenRoutes`).\n";

for (const row of rows) {
  const bc = breadcrumbFromPath(row.legacyPath);
  out += "    {\n";
  out += `      path: "/admin/kerisi/m/${row.menuId}",\n`;
  out += `      name: ${JSON.stringify(row.name)},\n`;
  out += `      component: CreditControlLegacyPlaceholderView,\n`;
  out += `      props: {\n`;
  out += `        title: ${JSON.stringify(row.title)},\n`;
  out += `        breadcrumb: ${JSON.stringify(bc)},\n`;
  out += `        description:\n          ${JSON.stringify(CC_LEGACY_DESC)},\n`;
  out += `        relatedPath: ${JSON.stringify(row.relatedPath)},\n`;
  out += `        relatedLabel: ${JSON.stringify(row.relatedLabel)},\n`;
  out += `      },\n`;
  out += `      meta: { requiresAuth: true, title: ${JSON.stringify(row.title)} },\n`;
  out += `    },\n`;
}

fs.writeFileSync("scripts/gen-credit-control-level3-routes-snippet.txt", out);
console.log("Wrote snippet", rows.length, "routes");
