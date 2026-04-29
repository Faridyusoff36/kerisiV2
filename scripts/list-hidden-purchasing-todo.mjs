import fs from "fs";

const j = JSON.parse(
  fs.readFileSync("C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL2.json", "utf8"),
);
const router = fs.readFileSync("client/src/router/index.ts", "utf8");
const ids = new Set();
const re = /path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g;
let m;
while ((m = re.exec(router))) ids.add(m[1]);

const byMenu = new Map();
for (const r of j) {
  const menu = String(r.Menu ?? "");
  if (!menu.includes("Purchas")) continue;
  const mid = String(r.MENUID);
  if (!byMenu.has(mid)) {
    byMenu.set(mid, {
      PAGEID: r.PAGEID,
      PAGETITLE: r.PAGETITLE,
      Menu: r.Menu,
      types: new Set(),
    });
  }
  byMenu.get(mid).types.add(r.COMPONENTTYPE);
}

const todo = [...byMenu.entries()]
  .filter(([mid]) => !ids.has(mid))
  .sort((a, b) => Number(a[0]) - Number(b[0]));

console.log("Purchasing hidden remaining", todo.length);
for (const [mid, d] of todo) {
  console.log(mid, d.PAGEID, d.PAGETITLE, d.Menu, [...d.types].join(","));
}
