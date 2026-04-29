import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-loan-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes('import LoanLegacyPlaceholderView')) {
  index = index.replace(
    'import GeneralLedgerLegacyPlaceholderView from "@/views/GeneralLedgerLegacyPlaceholderView.vue";',
    'import GeneralLedgerLegacyPlaceholderView from "@/views/GeneralLedgerLegacyPlaceholderView.vue";\nimport LoanLegacyPlaceholderView from "@/views/LoanLegacyPlaceholderView.vue";',
  );
}

const old = `    // Hidden Loan — HIDDEN_PAGE_LEVEL2–5 (\`Menu\` paths: Portal>Loan, payroll
    // deduction loan, student business / micro credit loan setup).
    ...kerisiLoanHiddenRoutes,`;
const next =
  `${snippet}\n    // Hidden Loan — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).\n    ...kerisiLoanHiddenRoutes,`;

if (!index.includes("...kerisiLoanHiddenRoutes,")) {
  console.error("Loan spread not found.");
  process.exit(1);
}
if (!index.includes(old)) {
  console.error("Expected Loan hidden comment block not found.");
  process.exit(1);
}

index = index.replace(old, next);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
