import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const routerPath = "client/src/router/index.ts";
const hiddenPath = "client/src/router/kerisi-portal-hidden-routes.ts";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const hidden = fs.readFileSync(hiddenPath, "utf8");
const exportIdx = hidden.indexOf("export const kerisiPortalHiddenRoutes");
const tail = exportIdx >= 0 ? hidden.slice(exportIdx) : hidden;
const hidIds = [];
let m;
const re = /^\s+portalHidden\(\s*(\d+)/gm;
while ((m = re.exec(tail))) hidIds.push(m[1]);

const overlap = hidIds.filter((id) => level3Ids.has(id)).sort((a, b) => Number(a) - Number(b));
console.log("Portal hidden ∩ global LEVEL3 MENUID (expect 0 after migrate):", overlap.length, overlap.join(",") || "(none)");

const MIGRATE = "1195,1416,1675,1712,1842,1844,1846,1890,1913,2645,2914".split(",");

const router = fs.readFileSync(routerPath, "utf8");
const inRouter = new Set();
for (const x of router.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inRouter.add(x[1]);

console.log("Migrated LEVEL3 IDs (should be INDEX):");
for (const id of MIGRATE) {
  const has = inRouter.has(id);
  const stub = hidIds.includes(id);
  const tag = has ? (stub ? "INDEX+STUB" : "INDEX") : stub ? "HIDDEN_ONLY" : "MISSING";
  console.log(id, tag);
}
