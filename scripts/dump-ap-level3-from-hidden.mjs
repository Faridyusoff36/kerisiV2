import fs from "fs";

const hid = fs.readFileSync("client/src/router/kerisi-account-payable-hidden-routes.ts", "utf8");
const re = /apHidden\(\s*(\d+),\s*"([^"]+)",\s*"((?:\\.|[^"\\])*)",\s*"((?:\\.|[^"\\])*)"/g;
const rows = [];
let m;
while ((m = re.exec(hid)) !== null) {
  rows.push({ id: m[1], vueName: m[2], title: m[3], menuPath: m[4] });
}
const level3Json = JSON.parse(fs.readFileSync("C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json", "utf8"));
const level3 = new Set(level3Json.filter((r) => String(r.Menu ?? "").toLowerCase().startsWith("account payable")).map((r) => String(r.MENUID)));

for (const row of rows) {
  if (!level3.has(row.id)) continue;
  const crumb = row.menuPath.replace(/>/g, " / ").replace(/\s+/g, " ").trim();
  console.log(row.id, row.vueName, "|", row.title, "|", crumb);
}
