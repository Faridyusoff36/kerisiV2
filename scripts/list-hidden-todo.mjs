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

const rows = [];
for (const f of LEVEL_FILES) {
  const fp = path.join(JSON_DIR, f);
  if (!fs.existsSync(fp)) continue;
  const j = JSON.parse(fs.readFileSync(fp, "utf8"));
  for (const r of j) rows.push(r);
}

const byMenu = new Map();
for (const r of rows) {
  const mid = String(r.MENUID);
  if (!byMenu.has(mid)) {
    byMenu.set(mid, { PAGEID: r.PAGEID, PAGETITLE: r.PAGETITLE, Menu: r.Menu });
  }
}

const todo = [...byMenu.entries()]
  .filter(([mid]) => !routeIds.has(mid))
  .sort((a, b) => Number(a[0]) - Number(b[0]));

const byTop = new Map();
for (const [mid, d] of todo) {
  const top = String(d.Menu || "").split(">")[0].trim() || "(unknown)";
  if (!byTop.has(top)) byTop.set(top, []);
  byTop.get(top).push([mid, d]);
}

console.log("All hidden remaining (levels 2–5):", todo.length);
console.log("Per top-level module:");
for (const [top, list] of [...byTop.entries()].sort((a, b) => b[1].length - a[1].length)) {
  console.log(`  ${list.length.toString().padStart(4)}  ${top}`);
}
console.log();
for (const [top, list] of [...byTop.entries()].sort((a, b) => a[0].localeCompare(b[0]))) {
  console.log(`### ${top} (${list.length})`);
  for (const [mid, d] of list) {
    console.log(mid, JSON.stringify(d.PAGETITLE), JSON.stringify(d.Menu));
  }
  console.log();
}
