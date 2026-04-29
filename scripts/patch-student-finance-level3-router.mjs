import fs from "fs";

const indexPath = "client/src/router/index.ts";
const snippetPath = "scripts/gen-student-finance-level3-routes-snippet.txt";

let index = fs.readFileSync(indexPath, "utf8");
const snippet = fs.readFileSync(snippetPath, "utf8").trimEnd();

if (!index.includes('import StudentFinanceLegacyPlaceholderView')) {
  index = index.replace(
    'import StudentJournalApprovalView from "@/views/StudentJournalApprovalView.vue";',
    'import StudentJournalApprovalView from "@/views/StudentJournalApprovalView.vue";\nimport StudentFinanceLegacyPlaceholderView from "@/views/StudentFinanceLegacyPlaceholderView.vue";',
  );
}

const old =
  "    // Hidden Student Finance — HIDDEN_PAGE_LEVEL2–5 (`Menu` paths starting `Student Finance>`).\n    ...kerisiStudentFinanceHiddenRoutes,";
const next =
  `${snippet}\n    // Hidden Student Finance — remaining LEVEL2–5 stubs (LEVEL3 explicit routes above).\n    ...kerisiStudentFinanceHiddenRoutes,`;

if (!index.includes(old)) {
  console.error("Expected Student Finance hidden block not found in index.ts.");
  process.exit(1);
}

index = index.replace(old, next);
fs.writeFileSync(indexPath, index);
console.log("patched", indexPath);
