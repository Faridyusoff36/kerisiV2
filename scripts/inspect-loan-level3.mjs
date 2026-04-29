import fs from "fs";

const hid = fs.readFileSync("client/src/router/kerisi-loan-hidden-routes.ts", "utf8");
const ids = [];
let m;
const re = /loanHidden\(\s*(\d+)/g;
while ((m = re.exec(hid))) ids.push(m[1]);
console.log("hidden count", ids.length);

const level3 = JSON.parse(
  fs.readFileSync("C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json", "utf8"),
);
const lvlSet = new Map();
for (const r of level3) {
  const id = String(r.MENUID);
  if (!lvlSet.has(id)) lvlSet.set(id, { Menu: r.Menu, title: r.PAGETITLE, PAGEID: r.PAGEID });
}

const migrate = ids.filter((id) => lvlSet.has(id)).sort((a, b) => Number(a) - Number(b));
const notInLvl = ids.filter((id) => !lvlSet.has(id)).sort((a, b) => Number(a) - Number(b));
console.log("intersect LEVEL3 (migrate)", migrate.length);
console.log(migrate.join(","));
console.log("in hidden not in LEVEL3 JSON (keep stubs)", notInLvl.length, notInLvl.join(","));
