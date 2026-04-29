import fs from "fs";

const j = JSON.parse(
  fs.readFileSync("C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json", "utf8"),
);
const rows = j.filter((r) => String(r.Menu ?? "").toLowerCase().startsWith("purchasing"));
const map = new Map();
for (const r of rows) {
  const id = String(r.MENUID);
  if (!map.has(id)) map.set(id, { PAGEID: r.PAGEID, title: r.PAGETITLE, menu: r.Menu });
}
const ids = [...map.keys()].sort((a, b) => Number(a) - Number(b));
console.log("unique Purchasing LEVEL3 MENUIDs:", ids.length);
console.log(ids.join(","));

const router = fs.readFileSync("client/src/router/index.ts", "utf8");
const hidden = fs.readFileSync("client/src/router/kerisi-purchasing-hidden-routes.ts", "utf8");
const inRouter = new Set();
for (const m of router.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inRouter.add(m[1]);
const inHidden = new Set();
for (const m of hidden.matchAll(/purchHidden\(\s*(\d+)/g)) inHidden.add(m[1]);

for (const id of ids) {
  const info = map.get(id);
  const has = inRouter.has(id);
  const stub = inHidden.has(id);
  const tag = has ? (stub ? "INDEX+STUB" : "INDEX") : stub ? "HIDDEN_ONLY" : "MISSING";
  console.log(id, tag, info.title, "PAGEID=" + info.PAGEID);
}
