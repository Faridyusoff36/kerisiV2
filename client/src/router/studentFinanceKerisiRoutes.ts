/**
 * Exhaustive SPA routes for **Student Finance** (`menuId` **1019**) in
 * `kerisi-menu-migrated.ts` (lines ~3416–4027). Every `menuId` in that subtree
 * has a dedicated static route registered **before** `/admin/kerisi/m/:menuId`.
 *
 * Migrated Kerisi screens reuse shared FIMS views where parity is clear;
 * remaining entries render `KerisiSfLevel3PageView` (Level-3 registry when
 * present, plus a synthetic grid when export metadata or columns are missing).
 */

import type { Component } from "vue";
import type { RouteRecordRaw } from "vue-router";

import { getKerisiMenuTrailByMenuId } from "@/config/kerisi-menu-resolve";
import { isKerisiRegisteredMenuId } from "@/config/kerisi-all-menu-ids.generated";

import KerisiSfLevel3PageView from "@/views/KerisiSfLevel3PageView.vue";

import AccountCodeView from "@/views/AccountCodeView.vue";
import AdvancePaymentView from "@/views/AdvancePaymentView.vue";
import BankAccountUpdateView from "@/views/BankAccountUpdateView.vue";
import CreditNoteFormView from "@/views/CreditNoteFormView.vue";
import CreditNoteView from "@/views/CreditNoteView.vue";
import DebitNoteFormView from "@/views/DebitNoteFormView.vue";
import DebitNoteView from "@/views/DebitNoteView.vue";
import DiscountNoteFormView from "@/views/DiscountNoteFormView.vue";
import DiscountNoteView from "@/views/DiscountNoteView.vue";
import InvoiceListView from "@/views/InvoiceListView.vue";
import ManualInvoiceFormView from "@/views/ManualInvoiceFormView.vue";
import ManualInvoiceListingView from "@/views/ManualInvoiceListingView.vue";
import OfferedStudentView from "@/views/OfferedStudentView.vue";
import PtptnDataView from "@/views/PtptnDataView.vue";
import SponsorInvoiceGenerationView from "@/views/SponsorInvoiceGenerationView.vue";
import SponsorListView from "@/views/SponsorListView.vue";
import SponsorProfileView from "@/views/SponsorProfileView.vue";
import SponsorPtptnView from "@/views/SponsorPtptnView.vue";
import StudentInsuranceListingView from "@/views/StudentInsuranceListingView.vue";
import StudentInvoiceGenerationView from "@/views/StudentInvoiceGenerationView.vue";
import StudentJournalApprovalView from "@/views/StudentJournalApprovalView.vue";
import StudentLedgerView from "@/views/StudentLedgerView.vue";

/**
 * All `menuId` values under Student Finance in `kerisi-menu-migrated.ts`
 * (must stay in sync when the menu CSV is regenerated).
 */
export const STUDENT_FINANCE_MENU_IDS: readonly number[] = [
  1019, 1023, 1025, 1029, 1030, 1031, 1032, 1038, 1039, 1058, 1059, 1061, 1067, 1069, 1070, 1071, 1072, 1073, 1074, 1075,
  1081, 1082, 1083, 1084, 1149, 1192, 1193, 1231, 1252, 1253, 1255, 1257, 1262, 1263, 1278, 1279, 1280, 1284, 1285, 1286,
  1298, 1311, 1313, 1323, 1326, 1327, 1328, 1335, 1339, 1354, 1491, 1507, 1509, 1529, 1530, 1538, 1539, 1550, 1570, 1571,
  1575, 1576, 1601, 1790, 1794, 1795, 1797, 1798, 1799, 1800, 1801, 1802, 1804, 1806, 1807, 1810, 1811, 1812, 1814, 1816,
  1822, 1899, 1900, 1911, 1916, 1918, 1923, 1924, 1949, 1954, 1956, 1965, 1984, 1988, 2008, 2010, 2020, 2026, 2029, 2064,
  2093, 2096, 2390, 2437, 2556, 2557, 2601, 2622, 2636, 2750, 2797, 2799, 2802, 2834, 2840, 2897, 2898, 2937, 3093,
];

if (import.meta.env.DEV) {
  const s = new Set(STUDENT_FINANCE_MENU_IDS);
  if (s.size !== STUDENT_FINANCE_MENU_IDS.length) {
    throw new Error("studentFinanceKerisiRoutes: duplicate menu IDs in STUDENT_FINANCE_MENU_IDS");
  }
  if (STUDENT_FINANCE_MENU_IDS.length !== 119) {
    throw new Error("studentFinanceKerisiRoutes: expected 119 Student Finance menu IDs — update STUDENT_FINANCE_MENU_IDS");
  }
  for (const id of STUDENT_FINANCE_MENU_IDS) {
    if (!isKerisiRegisteredMenuId(id)) {
      throw new Error(
        `studentFinanceKerisiRoutes: Student Finance menuId ${id} is not in KERISI_MENU_TREE — run npm run gen:kerisi-all-menu-ids or npm run build:kerisi-menu`,
      );
    }
  }
}

/** Dedicated views; all other Student Finance menus use `KerisiSfLevel3PageView` (registry + fallback grid). */
const COMPONENT_BY_MENU_ID: Partial<Record<number, Component>> = {
  1023: InvoiceListView,
  1025: SponsorProfileView,
  1031: PtptnDataView,
  1038: OfferedStudentView,
  1039: StudentInsuranceListingView,
  1231: StudentInvoiceGenerationView,
  1491: SponsorInvoiceGenerationView,
  1507: SponsorPtptnView,
  1509: StudentLedgerView,
  1529: CreditNoteView,
  1530: CreditNoteFormView,
  1570: DiscountNoteView,
  1571: DiscountNoteFormView,
  1575: DebitNoteView,
  1576: DebitNoteFormView,
  1070: AccountCodeView,
  1323: PtptnDataView,
  2636: OfferedStudentView,
  1081: BankAccountUpdateView,
  2937: BankAccountUpdateView,
  2802: BankAccountUpdateView,
  3093: BankAccountUpdateView,
  2897: ManualInvoiceListingView,
  2898: ManualInvoiceFormView,
  2020: AdvancePaymentView,
  2390: StudentJournalApprovalView,
  1916: SponsorListView,
  2797: StudentInsuranceListingView,
  2799: StudentInsuranceListingView,
};

const ROUTE_NAME_BY_MENU_ID: Partial<Record<number, string>> = {
  1023: "kerisi-student-finance-invoice",
  1025: "kerisi-student-finance-sponsor-profile",
  1031: "kerisi-student-finance-ptptn-data",
  1038: "kerisi-student-finance-insurance-list-offered-student",
  1039: "kerisi-student-finance-insurance-returning-student",
  1231: "kerisi-student-finance-invoice-generation",
  1491: "kerisi-student-finance-sponsor-invoice-generation",
  1507: "kerisi-student-finance-sponsor-ptptn",
  1509: "kerisi-student-finance-student-ledger",
  1529: "kerisi-sf-credit-note",
  1570: "kerisi-sf-discount-note",
  1575: "kerisi-sf-debit-note",
  2020: "kerisi-student-finance-sponsor-advance-payment",
  2390: "kerisi-student-finance-sponsor-student-journal-approval",
  2636: "kerisi-student-finance-list-of-offered",
  2897: "kerisi-student-finance-manual-invoice-listing",
  2898: "kerisi-student-finance-manual-invoice-form",
  1081: "kerisi-student-finance-bank-account-update",
  1916: "kerisi-student-finance-report-list-of-sponsor",
  2797: "kerisi-student-finance-insurance-ifas-invoice-list",
  2799: "kerisi-student-finance-insurance-duplicate-multiple",
};

function sfMetaTitle(menuId: number): string {
  const trail = getKerisiMenuTrailByMenuId(menuId);
  if (trail?.length) return trail.join(" / ");
  return `Kerisi / Menu ${menuId}`;
}

export const studentFinanceKerisiRoutes: RouteRecordRaw[] = STUDENT_FINANCE_MENU_IDS.map(
  (menuId): RouteRecordRaw => {
    const component = COMPONENT_BY_MENU_ID[menuId] ?? KerisiSfLevel3PageView;
    const name = ROUTE_NAME_BY_MENU_ID[menuId] ?? `kerisi-sf-m-${menuId}`;
    return {
      path: `/admin/kerisi/m/${menuId}`,
      name,
      component,
      meta: {
        requiresAuth: true,
        title: sfMetaTitle(menuId),
      },
    };
  },
);
