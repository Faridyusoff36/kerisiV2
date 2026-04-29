import fs from "fs";

const hiddenPath = "client/src/router/kerisi-portal-hidden-routes.ts";
const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const RELATED_MAP = {
  PORTAL_STAFF_PROFILE: { path: "/admin/kerisi/m/1914", label: "Open portal staff profile" },
  PORTAL_DEBTOR_STATEMENT: { path: "/admin/kerisi/m/2267", label: "Open debtors statement" },
  PORTAL_DEBTOR_REMINDER: { path: "/admin/kerisi/m/2584", label: "Open reminder" },
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

/** Returns index just after optional trailing comma following `portalHidden(...)`. */
function portalCallEnd(src, from) {
  const open = src.indexOf("portalHidden(", from);
  if (open === -1) return null;
  let i = open + "portalHidden(".length;
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
  return { open, argsInnerStart: open + "portalHidden(".length, argsInnerEnd: i - 1, end: j };
}

const PORTAL_LEGACY_DESC =
  "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.";

function breadcrumbFromPath(legacyMenuPath) {
  return legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
}

const exportIdx = hidden.indexOf("export const kerisiPortalHiddenRoutes");
const tail = exportIdx >= 0 ? hidden.slice(exportIdx) : hidden;

const rows = [];
let iter = 0;
while (true) {
  const idx = tail.indexOf("portalHidden(", iter);
  if (idx === -1) break;
  const r = portalCallEnd(tail, idx);
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
  let rel = RELATED_MAP.PORTAL_STAFF_PROFILE;
  const fifth = tok[4]?.trim();
  if (fifth && RELATED_MAP[fifth]) rel = RELATED_MAP[fifth];
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

const expected = 11;
if (rows.length !== expected) {
  console.error("Expected", expected, "LEVEL3 portal routes, got", rows.length);
  process.exit(1);
}

let out =
  "    // HIDDEN_PAGE_LEVEL3 Portal (`HIDDEN_PAGE_LEVEL3.json` ∩ `kerisi-portal-hidden-routes`; must stay before `...kerisiPortalHiddenRoutes`).\n";

for (const row of rows) {
  const bc = breadcrumbFromPath(row.legacyPath);
  out += "    {\n";
  out += `      path: "/admin/kerisi/m/${row.menuId}",\n`;
  out += `      name: ${JSON.stringify(row.name)},\n`;
  out += `      component: PortalLegacyPlaceholderView,\n`;
  out += `      props: {\n`;
  out += `        title: ${JSON.stringify(row.title)},\n`;
  out += `        breadcrumb: ${JSON.stringify(bc)},\n`;
  out += `        description:\n          ${JSON.stringify(PORTAL_LEGACY_DESC)},\n`;
  out += `        relatedPath: ${JSON.stringify(row.relatedPath)},\n`;
  out += `        relatedLabel: ${JSON.stringify(row.relatedLabel)},\n`;
  out += `      },\n`;
  out += `      meta: { requiresAuth: true, title: ${JSON.stringify(row.title)} },\n`;
  out += `    },\n`;
}

fs.writeFileSync("scripts/gen-portal-level3-routes-snippet.txt", out);
console.log("Wrote scripts/gen-portal-level3-routes-snippet.txt", rows.length, "routes");
