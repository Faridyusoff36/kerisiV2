import { createRouter, createWebHistory } from "vue-router";
import type { RouteLocationGeneric, RouteRecordRaw } from "vue-router";

import DashboardView from "@/views/DashboardView.vue";
import MainDashboardView from "@/views/MainDashboardView.vue";
import KitchenChartsView from "@/views/KitchenChartsView.vue";
import KitchenFormsView from "@/views/KitchenFormsView.vue";
import LoginView from "@/views/LoginView.vue";
import MediaLibraryView from "@/views/MediaLibraryView.vue";
import KitchenSinkView from "@/views/KitchenSinkView.vue";
import KitchenSinkPatternsView from "@/views/KitchenSinkPatternsView.vue";
import PageEditorView from "@/views/PageEditorView.vue";
import PagesListView from "@/views/PagesListView.vue";
import PostEditorView from "@/views/PostEditorView.vue";
import PostsListView from "@/views/PostsListView.vue";
import CategoriesListView from "@/views/CategoriesListView.vue";
import CategoryEditorView from "@/views/CategoryEditorView.vue";
import DatabaseSchemaView from "@/views/DatabaseSchemaView.vue";
import DevelopersGuideView from "@/views/DevelopersGuideView.vue";
import ApiManagementView from "@/views/ApiManagementView.vue";
import MenusView from "@/views/MenusView.vue";
import StorefrontMenuView from "@/views/StorefrontMenuView.vue";
import WebfrontSettingsView from "@/views/WebfrontSettingsView.vue";
import AuditLogsView from "@/views/AuditLogsView.vue";
import QueueMonitorView from "@/views/QueueMonitorView.vue";
import ComingSoonView from "@/views/ComingSoonView.vue";
import KerisiMenuPlaceholderView from "@/views/KerisiMenuPlaceholderView.vue";
import KerisiArPageView from "@/views/KerisiArPageView.vue";
import KerisiPayrollPageView from "@/views/KerisiPayrollPageView.vue";
import KerisiRemainingPageView from "@/views/KerisiRemainingPageView.vue";
import PurchasingItemMainView from "@/views/PurchasingItemMainView.vue";
import PurchasingJobscopeListView from "@/views/PurchasingJobscopeListView.vue";
import PurchasingPurchaseRequisitionView from "@/views/PurchasingPurchaseRequisitionView.vue";
import { studentFinanceKerisiRoutes } from "@/router/studentFinanceKerisiRoutes";
import RolesView from "@/views/RolesView.vue";
import SettingsView from "@/views/SettingsView.vue";
import SystemInfoView from "@/views/SystemInfoView.vue";
import UsersView from "@/views/UsersView.vue";
import UserEditView from "@/views/UserEditView.vue";
import FundTypeView from "@/views/FundTypeView.vue";
import ActivityCodeView from "@/views/ActivityCodeView.vue";
import AccountCodeView from "@/views/AccountCodeView.vue";
import PtjCodeView from "@/views/PtjCodeView.vue";
import CostCentreView from "@/views/CostCentreView.vue";
import CascadeStructureView from "@/views/CascadeStructureView.vue";
import AccountCodePpiView from "@/views/AccountCodePpiView.vue";
import BudgetMovementView from "@/views/BudgetMovementView.vue";
import BudgetMonitoringView from "@/views/BudgetMonitoringView.vue";
import BudgetInitialView from "@/views/BudgetInitialView.vue";
import BudgetClosingView from "@/views/BudgetClosingView.vue";
import AllocationView from "@/views/AllocationView.vue";
import BudgetCodeView from "@/views/BudgetCodeView.vue";
import BudgetPlanningListView from "@/views/BudgetPlanningListView.vue";
import BudgetPlanningScheduleView from "@/views/BudgetPlanningScheduleView.vue";
import LaporanBelanjawanView from "@/views/LaporanBelanjawanView.vue";
import PlanningNewApplicationView from "@/views/PlanningNewApplicationView.vue";
import StructureBudgetListView from "@/views/StructureBudgetListView.vue";
import TotalAllocationReportView from "@/views/TotalAllocationReportView.vue";
import BankSetupView from "@/views/BankSetupView.vue";
import BankMasterView from "@/views/BankMasterView.vue";
import BankAccountView from "@/views/BankAccountView.vue";
import CashbookListView from "@/views/CashbookListView.vue";
import PayeeRegistrationView from "@/views/PayeeRegistrationView.vue";
import UtilityRegistrationView from "@/views/UtilityRegistrationView.vue";
import AccountBankByPayeeView from "@/views/AccountBankByPayeeView.vue";
import AccountBankUpdatedView from "@/views/AccountBankUpdatedView.vue";
import DebtorView from "@/views/DebtorView.vue";
import CashbookPtjView from "@/views/CashbookPtjView.vue";
import CreditNoteView from "@/views/CreditNoteView.vue";
import CreditNoteFormView from "@/views/CreditNoteFormView.vue";
import DebitNoteView from "@/views/DebitNoteView.vue";
import DebitNoteFormView from "@/views/DebitNoteFormView.vue";
import DiscountNoteView from "@/views/DiscountNoteView.vue";
import DiscountNoteFormView from "@/views/DiscountNoteFormView.vue";
import AuthorizedReceiptingView from "@/views/AuthorizedReceiptingView.vue";
import AuthorizedReceiptingFormView from "@/views/AuthorizedReceiptingFormView.vue";
import DepositView from "@/views/DepositView.vue";
import ListOfDepositView from "@/views/ListOfDepositView.vue";
import InvoiceBalanceView from "@/views/InvoiceBalanceView.vue";
import DepositFormView from "@/views/DepositFormView.vue";
import DebtorProfileUpdateView from "@/views/DebtorProfileUpdateView.vue";
import GlYearMonthView from "@/views/GlYearMonthView.vue";
import GeneralLedgerListingView from "@/views/GeneralLedgerListingView.vue";
import JournalListingView from "@/views/JournalListingView.vue";
import ManualJournalListingView from "@/views/ManualJournalListingView.vue";
import PostingToTbView from "@/views/PostingToTbView.vue";
import BankAccountUpdateView from "@/views/BankAccountUpdateView.vue";
import ListOfAccrualView from "@/views/ListOfAccrualView.vue";
import InvestmentAccrualView from "@/views/InvestmentAccrualView.vue";
import InvestmentGenerateScheduleView from "@/views/InvestmentGenerateScheduleView.vue";
import InvestmentMonitoringView from "@/views/InvestmentMonitoringView.vue";
import InvestmentToBeWithdrawnView from "@/views/InvestmentToBeWithdrawnView.vue";
import ListOfInvestmentsView from "@/views/ListOfInvestmentsView.vue";
import SummaryListInvestmentsView from "@/views/SummaryListInvestmentsView.vue";
import PtptnDataView from "@/views/PtptnDataView.vue";
import StudentLedgerView from "@/views/StudentLedgerView.vue";
import OfferedStudentView from "@/views/OfferedStudentView.vue";
import InvoiceListView from "@/views/InvoiceListView.vue";
import ManualInvoiceFormView from "@/views/ManualInvoiceFormView.vue";
import ManualInvoiceListingView from "@/views/ManualInvoiceListingView.vue";
import StudentInvoiceGenerationView from "@/views/StudentInvoiceGenerationView.vue";
import AdvancePaymentView from "@/views/AdvancePaymentView.vue";
import SponsorPtptnView from "@/views/SponsorPtptnView.vue";
import SponsorProfileView from "@/views/SponsorProfileView.vue";
import SponsorInvoiceGenerationView from "@/views/SponsorInvoiceGenerationView.vue";
import StudentJournalApprovalView from "@/views/StudentJournalApprovalView.vue";
import StatusPoPrView from "@/views/StatusPoPrView.vue";
import TenderQuotationView from "@/views/TenderQuotationView.vue";
import AuditSystemTransactionView from "@/views/AuditSystemTransactionView.vue";
import VendorPoStatusView from "@/views/VendorPoStatusView.vue";
import VendorFinancialStatusView from "@/views/VendorFinancialStatusView.vue";
import SponsorLetterView from "@/views/SponsorLetterView.vue";
import StaffProfileView from "@/views/StaffProfileView.vue";
import VendorPortalView from "@/views/VendorPortalView.vue";
import AssetInventoryListView from "@/views/AssetInventoryListView.vue";
import ProjectListView from "@/views/ProjectListView.vue";
import ProjectUpdatedBalanceView from "@/views/ProjectUpdatedBalanceView.vue";
import VendorRegistrationFeeHistoryView from "@/views/VendorRegistrationFeeHistoryView.vue";
import DebtorReminderView from "@/views/DebtorReminderView.vue";
import DebtorStatementView from "@/views/DebtorStatementView.vue";
import LetterPhraseView from "@/views/LetterPhraseView.vue";
import IntegrationPtjView from "@/views/IntegrationPtjView.vue";
import IntegrationCostCentreView from "@/views/IntegrationCostCentreView.vue";
import IntegrationProfileView from "@/views/IntegrationProfileView.vue";
import IntegrationActivityView from "@/views/IntegrationActivityView.vue";
import BudgetNotExistsView from "@/views/BudgetNotExistsView.vue";
import ListOfCurrencyView from "@/views/ListOfCurrencyView.vue";
import AgRateView from "@/views/AgRateView.vue";
import PettyCashApplicationListView from "@/views/PettyCashApplicationListView.vue";
import PettyCashClaimFormView from "@/views/PettyCashClaimFormView.vue";
import PettyCashBillView from "@/views/PettyCashBillView.vue";
import PettyCashByPtjView from "@/views/PettyCashByPtjView.vue";
import PettyCashConfirmPaymentView from "@/views/PettyCashConfirmPaymentView.vue";
import PettyCashRecoupFormView from "@/views/PettyCashRecoupFormView.vue";
import PettyCashRecoupView from "@/views/PettyCashRecoupView.vue";
import PettyCashReleasePaidView from "@/views/PettyCashReleasePaidView.vue";
import PettyCashRequestListView from "@/views/PettyCashRequestListView.vue";
import PettyCashVoucherListView from "@/views/PettyCashVoucherListView.vue";
import VcTncView from "@/views/VcTncView.vue";
import CheckErrorView from "@/views/CheckErrorView.vue";
import BudgetStructureSearchView from "@/views/BudgetStructureSearchView.vue";
import StorefrontHomeView from "@/views/StorefrontHomeView.vue";
import StorefrontPageView from "@/views/StorefrontPageView.vue";
import { useAuthStore } from "@/stores/auth";
import { useSiteStore } from "@/stores/site";

const legacyAdminPaths = [
  "/login",
  "/portal/dashboard",
  "/posts",
  "/posts/new",
  "/posts/:id",
  "/categories",
  "/categories/new",
  "/categories/:id",
  "/pages",
  "/pages/new",
  "/pages/:id",
  "/media",
  "/menus",
  "/webfront-menu",
  "/webfront-settings",
  "/storefront-menu",
  "/kitchen-sink",
  "/kitchen-sink/forms",
  "/kitchen-sink/charts",
  "/kitchen-sink/patterns",
  "/development/database-schema",
  "/development/api-management",
  "/profile",
  "/settings",
  "/settings/users",
  "/settings/users/new",
  "/settings/users/:id",
  "/settings/roles",
  "/settings/audit-logs",
  "/settings/queue-monitor",
  "/settings/system",
];

// Backward-compat redirects: old /admin/settings/* → new /admin/platform/* paths
const settingsRedirects: RouteRecordRaw[] = [
  { path: "/admin/settings/users", redirect: "/admin/platform/identity/users" },
  { path: "/admin/settings/users/new", redirect: "/admin/platform/identity/users/new" },
  { path: "/admin/settings/users/:id", redirect: (to: RouteLocationGeneric) => `/admin/platform/identity/users/${String(to.params.id ?? "")}` },
  { path: "/admin/settings/roles", redirect: "/admin/platform/identity/roles" },
  { path: "/admin/settings/audit-logs", redirect: "/admin/platform/observability/audit-trail" },
  { path: "/admin/settings/queue-monitor", redirect: "/admin/platform/queue" },
];

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/admin/login", name: "login", component: LoginView, meta: { guestOnly: true, title: "Login" } },
    { path: "/admin", name: "main-dashboard", component: MainDashboardView, meta: { requiresAuth: true, title: "Main Dashboard" } },
    { path: "/admin/portal/dashboard", name: "dashboard", component: DashboardView, meta: { requiresAuth: true, title: "Dashboard" } },
    { path: "/admin/posts", name: "posts", component: PostsListView, meta: { requiresAuth: true, title: "Posts" } },
    { path: "/admin/posts/new", name: "post-create", component: PostEditorView, meta: { requiresAuth: true, title: "New Post" } },
    { path: "/admin/posts/:id", name: "post-edit", component: PostEditorView, meta: { requiresAuth: true, title: "Edit Post" } },
    { path: "/admin/categories", name: "categories", component: CategoriesListView, meta: { requiresAuth: true, title: "Categories" } },
    { path: "/admin/categories/new", name: "category-create", component: CategoryEditorView, meta: { requiresAuth: true, title: "New Category" } },
    { path: "/admin/categories/:id", name: "category-edit", component: CategoryEditorView, meta: { requiresAuth: true, title: "Edit Category" } },
    { path: "/admin/pages", name: "pages", component: PagesListView, meta: { requiresAuth: true, title: "Pages" } },
    { path: "/admin/pages/new", name: "page-create", component: PageEditorView, meta: { requiresAuth: true, title: "New Page" } },
    { path: "/admin/pages/:id", name: "page-edit", component: PageEditorView, meta: { requiresAuth: true, title: "Edit Page" } },
    { path: "/admin/media", name: "media", component: MediaLibraryView, meta: { requiresAuth: true, title: "Media" } },
    { path: "/admin/webfront-menu", name: "storefront-menu", component: StorefrontMenuView, meta: { requiresAuth: true, title: "Menus" } },
    { path: "/admin/storefront-menu", redirect: "/admin/webfront-menu" },
    { path: "/admin/webfront-settings", name: "webfront-settings", component: WebfrontSettingsView, meta: { requiresAuth: true, title: "Settings" } },
    { path: "/admin/menus", name: "menus", component: MenusView, meta: { requiresAuth: true, title: "Menus" } },
    { path: "/admin/kerisi/m/1551", name: "kerisi-fund-type", component: FundTypeView, meta: { requiresAuth: true, title: "Fund Type" } },
    { path: "/admin/kerisi/m/1552", name: "kerisi-account-code", component: AccountCodeView, meta: { requiresAuth: true, title: "Account Code" } },
    { path: "/admin/kerisi/m/1566", name: "kerisi-activity-code", component: ActivityCodeView, meta: { requiresAuth: true, title: "Activity Code" } },
    { path: "/admin/kerisi/m/1887", name: "kerisi-cost-centre", component: CostCentreView, meta: { requiresAuth: true, title: "Cost Centre" } },
    { path: "/admin/kerisi/m/2295", name: "kerisi-ptj-code", component: PtjCodeView, meta: { requiresAuth: true, title: "PTJ Code" } },
    { path: "/admin/kerisi/m/1546", name: "kerisi-cascade-structure", component: CascadeStructureView, meta: { requiresAuth: true, title: "Cascade Structure" } },
    // "List of ..." listing menus (FLC_SETUP&MAINTAINANCE) — reuse the setup-screen components.
    { path: "/admin/kerisi/m/2330", name: "kerisi-list-fund-type", component: FundTypeView, meta: { requiresAuth: true, title: "List of Fund Type" } },
    { path: "/admin/kerisi/m/1874", name: "kerisi-list-activity-code", component: ActivityCodeView, meta: { requiresAuth: true, title: "List of Activity Code" } },
    { path: "/admin/kerisi/m/1886", name: "kerisi-list-ptj-code", component: PtjCodeView, meta: { requiresAuth: true, title: "List of PTJ Code" } },
    { path: "/admin/kerisi/m/2360", name: "kerisi-list-cost-centre", component: CostCentreView, meta: { requiresAuth: true, title: "List of Cost Centre" } },
    { path: "/admin/kerisi/m/2167", name: "kerisi-list-cascade-structure", component: CascadeStructureView, meta: { requiresAuth: true, title: "List of Cascade Structure" } },
    { path: "/admin/kerisi/m/3453", name: "kerisi-list-account-code-ppi", component: AccountCodePpiView, meta: { requiresAuth: true, title: "List of Account Code (PPI)" } },
    // FIMS Budget — Increment / Decrement / Virement list pages. Editor pages
    // (menuID 1557/1558/1559) are not migrated yet; the row actions are
    // rendered for visual parity but are no-ops.
    { path: "/admin/kerisi/m/1554", name: "kerisi-budget-increment", component: BudgetMovementView, props: { type: "increment" }, meta: { requiresAuth: true, title: "Budget Increment" } },
    { path: "/admin/kerisi/m/1555", name: "kerisi-budget-decrement", component: BudgetMovementView, props: { type: "decrement" }, meta: { requiresAuth: true, title: "Budget Decrement" } },
    { path: "/admin/kerisi/m/1556", name: "kerisi-budget-virement", component: BudgetMovementView, props: { type: "virement" }, meta: { requiresAuth: true, title: "Budget Virement" } },
    // FIMS Budget — Monitoring (PAGEID 1201 / MENUID 1471), Initial V2
    // (PAGEID 1264 / MENUID 1541), and Closing (PAGEID 1953 / MENUID 2389
    // primary + 3154 alias) pages.
    { path: "/admin/kerisi/m/1471", name: "kerisi-budget-monitoring", component: BudgetMonitoringView, meta: { requiresAuth: true, title: "Budget Monitoring" } },
    { path: "/admin/kerisi/m/1541", name: "kerisi-budget-initial", component: BudgetInitialView, meta: { requiresAuth: true, title: "Budget Initial" } },
    { path: "/admin/kerisi/m/2389", name: "kerisi-budget-closing", component: BudgetClosingView, meta: { requiresAuth: true, title: "Budget Closing" } },
    { path: "/admin/kerisi/m/3154", name: "kerisi-budget-closing-alias", component: BudgetClosingView, meta: { requiresAuth: true, title: "Budget Closing" } },
    // FIMS Cashbook — Bank Setup (PAGEID 2680), Bank Master (PAGEID 1682),
    // Bank Account (PAGEID 1736), List of Cashbook Daily (PAGEID 1397) and
    // Monthly (PAGEID 2024). Daily/Monthly reuse CashbookListView via prop.
    { path: "/admin/kerisi/m/3246", name: "kerisi-bank-setup", component: BankSetupView, meta: { requiresAuth: true, title: "Bank Setup" } },
    { path: "/admin/kerisi/m/2036", name: "kerisi-bank-master", component: BankMasterView, meta: { requiresAuth: true, title: "Bank Master" } },
    { path: "/admin/kerisi/m/2097", name: "kerisi-bank-account", component: BankAccountView, meta: { requiresAuth: true, title: "Bank Account" } },
    { path: "/admin/kerisi/m/1702", name: "kerisi-cashbook-daily", component: CashbookListView, props: { type: "DAILY" }, meta: { requiresAuth: true, title: "List Of CashBook (Daily)" } },
    { path: "/admin/kerisi/m/2471", name: "kerisi-cashbook-monthly", component: CashbookListView, props: { type: "MONTHLY" }, meta: { requiresAuth: true, title: "List Of Cashbook (Monthly)" } },
    // FIMS Account Payable — Payee Registration (MENUID 1711), Utility
    // Registration (MENUID 3466), Account Bank by Payee (MENUID 2751),
    // Account Bank Updated (MENUID 2078).
    { path: "/admin/kerisi/m/1711", name: "kerisi-ap-payee-registration", component: PayeeRegistrationView, meta: { requiresAuth: true, title: "Payee Registration" } },
    { path: "/admin/kerisi/m/3466", name: "kerisi-ap-utility-registration", component: UtilityRegistrationView, meta: { requiresAuth: true, title: "Utility Registration" } },
    { path: "/admin/kerisi/m/2751", name: "kerisi-ap-account-bank-by-payee", component: AccountBankByPayeeView, meta: { requiresAuth: true, title: "Account Bank By Payee" } },
    { path: "/admin/kerisi/m/2078", name: "kerisi-ap-account-bank-updated", component: AccountBankUpdatedView, meta: { requiresAuth: true, title: "Account Bank Updated" } },
    { path: "/admin/kerisi/m/1727", name: "kerisi-ar-debtor", component: DebtorView, meta: { requiresAuth: true, title: "Debtor" } },
    { path: "/admin/kerisi/m/1049", name: "kerisi-ar-cashbook-ptj", component: CashbookPtjView, meta: { requiresAuth: true, title: "Cashbook PTJ" } },
    { path: "/admin/kerisi/m/1041", name: "kerisi-ar-credit-note", component: CreditNoteView, meta: { requiresAuth: true, title: "Credit Note" } },
    { path: "/admin/kerisi/m/1782", name: "kerisi-ar-credit-note-form", component: CreditNoteFormView, meta: { requiresAuth: true, title: "Credit Note Form" } },
    { path: "/admin/kerisi/m/1042", name: "kerisi-ar-debit-note", component: DebitNoteView, meta: { requiresAuth: true, title: "Debit Note" } },
    { path: "/admin/kerisi/m/1783", name: "kerisi-ar-debit-note-form", component: DebitNoteFormView, meta: { requiresAuth: true, title: "Debit Note Form" } },
    { path: "/admin/kerisi/m/1043", name: "kerisi-ar-discount-note", component: DiscountNoteView, meta: { requiresAuth: true, title: "Discount Note" } },
    // Student Finance note listings (1529 / 1575 / 1570) live under `studentFinanceKerisiRoutes.ts`.
    { path: "/admin/kerisi/m/1784", name: "kerisi-ar-discount-note-form", component: DiscountNoteFormView, meta: { requiresAuth: true, title: "Discount Note Form" } },
    { path: "/admin/kerisi/m/1952", name: "kerisi-ar-authorized-receipting", component: AuthorizedReceiptingView, meta: { requiresAuth: true, title: "Authorized Receipting" } },
    { path: "/admin/kerisi/m/1953", name: "kerisi-ar-authorized-receipting-form", component: AuthorizedReceiptingFormView, meta: { requiresAuth: true, title: "Authorized Receipting Form" } },
    // FIMS Credit Control — MENUID 1809 / 3066 / 3388 / 3397 (legacy PAGEIDs
    // 1445, 2159, 2561, 2688). Backed by DepositController /
    // ListOfDepositController / InvoiceBalanceController / DepositFormController.
    { path: "/admin/kerisi/m/1809", name: "kerisi-cc-deposit", component: DepositView, meta: { requiresAuth: true, title: "Deposit" } },
    { path: "/admin/kerisi/m/3066", name: "kerisi-cc-list-of-deposit", component: ListOfDepositView, meta: { requiresAuth: true, title: "List of Deposit" } },
    { path: "/admin/kerisi/m/3388", name: "kerisi-cc-invoice-balance", component: InvoiceBalanceView, meta: { requiresAuth: true, title: "Invoice Balance" } },
    { path: "/admin/kerisi/m/3397", name: "kerisi-cc-deposit-form", component: DepositFormView, meta: { requiresAuth: true, title: "Detail of Deposit" } },
    // Portal (debtor/vendor) read-only listings
    { path: "/admin/kerisi/m/2608", name: "kerisi-portal-debtor-profile-updates", component: DebtorProfileUpdateView, meta: { requiresAuth: true, title: "List of Profile Update Application" } },
    { path: "/admin/kerisi/m/2767", name: "kerisi-portal-tender-list", component: TenderQuotationView, meta: { requiresAuth: true, title: "Tender/Quotation List" } },
    // FIMS Audit Trail — MENUID 5 (PAGEID 3). Backed by AuditSystemTransactionController.
    { path: "/admin/kerisi/m/5", name: "kerisi-audit-system-transaction", component: AuditSystemTransactionView, meta: { requiresAuth: true, title: "Audit Trail / System Transaction" } },
    // FIMS Vendor Portal — MENUID 2015 (PAGEID 1664). Backed by VendorPoStatusController.
    { path: "/admin/kerisi/m/2015", name: "kerisi-vendor-po-status", component: VendorPoStatusView, meta: { requiresAuth: true, title: "Vendor Portal / Purchase Order Status" } },
    // FIMS Vendor Portal — MENUID 2072 (PAGEID 1714). Backed by VendorFinancialStatusController.
    { path: "/admin/kerisi/m/2072", name: "kerisi-vendor-financial-status", component: VendorFinancialStatusView, meta: { requiresAuth: true, title: "Vendor Portal / Financial Status" } },
    // FIMS Portal — MENUID 2823 (PAGEID 2330). Backed by SponsorLetterController.
    { path: "/admin/kerisi/m/2823", name: "kerisi-portal-letter-list", component: SponsorLetterView, meta: { requiresAuth: true, title: "Portal / List of Letter" } },
    // FIMS Portal — MENUID 1914 (PAGEID 1581). Backed by StaffProfileController.
    { path: "/admin/kerisi/m/1914", name: "kerisi-portal-staff-profile", component: StaffProfileView, meta: { requiresAuth: true, title: "Portal / Staff Profile" } },
    // FIMS Vendor Portal — MENUID 1961 (PAGEID 1622). Backed by VendorPortalController.
    // Read-only profile + 7 sub-tables; renewal/edit workflow deferred.
    { path: "/admin/kerisi/m/1961", name: "kerisi-portal-vendor-portal", component: VendorPortalView, meta: { requiresAuth: true, title: "Vendor Portal / Vendor Portal" } },
    // FIMS Asset — MENUID 1548 (PAGEID 1271). Backed by AssetInventoryListController.
    { path: "/admin/kerisi/m/1548", name: "kerisi-asset-list-of-asset", component: AssetInventoryListView, meta: { requiresAuth: true, title: "Asset / List of Asset" } },
    { path: "/admin/kerisi/m/1544", name: "kerisi-project-monitoring-list", component: ProjectListView, meta: { requiresAuth: true, title: "Project Monitoring / List of Project" } },
    { path: "/admin/kerisi/m/2065", name: "kerisi-project-monitoring-balance", component: ProjectUpdatedBalanceView, meta: { requiresAuth: true, title: "Project Monitoring / Updated Balance" } },
    { path: "/admin/kerisi/m/1841", name: "kerisi-purchasing-status-po-pr", component: StatusPoPrView, meta: { requiresAuth: true, title: "Status PO & PR" } },
    // Student Finance (`menuId` 1019) — exhaustive `menuId` coverage (119 routes); see module.
    ...studentFinanceKerisiRoutes,
    { path: "/admin/kerisi/m/1877", name: "kerisi-investment-list-of-accrual", component: ListOfAccrualView, meta: { requiresAuth: true, title: "List of Accrual" } },
    { path: "/admin/kerisi/m/2808", name: "kerisi-investment-summary-list", component: SummaryListInvestmentsView, meta: { requiresAuth: true, title: "Summary List of Investments" } },
    { path: "/admin/kerisi/m/1448", name: "kerisi-investment-list", component: ListOfInvestmentsView, meta: { requiresAuth: true, title: "List of Investments" } },
    { path: "/admin/kerisi/m/3485", name: "kerisi-investment-to-be-withdrawn", component: InvestmentToBeWithdrawnView, meta: { requiresAuth: true, title: "Investment to be Withdrawn" } },
    { path: "/admin/kerisi/m/1446", name: "kerisi-investment-accrual", component: InvestmentAccrualView, meta: { requiresAuth: true, title: "Accrual" } },
    { path: "/admin/kerisi/m/1475", name: "kerisi-investment-generate-schedule", component: InvestmentGenerateScheduleView, meta: { requiresAuth: true, title: "Generate Schedule" } },
    { path: "/admin/kerisi/m/1458", name: "kerisi-investment-monitoring", component: InvestmentMonitoringView, meta: { requiresAuth: true, title: "Investment Monitoring" } },
    { path: "/admin/kerisi/m/2056", name: "kerisi-gl-journal-listing", component: JournalListingView, meta: { requiresAuth: true, title: "Journal Listing" } },
    { path: "/admin/kerisi/m/3287", name: "kerisi-gl-year-month", component: GlYearMonthView, meta: { requiresAuth: true, title: "List of Year and Month" } },
    { path: "/admin/kerisi/m/1409", name: "kerisi-gl-posting-to-tb", component: PostingToTbView, meta: { requiresAuth: true, title: "Posting to GL (TB)" } },
    { path: "/admin/kerisi/m/2519", name: "kerisi-gl-listing", component: GeneralLedgerListingView, meta: { requiresAuth: true, title: "General Ledger Listing" } },
    { path: "/admin/kerisi/m/2089", name: "kerisi-gl-manual-journal-listing", component: ManualJournalListingView, meta: { requiresAuth: true, title: "Manual Journal Listing" } },
    { path: "/admin/kerisi/m/2003", name: "kerisi-portal-registration-fees", component: VendorRegistrationFeeHistoryView, meta: { requiresAuth: true, title: "Online Registration Fee History" } },
    { path: "/admin/kerisi/m/2584", name: "kerisi-portal-debtor-reminder", component: DebtorReminderView, meta: { requiresAuth: true, title: "Reminder" } },
    { path: "/admin/kerisi/m/2267", name: "kerisi-portal-debtor-statement", component: DebtorStatementView, meta: { requiresAuth: true, title: "Debtors Statement" } },
    // FIMS setup & maintenance pages migrated from legacy PAGE_SETUP_MAINTENANCE
    // (level 2) — MENUID maps to legacy MENUID and keeps URL parity with the
    // generic `/admin/kerisi/m/:menuId` pattern used by the sidebar.
    { path: "/admin/kerisi/m/3506", name: "kerisi-letter-phrase", component: LetterPhraseView, meta: { requiresAuth: true, title: "Letter Phrase" } },
    // Setup and Maintenance > Integration / Report / Currency — migrated from
    // PAGE_MENUID1003_LEVEL3.json (PAGEIDs 1860, 1861, 2000, 2003, 2200, 2636,
    // 2647). Each MENUID matches an existing leaf in the kerisi sidebar.
    { path: "/admin/kerisi/m/2277", name: "kerisi-integration-ptj", component: IntegrationPtjView, meta: { requiresAuth: true, title: "Integration - PTJ" } },
    { path: "/admin/kerisi/m/2278", name: "kerisi-integration-cost-centre", component: IntegrationCostCentreView, meta: { requiresAuth: true, title: "Integration - Cost Centre" } },
    { path: "/admin/kerisi/m/2443", name: "kerisi-integration-profile", component: IntegrationProfileView, meta: { requiresAuth: true, title: "Integration - Profile" } },
    { path: "/admin/kerisi/m/2444", name: "kerisi-integration-activity", component: IntegrationActivityView, meta: { requiresAuth: true, title: "Integration - Activity" } },
    { path: "/admin/kerisi/m/2657", name: "kerisi-budget-not-exists", component: BudgetNotExistsView, meta: { requiresAuth: true, title: "Budget Not Exists" } },
    { path: "/admin/kerisi/m/3198", name: "kerisi-list-of-currency", component: ListOfCurrencyView, meta: { requiresAuth: true, title: "List of Currency" } },
    { path: "/admin/kerisi/m/3199", name: "kerisi-ag-rate", component: AgRateView, meta: { requiresAuth: true, title: "AG Rate" } },
    {
      path: "/admin/kerisi/m/1532",
      name: "kerisi-petty-cash-recoup-list",
      component: PettyCashRecoupView,
      meta: { requiresAuth: true, title: "Petty Cash Recoup List" },
    },
    {
      path: "/admin/kerisi/m/1534",
      name: "kerisi-petty-cash-recoup-form",
      component: PettyCashRecoupFormView,
      meta: { requiresAuth: true, title: "Petty Cash Recoup Form" },
    },
    {
      path: "/admin/kerisi/m/1490",
      name: "kerisi-petty-cash-application-list",
      component: PettyCashApplicationListView,
      meta: { requiresAuth: true, title: "List of Petty Cash Application" },
    },
    {
      path: "/admin/kerisi/m/1872",
      name: "kerisi-petty-cash-claim-form",
      component: PettyCashClaimFormView,
      meta: { requiresAuth: true, title: "Petty Cash Claim Form" },
    },
    {
      path: "/admin/kerisi/m/2399",
      name: "kerisi-petty-cash-by-ptj",
      component: PettyCashByPtjView,
      meta: { requiresAuth: true, title: "List Petty Cash by PTJ" },
    },
    {
      path: "/admin/kerisi/m/2400",
      name: "kerisi-petty-cash-bill",
      component: PettyCashBillView,
      meta: { requiresAuth: true, title: "Bill Petty Cash" },
    },
    {
      path: "/admin/kerisi/m/2424",
      name: "kerisi-petty-cash-confirm-payment",
      component: PettyCashConfirmPaymentView,
      meta: { requiresAuth: true, title: "Confirmation Payment" },
    },
    {
      path: "/admin/kerisi/m/2456",
      name: "kerisi-petty-cash-request-list",
      component: PettyCashRequestListView,
      meta: { requiresAuth: true, title: "Request Petty Cash" },
    },
    {
      path: "/admin/kerisi/m/2761",
      name: "kerisi-petty-cash-release-paid",
      component: PettyCashReleasePaidView,
      meta: { requiresAuth: true, title: "List of Release Paid" },
    },
    {
      path: "/admin/kerisi/m/3344",
      name: "kerisi-petty-cash-voucher-list",
      component: PettyCashVoucherListView,
      meta: { requiresAuth: true, title: "List of Voucher Petty Cash" },
    },
    { path: "/admin/kerisi/m/2073", name: "kerisi-vc-tnc", component: VcTncView, meta: { requiresAuth: true, title: "HOD, VC & TNC" } },
    { path: "/admin/kerisi/m/2740", name: "kerisi-check-error", component: CheckErrorView, meta: { requiresAuth: true, title: "Cek yang mungkin error" } },
    { path: "/admin/kerisi/m/3224", name: "kerisi-budget-structure-search", component: BudgetStructureSearchView, meta: { requiresAuth: true, title: "Setup Carian Structure Budget" } },
    // FIMS Budget setup pages migrated from PAGE_MENUID1007_LEVEL3.json:
    //   - PAGEID 1475 / MENUID 1796: Budget Code (BL MM_API_BUDGET_SETUP_BUDGETCODE)
    //   - PAGEID 2872 / MENUID 3456: Budget Planning Schedule
    //     (BL SNA_API_BUDGET_SETUP_BDGPLANNINGSCHEDULE)
    { path: "/admin/kerisi/m/1796", name: "kerisi-budget-code", component: BudgetCodeView, meta: { requiresAuth: true, title: "Budget Code" } },
    { path: "/admin/kerisi/m/3456", name: "kerisi-budget-planning-schedule", component: BudgetPlanningScheduleView, meta: { requiresAuth: true, title: "Budget Planning Schedule" } },
    // Pass 2 of FIMS Budget migration (PAGE_MENUID1007_LEVEL3.json):
    //   - MENUID 1294: Allocation (Quarter Budget) — PAGEID 1035
    //   - MENUID 1334: Structure Budget List — PAGEID 1071
    //   - MENUID 1516: Planning New Application stub — PAGEID 1236 (workflow deferred)
    //   - MENUID 1968: Total Allocation Report — PAGEID 1626
    //   - MENUID 3457: Laporan Belanjawan — PAGEID 2873
    //   - Budget Planning lists (shared): 2506 / 3012 / 3013 / 3196 / 3279
    { path: "/admin/kerisi/m/1294", name: "kerisi-allocation", component: AllocationView, meta: { requiresAuth: true, title: "Allocation" } },
    { path: "/admin/kerisi/m/1334", name: "kerisi-structure-budget-list", component: StructureBudgetListView, meta: { requiresAuth: true, title: "Budget / Setup / Budget Structure List" } },
    { path: "/admin/kerisi/m/1516", name: "kerisi-planning-new-application", component: PlanningNewApplicationView, meta: { requiresAuth: true, title: "Planning New Application" } },
    { path: "/admin/kerisi/m/1968", name: "kerisi-total-allocation-report", component: TotalAllocationReportView, meta: { requiresAuth: true, title: "Budget / Report / Total Allocation, Expenditure and Balance" } },
    { path: "/admin/kerisi/m/3457", name: "kerisi-laporan-belanjawan", component: LaporanBelanjawanView, meta: { requiresAuth: true, title: "Laporan Belanjawan" } },
    { path: "/admin/kerisi/m/2506", name: "kerisi-planning-dasar-sedia-ada", component: BudgetPlanningListView, props: { scope: "yearly" }, meta: { requiresAuth: true, title: "Dasar Sedia Ada" } },
    { path: "/admin/kerisi/m/3012", name: "kerisi-planning-allocation-2", component: BudgetPlanningListView, props: { scope: "allocation_2" }, meta: { requiresAuth: true, title: "Allocation 2" } },
    { path: "/admin/kerisi/m/3013", name: "kerisi-planning-allocation-3", component: BudgetPlanningListView, props: { scope: "allocation_3" }, meta: { requiresAuth: true, title: "Allocation 3" } },
    { path: "/admin/kerisi/m/3196", name: "kerisi-planning-dasar-baru", component: BudgetPlanningListView, props: { scope: "one_off" }, meta: { requiresAuth: true, title: "Budget / Planning / Dasar Baru / One Off" } },
    { path: "/admin/kerisi/m/3279", name: "kerisi-planning-to-initial", component: BudgetPlanningListView, props: { scope: "to_initial" }, meta: { requiresAuth: true, title: "Budget / Planning / Planning to Initial" } },
    // ── Account Receivable pages — PAGE_MENUID1024_LEVEL3.json (all 32) ──────────
    // Invoice
    { path: "/admin/kerisi/m/1581", name: "kerisi-ar-my-request", component: KerisiArPageView, meta: { requiresAuth: true, title: "My Request" } },
    { path: "/admin/kerisi/m/1582", name: "kerisi-ar-my-request-form", component: KerisiArPageView, meta: { requiresAuth: true, title: "My Request Form" } },
    { path: "/admin/kerisi/m/1757", name: "kerisi-ar-recurring-list", component: KerisiArPageView, meta: { requiresAuth: true, title: "Recurring List" } },
    { path: "/admin/kerisi/m/1758", name: "kerisi-ar-recurring-contract-setup", component: KerisiArPageView, meta: { requiresAuth: true, title: "Recurring Contract Setup" } },
    { path: "/admin/kerisi/m/1728", name: "kerisi-ar-debtor-profile", component: KerisiArPageView, meta: { requiresAuth: true, title: "Debtor Profile" } },
    { path: "/admin/kerisi/m/3275", name: "kerisi-ar-salary-deduction-calculator", component: KerisiArPageView, meta: { requiresAuth: true, title: "Salary Deduction Calculator" } },
    { path: "/admin/kerisi/m/3280", name: "kerisi-ar-salary-deduction-listing", component: KerisiArPageView, meta: { requiresAuth: true, title: "Salary Deduction Schedule Listing" } },
    // Receipt
    { path: "/admin/kerisi/m/1590", name: "kerisi-ar-list-of-receipts", component: KerisiArPageView, meta: { requiresAuth: true, title: "List of Receipts" } },
    { path: "/admin/kerisi/m/1598", name: "kerisi-ar-entry-form", component: KerisiArPageView, meta: { requiresAuth: true, title: "Entry Form" } },
    { path: "/admin/kerisi/m/1742", name: "kerisi-ar-bankin-slip-generation", component: KerisiArPageView, meta: { requiresAuth: true, title: "Bank-In Slip Generation" } },
    { path: "/admin/kerisi/m/1761", name: "kerisi-ar-bankin-slip-download", component: KerisiArPageView, meta: { requiresAuth: true, title: "Bank-In Slip Download" } },
    { path: "/admin/kerisi/m/2347", name: "kerisi-ar-receipt-on-behalf-list", component: KerisiArPageView, meta: { requiresAuth: true, title: "List of Receipt on Behalf" } },
    { path: "/admin/kerisi/m/2348", name: "kerisi-ar-entry-form-on-behalf", component: KerisiArPageView, meta: { requiresAuth: true, title: "Entry Form on Behalf" } },
    { path: "/admin/kerisi/m/1740", name: "kerisi-ar-update-foreign-currency", component: KerisiArPageView, meta: { requiresAuth: true, title: "Update Foreign Currency" } },
    { path: "/admin/kerisi/m/1741", name: "kerisi-ar-update-card-info", component: KerisiArPageView, meta: { requiresAuth: true, title: "Update Card Info" } },
    { path: "/admin/kerisi/m/2939", name: "kerisi-ar-online-payment-requery", component: KerisiArPageView, meta: { requiresAuth: true, title: "Online Payment Requery" } },
    { path: "/admin/kerisi/m/3249", name: "kerisi-ar-cash-receipt-release", component: KerisiArPageView, meta: { requiresAuth: true, title: "Cash Receipt Release" } },
    // Cheque
    { path: "/admin/kerisi/m/1652", name: "kerisi-ar-cheque-registry", component: KerisiArPageView, meta: { requiresAuth: true, title: "Cheque Registry" } },
    { path: "/admin/kerisi/m/1656", name: "kerisi-ar-cheque-release", component: KerisiArPageView, meta: { requiresAuth: true, title: "Cheque Release" } },
    { path: "/admin/kerisi/m/2528", name: "kerisi-ar-cheque-release-view", component: KerisiArPageView, meta: { requiresAuth: true, title: "List of Cheque Release" } },
    { path: "/admin/kerisi/m/1720", name: "kerisi-ar-cheque-list", component: KerisiArPageView, meta: { requiresAuth: true, title: "Cheque List" } },
    // Cheque Return
    { path: "/admin/kerisi/m/1045", name: "kerisi-ar-return-cheque", component: KerisiArPageView, meta: { requiresAuth: true, title: "List Of Return Cheque" } },
    // Offline Receipt
    { path: "/admin/kerisi/m/2183", name: "kerisi-ar-offline-application", component: KerisiArPageView, meta: { requiresAuth: true, title: "Offline Receipt Application" } },
    { path: "/admin/kerisi/m/2370", name: "kerisi-ar-offline-application-form", component: KerisiArPageView, meta: { requiresAuth: true, title: "Offline Receipt Application Form" } },
    { path: "/admin/kerisi/m/2108", name: "kerisi-ar-offline-collection-entry", component: KerisiArPageView, meta: { requiresAuth: true, title: "Receipt Collection Entry" } },
    { path: "/admin/kerisi/m/2500", name: "kerisi-ar-offline-counter", component: KerisiArPageView, meta: { requiresAuth: true, title: "Counter" } },
    // Setup
    { path: "/admin/kerisi/m/1787", name: "kerisi-ar-discount-policy", component: KerisiArPageView, meta: { requiresAuth: true, title: "Discount Policy" } },
    { path: "/admin/kerisi/m/1789", name: "kerisi-ar-discount-policy-details", component: KerisiArPageView, meta: { requiresAuth: true, title: "Discount Policy Details" } },
    { path: "/admin/kerisi/m/1936", name: "kerisi-ar-non-invoice-structure", component: KerisiArPageView, meta: { requiresAuth: true, title: "Non-Invoice Structure" } },
    { path: "/admin/kerisi/m/2117", name: "kerisi-ar-invoice-structure", component: KerisiArPageView, meta: { requiresAuth: true, title: "Invoice Structure" } },
    { path: "/admin/kerisi/m/2572", name: "kerisi-ar-signature-setup", component: KerisiArPageView, meta: { requiresAuth: true, title: "Signature Setup" } },
    { path: "/admin/kerisi/m/3507", name: "kerisi-ar-premise-details", component: KerisiArPageView, meta: { requiresAuth: true, title: "Premise Details" } },
    // ─────────────────────────────────────────────────────────────────────────────

    // ── Payroll pages — PAGE_MENUID1122_LEVEL3.json (all 67) ─────────────────
    { path: "/admin/kerisi/m/1260", name: "kerisi-payroll-1260", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Payroll Process" } },
    { path: "/admin/kerisi/m/1325", name: "kerisi-payroll-1325", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "List Of Staff" } },
    { path: "/admin/kerisi/m/1425", name: "kerisi-payroll-1425", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Vouchers" } },
    { path: "/admin/kerisi/m/1440", name: "kerisi-payroll-1440", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Monthly Setup Salary" } },
    { path: "/admin/kerisi/m/1441", name: "kerisi-payroll-1441", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Employer Account Info" } },
    { path: "/admin/kerisi/m/1442", name: "kerisi-payroll-1442", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Income Type" } },
    { path: "/admin/kerisi/m/1443", name: "kerisi-payroll-1443", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Tax Child Relief" } },
    { path: "/admin/kerisi/m/1462", name: "kerisi-payroll-1462", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Status Staff" } },
    { path: "/admin/kerisi/m/1463", name: "kerisi-payroll-1463", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Job Status" } },
    { path: "/admin/kerisi/m/1464", name: "kerisi-payroll-1464", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Title" } },
    { path: "/admin/kerisi/m/1467", name: "kerisi-payroll-1467", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Religion" } },
    { path: "/admin/kerisi/m/1468", name: "kerisi-payroll-1468", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Race" } },
    { path: "/admin/kerisi/m/1469", name: "kerisi-payroll-1469", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "State" } },
    { path: "/admin/kerisi/m/1474", name: "kerisi-payroll-1474", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Service Scheme" } },
    { path: "/admin/kerisi/m/1476", name: "kerisi-payroll-1476", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Penyata Gaji Induk" } },
    { path: "/admin/kerisi/m/1480", name: "kerisi-payroll-1480", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Penyata Saraan dan Potongan" } },
    { path: "/admin/kerisi/m/1836", name: "kerisi-payroll-1836", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Listing of Staff" } },
    { path: "/admin/kerisi/m/1845", name: "kerisi-payroll-1845", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Allowance & Deduction Bulks" } },
    { path: "/admin/kerisi/m/1850", name: "kerisi-payroll-1850", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "List Of Allowance And Deduction" } },
    { path: "/admin/kerisi/m/1888", name: "kerisi-payroll-1888", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Jumlah Gaji Ke Bank" } },
    { path: "/admin/kerisi/m/1889", name: "kerisi-payroll-1889", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Variance Allowance And Deduction" } },
    { path: "/admin/kerisi/m/1891", name: "kerisi-payroll-1891", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Individual Allowance & Deduction" } },
    { path: "/admin/kerisi/m/1893", name: "kerisi-payroll-1893", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Laporan Pelarasan Pendapatan" } },
    { path: "/admin/kerisi/m/1894", name: "kerisi-payroll-1894", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "List Of Payment Receiver" } },
    { path: "/admin/kerisi/m/1917", name: "kerisi-payroll-1917", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Payslip" } },
    { path: "/admin/kerisi/m/1927", name: "kerisi-payroll-1927", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Payroll Journal" } },
    { path: "/admin/kerisi/m/1978", name: "kerisi-payroll-1978", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Data Change Checklist" } },
    { path: "/admin/kerisi/m/1995", name: "kerisi-payroll-1995", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Checklist Of Data Transfer" } },
    { path: "/admin/kerisi/m/2027", name: "kerisi-payroll-2027", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Salary Generation Verification" } },
    { path: "/admin/kerisi/m/2102", name: "kerisi-payroll-2102", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "GCR Listing" } },
    { path: "/admin/kerisi/m/2112", name: "kerisi-payroll-2112", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "EC Process" } },
    { path: "/admin/kerisi/m/2308", name: "kerisi-payroll-2308", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "EC Account Code" } },
    { path: "/admin/kerisi/m/2313", name: "kerisi-payroll-2313", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "EC Remuneration Staff" } },
    { path: "/admin/kerisi/m/2438", name: "kerisi-payroll-2438", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Other Deduction" } },
    { path: "/admin/kerisi/m/2526", name: "kerisi-payroll-2526", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "EC Process (Bills)" } },
    { path: "/admin/kerisi/m/2540", name: "kerisi-payroll-2540", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Other Deduction (Admin)" } },
    { path: "/admin/kerisi/m/2549", name: "kerisi-payroll-2549", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Journal Log" } },
    { path: "/admin/kerisi/m/2674", name: "kerisi-payroll-2674", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Tax Rate Calculator" } },
    { path: "/admin/kerisi/m/2675", name: "kerisi-payroll-2675", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Variance By Income Code" } },
    { path: "/admin/kerisi/m/2681", name: "kerisi-payroll-2681", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Variance By Type" } },
    { path: "/admin/kerisi/m/2698", name: "kerisi-payroll-2698", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Allowance and Deduction" } },
    { path: "/admin/kerisi/m/2735", name: "kerisi-payroll-2735", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Emolument" } },
    { path: "/admin/kerisi/m/2749", name: "kerisi-payroll-2749", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Monthly Loan" } },
    { path: "/admin/kerisi/m/2759", name: "kerisi-payroll-2759", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Process By Bill" } },
    { path: "/admin/kerisi/m/2773", name: "kerisi-payroll-2773", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Emergency Fund" } },
    { path: "/admin/kerisi/m/2835", name: "kerisi-payroll-2835", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Deduction List Per Month" } },
    { path: "/admin/kerisi/m/2913", name: "kerisi-payroll-2913", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Income Type List" } },
    { path: "/admin/kerisi/m/2926", name: "kerisi-payroll-2926", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Laporan Keberhutangan Staff" } },
    { path: "/admin/kerisi/m/2940", name: "kerisi-payroll-2940", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Income Code by Invoice Type" } },
    { path: "/admin/kerisi/m/2948", name: "kerisi-payroll-2948", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Activity Mapping" } },
    { path: "/admin/kerisi/m/2962", name: "kerisi-payroll-2962", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Monthly Invoice" } },
    { path: "/admin/kerisi/m/3020", name: "kerisi-payroll-3020", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Change EPF Contribution" } },
    { path: "/admin/kerisi/m/3032", name: "kerisi-payroll-3032", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Delete By Bulk" } },
    { path: "/admin/kerisi/m/3034", name: "kerisi-payroll-3034", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "PCB Calculator LHDN" } },
    { path: "/admin/kerisi/m/3222", name: "kerisi-payroll-3222", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Deduction Code by Account Code" } },
    { path: "/admin/kerisi/m/3309", name: "kerisi-payroll-3309", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Loan Deferred Payment" } },
    { path: "/admin/kerisi/m/3310", name: "kerisi-payroll-3310", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Job Type" } },
    { path: "/admin/kerisi/m/3328", name: "kerisi-payroll-3328", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Staff Prefix" } },
    { path: "/admin/kerisi/m/3329", name: "kerisi-payroll-3329", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Salary Grade" } },
    { path: "/admin/kerisi/m/3335", name: "kerisi-payroll-3335", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Senarai Tuntutan Elaun Lebih Masa" } },
    { path: "/admin/kerisi/m/3336", name: "kerisi-payroll-3336", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Laporan Kod Elaun / Potongan" } },
    { path: "/admin/kerisi/m/3337", name: "kerisi-payroll-3337", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Perjawatan" } },
    { path: "/admin/kerisi/m/3339", name: "kerisi-payroll-3339", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Bank Negara Malaysia (BNM)" } },
    { path: "/admin/kerisi/m/3347", name: "kerisi-payroll-3347", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Loan Status Complete" } },
    { path: "/admin/kerisi/m/3440", name: "kerisi-payroll-3440", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Kew.8" } },
    { path: "/admin/kerisi/m/3449", name: "kerisi-payroll-3449", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "List of Kew 8 Form" } },
    { path: "/admin/kerisi/m/3465", name: "kerisi-payroll-3465", component: KerisiPayrollPageView, meta: { requiresAuth: true, title: "Bonus Generation" } },
    // ── end Payroll pages ─────────────────────────────────────────────────────

    // ── Remaining FIMS pages — all 14 JSON files (271 menus) ────────────────
    { path: "/admin/kerisi/m/1157", name: "kerisi-rem-1157", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Petty Cash" } },
    { path: "/admin/kerisi/m/1175", name: "kerisi-rem-1175", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Emergency Fund" } },
    { path: "/admin/kerisi/m/1187", name: "kerisi-rem-1187", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Disbursement" } },
    { path: "/admin/kerisi/m/1194", name: "kerisi-rem-1194", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Work Order Instruction" } },
    { path: "/admin/kerisi/m/1196", name: "kerisi-rem-1196", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of OT Claim" } },
    { path: "/admin/kerisi/m/1220", name: "kerisi-rem-1220", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Work Order Instruction" } },
    { path: "/admin/kerisi/m/1249", name: "kerisi-rem-1249", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Notification OT Claim" } },
    { path: "/admin/kerisi/m/1408", name: "kerisi-rem-1408", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Institution" } },
    { path: "/admin/kerisi/m/1415", name: "kerisi-rem-1415", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Testing Surat" } },
    { path: "/admin/kerisi/m/1417", name: "kerisi-rem-1417", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bank" } },
    { path: "/admin/kerisi/m/1418", name: "kerisi-rem-1418", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Investment Type & GL Code" } },
    { path: "/admin/kerisi/m/1431", name: "kerisi-rem-1431", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Tenure" } },
    { path: "/admin/kerisi/m/1465", name: "kerisi-rem-1465", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Generate Letter for Withdraw" } },
    { path: "/admin/kerisi/m/1472", name: "kerisi-rem-1472", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Apply for Withdrawal" } },
    { path: "/admin/kerisi/m/1487", name: "kerisi-rem-1487", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan FD Summary" } },
    { path: "/admin/kerisi/m/1488", name: "kerisi-rem-1488", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan FD Details" } },
    { path: "/admin/kerisi/m/1493", name: "kerisi-rem-1493", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Withdrawal - Rejected" } },
    { path: "/admin/kerisi/m/1499", name: "kerisi-rem-1499", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Investments Withdrawal" } },
    { path: "/admin/kerisi/m/1514", name: "kerisi-rem-1514", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Reversal" } },
    { path: "/admin/kerisi/m/1517", name: "kerisi-rem-1517", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Monthly Schedule Investment" } },
    { path: "/admin/kerisi/m/1528", name: "kerisi-rem-1528", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Petty Cash Personnel" } },
    { path: "/admin/kerisi/m/1585", name: "kerisi-rem-1585", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Profile" } },
    { path: "/admin/kerisi/m/1591", name: "kerisi-rem-1591", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Disburse" } },
    { path: "/admin/kerisi/m/1592", name: "kerisi-rem-1592", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Generate Schedule" } },
    { path: "/admin/kerisi/m/1593", name: "kerisi-rem-1593", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Accrual Monthly" } },
    { path: "/admin/kerisi/m/1619", name: "kerisi-rem-1619", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Delete Schedule" } },
    { path: "/admin/kerisi/m/1622", name: "kerisi-rem-1622", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Depreciation Process" } },
    { path: "/admin/kerisi/m/1628", name: "kerisi-rem-1628", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Depreciation Accrual" } },
    { path: "/admin/kerisi/m/1639", name: "kerisi-rem-1639", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Information" } },
    { path: "/admin/kerisi/m/1647", name: "kerisi-rem-1647", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Disposal Application" } },
    { path: "/admin/kerisi/m/1660", name: "kerisi-rem-1660", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Bank Guarantee" } },
    { path: "/admin/kerisi/m/1663", name: "kerisi-rem-1663", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Execution & Enforcement" } },
    { path: "/admin/kerisi/m/1665", name: "kerisi-rem-1665", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bucket & Ageing" } },
    { path: "/admin/kerisi/m/1666", name: "kerisi-rem-1666", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Work Order" } },
    { path: "/admin/kerisi/m/1667", name: "kerisi-rem-1667", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Overtime Claim Application" } },
    { path: "/admin/kerisi/m/1674", name: "kerisi-rem-1674", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Disposal Listing" } },
    { path: "/admin/kerisi/m/1683", name: "kerisi-rem-1683", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "assetDepreciationListing" } },
    { path: "/admin/kerisi/m/1716", name: "kerisi-rem-1716", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bill Registration" } },
    { path: "/admin/kerisi/m/1733", name: "kerisi-rem-1733", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Profile" } },
    { path: "/admin/kerisi/m/1737", name: "kerisi-rem-1737", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Financial Staus" } },
    {
      path: "/admin/kerisi/m/1771",
      name: "kerisi-rem-1771",
      component: PurchasingPurchaseRequisitionView,
      meta: { requiresAuth: true, title: "New Purchase Requisition" },
    },
    { path: "/admin/kerisi/m/1773", name: "kerisi-rem-1773", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Purchase Requisition List" } },
    {
      path: "/admin/kerisi/m/1820",
      name: "kerisi-rem-1820",
      component: PurchasingItemMainView,
      meta: { requiresAuth: true, title: "Item Main" },
    },
    { path: "/admin/kerisi/m/1823", name: "kerisi-rem-1823", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Voucher Registration" } },
    { path: "/admin/kerisi/m/1828", name: "kerisi-rem-1828", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List Of Vendor" } },
    { path: "/admin/kerisi/m/1829", name: "kerisi-rem-1829", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Item Main Listing" } },
    { path: "/admin/kerisi/m/1833", name: "kerisi-rem-1833", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Purchase Order List" } },
    { path: "/admin/kerisi/m/1834", name: "kerisi-rem-1834", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Account bank Information / Update Account Bank" } },
    { path: "/admin/kerisi/m/1835", name: "kerisi-rem-1835", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "GRN" } },
    { path: "/admin/kerisi/m/1838", name: "kerisi-rem-1838", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Work Progress Note" } },
    { path: "/admin/kerisi/m/1839", name: "kerisi-rem-1839", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Good Receive Note List" } },
    { path: "/admin/kerisi/m/1840", name: "kerisi-rem-1840", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Work Progress Note List" } },
    { path: "/admin/kerisi/m/1843", name: "kerisi-rem-1843", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Credit Note" } },
    { path: "/admin/kerisi/m/1847", name: "kerisi-rem-1847", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Payment History" } },
    { path: "/admin/kerisi/m/1857", name: "kerisi-rem-1857", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Discount Note" } },
    { path: "/admin/kerisi/m/1858", name: "kerisi-rem-1858", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Sponsor" } },
    { path: "/admin/kerisi/m/1859", name: "kerisi-rem-1859", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Receipt" } },
    { path: "/admin/kerisi/m/1860", name: "kerisi-rem-1860", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Payment Information" } },
    { path: "/admin/kerisi/m/1871", name: "kerisi-rem-1871", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List Of Bank Statement" } },
    { path: "/admin/kerisi/m/1875", name: "kerisi-rem-1875", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Debit Note" } },
    { path: "/admin/kerisi/m/1876", name: "kerisi-rem-1876", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Voucher Information" } },
    { path: "/admin/kerisi/m/1881", name: "kerisi-rem-1881", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Of Petty Cash Recoup" } },
    { path: "/admin/kerisi/m/1882", name: "kerisi-rem-1882", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "GL Opening" } },
    { path: "/admin/kerisi/m/1892", name: "kerisi-rem-1892", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Advance Payment / Deposit" } },
    { path: "/admin/kerisi/m/1897", name: "kerisi-rem-1897", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Other Payment V2" } },
    { path: "/admin/kerisi/m/1928", name: "kerisi-rem-1928", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Refund" } },
    { path: "/admin/kerisi/m/1932", name: "kerisi-rem-1932", component: PurchasingJobscopeListView, meta: { requiresAuth: true, title: "List Of Jobscope" } },
    { path: "/admin/kerisi/m/1939", name: "kerisi-rem-1939", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bendahari" } },
    { path: "/admin/kerisi/m/1941", name: "kerisi-rem-1941", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "PTJ" } },
    { path: "/admin/kerisi/m/1946", name: "kerisi-rem-1946", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Journal" } },
    { path: "/admin/kerisi/m/1955", name: "kerisi-rem-1955", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bank Account No For Updated" } },
    { path: "/admin/kerisi/m/1957", name: "kerisi-rem-1957", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Staff Overtime Hierarchy" } },
    { path: "/admin/kerisi/m/1959", name: "kerisi-rem-1959", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Approval (GRN)" } },
    { path: "/admin/kerisi/m/1971", name: "kerisi-rem-1971", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Accrual" } },
    { path: "/admin/kerisi/m/1981", name: "kerisi-rem-1981", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Collected Hours" } },
    { path: "/admin/kerisi/m/1985", name: "kerisi-rem-1985", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New Application" } },
    { path: "/admin/kerisi/m/1986", name: "kerisi-rem-1986", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Payee List Report" } },
    { path: "/admin/kerisi/m/1993", name: "kerisi-rem-1993", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Emergency Fund" } },
    { path: "/admin/kerisi/m/2017", name: "kerisi-rem-2017", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Application (Special Case)" } },
    { path: "/admin/kerisi/m/2030", name: "kerisi-rem-2030", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New PO Cancellation" } },
    { path: "/admin/kerisi/m/2039", name: "kerisi-rem-2039", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Purchase Order Cancellation" } },
    { path: "/admin/kerisi/m/2041", name: "kerisi-rem-2041", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Cancel PO Partial Listing" } },
    { path: "/admin/kerisi/m/2042", name: "kerisi-rem-2042", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New Cancel PO Partial" } },
    { path: "/admin/kerisi/m/2046", name: "kerisi-rem-2046", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Refund Application" } },
    { path: "/admin/kerisi/m/2053", name: "kerisi-rem-2053", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Refund Application" } },
    { path: "/admin/kerisi/m/2059", name: "kerisi-rem-2059", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Refund Application" } },
    { path: "/admin/kerisi/m/2060", name: "kerisi-rem-2060", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Refund Application" } },
    { path: "/admin/kerisi/m/2066", name: "kerisi-rem-2066", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Assessment Question" } },
    { path: "/admin/kerisi/m/2067", name: "kerisi-rem-2067", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Regenerate Schedule" } },
    { path: "/admin/kerisi/m/2081", name: "kerisi-rem-2081", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Reverse Journal Listing" } },
    { path: "/admin/kerisi/m/2082", name: "kerisi-rem-2082", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Work Progress Note Cancel List" } },
    { path: "/admin/kerisi/m/2085", name: "kerisi-rem-2085", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Good Receive Note Cancel" } },
    { path: "/admin/kerisi/m/2094", name: "kerisi-rem-2094", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List Of Bank Statement" } },
    { path: "/admin/kerisi/m/2099", name: "kerisi-rem-2099", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Listing Of Bank Statement" } },
    { path: "/admin/kerisi/m/2100", name: "kerisi-rem-2100", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Listing Of Bank Statement" } },
    { path: "/admin/kerisi/m/2107", name: "kerisi-rem-2107", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bill Registration Cancellation @ Knockoff" } },
    { path: "/admin/kerisi/m/2110", name: "kerisi-rem-2110", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Budget" } },
    { path: "/admin/kerisi/m/2115", name: "kerisi-rem-2115", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Budget" } },
    { path: "/admin/kerisi/m/2139", name: "kerisi-rem-2139", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Vehicle Information" } },
    { path: "/admin/kerisi/m/2151", name: "kerisi-rem-2151", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bill" } },
    { path: "/admin/kerisi/m/2158", name: "kerisi-rem-2158", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Application" } },
    { path: "/admin/kerisi/m/2159", name: "kerisi-rem-2159", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Listing Application" } },
    { path: "/admin/kerisi/m/2166", name: "kerisi-rem-2166", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bilangan Hari Daftar Bil Belum Bayar" } },
    { path: "/admin/kerisi/m/2198", name: "kerisi-rem-2198", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Baucar Bayaran" } },
    { path: "/admin/kerisi/m/2221", name: "kerisi-rem-2221", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Unidentified Receipt" } },
    { path: "/admin/kerisi/m/2225", name: "kerisi-rem-2225", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Manual Journal Entry" } },
    { path: "/admin/kerisi/m/2263", name: "kerisi-rem-2263", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Attachment 3" } },
    { path: "/admin/kerisi/m/2264", name: "kerisi-rem-2264", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Attachment 2" } },
    { path: "/admin/kerisi/m/2269", name: "kerisi-rem-2269", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Attachment 1 - Baucar Yang Belum Didebitkan oleh Bank" } },
    { path: "/admin/kerisi/m/2279", name: "kerisi-rem-2279", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Receipt" } },
    { path: "/admin/kerisi/m/2280", name: "kerisi-rem-2280", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Entry Form Unidentified Receipt" } },
    { path: "/admin/kerisi/m/2281", name: "kerisi-rem-2281", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Matching Report Transaction" } },
    { path: "/admin/kerisi/m/2282", name: "kerisi-rem-2282", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Credit Note" } },
    { path: "/admin/kerisi/m/2283", name: "kerisi-rem-2283", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Debit Note" } },
    { path: "/admin/kerisi/m/2297", name: "kerisi-rem-2297", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Voucher Listing" } },
    { path: "/admin/kerisi/m/2298", name: "kerisi-rem-2298", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Voucher Cancel" } },
    { path: "/admin/kerisi/m/2320", name: "kerisi-rem-2320", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Purchase Requisition Cancellation" } },
    { path: "/admin/kerisi/m/2331", name: "kerisi-rem-2331", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Penyata Penyesuaian Bank" } },
    { path: "/admin/kerisi/m/2333", name: "kerisi-rem-2333", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Committe & Participant" } },
    { path: "/admin/kerisi/m/2336", name: "kerisi-rem-2336", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Ageing" } },
    { path: "/admin/kerisi/m/2337", name: "kerisi-rem-2337", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Voucher Replace" } },
    { path: "/admin/kerisi/m/2355", name: "kerisi-rem-2355", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bill Cancel @ Knockoff Listing" } },
    { path: "/admin/kerisi/m/2361", name: "kerisi-rem-2361", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Cancel Partial PR" } },
    { path: "/admin/kerisi/m/2364", name: "kerisi-rem-2364", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Statistik Reminder" } },
    { path: "/admin/kerisi/m/2366", name: "kerisi-rem-2366", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Statement of Account" } },
    { path: "/admin/kerisi/m/2397", name: "kerisi-rem-2397", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Disposal Application Listing" } },
    { path: "/admin/kerisi/m/2409", name: "kerisi-rem-2409", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Journal Bill Cancel @ Knockoff" } },
    { path: "/admin/kerisi/m/2411", name: "kerisi-rem-2411", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report of Identified Receipts" } },
    { path: "/admin/kerisi/m/2412", name: "kerisi-rem-2412", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Journal Voucher Cancel Listing" } },
    { path: "/admin/kerisi/m/2417", name: "kerisi-rem-2417", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Bancar Panjar Wang Runcit" } },
    { path: "/admin/kerisi/m/2431", name: "kerisi-rem-2431", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Aset Mengikut Jabatan Dan Lokasi" } },
    { path: "/admin/kerisi/m/2479", name: "kerisi-rem-2479", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Cashbook Closing" } },
    { path: "/admin/kerisi/m/2495", name: "kerisi-rem-2495", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Disposal Registration" } },
    { path: "/admin/kerisi/m/2502", name: "kerisi-rem-2502", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Bancar Panjar Wang Runcit" } },
    { path: "/admin/kerisi/m/2533", name: "kerisi-rem-2533", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Closing - Attachment 1" } },
    { path: "/admin/kerisi/m/2534", name: "kerisi-rem-2534", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Closing - Attachment 2" } },
    { path: "/admin/kerisi/m/2535", name: "kerisi-rem-2535", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Closing - Attachment 3" } },
    { path: "/admin/kerisi/m/2542", name: "kerisi-rem-2542", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Master File" } },
    { path: "/admin/kerisi/m/2543", name: "kerisi-rem-2543", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Transfer Listing" } },
    { path: "/admin/kerisi/m/2544", name: "kerisi-rem-2544", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Transfer (Outside PTJ)" } },
    { path: "/admin/kerisi/m/2553", name: "kerisi-rem-2553", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Cashbook (Monthly)" } },
    { path: "/admin/kerisi/m/2593", name: "kerisi-rem-2593", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "TP1 Application" } },
    { path: "/admin/kerisi/m/2594", name: "kerisi-rem-2594", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Application" } },
    { path: "/admin/kerisi/m/2618", name: "kerisi-rem-2618", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Advertisement Request" } },
    { path: "/admin/kerisi/m/2624", name: "kerisi-rem-2624", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Good Receive Note" } },
    { path: "/admin/kerisi/m/2626", name: "kerisi-rem-2626", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Work Progress Note" } },
    { path: "/admin/kerisi/m/2627", name: "kerisi-rem-2627", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Advance Monitoring" } },
    { path: "/admin/kerisi/m/2633", name: "kerisi-rem-2633", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Payment Notice" } },
    { path: "/admin/kerisi/m/2642", name: "kerisi-rem-2642", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Detail Ageing" } },
    { path: "/admin/kerisi/m/2663", name: "kerisi-rem-2663", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Vendor" } },
    { path: "/admin/kerisi/m/2664", name: "kerisi-rem-2664", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Daily Summary" } },
    { path: "/admin/kerisi/m/2665", name: "kerisi-rem-2665", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Monthly Summary" } },
    { path: "/admin/kerisi/m/2667", name: "kerisi-rem-2667", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Subsidiary Statement" } },
    { path: "/admin/kerisi/m/2670", name: "kerisi-rem-2670", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Cashbook Balance By Fund" } },
    { path: "/admin/kerisi/m/2673", name: "kerisi-rem-2673", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Allocation Receive Log" } },
    { path: "/admin/kerisi/m/2706", name: "kerisi-rem-2706", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Print SAB By Batch" } },
    { path: "/admin/kerisi/m/2708", name: "kerisi-rem-2708", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Emergency Fund (Paid)" } },
    { path: "/admin/kerisi/m/2709", name: "kerisi-rem-2709", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Emergency Fund (Unblock)" } },
    { path: "/admin/kerisi/m/2717", name: "kerisi-rem-2717", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Listing" } },
    { path: "/admin/kerisi/m/2721", name: "kerisi-rem-2721", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Advertisement Request List" } },
    { path: "/admin/kerisi/m/2724", name: "kerisi-rem-2724", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Selection" } },
    { path: "/admin/kerisi/m/2736", name: "kerisi-rem-2736", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Received Recoup Amount" } },
    { path: "/admin/kerisi/m/2752", name: "kerisi-rem-2752", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of All Application" } },
    { path: "/admin/kerisi/m/2754", name: "kerisi-rem-2754", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Emergency Fund Cashbook Report" } },
    { path: "/admin/kerisi/m/2762", name: "kerisi-rem-2762", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Advertisement Complete" } },
    { path: "/admin/kerisi/m/2765", name: "kerisi-rem-2765", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Monthly Loan" } },
    { path: "/admin/kerisi/m/2766", name: "kerisi-rem-2766", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Listing of Payee" } },
    { path: "/admin/kerisi/m/2768", name: "kerisi-rem-2768", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Emergency Fund (Verify)" } },
    { path: "/admin/kerisi/m/2774", name: "kerisi-rem-2774", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Movement Report" } },
    { path: "/admin/kerisi/m/2817", name: "kerisi-rem-2817", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Download Voucher Supplier By Batch" } },
    { path: "/admin/kerisi/m/2818", name: "kerisi-rem-2818", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Change Status to Complete" } },
    { path: "/admin/kerisi/m/2819", name: "kerisi-rem-2819", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Stop Accrual" } },
    { path: "/admin/kerisi/m/2827", name: "kerisi-rem-2827", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Tender/Quotation Cancellation" } },
    { path: "/admin/kerisi/m/2828", name: "kerisi-rem-2828", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Advertisement Time" } },
    { path: "/admin/kerisi/m/2833", name: "kerisi-rem-2833", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Roster Schedule" } },
    { path: "/admin/kerisi/m/2842", name: "kerisi-rem-2842", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Shift Day Off" } },
    { path: "/admin/kerisi/m/2845", name: "kerisi-rem-2845", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Generated Offer Letter" } },
    { path: "/admin/kerisi/m/2846", name: "kerisi-rem-2846", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of VO" } },
    { path: "/admin/kerisi/m/2847", name: "kerisi-rem-2847", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Senarai Semak Pembatalan Pendaftaran Aset" } },
    { path: "/admin/kerisi/m/2848", name: "kerisi-rem-2848", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New Variation Order (VO)" } },
    { path: "/admin/kerisi/m/2868", name: "kerisi-rem-2868", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New Application" } },
    { path: "/admin/kerisi/m/2878", name: "kerisi-rem-2878", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Send Email" } },
    { path: "/admin/kerisi/m/2879", name: "kerisi-rem-2879", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Historical Sender" } },
    { path: "/admin/kerisi/m/2895", name: "kerisi-rem-2895", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Bill Report" } },
    { path: "/admin/kerisi/m/2906", name: "kerisi-rem-2906", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Senarai Aset Alih Tidak Dilulus Untuk Dilupuskan" } },
    { path: "/admin/kerisi/m/2915", name: "kerisi-rem-2915", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Application" } },
    { path: "/admin/kerisi/m/2934", name: "kerisi-rem-2934", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Summary Report" } },
    { path: "/admin/kerisi/m/2949", name: "kerisi-rem-2949", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List Assign" } },
    { path: "/admin/kerisi/m/2951", name: "kerisi-rem-2951", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Completed List" } },
    { path: "/admin/kerisi/m/2963", name: "kerisi-rem-2963", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Project Monitoring" } },
    { path: "/admin/kerisi/m/2974", name: "kerisi-rem-2974", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Transaction History" } },
    { path: "/admin/kerisi/m/3009", name: "kerisi-rem-3009", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Penyata Penyesuaian Bank" } },
    { path: "/admin/kerisi/m/3015", name: "kerisi-rem-3015", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New Application" } },
    { path: "/admin/kerisi/m/3026", name: "kerisi-rem-3026", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Yearly Closing Process" } },
    { path: "/admin/kerisi/m/3038", name: "kerisi-rem-3038", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of PR To Be Cancel" } },
    {
      path: "/admin/kerisi/m/3039",
      name: "kerisi-rem-3039",
      component: () => import("@/views/PurchasingPrCancelView.vue"),
      meta: { requiresAuth: true, title: "Purchase Requisition Cancel" },
    },
    { path: "/admin/kerisi/m/3041", name: "kerisi-rem-3041", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of PR To Be Cancel Partial" } },
    { path: "/admin/kerisi/m/3042", name: "kerisi-rem-3042", component: () => import("@/views/PurchasingPrCancelPartialView.vue"), meta: { requiresAuth: true, title: "Purchase Requisition Cancel Partial" } },
    { path: "/admin/kerisi/m/3060", name: "kerisi-rem-3060", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Advance Payment" } },
    { path: "/admin/kerisi/m/3064", name: "kerisi-rem-3064", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Auditor Asset Listing" } },
    { path: "/admin/kerisi/m/3069", name: "kerisi-rem-3069", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Deposit" } },
    { path: "/admin/kerisi/m/3077", name: "kerisi-rem-3077", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Auditor WIP Report" } },
    { path: "/admin/kerisi/m/3086", name: "kerisi-rem-3086", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Journal Adjustment Listing" } },
    { path: "/admin/kerisi/m/3087", name: "kerisi-rem-3087", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Journal Adjustment Form" } },
    { path: "/admin/kerisi/m/3100", name: "kerisi-rem-3100", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Vendor Assessment" } },
    { path: "/admin/kerisi/m/3106", name: "kerisi-rem-3106", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Vendor Assessment (WPN)" } },
    { path: "/admin/kerisi/m/3133", name: "kerisi-rem-3133", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Payee List Report by PTJ" } },
    { path: "/admin/kerisi/m/3188", name: "kerisi-rem-3188", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Investment Accrual (Posting)" } },
    { path: "/admin/kerisi/m/3191", name: "kerisi-rem-3191", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Accrual (No Posting Number)" } },
    { path: "/admin/kerisi/m/3226", name: "kerisi-rem-3226", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New Applicaton" } },
    { path: "/admin/kerisi/m/3231", name: "kerisi-rem-3231", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of New Application - Draft" } },
    { path: "/admin/kerisi/m/3232", name: "kerisi-rem-3232", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of New Application - Rejected" } },
    { path: "/admin/kerisi/m/3242", name: "kerisi-rem-3242", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Credit Note Form" } },
    { path: "/admin/kerisi/m/3243", name: "kerisi-rem-3243", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Credit Note Listing" } },
    { path: "/admin/kerisi/m/3254", name: "kerisi-rem-3254", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Journal Revaluation Process" } },
    { path: "/admin/kerisi/m/3256", name: "kerisi-rem-3256", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Unmatched Matching Record" } },
    { path: "/admin/kerisi/m/3264", name: "kerisi-rem-3264", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Credit Note Cancellation" } },
    { path: "/admin/kerisi/m/3268", name: "kerisi-rem-3268", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Generate Instruction Letter / Cadangan Pelaburan" } },
    { path: "/admin/kerisi/m/3270", name: "kerisi-rem-3270", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Money Transfer" } },
    { path: "/admin/kerisi/m/3272", name: "kerisi-rem-3272", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Evaluation List" } },
    { path: "/admin/kerisi/m/3290", name: "kerisi-rem-3290", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Update Loan Payment" } },
    { path: "/admin/kerisi/m/3302", name: "kerisi-rem-3302", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Registration Approval (Bill/Journal)" } },
    { path: "/admin/kerisi/m/3306", name: "kerisi-rem-3306", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Committee Report" } },
    { path: "/admin/kerisi/m/3307", name: "kerisi-rem-3307", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Loan Change Status to Cancel" } },
    { path: "/admin/kerisi/m/3312", name: "kerisi-rem-3312", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Investments" } },
    { path: "/admin/kerisi/m/3313", name: "kerisi-rem-3313", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Journal Details" } },
    { path: "/admin/kerisi/m/3319", name: "kerisi-rem-3319", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Investment Report" } },
    { path: "/admin/kerisi/m/3320", name: "kerisi-rem-3320", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Agreement" } },
    { path: "/admin/kerisi/m/3323", name: "kerisi-rem-3323", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "New Variation Order (VO)" } },
    { path: "/admin/kerisi/m/3330", name: "kerisi-rem-3330", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Auditor Asset Report" } },
    { path: "/admin/kerisi/m/3345", name: "kerisi-rem-3345", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Other Payment (Update Payment Date)" } },
    { path: "/admin/kerisi/m/3346", name: "kerisi-rem-3346", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Generate Voucher Draft" } },
    { path: "/admin/kerisi/m/3348", name: "kerisi-rem-3348", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "List of Voucher Money Transfer" } },
    { path: "/admin/kerisi/m/3349", name: "kerisi-rem-3349", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Susut Nilai Aset" } },
    { path: "/admin/kerisi/m/3350", name: "kerisi-rem-3350", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Buku Tunai" } },
    { path: "/admin/kerisi/m/3372", name: "kerisi-rem-3372", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Buku Daftar Terimaan" } },
    { path: "/admin/kerisi/m/3374", name: "kerisi-rem-3374", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Pendaftaran Aset" } },
    { path: "/admin/kerisi/m/3385", name: "kerisi-rem-3385", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Deposit Statement" } },
    { path: "/admin/kerisi/m/3387", name: "kerisi-rem-3387", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Disposal Listing" } },
    { path: "/admin/kerisi/m/3400", name: "kerisi-rem-3400", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Debt Movement Report" } },
    { path: "/admin/kerisi/m/3414", name: "kerisi-rem-3414", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Setup Belanja Mengikut Segmen" } },
    { path: "/admin/kerisi/m/3426", name: "kerisi-rem-3426", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "" } },
    { path: "/admin/kerisi/m/3428", name: "kerisi-rem-3428", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Trial Balance Summary" } },
    { path: "/admin/kerisi/m/3430", name: "kerisi-rem-3430", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Details Transaction" } },
    { path: "/admin/kerisi/m/3433", name: "kerisi-rem-3433", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Deposit Statement (Management)" } },
    { path: "/admin/kerisi/m/3438", name: "kerisi-rem-3438", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Laporan Daftar Pelaburan" } },
    { path: "/admin/kerisi/m/3460", name: "kerisi-rem-3460", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "" } },
    { path: "/admin/kerisi/m/3461", name: "kerisi-rem-3461", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Direct Voucher" } },
    { path: "/admin/kerisi/m/3472", name: "kerisi-rem-3472", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Treatment Ledger by Year" } },
    { path: "/admin/kerisi/m/3473", name: "kerisi-rem-3473", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Notification Letter" } },
    { path: "/admin/kerisi/m/3474", name: "kerisi-rem-3474", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Overall Treatment Ledger by Staff" } },
    { path: "/admin/kerisi/m/3475", name: "kerisi-rem-3475", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Treatment Statement by Staff" } },
    { path: "/admin/kerisi/m/3487", name: "kerisi-rem-3487", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Combine Report" } },
    { path: "/admin/kerisi/m/3495", name: "kerisi-rem-3495", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Movement List" } },
    { path: "/admin/kerisi/m/3496", name: "kerisi-rem-3496", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Movement Application" } },
    { path: "/admin/kerisi/m/3504", name: "kerisi-rem-3504", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Asset Lost Losting" } },
    { path: "/admin/kerisi/m/3505", name: "kerisi-rem-3505", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Damage Asset Application" } },
    { path: "/admin/kerisi/m/3508", name: "kerisi-rem-3508", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Trial Balance" } },
    { path: "/admin/kerisi/m/3516", name: "kerisi-rem-3516", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "BS P&L" } },
    { path: "/admin/kerisi/m/3526", name: "kerisi-rem-3526", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Update Bank Account and Factoring for Bill" } },
    { path: "/admin/kerisi/m/3529", name: "kerisi-rem-3529", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Update Bank Account and Factoring for Voucher" } },
    { path: "/admin/kerisi/m/3534", name: "kerisi-rem-3534", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Download Voucher By Reference" } },
    { path: "/admin/kerisi/m/3535", name: "kerisi-rem-3535", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Voucher Process" } },
    { path: "/admin/kerisi/m/3538", name: "kerisi-rem-3538", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Payment Reject Batch" } },
    { path: "/admin/kerisi/m/3543", name: "kerisi-rem-3543", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Debit Note Form" } },
    { path: "/admin/kerisi/m/3546", name: "kerisi-rem-3546", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Voucher Information Creditor" } },
    { path: "/admin/kerisi/m/3548", name: "kerisi-rem-3548", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Debit Note Form" } },
    { path: "/admin/kerisi/m/3549", name: "kerisi-rem-3549", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Debit Note Cancellation" } },
    { path: "/admin/kerisi/m/3550", name: "kerisi-rem-3550", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Debit Note Cancellation Form" } },
    { path: "/admin/kerisi/m/3558", name: "kerisi-rem-3558", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Payment Record" } },
    { path: "/admin/kerisi/m/3582", name: "kerisi-rem-3582", component: KerisiRemainingPageView, meta: { requiresAuth: true, title: "Report Item Setup" } },
    // ── end Remaining FIMS pages ─────────────────────────────────────────────

    {
      path: "/admin/kerisi/m/:menuId",
      name: "kerisi-menu",
      component: KerisiMenuPlaceholderView,
      meta: { requiresAuth: true },
    },
    { path: "/admin/kitchen-sink", name: "kitchen-sink", component: KitchenSinkView, meta: { requiresAuth: true, title: "Kitchen Sink" } },
    { path: "/admin/kitchen-sink/forms", name: "kitchen-forms", component: KitchenFormsView, meta: { requiresAuth: true, title: "Forms" } },
    { path: "/admin/kitchen-sink/charts", name: "kitchen-charts", component: KitchenChartsView, meta: { requiresAuth: true, title: "Charts" } },
    {
      path: "/admin/kitchen-sink/patterns",
      name: "kitchen-patterns",
      component: KitchenSinkPatternsView,
      meta: { requiresAuth: true, title: "Kitchen Sink Patterns" },
    },
    { path: "/admin/development/developers-guide", name: "developers-guide", component: DevelopersGuideView, meta: { requiresAuth: true, title: "Developers Guide" } },
    { path: "/admin/development/database-schema", name: "database-schema", component: DatabaseSchemaView, meta: { requiresAuth: true, title: "Database Schema" } },
    { path: "/admin/development/api-explorer", name: "api-explorer", component: ApiManagementView, meta: { requiresAuth: true, title: "API Explorer" } },
    { path: "/admin/development/api-management", redirect: "/admin/development/api-explorer" },
    {
      path: "/admin/profile",
      name: "profile",
      meta: { requiresAuth: true },
      beforeEnter: async () => {
        const auth = useAuthStore();
        await auth.initialize();
        if (auth.user?.id) return `/admin/platform/identity/users/${auth.user.id}`;
        return { name: "login" };
      },
      component: { template: "" },
    },

    // ── Administration ──
    { path: "/admin/settings", name: "settings", component: SettingsView, meta: { requiresAuth: true, title: "Settings" } },
    { path: "/admin/settings/system", name: "settings-system", component: SystemInfoView, meta: { requiresAuth: true, title: "System Info" } },

    // ── Core Platform: Identity & Access ──
    { path: "/admin/platform/identity", redirect: "/admin/platform/identity/users" },
    { path: "/admin/platform/identity/users", name: "platform-users", component: UsersView, meta: { requiresAuth: true, title: "Users" } },
    { path: "/admin/platform/identity/users/new", name: "platform-user-create", component: UserEditView, meta: { requiresAuth: true, title: "New User" } },
    { path: "/admin/platform/identity/users/:id", name: "platform-user-edit", component: UserEditView, meta: { requiresAuth: true, title: "Edit User" } },
    { path: "/admin/platform/identity/roles", name: "platform-rbac", component: RolesView, meta: { requiresAuth: true, title: "RBAC" } },
    { path: "/admin/platform/identity/tokens", name: "platform-tokens", component: ComingSoonView, meta: { requiresAuth: true, title: "Token Management" } },

    // ── Core Platform: Observability (Grafana) ──
    { path: "/admin/platform/observability", redirect: "/admin/platform/observability/audit-trail" },
    { path: "/admin/platform/observability/audit-trail", name: "platform-audit-trail", component: AuditLogsView, meta: { requiresAuth: true, title: "Audit Trail" } },
    { path: "/admin/platform/observability/activity-log", name: "platform-activity-log", component: ComingSoonView, meta: { requiresAuth: true, title: "Activity Log" } },
    { path: "/admin/platform/observability/logging", name: "platform-logging", component: ComingSoonView, meta: { requiresAuth: true, title: "Logging" } },
    { path: "/admin/platform/observability/errors", name: "platform-error-tracking", component: ComingSoonView, meta: { requiresAuth: true, title: "Error Tracking" } },
    { path: "/admin/platform/observability/monitoring", name: "platform-monitoring", component: ComingSoonView, meta: { requiresAuth: true, title: "Monitoring" } },

    // ── Core Platform: Queue (Laravel Queue) ──
    { path: "/admin/platform/queue", name: "platform-queue", component: QueueMonitorView, meta: { requiresAuth: true, title: "Queue" } },
    { path: "/admin/platform/queue/failed", name: "platform-queue-failed", component: ComingSoonView, meta: { requiresAuth: true, title: "Failed Jobs" } },
    { path: "/admin/platform/queue/scheduled", name: "platform-queue-scheduled", component: ComingSoonView, meta: { requiresAuth: true, title: "Scheduled Jobs" } },

    // ── Core Platform: Messaging ──
    { path: "/admin/platform/messaging", redirect: "/admin/platform/messaging/event-bus" },
    { path: "/admin/platform/messaging/event-bus", name: "platform-event-bus", component: ComingSoonView, meta: { requiresAuth: true, title: "Event Bus" } },
    { path: "/admin/platform/messaging/notifications", name: "platform-notifications", component: ComingSoonView, meta: { requiresAuth: true, title: "Notifications" } },

    // ── Backward-compat redirects from old governance/communication paths ──
    { path: "/admin/platform/governance", redirect: "/admin/platform/observability/audit-trail" },
    { path: "/admin/platform/governance/audit-trail", redirect: "/admin/platform/observability/audit-trail" },
    { path: "/admin/platform/governance/activity-log", redirect: "/admin/platform/observability/activity-log" },
    { path: "/admin/platform/communication", redirect: "/admin/platform/messaging/notifications" },
    { path: "/admin/platform/communication/notifications", redirect: "/admin/platform/messaging/notifications" },
    { path: "/admin/platform/messaging/queue", redirect: "/admin/platform/queue" },
    { path: "/admin/platform/messaging/queue/failed", redirect: "/admin/platform/queue/failed" },
    { path: "/admin/platform/messaging/queue/scheduled", redirect: "/admin/platform/queue/scheduled" },

    // ── Core Platform: System Management ──
    { path: "/admin/platform/system", redirect: "/admin/platform/system/configuration" },
    { path: "/admin/platform/system/configuration", name: "platform-config", component: ComingSoonView, meta: { requiresAuth: true, title: "Configuration" } },
    { path: "/admin/platform/system/feature-flags", name: "platform-feature-flags", component: ComingSoonView, meta: { requiresAuth: true, title: "Feature Flags" } },
    { path: "/admin/platform/system/scheduler", name: "platform-scheduler", component: ComingSoonView, meta: { requiresAuth: true, title: "Scheduler" } },

    // ── Core Platform: Storage ──
    { path: "/admin/platform/storage", redirect: "/admin/platform/storage/media" },
    { path: "/admin/platform/storage/media", name: "platform-file-media", component: ComingSoonView, meta: { requiresAuth: true, title: "File / Media Management" } },

    // ── Core Platform: API Gateway (APISIX) ──
    { path: "/admin/platform/gateway", redirect: "/admin/platform/gateway/routes" },
    { path: "/admin/platform/gateway/routes", name: "platform-gateway-routes", component: ComingSoonView, meta: { requiresAuth: true, title: "Routes" } },
    { path: "/admin/platform/gateway/upstreams", name: "platform-gateway-upstreams", component: ComingSoonView, meta: { requiresAuth: true, title: "Upstreams" } },
    { path: "/admin/platform/gateway/consumers", name: "platform-gateway-consumers", component: ComingSoonView, meta: { requiresAuth: true, title: "Consumers" } },
    { path: "/admin/platform/gateway/plugins", name: "platform-gateway-plugins", component: ComingSoonView, meta: { requiresAuth: true, title: "Plugins" } },
    { path: "/admin/platform/gateway/ssl", name: "platform-gateway-ssl", component: ComingSoonView, meta: { requiresAuth: true, title: "SSL Certificates" } },
    { path: "/admin/platform/gateway/webhooks", name: "platform-webhooks", component: ComingSoonView, meta: { requiresAuth: true, title: "Webhooks" } },

    // ── Backward-compat redirects from old integration paths ──
    { path: "/admin/platform/integration", redirect: "/admin/platform/gateway/routes" },
    { path: "/admin/platform/integration/api", redirect: "/admin/platform/gateway/routes" },
    { path: "/admin/platform/integration/webhooks", redirect: "/admin/platform/gateway/webhooks" },

    // ── Core Platform: AI Integration ──
    { path: "/admin/platform/ai", redirect: "/admin/platform/ai/providers" },
    { path: "/admin/platform/ai/providers", name: "platform-ai-providers", component: ComingSoonView, meta: { requiresAuth: true, title: "AI Providers" } },
    { path: "/admin/platform/ai/models", name: "platform-ai-models", component: ComingSoonView, meta: { requiresAuth: true, title: "AI Models" } },
    { path: "/admin/platform/ai/prompts", name: "platform-ai-prompts", component: ComingSoonView, meta: { requiresAuth: true, title: "Prompt Templates" } },
    { path: "/admin/platform/ai/usage", name: "platform-ai-usage", component: ComingSoonView, meta: { requiresAuth: true, title: "AI Usage & Billing" } },

    // ── Backward-compat redirects from old settings paths ──
    ...settingsRedirects,

    ...legacyAdminPaths.map<RouteRecordRaw>((path) => ({
      path,
      redirect: (to: RouteLocationGeneric) => `/admin${to.fullPath}`,
    })),

    { path: "/", name: "storefront-home", component: StorefrontHomeView, meta: { title: "Webfront" } },
    { path: "/:slug", name: "storefront-page", component: StorefrontPageView, meta: { title: "Webfront" } },
  ],
});

router.beforeEach(async (to) => {
  const auth = useAuthStore();
  await auth.initialize();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: "login" };
  }

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: "main-dashboard" };
  }

  return true;
});

router.afterEach((to) => {
  if (to.name === "kerisi-menu") {
    return;
  }
  const site = useSiteStore();
  const pageTitle = (to.meta.title as string) || "Admin";
  site.setDocumentTitle(pageTitle);
});

export default router;
