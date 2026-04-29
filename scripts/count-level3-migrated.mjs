import fs from "fs";

const LEVEL3_PATH = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const LEVEL3 = JSON.parse(fs.readFileSync(LEVEL3_PATH, "utf8"));
const level3MenuIds = new Set(LEVEL3.map((r) => String(r.MENUID)));

/** Hidden Kerisi routes (`*Hidden(...)`) define placeholder components — apply first. */
/** @type {Map<string, string>} */
const routeByMenuId = new Map();

for (const fname of fs.readdirSync("client/src/router")) {
  if (!fname.endsWith("-hidden-routes.ts")) continue;
  const src = fs.readFileSync(`client/src/router/${fname}`, "utf8");
  const importM = src.match(/import\s+([A-Za-z0-9_]+)\s+from\s+["']@\/views\//);
  const comp = importM ? importM[1] : "?";
  const re = /\b\w+Hidden\(\s*(\d+)/g;
  let m;
  while ((m = re.exec(src))) {
    routeByMenuId.set(m[1], comp);
  }
}

/** Explicit `index.ts` routes override (migrated real views must win over hidden placeholders). */
const IX = fs.readFileSync("client/src/router/index.ts", "utf8");
const ixChunks = IX.split(/(?=\{\s*path:\s*"\/admin\/kerisi\/m\/)/);
for (const chunk of ixChunks) {
  const pathM = chunk.match(/path:\s*"\/admin\/kerisi\/m\/(\d+)"/);
  if (!pathM) continue;
  const id = pathM[1];
  const compM = chunk.match(/component:\s*([A-Za-z0-9_]+)/);
  const comp = compM ? compM[1] : "?";
  routeByMenuId.set(id, comp);
}

function isPlaceholder(comp) {
  return (
    !comp ||
    comp === "?" ||
    comp.includes("Placeholder") ||
    comp.includes("LegacyPlaceholder") ||
    comp === "GenericLegacyPlaceholderView"
  );
}

let migratedLevel3 = 0;
/** @type {Array<{ menuId: string; comp: string }>} */
const level3Sample = [];

for (const mid of level3MenuIds) {
  const comp = routeByMenuId.get(mid) ?? "";
  const ph = !routeByMenuId.has(mid) || isPlaceholder(comp);
  if (!ph) {
    migratedLevel3++;
    if (level3Sample.length < 25) level3Sample.push({ menuId: mid, comp });
  }
}

console.log("HIDDEN_PAGE_LEVEL3 unique MENU IDs (pages):", level3MenuIds.size);
console.log("LEVEL 3 menus with a non-placeholder route component:", migratedLevel3);
console.log("Non-placeholder samples (up to 25):");
for (const s of level3Sample) {
  console.log(`  ${s.menuId}\t${s.comp}`);
}
