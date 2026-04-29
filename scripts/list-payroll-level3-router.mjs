import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const routerPath = "client/src/router/index.ts";
const hiddenPath = "client/src/router/kerisi-payroll-hidden-routes.ts";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const hidden = fs.readFileSync(hiddenPath, "utf8");
const tail = hidden.slice(hidden.indexOf("export const kerisiPayrollHiddenRoutes"));
const hidIds = [];
let m;
const re = /^\s+payrollHidden\(\s*(\d+)/gm;
while ((m = re.exec(tail))) hidIds.push(m[1]);

const overlap = hidIds.filter((id) => level3Ids.has(id));
console.log("Payroll hidden ∩ global LEVEL3 MENUID after migrate (expect 0):", overlap.length, overlap.join(",") || "(none)");

const MIGRATE =
  "1216,1219,1340,1439,1459,1460,1466,1929,1931,1989,2156,2503,2541,3332,3340,3444".split(",");

const router = fs.readFileSync(routerPath, "utf8");
const inRouter = new Set();
for (const x of router.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inRouter.add(x[1]);

for (const id of MIGRATE) {
  const has = inRouter.has(id);
  const stub = hidIds.includes(id);
  const tag = has ? (stub ? "INDEX+STUB" : "INDEX") : stub ? "HIDDEN_ONLY" : "MISSING";
  console.log(id, tag);
}
