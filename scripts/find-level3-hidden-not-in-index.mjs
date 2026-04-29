import fs from "fs";
import path from "path";

const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";
const indexPath = "client/src/router/index.ts";
const routerDir = "client/src/router";

const L3 = new Set(JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)));

const idx = fs.readFileSync(indexPath, "utf8");
const inIndex = new Set();
for (const m of idx.matchAll(/path:\s*["']\/admin\/kerisi\/m\/(\d+)["']/g)) inIndex.add(m[1]);

const files = fs.readdirSync(routerDir).filter((f) => f.includes("hidden") && f.endsWith(".ts"));

const report = [];
for (const f of files) {
  const s = fs.readFileSync(path.join(routerDir, f), "utf8");
  const ids = new Set();
  for (const m of s.matchAll(/\b\w+Hidden\(\s*(\d+)/g)) ids.add(m[1]);
  const todo = [...ids].filter((id) => L3.has(id) && !inIndex.has(id)).sort((a, b) => Number(a) - Number(b));
  if (todo.length) report.push({ file: f, ids: todo });
}

if (report.length === 0) {
  console.log("No LEVEL3 MENUID still missing explicit path in index.ts (good).");
  process.exit(0);
}

console.log("LEVEL3 stubs still needing explicit route in index.ts (in hidden file, path not in index.ts):");
for (const { file, ids } of report) {
  console.log(file, ids.length, ids.join(","));
}
