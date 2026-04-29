import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const routerPath = "client/src/router/index.ts";
const hiddenPath = "client/src/router/kerisi-asset-hidden-routes.ts";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const hidden = fs.readFileSync(hiddenPath, "utf8");
const tail = hidden.slice(hidden.indexOf("export const kerisiAssetHiddenRoutes"));
const hidIds = [];
let m;
const re = /^\s+assetHidden\(\s*(\d+)/gm;
while ((m = re.exec(tail))) hidIds.push(m[1]);

const overlap = hidIds.filter((id) => level3Ids.has(id));
console.log("Asset hidden ∩ global LEVEL3 (expect 0 after migrate):", overlap.length, overlap.join(",") || "(none)");

const MIGRATE = ["1671", "2401"];
const router = fs.readFileSync(routerPath, "utf8");
const inRouter = new Set();
for (const x of router.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inRouter.add(x[1]);

for (const id of MIGRATE) {
  const has = inRouter.has(id);
  const stub = hidIds.includes(id);
  const tag = has ? (stub ? "INDEX+STUB" : "INDEX") : stub ? "HIDDEN_ONLY" : "MISSING";
  console.log(id, tag);
}
