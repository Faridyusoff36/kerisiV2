import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const routerPath = "client/src/router/index.ts";
const hiddenPath = "client/src/router/kerisi-credit-control-hidden-routes.ts";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const migrate =
  "1766,1910,1921,2150,2162,2178,2352,2353,2358,2359,2518,2521,2607,2632,2634,2662,2668,2803,2806,2862,2873,2969".split(",");

const hidden = fs.readFileSync(hiddenPath, "utf8");
const tail = hidden.slice(hidden.indexOf("export const kerisiCreditControlHiddenRoutes"));
const hidIds = [];
let re = /^\s+ccHidden\(\s*(\d+)/gm;
let m;
while ((m = re.exec(tail))) hidIds.push(m[1]);

const overlap = hidIds.filter((id) => level3Ids.has(id));
console.log("CC hidden ∩ global LEVEL3 (expect 0 after migrate):", overlap.length);

const router = fs.readFileSync(routerPath, "utf8");
const inRouter = new Set();
for (const x of router.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inRouter.add(x[1]);

for (const id of migrate) {
  const ok = inRouter.has(id);
  const stub = hidIds.includes(id);
  const tag = ok ? (stub ? "INDEX+STUB" : "INDEX") : stub ? "HIDDEN_ONLY" : "MISSING";
  console.log(id, tag);
}
