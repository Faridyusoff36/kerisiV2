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

const ROUTER_DIR = "client/src/router";
if (fs.existsSync(ROUTER_DIR)) {
  for (const f of fs.readdirSync(ROUTER_DIR)) {
    if (!f.endsWith("-hidden-routes.ts")) continue;
    const src = fs.readFileSync(path.join(ROUTER_DIR, f), "utf8");
    const fnRe = /\b[a-zA-Z]+Hidden\(\s*(\d+)/g;
    while ((m = fnRe.exec(src))) routeIds.add(m[1]);
  }
}

function isSetupMaintenanceMenu(menu) {
  const s = String(menu ?? "").toLowerCase();
  return s.startsWith("setup and maintenance>");
}

const rows = [];
for (const f of LEVEL_FILES) {
  const fp = path.join(JSON_DIR, f);
  if (!fs.existsSync(fp)) continue;
  const j = JSON.parse(fs.readFileSync(fp, "utf8"));
  for (const r of j) {
    if (!isSetupMaintenanceMenu(r.Menu)) continue;
    rows.push(r);
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
    });
  }
}

const todo = [...byMenu.entries()]
  .filter(([mid]) => !routeIds.has(mid))
  .sort((a, b) => Number(a[0]) - Number(b[0]));

console.log("Setup and Maintenance hidden (levels 2–5) remaining", todo.length);
for (const [mid, d] of todo) {
  console.log(mid, d.PAGEID, d.PAGETITLE, d.Menu);
}
