import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-account-payable-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const ins = fs.readFileSync(snippetPath, "utf8").trimEnd();

const marker =
  "// Hidden Account Payable / Account Receivable / Budget / Purchasing /";
const idx = index.indexOf(marker);
if (idx === -1) {
  console.error("Marker not found — index.ts may have changed.");
  process.exit(1);
}

const spread = "...kerisiAccountPayableHiddenRoutes,";
const endIdx = index.indexOf(spread, idx);
if (endIdx === -1) {
  console.error("Spread not found after marker.");
  process.exit(1);
}

index =
  index.slice(0, idx) +
  ins +
  "\n    " +
  spread +
  index.slice(endIdx + spread.length);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
