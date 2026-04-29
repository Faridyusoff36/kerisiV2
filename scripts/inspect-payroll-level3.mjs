import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const hiddenPath = "client/src/router/kerisi-payroll-hidden-routes.ts";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const hid = fs.readFileSync(hiddenPath, "utf8");
const exportIdx = hid.indexOf("export const kerisiPayrollHiddenRoutes");
const tail = exportIdx >= 0 ? hid.slice(exportIdx) : hid;
const ids = [];
let m;
const re = /^\s+payrollHidden\(\s*(\d+)/gm;
while ((m = re.exec(tail))) ids.push(m[1]);

const migrate = ids.filter((id) => level3Ids.has(id)).sort((a, b) => Number(a) - Number(b));
const keep = ids.filter((id) => !level3Ids.has(id)).sort((a, b) => Number(a) - Number(b));

console.log("payrollHidden entries:", ids.length);
console.log("migrate (intersect LEVEL3):", migrate.length, migrate.join(","));
console.log("keep:", keep.length, keep.join(","));
