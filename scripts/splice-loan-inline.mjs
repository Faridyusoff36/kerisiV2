import fs from "fs";
import path from "path";
import { fileURLToPath } from "url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, "..");
const indexPath = path.join(root, "client/src/router/index.ts");
const snipPath = path.join(root, "scripts/_loan-inline-clean.txt");

let idx = fs.readFileSync(indexPath, "utf8");
const snip = fs.readFileSync(snipPath, "utf8");

const oldBlock = `    // Hidden Loan — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).
    ...kerisiLoanHiddenRoutes,
`;

if (!idx.includes(oldBlock)) {
  console.error("oldBlock not found in index.ts");
  process.exit(1);
}

idx = idx.replace(oldBlock, snip);

idx = idx.replace(
  /import \{ kerisiLoanHiddenRoutes \} from "@\/router\/kerisi-loan-hidden-routes";\r?\n/,
  "",
);

idx = idx.replace(
  /\/\/ HIDDEN_PAGE_LEVEL3 Loan \(`HIDDEN_PAGE_LEVEL3\.json` overlaps; must stay before `\.\.\.kerisiLoanHiddenRoutes`\)\./,
  "// HIDDEN_PAGE_LEVEL3 Loan (`HIDDEN_PAGE_LEVEL3.json` overlaps; inlined LEVEL4 stubs follow this block).",
);

fs.writeFileSync(indexPath, idx);
console.log("Patched index.ts");
