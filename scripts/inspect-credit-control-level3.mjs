import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const hiddenPath = "client/src/router/kerisi-credit-control-hidden-routes.ts";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const hid = fs.readFileSync(hiddenPath, "utf8");
const exportIdx = hid.indexOf("export const kerisiCreditControlHiddenRoutes");
const tail = exportIdx >= 0 ? hid.slice(exportIdx) : hid;

const ids = [];
const re = /^\s+ccHidden\(\s*(\d+)/gm;
let m;
while ((m = re.exec(tail))) ids.push(m[1]);

const migrate = ids.filter((id) => level3Ids.has(id)).sort((a, b) => Number(a) - Number(b));
const keep = ids.filter((id) => !level3Ids.has(id)).sort((a, b) => Number(a) - Number(b));

console.log("ccHidden entries:", ids.length);
console.log("migrate (intersect LEVEL3):", migrate.length);
console.log("migrate IDs:", migrate.join(","));
console.log("keep:", keep.length);
