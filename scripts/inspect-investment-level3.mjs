import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const hiddenPath = "client/src/router/kerisi-investment-hidden-routes.ts";

const level3Inv = new Map();
for (const r of JSON.parse(fs.readFileSync(level3Path, "utf8"))) {
  if (!/^investment/i.test(String(r.Menu ?? ""))) continue;
  const id = String(r.MENUID);
  if (!level3Inv.has(id)) level3Inv.set(id, { title: r.PAGETITLE, PAGEID: r.PAGEID });
}

const hid = fs.readFileSync(hiddenPath, "utf8");
const ids = [];
let m;
const re = /investmentHidden\(\s*(\d+)/g;
while ((m = re.exec(hid))) ids.push(m[1]);

const migrate = ids.filter((id) => level3Inv.has(id)).sort((a, b) => Number(a) - Number(b));
const keep = ids.filter((id) => !level3Inv.has(id)).sort((a, b) => Number(a) - Number(b));
console.log("LEVEL3 Investment count in JSON:", level3Inv.size);
console.log("hidden investmentHidden count:", ids.length);
console.log("migrate (intersection):", migrate.length, migrate.join(","));
console.log("keep in hidden:", keep.length, keep.join(","));
