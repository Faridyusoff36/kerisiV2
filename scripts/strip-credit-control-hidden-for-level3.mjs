/**
 * Rewrite kerisi-credit-control-hidden-routes.ts: drop ccHidden(...) whose MENUID is in HIDDEN_PAGE_LEVEL3.json global set (migrated in index.ts).
 */
import fs from "fs";

const hiddenPath = "client/src/router/kerisi-credit-control-hidden-routes.ts";
const level3Path = "C:/KerisiAI/02MigrateFromOldKerisi/JSON_DATA/HIDDEN_PAGE_LEVEL3.json";

const level3Ids = new Set(
  JSON.parse(fs.readFileSync(level3Path, "utf8")).map((r) => String(r.MENUID)),
);

const hidden = fs.readFileSync(hiddenPath, "utf8");

function ccCallEnd(src, from) {
  const open = src.indexOf("ccHidden(", from);
  if (open === -1) return null;
  let i = open + "ccHidden(".length;
  let depth = 1;
  let inStr = false;
  let quote = "";
  for (; i < src.length && depth > 0; i++) {
    const c = src[i];
    if (inStr) {
      if (c === "\\" && quote) {
        i++;
        continue;
      }
      if (c === quote) inStr = false;
      continue;
    }
    if (c === '"' || c === "'") {
      inStr = true;
      quote = c;
      continue;
    }
    if (c === "(") depth++;
    else if (c === ")") depth--;
  }
  let j = i;
  while (j < src.length && /\s/.test(src[j])) j++;
  if (src[j] === ",") j++;
  return { open, end: j };
}

const expIdx = hidden.indexOf("export const kerisiCreditControlHiddenRoutes");
if (expIdx === -1) throw new Error("export not found");
const eqIdx = hidden.indexOf("=", expIdx);
if (eqIdx === -1) throw new Error("= not found");
let arrOpen = -1;
for (let p = eqIdx + 1; p < hidden.length; p++) {
  if (hidden[p] === "[") {
    arrOpen = p;
    break;
  }
}
if (arrOpen === -1) throw new Error("array `[` after `=` not found");
const header = hidden.slice(0, arrOpen + 1);

const kept = [];
let search = arrOpen + 1;
while (true) {
  const idx = hidden.indexOf("ccHidden(", search);
  if (idx === -1) break;
  const r = ccCallEnd(hidden, idx);
  if (!r) break;
  const slice = hidden.slice(r.open, r.end);
  const id = slice.match(/ccHidden\(\s*(\d+)/)?.[1];
  const drop = id && level3Ids.has(id);
  if (!drop) kept.push(slice.trimEnd());
  search = r.end;
}

const inner = `${kept.map((b) => `  ${b}`).join("\n")}\n`;
fs.writeFileSync(hiddenPath, `${header}\n${inner}];\n`);
console.log("rewrote", hiddenPath, "kept ccHidden:", kept.length);
