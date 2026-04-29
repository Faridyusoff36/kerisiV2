import fs from "fs";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const routerPath = "client/src/router/index.ts";
const hiddenPath = "client/src/router/kerisi-student-finance-hidden-routes.ts";

const j = JSON.parse(fs.readFileSync(level3Path, "utf8"));
const map = new Map();
for (const r of j) {
  if (!/^student finance/i.test(String(r.Menu ?? ""))) continue;
  const id = String(r.MENUID);
  if (!map.has(id)) map.set(id, { PAGEID: r.PAGEID, title: r.PAGETITLE, menu: r.Menu });
}
const ids = [...map.keys()].sort((a, b) => Number(a) - Number(b));
console.log("unique Student Finance (`Menu` prefix) LEVEL3 MENUIDs:", ids.length);

const router = fs.readFileSync(routerPath, "utf8");
const hidden = fs.readFileSync(hiddenPath, "utf8");
const inRouter = new Set();
for (const m of router.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inRouter.add(m[1]);
const inHidden = new Set();
for (const m of hidden.matchAll(/sfHidden\(\s*(\d+)/g)) inHidden.add(m[1]);

for (const id of ids) {
  const info = map.get(id);
  const has = inRouter.has(id);
  const stub = inHidden.has(id);
  const tag = has ? (stub ? "INDEX+STUB" : "INDEX") : stub ? "HIDDEN_ONLY" : "MISSING";
  console.log(id, tag, info.title, "PAGEID=" + info.PAGEID);
}
