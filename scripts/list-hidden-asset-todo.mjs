import fs from "fs";
import path from "path";

const JSON_DIR = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA";
const LEVEL_FILES = [
  "HIDDEN_PAGE_LEVEL2.json",
  "HIDDEN_PAGE_LEVEL3.json",
  "HIDDEN_PAGE_LEVEL4.json",
  "HIDDEN_PAGE_LEVEL5.json",
];

const router = fs.readFileSync("client/src/router/index.ts", "utf8");
const routeIds = new Set();
const re = /path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g;
let m;
while ((m = re.exec(router))) routeIds.add(m[1]);

const extraRoutesTs = "client/src/router/kerisi-asset-hidden-routes.ts";
if (fs.existsSync(extraRoutesTs)) {
  const src = fs.readFileSync(extraRoutesTs, "utf8");
  const fnRe = /assetHidden\(\s*(\d+)/g;
  while ((m = fnRe.exec(src))) routeIds.add(m[1]);
}

function isAssetModuleMenu(menu) {
  const s = String(menu ?? "").toLowerCase();
  if (s.startsWith("asset>")) return true;
  return false;
}

const rows = [];
for (const f of LEVEL_FILES) {
  const fp = path.join(JSON_DIR, f);
  if (!fs.existsSync(fp)) continue;
  const j = JSON.parse(fs.readFileSync(fp, "utf8"));
  for (const r of j) {
    if (!isAssetModuleMenu(r.Menu)) continue;
    rows.push({ ...r, _file: f });
  }
}

const byMenu = new Map();
for (const r of rows) {
  const mid = String(r.MENUID);
  if (!byMenu.has(mid)) {
    byMenu.set(mid, {
      PAGEID: r.PAGEID,
      PAGETITLE: r.PAGETITLE,
      Menu: r.Menu,
      levels: new Set(),
      types: new Set(),
    });
  }
  const d = byMenu.get(mid);
  d.levels.add(r._file);
  if (r.COMPONENTTYPE) d.types.add(r.COMPONENTTYPE);
}

const todo = [...byMenu.entries()]
  .filter(([mid]) => !routeIds.has(mid))
  .sort((a, b) => Number(a[0]) - Number(b[0]));

console.log("Asset hidden (levels 2–5) remaining", todo.length);
for (const [mid, d] of todo) {
  console.log(
    mid,
    d.PAGEID,
    d.PAGETITLE,
    d.Menu.replace(/\s+/g, " ").slice(0, 120),
    [...d.levels].join(","),
    [...d.types].join(","),
  );
}
