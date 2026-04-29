import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-payroll-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes('import PayrollLegacyPlaceholderView')) {
  index = index.replace(
    'import StaffProfileView from "@/views/StaffProfileView.vue";',
    'import StaffProfileView from "@/views/StaffProfileView.vue";\nimport PayrollLegacyPlaceholderView from "@/views/PayrollLegacyPlaceholderView.vue";',
  );
}

const old = `    // Hidden Payroll — HIDDEN_PAGE_LEVEL2–5 (\`Menu\` starts \`Payroll>\`).
    ...kerisiPayrollHiddenRoutes,`;
const next =
  `${snippet}\n    // Hidden Payroll — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).\n    ...kerisiPayrollHiddenRoutes,`;

if (!index.includes(old)) {
  console.error("Expected Payroll hidden block not found.");
  process.exit(1);
}

index = index.replace(old, next);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
