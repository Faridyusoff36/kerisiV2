import fs from "fs";

const hiddenPath = "client/src/router/kerisi-account-receivable-hidden-routes.ts";
const hidden = fs.readFileSync(hiddenPath, "utf8");

const level3Json = JSON.parse(fs.readFileSync("C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json", "utf8"));
const level3Ids = new Set();
for (const r of level3Json) {
  if (/^account receivable/i.test(String(r.Menu ?? ""))) level3Ids.add(String(r.MENUID));
}

function breadcrumbFromPath(legacyMenuPath) {
  return legacyMenuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
}

const DESC =
  "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.";

const rows = [];
const re = /arHidden\(\s*(\d+)\s*,\s*"([^"]+)"\s*,\s*"([^"]*)"\s*,\s*"((?:[^"\\]|\\.)*)"\)/g;
let m;
while ((m = re.exec(hidden))) {
  const id = m[1];
  if (!level3Ids.has(id)) continue;
  rows.push({
    menuId: id,
    name: m[2],
    title: m[3],
    legacyPath: m[4],
  });
}
rows.sort((a, b) => Number(a.menuId) - Number(b.menuId));

if (rows.length !== level3Ids.size) {
  console.error("Expected", level3Ids.size, "LEVEL3 rows from hidden, got", rows.length);
  process.exit(1);
}

let out = `    // HIDDEN_PAGE_LEVEL3 Account Receivable (\`HIDDEN_PAGE_LEVEL3.json\`; must stay before \`...kerisiAccountReceivableHiddenRoutes\`).\n`;

for (const r of rows) {
  const bc = breadcrumbFromPath(r.legacyPath);
  out += `    {\n`;
  out += `      path: "/admin/kerisi/m/${r.menuId}",\n`;
  out += `      name: "${r.name}",\n`;
  out += `      component: AccountReceivableLegacyPlaceholderView,\n`;
  out += `      props: {\n`;
  out += `        title: ${JSON.stringify(r.title)},\n`;
  out += `        breadcrumb: ${JSON.stringify(bc)},\n`;
  out += `        description:\n          ${JSON.stringify(DESC)},\n`;
  out += `        relatedPath: "/admin/kerisi/m/1023",\n`;
  out += `        relatedLabel: "Open invoice listing",\n`;
  out += `      },\n`;
  out += `      meta: { requiresAuth: true, title: ${JSON.stringify(r.title)} },\n`;
  out += `    },\n`;
}

fs.writeFileSync("scripts/gen-account-receivable-level3-routes-snippet.txt", out);
console.log("Wrote scripts/gen-account-receivable-level3-routes-snippet.txt", rows.length, "routes");
