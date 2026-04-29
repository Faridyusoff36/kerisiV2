import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const routerPath = "client/src/router/index.ts";
const hiddenPath = "client/src/router/kerisi-loan-hidden-routes.ts";

const level3 = JSON.parse(fs.readFileSync(level3Path, "utf8"));
const level3Ids = new Set(level3.map((r) => String(r.MENUID)));

const hidden = fs.readFileSync(hiddenPath, "utf8");
const loanIds = [];
let m;
const re = /loanHidden\(\s*(\d+)/g;
while ((m = re.exec(hidden))) loanIds.push(m[1]);

const overlap = loanIds.filter((id) => level3Ids.has(id)).sort((a, b) => Number(a) - Number(b));
console.log("Loan hidden ∩ HIDDEN_PAGE_LEVEL3 (expect 0 after migration):", overlap.length, overlap.join(",") || "(none)");

const router = fs.readFileSync(routerPath, "utf8");
const inRouter = new Set();
for (const x of router.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inRouter.add(x[1]);

const MIGRATE = ["1586", "1627", "1640", "2051", "2052", "2484", "3292"];
console.log("LEVEL3-overlap MENUIDs (should be INDEX only, not HIDDEN_ONLY):");
for (const id of MIGRATE) {
  const has = inRouter.has(id);
  const stub = loanIds.includes(id);
  const tag = has ? (stub ? "INDEX+STUB" : "INDEX") : stub ? "HIDDEN_ONLY" : "MISSING";
  console.log(id, tag);
}
