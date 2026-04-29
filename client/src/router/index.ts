import { createRouter, createWebHistory } from "vue-router";
import type { RouteLocationGeneric, RouteRecordRaw } from "vue-router";

import { kerisiAccountPayableHiddenRoutes } from "@/router/kerisi-account-payable-hidden-routes";
import { kerisiAccountReceivableHiddenRoutes } from "@/router/kerisi-account-receivable-hidden-routes";
import { kerisiAssetHiddenRoutes } from "@/router/kerisi-asset-hidden-routes";
import { kerisiCreditControlHiddenRoutes } from "@/router/kerisi-credit-control-hidden-routes";
import { kerisiGeneralLedgerExtraHiddenRoutes } from "@/router/kerisi-general-ledger-extra-hidden-routes";
import { kerisiPayrollHiddenRoutes } from "@/router/kerisi-payroll-hidden-routes";
import { kerisiPortalHiddenRoutes } from "@/router/kerisi-portal-hidden-routes";
import { kerisiPurchasingHiddenRoutes } from "@/router/kerisi-purchasing-hidden-routes";
import { kerisiSetupMaintenanceHiddenRoutes } from "@/router/kerisi-setup-maintenance-hidden-routes";
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
import FloatingPointProfileSetupView from "@/views/FloatingPointProfileSetupView.vue";
import ProjectProfileSoCodeSetupView from "@/views/ProjectProfileSoCodeSetupView.vue";
import AccountCodePpiView from "@/views/AccountCodePpiView.vue";
import BudgetMovementView from "@/views/BudgetMovementView.vue";
import BudgetMovementFormView from "@/views/BudgetMovementFormView.vue";
import BudgetMonitoringView from "@/views/BudgetMonitoringView.vue";
import BudgetAdvanceControlledView from "@/views/BudgetAdvanceControlledView.vue";
import BudgetAdvanceControlledDetailView from "@/views/BudgetAdvanceControlledDetailView.vue";
import BudgetListingView from "@/views/BudgetListingView.vue";
import BudgetInitialView from "@/views/BudgetInitialView.vue";
import BudgetInitialNewV2View from "@/views/BudgetInitialNewV2View.vue";
import BudgetClosingView from "@/views/BudgetClosingView.vue";
import AllocationView from "@/views/AllocationView.vue";
import BudgetCodeView from "@/views/BudgetCodeView.vue";
import BudgetPlanningListView from "@/views/BudgetPlanningListView.vue";
import BudgetPlanningScheduleView from "@/views/BudgetPlanningScheduleView.vue";
import LaporanBelanjawanView from "@/views/LaporanBelanjawanView.vue";
import PlanningNewApplicationView from "@/views/PlanningNewApplicationView.vue";
import StructureBudgetListView from "@/views/StructureBudgetListView.vue";
import TotalAllocationReportView from "@/views/TotalAllocationReportView.vue";
import UmumAllocationPtjView from "@/views/UmumAllocationPtjView.vue";
import BudgetV2SummaryReportView from "@/views/BudgetV2SummaryReportView.vue";
import BudgetLegacyReportPlaceholderView from "@/views/BudgetLegacyReportPlaceholderView.vue";
import BankSetupView from "@/views/BankSetupView.vue";
import BankMasterView from "@/views/BankMasterView.vue";
import BankAccountView from "@/views/BankAccountView.vue";
import CashbookListView from "@/views/CashbookListView.vue";
import PayeeRegistrationView from "@/views/PayeeRegistrationView.vue";
import PayeeRegistrationDetailView from "@/views/PayeeRegistrationDetailView.vue";
import AccountPayableLegacyPlaceholderView from "@/views/AccountPayableLegacyPlaceholderView.vue";
import AccountReceivableLegacyPlaceholderView from "@/views/AccountReceivableLegacyPlaceholderView.vue";
import UtilityRegistrationView from "@/views/UtilityRegistrationView.vue";
import AccountBankByPayeeView from "@/views/AccountBankByPayeeView.vue";
import AccountBankUpdatedView from "@/views/AccountBankUpdatedView.vue";
import DebtorView from "@/views/DebtorView.vue";
import CashbookPtjView from "@/views/CashbookPtjView.vue";
import CashbookLegacyPlaceholderView from "@/views/CashbookLegacyPlaceholderView.vue";
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
import CreditControlLegacyPlaceholderView from "@/views/CreditControlLegacyPlaceholderView.vue";
import InvoiceBalanceView from "@/views/InvoiceBalanceView.vue";
import DepositFormView from "@/views/DepositFormView.vue";
import DebtorProfileUpdateView from "@/views/DebtorProfileUpdateView.vue";
import GlYearMonthView from "@/views/GlYearMonthView.vue";
import GeneralLedgerListingView from "@/views/GeneralLedgerListingView.vue";
import JournalListingView from "@/views/JournalListingView.vue";
import ManualJournalListingView from "@/views/ManualJournalListingView.vue";
import PostingToTbView from "@/views/PostingToTbView.vue";
import GeneralLedgerLegacyPlaceholderView from "@/views/GeneralLedgerLegacyPlaceholderView.vue";
import LoanLegacyPlaceholderView from "@/views/LoanLegacyPlaceholderView.vue";
import BankAccountUpdateView from "@/views/BankAccountUpdateView.vue";
import ListOfAccrualView from "@/views/ListOfAccrualView.vue";
import InvestmentAccrualView from "@/views/InvestmentAccrualView.vue";
import InvestmentGenerateScheduleView from "@/views/InvestmentGenerateScheduleView.vue";
import InvestmentMonitoringView from "@/views/InvestmentMonitoringView.vue";
import InvestmentToBeWithdrawnView from "@/views/InvestmentToBeWithdrawnView.vue";
import ListOfInvestmentsView from "@/views/ListOfInvestmentsView.vue";
import InvestmentLegacyPlaceholderView from "@/views/InvestmentLegacyPlaceholderView.vue";
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
import StudentFinanceLegacyPlaceholderView from "@/views/StudentFinanceLegacyPlaceholderView.vue";
import StatusPoPrView from "@/views/StatusPoPrView.vue";
import ListOfVendorView from "@/views/ListOfVendorView.vue";
import PurchasingLegacyPlaceholderView from "@/views/PurchasingLegacyPlaceholderView.vue";
import PostDatedChequeView from "@/views/PostDatedChequeView.vue";
import CcEfApprovedListingView from "@/views/CcEfApprovedListingView.vue";
import CcEmergencyFundAccrualTabs1983View from "@/views/CcEmergencyFundAccrualTabs1983View.vue";
import CcEmergencyFundRelease2028View from "@/views/CcEmergencyFundRelease2028View.vue";
import CcEfReminderReportView from "@/views/CcEfReminderReportView.vue";
import TenderQuotationView from "@/views/TenderQuotationView.vue";
import AuditSystemTransactionView from "@/views/AuditSystemTransactionView.vue";
import VendorPoStatusView from "@/views/VendorPoStatusView.vue";
import VendorFinancialStatusView from "@/views/VendorFinancialStatusView.vue";
import SponsorLetterView from "@/views/SponsorLetterView.vue";
import PortalAdvanceGenerateBillRecoupmentView from "@/views/PortalAdvanceGenerateBillRecoupmentView.vue";
import PortalAdvanceRecoupmentDetailView from "@/views/PortalAdvanceRecoupmentDetailView.vue";
import PortalAdvanceRecoupmentStatusView from "@/views/PortalAdvanceRecoupmentStatusView.vue";
import PortalLegacyPlaceholderView from "@/views/PortalLegacyPlaceholderView.vue";
import StaffProfileView from "@/views/StaffProfileView.vue";
import PayrollLegacyPlaceholderView from "@/views/PayrollLegacyPlaceholderView.vue";
import VendorPortalView from "@/views/VendorPortalView.vue";
import AssetInventoryListView from "@/views/AssetInventoryListView.vue";
import AssetLegacyPlaceholderView from "@/views/AssetLegacyPlaceholderView.vue";
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
import GenericLegacyPlaceholderView from "@/views/GenericLegacyPlaceholderView.vue";
import ProjectMonitoringLegacyPlaceholderView from "@/views/ProjectMonitoringLegacyPlaceholderView.vue";
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
    // HIDDEN_PAGE_LEVEL3 Floating Point listing (PAGEID 1943 / MENUID 2375) — overrides setup-maintenance legacy placeholder route.
    { path: "/admin/kerisi/m/2375", name: "kerisi-setup-gl-floating-point-profile", component: FloatingPointProfileSetupView, meta: { requiresAuth: true, title: "Floating Point for Profile Setup" } },
    // HIDDEN_PAGE_LEVEL3 Setup & Maintenance (hidden JSON) — real UIs / route variants (must stay before `...kerisiSetupMaintenanceHiddenRoutes`).
    { path: "/admin/kerisi/m/1615", name: "kerisi-setup-gl-project-profile-so-code", component: ProjectProfileSoCodeSetupView, meta: { requiresAuth: true, title: "Project Profile So Code" } },
    {
      path: "/admin/kerisi/m/1693",
      name: "kerisi-setup-integration-project-profile-details",
      component: IntegrationProfileView,
      props: { pageName: "Project Profile Details", pageBreadcrumb: "Setup and Maintenance / Integration / Project Profile Details" },
      meta: { requiresAuth: true, title: "Project Profile Details" },
    },
    {
      path: "/admin/kerisi/m/1808",
      name: "kerisi-setup-list-gl-account-code",
      component: AccountCodeView,
      props: { pageHeading: "Setup and Maintenance / List of General Ledger Structure / List of Account Code" },
      meta: { requiresAuth: true, title: "List of Account Code" },
    },
    {
      path: "/admin/kerisi/m/2650",
      name: "kerisi-setup-gl-edit-project-profile",
      component: ProjectListView,
      props: {
        pageHeading: "Setup and Maintenance / General Ledger Structure / Edit Project Profile",
        cardTitle: "Capital projects",
        exportFileBase: "Edit_Project_Profile_List",
        extendedGlColumns: true,
        linkToProjectProfile: true,
      },
      meta: { requiresAuth: true, title: "Edit Project Profile" },
    },
    {
      path: "/admin/kerisi/m/3210",
      name: "kerisi-setup-currency-ag-rate-details",
      component: AgRateView,
      props: { pageName: "AG Rate Details", pageBreadcrumb: "Setup and Maintenance / Currency / AG Rate Details" },
      meta: { requiresAuth: true, title: "AG Rate Details" },
    },
    // "List of ..." listing menus (FLC_SETUP&MAINTAINANCE) — reuse the setup-screen components.
    { path: "/admin/kerisi/m/2330", name: "kerisi-list-fund-type", component: FundTypeView, meta: { requiresAuth: true, title: "List of Fund Type" } },
    { path: "/admin/kerisi/m/1874", name: "kerisi-list-activity-code", component: ActivityCodeView, meta: { requiresAuth: true, title: "List of Activity Code" } },
    { path: "/admin/kerisi/m/1886", name: "kerisi-list-ptj-code", component: PtjCodeView, meta: { requiresAuth: true, title: "List of PTJ Code" } },
    { path: "/admin/kerisi/m/2360", name: "kerisi-list-cost-centre", component: CostCentreView, meta: { requiresAuth: true, title: "List of Cost Centre" } },
    { path: "/admin/kerisi/m/2167", name: "kerisi-list-cascade-structure", component: CascadeStructureView, meta: { requiresAuth: true, title: "List of Cascade Structure" } },
    { path: "/admin/kerisi/m/3453", name: "kerisi-list-account-code-ppi", component: AccountCodePpiView, meta: { requiresAuth: true, title: "List of Account Code (PPI)" } },
    // FIMS Budget — Increment / Decrement / Virement list pages + read-only form pages (1557–1559).
    { path: "/admin/kerisi/m/1554", name: "kerisi-budget-increment", component: BudgetMovementView, props: { type: "increment" }, meta: { requiresAuth: true, title: "Budget Increment" } },
    { path: "/admin/kerisi/m/1555", name: "kerisi-budget-decrement", component: BudgetMovementView, props: { type: "decrement" }, meta: { requiresAuth: true, title: "Budget Decrement" } },
    { path: "/admin/kerisi/m/1556", name: "kerisi-budget-virement", component: BudgetMovementView, props: { type: "virement" }, meta: { requiresAuth: true, title: "Budget Virement" } },
    { path: "/admin/kerisi/m/1557", name: "kerisi-budget-increment-form", component: BudgetMovementFormView, props: { type: "increment" }, meta: { requiresAuth: true, title: "Increment Form" } },
    { path: "/admin/kerisi/m/1558", name: "kerisi-budget-decrement-form", component: BudgetMovementFormView, props: { type: "decrement" }, meta: { requiresAuth: true, title: "Decrement Form" } },
    { path: "/admin/kerisi/m/1559", name: "kerisi-budget-virement-form", component: BudgetMovementFormView, props: { type: "virement" }, meta: { requiresAuth: true, title: "Virement Form" } },
    // Hidden Kerisi menus (HIDDEN_PAGE_LEVEL2): same UIs as above with legacy titles/breadcrumbs
    // (PAGEID 999 / 1073 / 1044 / 1075).
    {
      path: "/admin/kerisi/m/1254",
      name: "kerisi-budget-new-increment",
      component: BudgetMovementView,
      props: {
        type: "increment",
        overrideBreadcrumb: "Budget / Increment / Increment",
        overrideCardTitle: "New Increment",
        overrideExportPageName: "New Increment",
      },
      meta: { requiresAuth: true, title: "New Increment" },
    },
    {
      path: "/admin/kerisi/m/1267",
      name: "kerisi-budget-new-virement",
      component: BudgetMovementView,
      props: {
        type: "virement",
        overrideBreadcrumb: "Budget / Virement",
        overrideCardTitle: "New Virement",
        overrideExportPageName: "New Virement",
      },
      meta: { requiresAuth: true, title: "New Virement" },
    },
    {
      path: "/admin/kerisi/m/1304",
      name: "kerisi-budget-new-budget-code",
      component: BudgetCodeView,
      props: {
        openCreateOnMount: true,
        pageHeading: "Budget / New Budget Code",
        cardTitle: "Budget Code",
        exportPageName: "New Budget Code",
      },
      meta: { requiresAuth: true, title: "New Budget Code" },
    },
    {
      path: "/admin/kerisi/m/1337",
      name: "kerisi-budget-new-initial",
      component: BudgetInitialNewV2View,
      props: { listHeading: "Budget / Initial / Initial" },
      meta: { requiresAuth: true, title: "New Initial" },
    },
    // FIMS Budget — Monitoring (PAGEID 1201 / MENUID 1471), Advance Controlled
    // hidden list (PAGEID 1784 / MENUID 2160), Budget Listing detail (PAGEID 1510 /
    // MENUID 1831), Initial (PAGEID 1264 / MENUID 1541), New Initial V2 editor
    // (PAGEID 1277 / MENUID 1560), and Closing (PAGEID 1953 / MENUID 2389 primary +
    // 3154 alias).
    { path: "/admin/kerisi/m/1471", name: "kerisi-budget-monitoring", component: BudgetMonitoringView, meta: { requiresAuth: true, title: "Budget Monitoring" } },
    { path: "/admin/kerisi/m/2160", name: "kerisi-budget-advance-controlled", component: BudgetAdvanceControlledView, meta: { requiresAuth: true, title: "Budget Advance Controlled" } },
    // Hidden Budget (HIDDEN_PAGE_LEVEL2): Monitoring alias, legacy laporan stubs, In Advance list,
    // advance detail, New Initial V2 under “New In Advance”, Listing Budget alias, Budget View (API_MM_BUDGET_MONITORING_BUDGETVIEW).
    { path: "/admin/kerisi/m/1198", name: "kerisi-budget-hidden-monitoring", component: BudgetMonitoringView, props: { pageTitle: "Monitoring", pageBreadcrumb: "Budget / Monitoring", listingKerisiPath: "/admin/kerisi/m/1831" }, meta: { requiresAuth: true, title: "Monitoring" } },
    {
      path: "/admin/kerisi/m/1378",
      name: "kerisi-budget-laporan-pengurusan-belanja",
      component: BudgetLegacyReportPlaceholderView,
      props: {
        title: "Laporan Pengurusan Belanja",
        description:
          "This management expenditure report is not reproduced here. Use Kerisi Classic for full parameters, export, and scheduled runs.",
      },
      meta: { requiresAuth: true, title: "Laporan Pengurusan Belanja" },
    },
    {
      path: "/admin/kerisi/m/1382",
      name: "kerisi-budget-laporan-peruntukan",
      component: BudgetLegacyReportPlaceholderView,
      props: {
        title: "Laporan Jumlah Peruntukan, Perbelanjaan dan Baki Peruntukan",
        description:
          "This allocation, expenditure, and balance report is not reproduced here. Use Kerisi Classic for full behaviour.",
      },
      meta: { requiresAuth: true, title: "Laporan Jumlah Peruntukan, Perbelanjaan dan Baki Peruntukan" },
    },
    { path: "/admin/kerisi/m/2098", name: "kerisi-budget-in-advance", component: BudgetAdvanceControlledView, props: { variant: "in_advance" }, meta: { requiresAuth: true, title: "Budget In Advance" } },
    { path: "/admin/kerisi/m/2161", name: "kerisi-budget-advance-detail", component: BudgetAdvanceControlledDetailView, meta: { requiresAuth: true, title: "Budget Advance Controlled Details" } },
    {
      path: "/admin/kerisi/m/2165",
      name: "kerisi-budget-new-in-advance-initial-v2",
      component: BudgetInitialNewV2View,
      props: { listHeading: "Budget / New In Advance" },
      meta: { requiresAuth: true, title: "New Initial V2" },
    },
    {
      path: "/admin/kerisi/m/2581",
      name: "kerisi-budget-listing-budget",
      component: BudgetListingView,
      props: {
        pageHeading: "Budget / Listing Budget",
        monitoringFallbackPath: "/admin/kerisi/m/3074",
        emptyStateHint:
          "Open from Budget View or Budget Monitoring using View Budget, or append ?bgdId=…&year=… to the URL.",
      },
      meta: { requiresAuth: true, title: "Listing Budget" },
    },
    {
      path: "/admin/kerisi/m/3074",
      name: "kerisi-budget-view",
      component: BudgetMonitoringView,
      props: {
        pageTitle: "Budget View",
        pageBreadcrumb: "Budget / Budget View",
        listingKerisiPath: "/admin/kerisi/m/2581",
      },
      meta: { requiresAuth: true, title: "Budget View" },
    },
    { path: "/admin/kerisi/m/1831", name: "kerisi-budget-listing", component: BudgetListingView, meta: { requiresAuth: true, title: "Budget Listing" } },
    { path: "/admin/kerisi/m/1541", name: "kerisi-budget-initial", component: BudgetInitialView, meta: { requiresAuth: true, title: "Budget Initial" } },
    { path: "/admin/kerisi/m/1560", name: "kerisi-budget-initial-new-v2", component: BudgetInitialNewV2View, meta: { requiresAuth: true, title: "New Initial V2" } },
    { path: "/admin/kerisi/m/2389", name: "kerisi-budget-closing", component: BudgetClosingView, meta: { requiresAuth: true, title: "Budget Closing" } },
    { path: "/admin/kerisi/m/3154", name: "kerisi-budget-closing-alias", component: BudgetClosingView, meta: { requiresAuth: true, title: "Budget Closing" } },
    // FIMS Cashbook — Bank Setup (PAGEID 2680), Bank Master (PAGEID 1682),
    // Bank Account (PAGEID 1736), List of Cashbook Daily (PAGEID 1397) and
    // Monthly (PAGEID 2024). Daily/Monthly reuse CashbookListView via prop.
    // HIDDEN_PAGE_LEVEL3 Cashbook (`Menu` prefix `Cashbook>`): upload stubs only MENUID 1873, 2062 —
    // explicit `CashbookLegacyPlaceholderView` routes below (no separate kerisi-cashbook-hidden-routes).
    { path: "/admin/kerisi/m/3246", name: "kerisi-bank-setup", component: BankSetupView, meta: { requiresAuth: true, title: "Bank Setup" } },
    { path: "/admin/kerisi/m/2036", name: "kerisi-bank-master", component: BankMasterView, meta: { requiresAuth: true, title: "Bank Master" } },
    { path: "/admin/kerisi/m/2097", name: "kerisi-bank-account", component: BankAccountView, meta: { requiresAuth: true, title: "Bank Account" } },
    { path: "/admin/kerisi/m/1702", name: "kerisi-cashbook-daily", component: CashbookListView, props: { type: "DAILY" }, meta: { requiresAuth: true, title: "List Of CashBook (Daily)" } },
    { path: "/admin/kerisi/m/2471", name: "kerisi-cashbook-monthly", component: CashbookListView, props: { type: "MONTHLY" }, meta: { requiresAuth: true, title: "List Of Cashbook (Monthly)" } },
    { path: "/admin/kerisi/m/2553", name: "kerisi-cashbook-monthly-closing-alias", component: CashbookListView, props: { type: "MONTHLY" }, meta: { requiresAuth: true, title: "List of Cashbook (Monthly)" } },
    {
      path: "/admin/kerisi/m/2479",
      name: "kerisi-cashbook-closing",
      component: CashbookLegacyPlaceholderView,
      props: {
        title: "Cashbook Closing",
        breadcrumb: "Cashbook / Closing / Cashbook Closing",
        description:
          "Period closing and lock steps for cashbook are not ported yet. Use Kerisi Classic to run closing, or review the monthly cashbook list here.",
        relatedPath: "/admin/kerisi/m/2471",
        relatedLabel: "Open list of cashbook (monthly)",
      },
      meta: { requiresAuth: true, title: "Cashbook Closing" },
    },
    {
      path: "/admin/kerisi/m/1873",
      name: "kerisi-cashbook-upload-bank-statement-daily",
      component: CashbookLegacyPlaceholderView,
      props: {
        title: "Upload Bank Statement",
        breadcrumb: "Cashbook / Daily / Upload Bank Statement",
        description: "Legacy bank-statement upload (daily) is not ported. Use Kerisi Classic, or work from the daily cashbook list.",
        relatedPath: "/admin/kerisi/m/1702",
        relatedLabel: "Open daily cashbook list",
      },
      meta: { requiresAuth: true, title: "Upload Bank Statement (Daily)" },
    },
    {
      path: "/admin/kerisi/m/2062",
      name: "kerisi-cashbook-upload-bank-statement-monthly",
      component: CashbookLegacyPlaceholderView,
      props: {
        title: "Upload Bank Statement",
        breadcrumb: "Cashbook / Monthly / Upload Bank Statement",
        description: "Legacy bank-statement upload (monthly) is not ported. Use Kerisi Classic, or work from the monthly cashbook list.",
        relatedPath: "/admin/kerisi/m/2471",
        relatedLabel: "Open monthly cashbook list",
      },
      meta: { requiresAuth: true, title: "Upload Bank Statement (Monthly)" },
    },
    // FIMS Account Payable — Payee Registration (MENUID 1711), Utility
    // Registration (MENUID 3466), Account Bank by Payee (MENUID 2751),
    // Account Bank Updated (MENUID 2078).
    { path: "/admin/kerisi/m/1711", name: "kerisi-ap-payee-registration", component: PayeeRegistrationView, meta: { requiresAuth: true, title: "Payee Registration" } },
    {
      path: "/admin/kerisi/m/1713",
      name: "kerisi-ap-payee-registration-detail",
      component: PayeeRegistrationDetailView,
      meta: { requiresAuth: true, title: "Payee Registration Details" },
    },
    {
      path: "/admin/kerisi/m/1718",
      name: "kerisi-ap-bill-registration-form",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Bill Registration Form",
        description:
          "Bill registration wizard (NF_BL_*) is not migrated here. Use Kerisi Classic, or maintain payee banking on Account Bank Updated after filtering by payee.",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Open Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Bill Registration Form" },
    },
    {
      path: "/admin/kerisi/m/1821",
      name: "kerisi-ap-voucher-registration-details",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Voucher Registration Details",
        description:
          "Voucher registration detail grids are not migrated. Use Kerisi Classic, or use Account Bank Updated (voucher tab) for bank updates scoped to a payee.",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Open Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Voucher Registration Details" },
    },
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
    // Student Finance list aliases — same backend tables/columns as the admin
    // AR listings above; the legacy `DT_CREDIT_NOTE_LIST` / `DT_DEBIT_NOTE_LIST`
    // / `DT_DISCOUNT_NOTE_LIST` BL files are not present in the available
    // source JSON, so they are assumed to be alternate menu placements of the
    // same `DT_AR_*_LIST` listings already wired for MENUID 1041/1042/1043.
    { path: "/admin/kerisi/m/1529", name: "kerisi-sf-credit-note", component: CreditNoteView, meta: { requiresAuth: true, title: "Credit Note" } },
    { path: "/admin/kerisi/m/1575", name: "kerisi-sf-debit-note", component: DebitNoteView, meta: { requiresAuth: true, title: "Debit Note" } },
    { path: "/admin/kerisi/m/1570", name: "kerisi-sf-discount-note", component: DiscountNoteView, meta: { requiresAuth: true, title: "Discount Note" } },
    { path: "/admin/kerisi/m/1784", name: "kerisi-ar-discount-note-form", component: DiscountNoteFormView, meta: { requiresAuth: true, title: "Discount Note Form" } },
    { path: "/admin/kerisi/m/1952", name: "kerisi-ar-authorized-receipting", component: AuthorizedReceiptingView, meta: { requiresAuth: true, title: "Authorized Receipting" } },
    { path: "/admin/kerisi/m/1953", name: "kerisi-ar-authorized-receipting-form", component: AuthorizedReceiptingFormView, meta: { requiresAuth: true, title: "Authorized Receipting Form" } },
    // FIMS Credit Control — MENUID 1809 / 1755 / 3066 / 3388 / 3397 (legacy PAGEIDs
    // 1445, 1436, 2159, 2561, 2688). Backed by DepositController /
    // PostDatedChequeController / ListOfDepositController / InvoiceBalanceController /
    // DepositFormController.
    { path: "/admin/kerisi/m/1809", name: "kerisi-cc-deposit", component: DepositView, meta: { requiresAuth: true, title: "Deposit" } },
    { path: "/admin/kerisi/m/1755", name: "kerisi-cc-post-dated-cheque", component: PostDatedChequeView, meta: { requiresAuth: true, title: "Post Dated Cheque" } },
    { path: "/admin/kerisi/m/3066", name: "kerisi-cc-list-of-deposit", component: ListOfDepositView, meta: { requiresAuth: true, title: "List of Deposit" } },
    { path: "/admin/kerisi/m/3388", name: "kerisi-cc-invoice-balance", component: InvoiceBalanceView, meta: { requiresAuth: true, title: "Invoice Balance" } },
    { path: "/admin/kerisi/m/3397", name: "kerisi-cc-deposit-form", component: DepositFormView, meta: { requiresAuth: true, title: "Detail of Deposit" } },
    // HIDDEN_PAGE_LEVEL3 Credit Control (from `HIDDEN_PAGE_LEVEL3.json`; must stay before LEVEL4 CC routes and `...kerisiCreditControlHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1766",
      name: "kerisi-cc-m-1766",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Breach of Contract",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / List of Breach Contract",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Breach of Contract" },
    },
    {
      path: "/admin/kerisi/m/1910",
      name: "kerisi-cc-m-1910",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Doubtful Debt",
        breadcrumb: "Credit Control / Debt Monitoring / Doubtful Debt",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Doubtful Debt" },
    },
    {
      path: "/admin/kerisi/m/1921",
      name: "kerisi-cc-m-1921",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Account Code Mapping",
        breadcrumb: "Credit Control / Setup / Account Code Mapping",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Account Code Mapping" },
    },
    {
      path: "/admin/kerisi/m/2150",
      name: "kerisi-cc-m-2150",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Listing",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Listing" },
    },
    {
      path: "/admin/kerisi/m/2162",
      name: "kerisi-cc-m-2162",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Applicant Details",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / New Application",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Applicant Details" },
    },
    {
      path: "/admin/kerisi/m/2178",
      name: "kerisi-cc-m-2178",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Generate Schedule",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Generate Schedule",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Generate Schedule" },
    },
    {
      path: "/admin/kerisi/m/2352",
      name: "kerisi-cc-m-2352",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Monitoring By Account Code",
        breadcrumb: "Credit Control / Payment In Advance / Monitoring By Account Code",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Open deposit",
      },
      meta: { requiresAuth: true, title: "Monitoring By Account Code" },
    },
    {
      path: "/admin/kerisi/m/2353",
      name: "kerisi-cc-m-2353",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Monitoring By Payee",
        breadcrumb: "Credit Control / Payment In Advance / Monitoring By Payee",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Open deposit",
      },
      meta: { requiresAuth: true, title: "Monitoring By Payee" },
    },
    {
      path: "/admin/kerisi/m/2358",
      name: "kerisi-cc-m-2358",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Knockoff",
        breadcrumb: "Credit Control / Knockoff Payment In Advance / List of Knockoff",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Open deposit",
      },
      meta: { requiresAuth: true, title: "List of Knockoff" },
    },
    {
      path: "/admin/kerisi/m/2359",
      name: "kerisi-cc-m-2359",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Knockoff Details",
        breadcrumb: "Credit Control / Knockoff Payment In Advance / Knockoff Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Open deposit",
      },
      meta: { requiresAuth: true, title: "Knockoff Details" },
    },
    {
      path: "/admin/kerisi/m/2518",
      name: "kerisi-cc-m-2518",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Generate Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Listing" },
    },
    {
      path: "/admin/kerisi/m/2521",
      name: "kerisi-cc-m-2521",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund Setup Listing",
        breadcrumb: "Credit Control / Setup / Refund Setup",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Open deposit",
      },
      meta: { requiresAuth: true, title: "Refund Setup Listing" },
    },
    {
      path: "/admin/kerisi/m/2607",
      name: "kerisi-cc-m-2607",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing",
        breadcrumb: "Credit Control / Payment In Advance / Listing",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Open deposit",
      },
      meta: { requiresAuth: true, title: "Listing" },
    },
    {
      path: "/admin/kerisi/m/2632",
      name: "kerisi-cc-m-2632",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Report Hutang By PTJ UUM",
        breadcrumb: "Credit Control / Report / Report Hutang By PTJ UUM",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Report Hutang By PTJ UUM" },
    },
    {
      path: "/admin/kerisi/m/2634",
      name: "kerisi-cc-m-2634",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Report Pergerakan Hutang UUM",
        breadcrumb: "Credit Control / Report / Report Pergerakan Hutang UUM",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Report Pergerakan Hutang UUM" },
    },
    {
      path: "/admin/kerisi/m/2662",
      name: "kerisi-cc-m-2662",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing Payment In Advance",
        breadcrumb: "Credit Control / Knockoff Payment In Advance / Listing Payment In Advance",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Open deposit",
      },
      meta: { requiresAuth: true, title: "Listing Payment In Advance" },
    },
    {
      path: "/admin/kerisi/m/2668",
      name: "kerisi-cc-m-2668",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Generate Schedule BOC",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Generate Schedule BOC",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Generate Schedule BOC" },
    },
    {
      path: "/admin/kerisi/m/2803",
      name: "kerisi-cc-m-2803",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Cancel Application",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Listing" },
    },
    {
      path: "/admin/kerisi/m/2806",
      name: "kerisi-cc-m-2806",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Statement Detail",
        breadcrumb: "Credit Control / Report / Statement Detail",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Statement Detail" },
    },
    {
      path: "/admin/kerisi/m/2862",
      name: "kerisi-cc-m-2862",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "BOC Account Statement",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / BOC Account Statement",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "BOC Account Statement" },
    },
    {
      path: "/admin/kerisi/m/2873",
      name: "kerisi-cc-m-2873",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Delete && Active/Unactive BOC Schedule",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Delete/Active & Inactive Schedule BOC",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Delete && Active/Unactive BOC Schedule" },
    },
    {
      path: "/admin/kerisi/m/2969",
      name: "kerisi-cc-m-2969",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing Posting (BOC)",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Listing Posting (BOC)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "Open list of deposit",
      },
      meta: { requiresAuth: true, title: "Listing Posting (BOC)" },
    },
    // HIDDEN_PAGE_LEVEL4 Credit Control (were `kerisi-credit-control-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1687",
      name: "kerisi-cc-m-1687",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Staff Study",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Staff Study / List Of Staff Study Old",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Staff Study" },
    },
    {
      path: "/admin/kerisi/m/1688",
      name: "kerisi-cc-m-1688",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Staff Study Details",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Staff Study / Staff Study Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Staff Study Details" },
    },
    {
      path: "/admin/kerisi/m/1690",
      name: "kerisi-cc-m-1690",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Fund Main",
        breadcrumb: "Credit Control / Setup / Emergency Fund / Fund Main",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Fund Main" },
    },
    {
      path: "/admin/kerisi/m/1697",
      name: "kerisi-cc-m-1697",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List Of Category",
        breadcrumb: "Credit Control / Setup / Emergency Fund / Category",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List Of Category" },
    },
    {
      path: "/admin/kerisi/m/1698",
      name: "kerisi-cc-m-1698",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Setup Parent",
        breadcrumb: "Credit Control / Setup / Emergency Fund / Parent",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Setup Parent" },
    },
    {
      path: "/admin/kerisi/m/1705",
      name: "kerisi-cc-m-1705",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Setup Region",
        breadcrumb: "Credit Control / Setup / Emergency Fund / Region",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Setup Region" },
    },
    {
      path: "/admin/kerisi/m/1723",
      name: "kerisi-cc-m-1723",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "fundSetup",
        breadcrumb: "Credit Control / Setup / Emergency Fund / Fund Setup",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "fundSetup" },
    },
    {
      path: "/admin/kerisi/m/1926",
      name: "kerisi-cc-m-1926",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Admin / Refund Application",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund" },
    },
    {
      path: "/admin/kerisi/m/1983",
      name: "kerisi-cc-m-1983",
      component: CcEmergencyFundAccrualTabs1983View,
      meta: { requiresAuth: true, title: "Accrual" },
    },
    {
      path: "/admin/kerisi/m/2001",
      name: "kerisi-cc-m-2001",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "New Payment In Advance",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Admin / List of Refund",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "New Payment In Advance" },
    },
    {
      path: "/admin/kerisi/m/2004",
      name: "kerisi-cc-m-2004",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Payment In Advance Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details" },
    },
    {
      path: "/admin/kerisi/m/2028",
      name: "kerisi-cc-m-2028",
      component: CcEmergencyFundRelease2028View,
      meta: { requiresAuth: true, title: "Release List" },
    },
    {
      path: "/admin/kerisi/m/2032",
      name: "kerisi-cc-m-2032",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Reminder",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Reminder" },
    },
    {
      path: "/admin/kerisi/m/2037",
      name: "kerisi-cc-m-2037",
      component: CcEfReminderReportView,
      meta: { requiresAuth: true, title: "Reminder" },
    },
    {
      path: "/admin/kerisi/m/2038",
      name: "kerisi-cc-m-2038",
      component: CcEfApprovedListingView,
      meta: { requiresAuth: true, title: "Listing" },
    },
    {
      path: "/admin/kerisi/m/2040",
      name: "kerisi-cc-m-2040",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Bill Registration Integration List",
        breadcrumb: "Credit Control / Refund / Refund (Student) / List of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Bill Registration Integration List" },
    },
    {
      path: "/admin/kerisi/m/2050",
      name: "kerisi-cc-m-2050",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Payment In Advance Details (Draft)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details" },
    },
    {
      path: "/admin/kerisi/m/2068",
      name: "kerisi-cc-m-2068",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Manual Recoup",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Recoupment / Manual Recoup",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Manual Recoup" },
    },
    {
      path: "/admin/kerisi/m/2069",
      name: "kerisi-cc-m-2069",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Recoup Form",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Recoupment / Recoup Form",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Recoup Form" },
    },
    {
      path: "/admin/kerisi/m/2070",
      name: "kerisi-cc-m-2070",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Recoupment Details Draft",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Recoupment / Recoupment Details (Draft)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Recoupment Details Draft" },
    },
    {
      path: "/admin/kerisi/m/2077",
      name: "kerisi-cc-m-2077",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund",
        breadcrumb: "Credit Control / Refund / Refund (Student) / List of Refund Application (Portal)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund" },
    },
    {
      path: "/admin/kerisi/m/2079",
      name: "kerisi-cc-m-2079",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Recoupment Details",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Recoupment / Recoupment Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Recoupment Details" },
    },
    {
      path: "/admin/kerisi/m/2080",
      name: "kerisi-cc-m-2080",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Recoupment",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Recoupment / List of Recoupment Application (Portal)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Recoupment" },
    },
    {
      path: "/admin/kerisi/m/2084",
      name: "kerisi-cc-m-2084",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Recoupment Status",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Recoupment / Recoupment Status",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Recoupment Status" },
    },
    {
      path: "/admin/kerisi/m/2126",
      name: "kerisi-cc-m-2126",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Invoice Details",
        breadcrumb: "Credit Control / Debt Monitoring / Reminder / Staff / Invoice Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Invoice Details" },
    },
    {
      path: "/admin/kerisi/m/2176",
      name: "kerisi-cc-m-2176",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Report of Refund Bill",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Report of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Report of Refund Bill" },
    },
    {
      path: "/admin/kerisi/m/2182",
      name: "kerisi-cc-m-2182",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Generated Reminder",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Outstanding / Reminder 1 / Generated Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Generated Reminder" },
    },
    {
      path: "/admin/kerisi/m/2199",
      name: "kerisi-cc-m-2199",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Outstanding",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Outstanding / Reminder 1 / List of Outstanding",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Outstanding" },
    },
    {
      path: "/admin/kerisi/m/2218",
      name: "kerisi-cc-m-2218",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Reminder",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Reminder / Reminder 2 / List of Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Reminder" },
    },
    {
      path: "/admin/kerisi/m/2219",
      name: "kerisi-cc-m-2219",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Generated Reminder 2",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Reminder / Reminder 2 / Generated Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Generated Reminder 2" },
    },
    {
      path: "/admin/kerisi/m/2223",
      name: "kerisi-cc-m-2223",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Reminder",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Reminder / Reminder 3 / List of Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Reminder" },
    },
    {
      path: "/admin/kerisi/m/2224",
      name: "kerisi-cc-m-2224",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Generated Reminder 3",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Reminder / Reminder 3 / Generated Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Generated Reminder 3" },
    },
    {
      path: "/admin/kerisi/m/2243",
      name: "kerisi-cc-m-2243",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List to Assign Panel lawyer",
        breadcrumb: "Credit Control / Debt Monitoring / Lawyer / Assign Panel Lawyer",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List to Assign Panel lawyer" },
    },
    {
      path: "/admin/kerisi/m/2261",
      name: "kerisi-cc-m-2261",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Panel lawyer",
        breadcrumb: "Credit Control / Debt Monitoring / Lawyer / List of Panel Lawyer",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Panel lawyer" },
    },
    {
      path: "/admin/kerisi/m/2288",
      name: "kerisi-cc-m-2288",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details",
        breadcrumb: "Credit Control / Refund / Refund (Staff) / Payment In Advance Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details" },
    },
    {
      path: "/admin/kerisi/m/2300",
      name: "kerisi-cc-m-2300",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund",
        breadcrumb: "Credit Control / Refund / Refund (Sponsor) / Admin / Refund Application",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund" },
    },
    {
      path: "/admin/kerisi/m/2301",
      name: "kerisi-cc-m-2301",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "New Payment In Advance",
        breadcrumb: "Credit Control / Refund / Refund (Sponsor) / Admin / List of Refund",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "New Payment In Advance" },
    },
    {
      path: "/admin/kerisi/m/2302",
      name: "kerisi-cc-m-2302",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details",
        breadcrumb: "Credit Control / Refund / Refund (Sponsor) / Payment In Advance Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details" },
    },
    {
      path: "/admin/kerisi/m/2303",
      name: "kerisi-cc-m-2303",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Bill Registration Integration List",
        breadcrumb: "Credit Control / Refund / Refund (Sponsor) / List Of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Bill Registration Integration List" },
    },
    {
      path: "/admin/kerisi/m/2304",
      name: "kerisi-cc-m-2304",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Detail Listing Of Refund Process",
        breadcrumb: "Credit Control / Refund / Refund (Sponsor) / Report of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Detail Listing Of Refund Process" },
    },
    {
      path: "/admin/kerisi/m/2305",
      name: "kerisi-cc-m-2305",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund",
        breadcrumb: "Credit Control / Refund / Refund (Sponsor) / List of Refund Portal",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund" },
    },
    {
      path: "/admin/kerisi/m/2306",
      name: "kerisi-cc-m-2306",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details",
        breadcrumb: "Credit Control / Refund / Refund (Sponsor) / Payment In Advance Details (Draft)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details" },
    },
    {
      path: "/admin/kerisi/m/2349",
      name: "kerisi-cc-m-2349",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Student Account Statement",
        breadcrumb: "Credit Control / Debt Monitoring / Reminder / Student / Student Account Statement",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Student Account Statement" },
    },
    {
      path: "/admin/kerisi/m/2350",
      name: "kerisi-cc-m-2350",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Panel Assign",
        breadcrumb: "Credit Control / Debt Monitoring / Lawyer / List of Panel Assign / By Type",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Panel Assign" },
    },
    {
      path: "/admin/kerisi/m/2351",
      name: "kerisi-cc-m-2351",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Upload NOD",
        breadcrumb: "Credit Control / Debt Monitoring / Lawyer / Upload NOD",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Upload NOD" },
    },
    {
      path: "/admin/kerisi/m/2379",
      name: "kerisi-cc-m-2379",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Ledger",
        breadcrumb: "Credit Control / Report / Ledger / Ledger (Old)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Ledger" },
    },
    {
      path: "/admin/kerisi/m/2384",
      name: "kerisi-cc-m-2384",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund Application",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / Admin / Refund Application",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund Application" },
    },
    {
      path: "/admin/kerisi/m/2388",
      name: "kerisi-cc-m-2388",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Jumlah Gantirugi Mengikut Kategori Staff",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Report / Jumlah Gantirugi Mengikut Kategori Staff",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Jumlah Gantirugi Mengikut Kategori Staff" },
    },
    {
      path: "/admin/kerisi/m/2391",
      name: "kerisi-cc-m-2391",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Jumlah Kutipan Gantirugi",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Report / Jumlah Kutipan Gantirugi",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Jumlah Kutipan Gantirugi" },
    },
    {
      path: "/admin/kerisi/m/2392",
      name: "kerisi-cc-m-2392",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Refund",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / Admin / List of Refund",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "List of Refund" },
    },
    {
      path: "/admin/kerisi/m/2393",
      name: "kerisi-cc-m-2393",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Bill Registration Integration List",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / List Of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Bill Registration Integration List" },
    },
    {
      path: "/admin/kerisi/m/2394",
      name: "kerisi-cc-m-2394",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / Payment In Advance Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details" },
    },
    {
      path: "/admin/kerisi/m/2396",
      name: "kerisi-cc-m-2396",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details (Draft)",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / Payment In Advance Details (Draft)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details (Draft)" },
    },
    {
      path: "/admin/kerisi/m/2398",
      name: "kerisi-cc-m-2398",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Detail Listing Of Refund Process",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / Report of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Detail Listing Of Refund Process" },
    },
    {
      path: "/admin/kerisi/m/2407",
      name: "kerisi-cc-m-2407",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Reminder",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Outstanding / Reminder 2 / List of Outstanding",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Reminder" },
    },
    {
      path: "/admin/kerisi/m/2408",
      name: "kerisi-cc-m-2408",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Generated Reminder",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Outstanding / Reminder 2 / Generated Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Generated Reminder" },
    },
    {
      path: "/admin/kerisi/m/2413",
      name: "kerisi-cc-m-2413",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Reminder",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Outstanding / Reminder 3 / List Of Outstanding",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Reminder" },
    },
    {
      path: "/admin/kerisi/m/2415",
      name: "kerisi-cc-m-2415",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Generated Reminder",
        breadcrumb: "Credit Control / Student Micro Credit (SMC) / Outstanding / Reminder 3 / Generated Reminder",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Generated Reminder" },
    },
    {
      path: "/admin/kerisi/m/2427",
      name: "kerisi-cc-m-2427",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Panel Assign By Panel",
        breadcrumb: "Credit Control / Debt Monitoring / Lawyer / List of Panel Assign / By Panel",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Panel Assign By Panel" },
    },
    {
      path: "/admin/kerisi/m/2428",
      name: "kerisi-cc-m-2428",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of NOD Uploaded",
        breadcrumb: "Credit Control / Debt Monitoring / Lawyer / List of NOD Uploaded",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of NOD Uploaded" },
    },
    {
      path: "/admin/kerisi/m/2435",
      name: "kerisi-cc-m-2435",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List Of DCA",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / List of DCA",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List Of DCA" },
    },
    {
      path: "/admin/kerisi/m/2436",
      name: "kerisi-cc-m-2436",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Case to be Assigned to DCA",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Case Assigned / Case to be Assigned to DCA",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Case to be Assigned to DCA" },
    },
    {
      path: "/admin/kerisi/m/2439",
      name: "kerisi-cc-m-2439",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of DCA by Customer",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Listing DCA / List of DCA by Customer",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of DCA by Customer" },
    },
    {
      path: "/admin/kerisi/m/2440",
      name: "kerisi-cc-m-2440",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Customer by DCA",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Listing DCA / List of Customer by DCA",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Customer by DCA" },
    },
    {
      path: "/admin/kerisi/m/2441",
      name: "kerisi-cc-m-2441",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Update Customer Feedback",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Update Customer Feedback",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Update Customer Feedback" },
    },
    {
      path: "/admin/kerisi/m/2460",
      name: "kerisi-cc-m-2460",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Request Refund",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Request Refund",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Request Refund" },
    },
    {
      path: "/admin/kerisi/m/2464",
      name: "kerisi-cc-m-2464",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Request Refund",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / Request Refund",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Request Refund" },
    },
    {
      path: "/admin/kerisi/m/2472",
      name: "kerisi-cc-m-2472",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Outstanding",
        breadcrumb: "Credit Control / Exec. & Enforcement (Credit Card Corporat) / Reminder 1 / List Of Outstanding",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Outstanding" },
    },
    {
      path: "/admin/kerisi/m/2485",
      name: "kerisi-cc-m-2485",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details (Draft)",
        breadcrumb: "Credit Control / Refund / Refund (Staff) / Payment In Advance Details (Draft)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details (Draft)" },
    },
    {
      path: "/admin/kerisi/m/2522",
      name: "kerisi-cc-m-2522",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / List of Refund Application (Portal)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund" },
    },
    {
      path: "/admin/kerisi/m/2555",
      name: "kerisi-cc-m-2555",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund",
        breadcrumb: "Credit Control / Refund / Refund (Student) / List of Refund Portal",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund" },
    },
    {
      path: "/admin/kerisi/m/2585",
      name: "kerisi-cc-m-2585",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Change Method",
        breadcrumb: "Credit Control / Debt Monitoring / Reminder / Student / Change Method",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Change Method" },
    },
    {
      path: "/admin/kerisi/m/2599",
      name: "kerisi-cc-m-2599",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Reminder Status",
        breadcrumb: "Credit Control / Debt Monitoring / Reminder / Student / Reminder Status",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Reminder Status" },
    },
    {
      path: "/admin/kerisi/m/2603",
      name: "kerisi-cc-m-2603",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Reminder Status",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Report / List of Reminder Status",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Reminder Status" },
    },
    {
      path: "/admin/kerisi/m/2606",
      name: "kerisi-cc-m-2606",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Refund Portal",
        breadcrumb: "Credit Control / Refund / Refund (Debtor) / List of Refund Portal",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "List of Refund Portal" },
    },
    {
      path: "/admin/kerisi/m/2615",
      name: "kerisi-cc-m-2615",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Review by Type",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / List of Review by Type",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Review by Type" },
    },
    {
      path: "/admin/kerisi/m/2621",
      name: "kerisi-cc-m-2621",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Breach Contract",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Report / List of Breach Contract",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Breach Contract" },
    },
    {
      path: "/admin/kerisi/m/2628",
      name: "kerisi-cc-m-2628",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Journal Release",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Release / Journal Release",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Journal Release" },
    },
    {
      path: "/admin/kerisi/m/2629",
      name: "kerisi-cc-m-2629",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Journal Release Details",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Release / Journal Release Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Journal Release Details" },
    },
    {
      path: "/admin/kerisi/m/2630",
      name: "kerisi-cc-m-2630",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Confirmation Payment",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Confirmation Payment",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Confirmation Payment" },
    },
    {
      path: "/admin/kerisi/m/2638",
      name: "kerisi-cc-m-2638",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Journal Accrual",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Accrual / Journal Accrual",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Journal Accrual" },
    },
    {
      path: "/admin/kerisi/m/2639",
      name: "kerisi-cc-m-2639",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Journal Accrual Details",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Accrual / Journal Accrual Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Journal Accrual Details" },
    },
    {
      path: "/admin/kerisi/m/2649",
      name: "kerisi-cc-m-2649",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Accrual / Listing",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Listing" },
    },
    {
      path: "/admin/kerisi/m/2652",
      name: "kerisi-cc-m-2652",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Release / Listing",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Listing" },
    },
    {
      path: "/admin/kerisi/m/2653",
      name: "kerisi-cc-m-2653",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Status Application Portal",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Status Application Portal",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Status Application Portal" },
    },
    {
      path: "/admin/kerisi/m/2655",
      name: "kerisi-cc-m-2655",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Print Application Student (Portal)",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Print Application Student (Portal)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Print Application Student (Portal)" },
    },
    {
      path: "/admin/kerisi/m/2658",
      name: "kerisi-cc-m-2658",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Print BRF B Batch",
        breadcrumb: "Credit Control / Refund / Refund (Student) / Print BRF By Batch",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Print BRF B Batch" },
    },
    {
      path: "/admin/kerisi/m/2659",
      name: "kerisi-cc-m-2659",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Create Bill From Receipt",
        breadcrumb: "Credit Control / Refund UUM Plate",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Create Bill From Receipt" },
    },
    {
      path: "/admin/kerisi/m/2695",
      name: "kerisi-cc-m-2695",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing Status Application",
        breadcrumb: "Credit Control / Advance / Emergency Fund / Listing Status Application",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Listing Status Application" },
    },
    {
      path: "/admin/kerisi/m/2722",
      name: "kerisi-cc-m-2722",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "New Application (Portal)",
        breadcrumb: "Credit Control / Advance / Emergency Fund / New Application (Portal)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "New Application (Portal)" },
    },
    {
      path: "/admin/kerisi/m/2729",
      name: "kerisi-cc-m-2729",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Outstanding List",
        breadcrumb: "Credit Control / Debt Monitoring / Reminder / Emergency Fund / Outstanding List",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Outstanding List" },
    },
    {
      path: "/admin/kerisi/m/2771",
      name: "kerisi-cc-m-2771",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "ReAssign or Withdraw From DCA",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / ReAssign or Withdraw From DCA",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "ReAssign or Withdraw From DCA" },
    },
    {
      path: "/admin/kerisi/m/2785",
      name: "kerisi-cc-m-2785",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Assigned Case History",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Assigned Case History",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Assigned Case History" },
    },
    {
      path: "/admin/kerisi/m/2786",
      name: "kerisi-cc-m-2786",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Withdrawal List",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Withdrawal List (old)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Withdrawal List" },
    },
    {
      path: "/admin/kerisi/m/2821",
      name: "kerisi-cc-m-2821",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Address Student",
        breadcrumb: "Credit Control / Debt Monitoring / Reminder / Student / Address Student",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Address Student" },
    },
    {
      path: "/admin/kerisi/m/2843",
      name: "kerisi-cc-m-2843",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Staff Study",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Staff Study / List Of Staff Study",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Staff Study" },
    },
    {
      path: "/admin/kerisi/m/2844",
      name: "kerisi-cc-m-2844",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Staff Study Details",
        breadcrumb: "Credit Control / Breach of Contract (BOC) / Staff Study / Staff Study Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Staff Study Details" },
    },
    {
      path: "/admin/kerisi/m/2871",
      name: "kerisi-cc-m-2871",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Outstanding",
        breadcrumb: "Credit Control / Debt Monitoring / Reminder / Credit Card / List Of Outstanding",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "List of Outstanding" },
    },
    {
      path: "/admin/kerisi/m/2876",
      name: "kerisi-cc-m-2876",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "New Payment In advance",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Refund C12154 / Admin / List of Refund",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "New Payment In advance" },
    },
    {
      path: "/admin/kerisi/m/2882",
      name: "kerisi-cc-m-2882",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "By Invoice",
        breadcrumb: "Credit Control / Report / Listing / By Invoice",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "By Invoice" },
    },
    {
      path: "/admin/kerisi/m/2885",
      name: "kerisi-cc-m-2885",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Refund",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Refund C12154 / Admin / Refund Application",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Refund" },
    },
    {
      path: "/admin/kerisi/m/2890",
      name: "kerisi-cc-m-2890",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Refund Bill",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Refund C12154 / List of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "List of Refund Bill" },
    },
    {
      path: "/admin/kerisi/m/2891",
      name: "kerisi-cc-m-2891",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Report of Refund Bill",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Refund C12154 / Report of Refund Bill",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Report of Refund Bill" },
    },
    {
      path: "/admin/kerisi/m/2893",
      name: "kerisi-cc-m-2893",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Refund C12154 / Payment In Advance Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details" },
    },
    {
      path: "/admin/kerisi/m/2894",
      name: "kerisi-cc-m-2894",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment In Advance Details (Draft)",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Refund C12154 / Payment In Advance Details (Draft)",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Payment In Advance Details (Draft)" },
    },
    {
      path: "/admin/kerisi/m/2899",
      name: "kerisi-cc-m-2899",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Activity Type",
        breadcrumb: "Credit Control / Setup / DCA / Activity Type",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Activity Type" },
    },
    {
      path: "/admin/kerisi/m/2903",
      name: "kerisi-cc-m-2903",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Debtor Profile",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Debtor Profile",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Debtor Profile" },
    },
    {
      path: "/admin/kerisi/m/2917",
      name: "kerisi-cc-m-2917",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Report By DCA",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Report / Report By DCA",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Report By DCA" },
    },
    {
      path: "/admin/kerisi/m/2918",
      name: "kerisi-cc-m-2918",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Report All DCA",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Report / Report All DCA",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Report All DCA" },
    },
    {
      path: "/admin/kerisi/m/2936",
      name: "kerisi-cc-m-2936",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Debt Type",
        breadcrumb: "Credit Control / Setup / DCA / Debt Type",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Debt Type" },
    },
    {
      path: "/admin/kerisi/m/2938",
      name: "kerisi-cc-m-2938",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Commission Rate",
        breadcrumb: "Credit Control / Setup / DCA / Commission Rate",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Commission Rate" },
    },
    {
      path: "/admin/kerisi/m/2961",
      name: "kerisi-cc-m-2961",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Staff Profile",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Staff Profile",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Staff Profile" },
    },
    {
      path: "/admin/kerisi/m/2965",
      name: "kerisi-cc-m-2965",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Statement C12154",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Report / Statement C12154",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Statement C12154" },
    },
    {
      path: "/admin/kerisi/m/2979",
      name: "kerisi-cc-m-2979",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing of Invoice Fully Paid",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Listing of Invoice Fully Paid",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Listing of Invoice Fully Paid" },
    },
    {
      path: "/admin/kerisi/m/2980",
      name: "kerisi-cc-m-2980",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Comment Details",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Comment Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Comment Details" },
    },
    {
      path: "/admin/kerisi/m/2981",
      name: "kerisi-cc-m-2981",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing of Collection",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Listing of Collection",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Listing of Collection" },
    },
    {
      path: "/admin/kerisi/m/2982",
      name: "kerisi-cc-m-2982",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Payment History",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Payment History",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Payment History" },
    },
    {
      path: "/admin/kerisi/m/2997",
      name: "kerisi-cc-m-2997",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Calculate Commission",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Commission / Calculate Commission",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Calculate Commission" },
    },
    {
      path: "/admin/kerisi/m/3008",
      name: "kerisi-cc-m-3008",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Listing of Commission Calculated",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Commission / Listing of Commission Calculated",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Listing of Commission Calculated" },
    },
    {
      path: "/admin/kerisi/m/3014",
      name: "kerisi-cc-m-3014",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Confirm to be Assigned",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Case Assigned / Confirm to be Assigned",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Confirm to be Assigned" },
    },
    {
      path: "/admin/kerisi/m/3030",
      name: "kerisi-cc-m-3030",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "List of Knockoff",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Knockoff C12154 / List of Knockoff",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "List of Knockoff" },
    },
    {
      path: "/admin/kerisi/m/3031",
      name: "kerisi-cc-m-3031",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Knockoff Details",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Knockoff C12154 / Knockoff Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Knockoff Details" },
    },
    {
      path: "/admin/kerisi/m/3083",
      name: "kerisi-cc-m-3083",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Withdraw List",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Withdrawal List",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Withdraw List" },
    },
    {
      path: "/admin/kerisi/m/3084",
      name: "kerisi-cc-m-3084",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Cooling-off Period",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Cooling-off Period",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Cooling-off Period" },
    },
    {
      path: "/admin/kerisi/m/3092",
      name: "kerisi-cc-m-3092",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Monitoring C12154",
        breadcrumb: "Credit Control / Creditor UECSB (C12154) / Knockoff C12154 / Monitoring C12154",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/1809",
        relatedLabel: "Deposit",
      },
      meta: { requiresAuth: true, title: "Monitoring C12154" },
    },
    {
      path: "/admin/kerisi/m/3097",
      name: "kerisi-cc-m-3097",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Report JKTK",
        breadcrumb: "Credit Control / Debt Monitoring / Debt Collector Agent (DCA) / Report / Report JKTK",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Report JKTK" },
    },
    {
      path: "/admin/kerisi/m/3377",
      name: "kerisi-cc-m-3377",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Debtor Ageing Summary",
        breadcrumb: "Credit Control / Report / Debtor Ageing / Debtor Ageing Summary",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Debtor Ageing Summary" },
    },
    {
      path: "/admin/kerisi/m/3378",
      name: "kerisi-cc-m-3378",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Debtor Ageing Details",
        breadcrumb: "Credit Control / Report / Debtor Ageing / Debtor Ageing Details",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Debtor Ageing Details" },
    },
    {
      path: "/admin/kerisi/m/3386",
      name: "kerisi-cc-m-3386",
      component: CreditControlLegacyPlaceholderView,
      props: {
        title: "Advance Ageing Summary",
        breadcrumb: "Credit Control / Report / Advance Ageing / Advance Ageing Summary",
        description:
          "This legacy Credit Control screen is not reproduced in Kerisi20. Use Kerisi Classic for the full workflow.",
        relatedPath: "/admin/kerisi/m/3066",
        relatedLabel: "List of deposit",
      },
      meta: { requiresAuth: true, title: "Advance Ageing Summary" },
    },
    ...kerisiCreditControlHiddenRoutes,
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
    // HIDDEN_PAGE_LEVEL3 Asset (from `HIDDEN_PAGE_LEVEL3.json`; must stay before LEVEL4 Asset routes and `...kerisiAssetHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1671",
      name: "kerisi-asset-registration-donation-special",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Donation / Special Category",
        breadcrumb: "Asset / Asset Registration / Donation / Special Category",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Donation / Special Category" },
    },
    {
      path: "/admin/kerisi/m/2401",
      name: "kerisi-asset-registration-list-hidden",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Registration List",
        breadcrumb: "Asset / Asset Registration / Asset Registration List",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Registration List" },
    },
    // HIDDEN_PAGE_LEVEL4 Asset (were `kerisi-asset-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1553",
      name: "kerisi-asset-profile",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Profile",
        breadcrumb: "Asset / Profile",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Profile" },
    },
    {
      path: "/admin/kerisi/m/1646",
      name: "kerisi-asset-adjustment-cost-depreciation-workflow",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Adjustment Cost/Depreciation Workflow",
        breadcrumb: "Asset / Adjustment Cost/Depreciation Workflow",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Adjustment Cost/Depreciation Workflow" },
    },
    {
      path: "/admin/kerisi/m/2600",
      name: "kerisi-asset-verification-workflow",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Verification Workflow",
        breadcrumb: "Asset / Asset Verification Workflow",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Verification Workflow" },
    },
    {
      path: "/admin/kerisi/m/2699",
      name: "kerisi-asset-cancellation-for-approval",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Cancellation For Approval",
        breadcrumb: "Asset / Asset Cancellation For Approval",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Cancellation For Approval" },
    },
    {
      path: "/admin/kerisi/m/2851",
      name: "kerisi-asset-report-kewpa-24",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Kew Pa 24 - Kenyataan Tawaran Tender Pelupusan Aset Alih",
        breadcrumb:
          "Asset / Report / Report KewPa / Kew Pa 24 - Kenyataan Tawaran Tender Pelupusan Aset Alih",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Kew Pa 24 - Kenyataan Tawaran Tender Pelupusan Aset Alih" },
    },
    {
      path: "/admin/kerisi/m/2856",
      name: "kerisi-asset-report-kewpa-26",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Kew Pa 26 - Jadual Tender Pelupusan Aset Alih",
        breadcrumb:
          "Asset / Report / Report KewPa / Kew Pa 26 - Jadual Tender Pelupusan Aset Alih",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Kew Pa 26 - Jadual Tender Pelupusan Aset Alih" },
    },
    {
      path: "/admin/kerisi/m/2858",
      name: "kerisi-asset-report-kewpa-28",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Kew Pa 28 - Borang Sebut Harga Pelupusan Aset Alih",
        breadcrumb:
          "Asset / Report / Report KewPa / Kew Pa 28 - Borang Sebut Harga Pelupusan Aset Alih",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Kew Pa 28 - Borang Sebut Harga Pelupusan Aset Alih" },
    },
    {
      path: "/admin/kerisi/m/2860",
      name: "kerisi-asset-report-kewpa-30",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Kew Pa 30 - Kenyataan Jualan Lelong Aset Alih",
        breadcrumb:
          "Asset / Report / Report KewPa / Kew Pa 30 - Kenyataan Jualan Lelong Aset Alih",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Kew Pa 30 - Kenyataan Jualan Lelong Aset Alih" },
    },
    {
      path: "/admin/kerisi/m/2902",
      name: "kerisi-asset-write-off-workflow",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Write Off Workflow",
        breadcrumb: "Asset / Write Off Workflow",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Write Off Workflow" },
    },
    {
      path: "/admin/kerisi/m/3356",
      name: "kerisi-asset-integration-registration-grn",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Registration GRN/Bill/Journal",
        breadcrumb: "Asset / Asset Registration / Integration / Asset Registration GRN/Bill/Journal",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Registration GRN/Bill/Journal" },
    },
    {
      path: "/admin/kerisi/m/3359",
      name: "kerisi-asset-integration-approval-grn",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Approval GRN/Bill/Journal",
        breadcrumb: "Asset / Asset Registration / Integration / Asset Approval GRN/Bill/Journal",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Approval GRN/Bill/Journal" },
    },
    {
      path: "/admin/kerisi/m/3383",
      name: "kerisi-asset-integration-journal-g-asset",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Journal G-Asset",
        breadcrumb: "Asset / Asset Registration / Integration / Journal G-Asset",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Journal G-Asset" },
    },
    {
      path: "/admin/kerisi/m/3395",
      name: "kerisi-asset-integration-cancellation-g-asset",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Cancellation - G-Asset",
        breadcrumb: "Asset / Asset Registration / Integration / Asset Cancellation - G-Asset",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Cancellation - G-Asset" },
    },
    {
      path: "/admin/kerisi/m/3398",
      name: "kerisi-asset-integration-cancellation-verify-g-asset",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Cancellation For Verification G-Asset",
        breadcrumb: "Asset / Asset Registration / Integration / Asset Cancellation For Verification G-Asset",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Cancellation For Verification G-Asset" },
    },
    {
      path: "/admin/kerisi/m/3399",
      name: "kerisi-asset-integration-cancellation-approval-g-asset",
      component: AssetLegacyPlaceholderView,
      props: {
        title: "Asset Cancellation For Approval G-Asset",
        breadcrumb: "Asset / Asset Registration / Integration / Asset Cancellation For Approval G-Asset",
        description:
          "This legacy asset registration, workflow, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1548",
        relatedLabel: "Open list of asset",
      },
      meta: { requiresAuth: true, title: "Asset Cancellation For Approval G-Asset" },
    },
    ...kerisiAssetHiddenRoutes,
    { path: "/admin/kerisi/m/1544", name: "kerisi-project-monitoring-list", component: ProjectListView, meta: { requiresAuth: true, title: "Project Monitoring / List of Project" } },
    { path: "/admin/kerisi/m/2065", name: "kerisi-project-monitoring-balance", component: ProjectUpdatedBalanceView, meta: { requiresAuth: true, title: "Project Monitoring / Updated Balance" } },
    { path: "/admin/kerisi/m/1841", name: "kerisi-purchasing-status-po-pr", component: StatusPoPrView, meta: { requiresAuth: true, title: "Status PO & PR" } },
    { path: "/admin/kerisi/m/1685", name: "kerisi-purchasing-list-of-vendor", component: ListOfVendorView, meta: { requiresAuth: true, title: "List of Vendor" } },
    // Hidden Purchasing (HIDDEN_PAGE_LEVEL2): vendor wizard shell + legacy forms without BL in this repo.
    {
      path: "/admin/kerisi/m/1824",
      name: "kerisi-purchasing-new-vendor",
      component: ListOfVendorView,
      props: {
        pageBreadcrumb: "Purchasing / New Vendor",
        cardTitle: "New Vendor",
        listBanner:
          "Full vendor registration and approval is not ported here yet. Use Kerisi Classic to create a vendor. The table below is the same read-only directory as List of Vendor.",
        exportPageName: "Purchasing - New Vendor",
      },
      meta: { requiresAuth: true, title: "New Vendor" },
    },
    {
      path: "/admin/kerisi/m/2031",
      name: "kerisi-purchasing-cancel-po-form",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Cancel PO Form",
        description:
          "Purchase order cancellation (full form and workflow) is not migrated. Use Kerisi Classic, or review purchase orders and requisitions under Status PO & PR.",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Open Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Cancel PO Form" },
    },
    {
      path: "/admin/kerisi/m/2043",
      name: "kerisi-purchasing-cancel-po-partial-form",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Cancel PO Partial Form",
        description:
          "Partial PO cancellation is not migrated. Use Kerisi Classic for this screen, or open Status PO & PR for context on existing orders.",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Open Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Cancel PO Partial Form" },
    },
    {
      path: "/admin/kerisi/m/2074",
      name: "kerisi-purchasing-vendor-assessment",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Vendor Assessment",
        description:
          "Vendor assessment questionnaires and scoring are not migrated. Use Kerisi Classic for assessments tied to goods receipt (GRN).",
        relatedPath: "/admin/kerisi/m/1685",
        relatedLabel: "Open List of Vendor",
      },
      meta: { requiresAuth: true, title: "Vendor Assessment" },
    },
    {
      path: "/admin/kerisi/m/2524",
      name: "kerisi-purchasing-vendor-assessment-wpn",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Vendor Assessment WPN",
        breadcrumb: "Purchasing / Vendor Assessment WPN",
        description:
          "Work progress note (WPN) vendor assessment is not migrated. Use Kerisi Classic for the WPN assessment flow.",
        relatedPath: "/admin/kerisi/m/1685",
        relatedLabel: "Open List of Vendor",
      },
      meta: { requiresAuth: true, title: "Vendor Assessment WPN" },
    },
    {
      path: "/admin/kerisi/m/2083",
      name: "kerisi-purchasing-wpn-cancel-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Work Progress Note Cancel Detail",
        description:
          "WPN cancellation detail grids and approvals are not migrated. Use Kerisi Classic for this workflow.",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Open Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Work Progress Note Cancel Detail" },
    },
    {
      path: "/admin/kerisi/m/2661",
      name: "kerisi-purchasing-agreement-details",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Agreement Details",
        description:
          "Purchasing agreement detail forms are not migrated. Use Kerisi Classic for agreement maintenance.",
        relatedPath: "/admin/kerisi/m/1685",
        relatedLabel: "Open List of Vendor",
      },
      meta: { requiresAuth: true, title: "Agreement Details" },
    },
    { path: "/admin/kerisi/m/1031", name: "kerisi-student-finance-ptptn-data", component: PtptnDataView, meta: { requiresAuth: true, title: "PTPTN Data" } },
    { path: "/admin/kerisi/m/2636", name: "kerisi-student-finance-list-of-offered", component: OfferedStudentView, meta: { requiresAuth: true, title: "List of Offered" } },
    { path: "/admin/kerisi/m/1023", name: "kerisi-student-finance-invoice", component: InvoiceListView, meta: { requiresAuth: true, title: "Invoice" } },
    {
      path: "/admin/kerisi/m/2897",
      name: "kerisi-student-finance-manual-invoice-listing",
      component: ManualInvoiceListingView,
      meta: { requiresAuth: true, title: "Manual Invoice Listing" },
    },
    {
      path: "/admin/kerisi/m/2898",
      name: "kerisi-student-finance-manual-invoice-form",
      component: ManualInvoiceFormView,
      meta: { requiresAuth: true, title: "Manual Invoice Form" },
    },
    { path: "/admin/kerisi/m/1231", name: "kerisi-student-finance-invoice-generation", component: StudentInvoiceGenerationView, meta: { requiresAuth: true, title: "Generate Student Invoice" } },
    // Sponsor sub-section (parent menuId 1149) — pages migrated from
    // PAGE_MENUID1019_LEVEL3.json (PAGEIDs 1669, 1231, 845, 1218, 1954).
    { path: "/admin/kerisi/m/2020", name: "kerisi-student-finance-sponsor-advance-payment", component: AdvancePaymentView, meta: { requiresAuth: true, title: "Advance Payment" } },
    { path: "/admin/kerisi/m/1507", name: "kerisi-student-finance-sponsor-ptptn", component: SponsorPtptnView, meta: { requiresAuth: true, title: "PTPTN" } },
    { path: "/admin/kerisi/m/1025", name: "kerisi-student-finance-sponsor-profile", component: SponsorProfileView, meta: { requiresAuth: true, title: "Sponsor Profile" } },
    { path: "/admin/kerisi/m/1491", name: "kerisi-student-finance-sponsor-invoice-generation", component: SponsorInvoiceGenerationView, meta: { requiresAuth: true, title: "Sponsor Invoice Generation" } },
    { path: "/admin/kerisi/m/2390", name: "kerisi-student-finance-sponsor-student-journal-approval", component: StudentJournalApprovalView, meta: { requiresAuth: true, title: "Student Journal Approval" } },
    // HIDDEN_PAGE_LEVEL3 Student Finance (`HIDDEN_PAGE_LEVEL3.json`; inlined LEVEL4 stubs follow LEVEL3 placeholders in this section).
    {
      path: "/admin/kerisi/m/1034",
      name: "kerisi-sf-barring",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Barring",
        breadcrumb: "Student Finance / Barring / Barring",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Barring" },
    },
    {
      path: "/admin/kerisi/m/1062",
      name: "kerisi-sf-form-invoice",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Invoice Form",
        breadcrumb: "Student Finance / form / invoice form",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Invoice Form" },
    },
    {
      path: "/admin/kerisi/m/1065",
      name: "kerisi-sf-activity-tag-sponsor-masri",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Tag Sponsor to Student (Masri)",
        breadcrumb: "Student Finance / Activity / Tag Sponsor to Student (Masri)",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Tag Sponsor to Student (Masri)" },
    },
    {
      path: "/admin/kerisi/m/1068",
      name: "kerisi-sf-sponsor-invoice-form",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Invoice Form",
        breadcrumb: "Student Finance / Sponsor / Sponsor Details",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1025",
        relatedLabel: "Open sponsor profile",
      },
      meta: { requiresAuth: true, title: "Invoice Form" },
    },
    {
      path: "/admin/kerisi/m/1076",
      name: "kerisi-sf-activity-process-data",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Process Data",
        breadcrumb: "Student Finance / Activity / Process Data",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Process Data" },
    },
    {
      path: "/admin/kerisi/m/1077",
      name: "kerisi-sf-activity-generate-invoice",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Generate Invoice",
        breadcrumb: "Student Finance / Activity / Generate Invoice",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Generate Invoice" },
    },
    {
      path: "/admin/kerisi/m/1078",
      name: "kerisi-sf-activity-export-data",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Export Data",
        breadcrumb: "Student Finance / Activity / Export Data",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Export Data" },
    },
    {
      path: "/admin/kerisi/m/1079",
      name: "kerisi-sf-activity-generate-refund",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Generate Data for Refund",
        breadcrumb: "Student Finance / Activity / Generate Data for Refund",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Generate Data for Refund" },
    },
    {
      path: "/admin/kerisi/m/1080",
      name: "kerisi-sf-activity-generate-renewal",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Generate Data for Inv. Renewal",
        breadcrumb: "Student Finance / Activity / Generate Data for Inv. Renewal",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Generate Data for Inv. Renewal" },
    },
    {
      path: "/admin/kerisi/m/1478",
      name: "kerisi-sf-sponsor-student",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Sponsor Student",
        breadcrumb: "Student Finance / Sponsor / Sponsor Student",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1025",
        relatedLabel: "Open sponsor profile",
      },
      meta: { requiresAuth: true, title: "Sponsor Student" },
    },
    {
      path: "/admin/kerisi/m/1479",
      name: "kerisi-sf-sponsor-student-details",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Sponsor Student Details",
        breadcrumb: "Student Finance / Sponsor / Sponsor Student Details",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1025",
        relatedLabel: "Open sponsor profile",
      },
      meta: { requiresAuth: true, title: "Sponsor Student Details" },
    },
    {
      path: "/admin/kerisi/m/1492",
      name: "kerisi-sf-activity-approval-invoice",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Approval For Invoice",
        breadcrumb: "Student Finance / Activity / Approval For Invoice",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Approval For Invoice" },
    },
    {
      path: "/admin/kerisi/m/1501",
      name: "kerisi-sf-activity-tag-sponsor",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Tag Sponsor to Student",
        breadcrumb: "Student Finance / Activity / Tag Sponsor to Student",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Tag Sponsor to Student" },
    },
    {
      path: "/admin/kerisi/m/1502",
      name: "kerisi-sf-sponsor-v2-sfsp-tsts",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "V2_SFSP_TSTS_FORM",
        breadcrumb: "Student Finance / Sponsor / V2_SFSP_TSTS_FORM",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1025",
        relatedLabel: "Open sponsor profile",
      },
      meta: { requiresAuth: true, title: "V2_SFSP_TSTS_FORM" },
    },
    {
      path: "/admin/kerisi/m/1503",
      name: "kerisi-sf-sponsor-details",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Sponsor Details",
        breadcrumb: "Student Finance / Sponsor / Sponsor Details",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1025",
        relatedLabel: "Open sponsor profile",
      },
      meta: { requiresAuth: true, title: "Sponsor Details" },
    },
    {
      path: "/admin/kerisi/m/1525",
      name: "kerisi-sf-form-fee-structure",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Fee Structure Form",
        breadcrumb: "Student Finance / form / Fee Structure Form",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Fee Structure Form" },
    },
    {
      path: "/admin/kerisi/m/1536",
      name: "kerisi-sf-sponsor-sfsi-approval-invoice",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "SFSI Approval For Invoice",
        breadcrumb: "Student Finance / Sponsor / SFSI Approval For Invoice",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1025",
        relatedLabel: "Open sponsor profile",
      },
      meta: { requiresAuth: true, title: "SFSI Approval For Invoice" },
    },
    {
      path: "/admin/kerisi/m/1569",
      name: "kerisi-sf-setup-discount-form",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "V2_SFS_DISCOUNT_FORM",
        breadcrumb: "Student Finance / setup / V2_SFS_DISCOUNT_FORM",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "V2_SFS_DISCOUNT_FORM" },
    },
    {
      path: "/admin/kerisi/m/1658",
      name: "kerisi-sf-letter-cover-invoice",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Cover Letter Invoice",
        breadcrumb: "Student Finance / Letter / 1.Cover Letter Invoice",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Cover Letter Invoice" },
    },
    {
      path: "/admin/kerisi/m/1765",
      name: "kerisi-sf-letter-confirm-debt",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Confirmation of Student Debt",
        breadcrumb: "Student Finance / Letter / 2.Confirmation of Student Debt",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Confirmation of Student Debt" },
    },
    {
      path: "/admin/kerisi/m/1768",
      name: "kerisi-sf-letter-sponsorship-status",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Student Sponsorship Status",
        breadcrumb: "Student Finance / Letter / 3.Student Sponsorship Status",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Student Sponsorship Status" },
    },
    {
      path: "/admin/kerisi/m/1769",
      name: "kerisi-sf-letter-study-fee",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Study and Fee Information",
        breadcrumb: "Student Finance / Letter / 4.Study and Fee Information",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Study and Fee Information" },
    },
    {
      path: "/admin/kerisi/m/1770",
      name: "kerisi-sf-letter-confirm-payment",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Confirmation of Payment Received",
        breadcrumb: "Student Finance / Letter / 5.Confirmation of Payment Received",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Confirmation of Payment Received" },
    },
    {
      path: "/admin/kerisi/m/1775",
      name: "kerisi-sf-letter-outstanding",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Outstanding Letter",
        breadcrumb: "Student Finance / Letter / 6.Outstanding Letter",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Outstanding Letter" },
    },
    {
      path: "/admin/kerisi/m/1776",
      name: "kerisi-sf-letter-tuition",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Tuition Fees",
        breadcrumb: "Student Finance / Letter / 7.Tuition Fees",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Tuition Fees" },
    },
    {
      path: "/admin/kerisi/m/1912",
      name: "kerisi-sf-form-non-fee-structure",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Non-fee Structure Form",
        breadcrumb: "Student Finance / form / Non-fee Structure Form",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Non-fee Structure Form" },
    },
    {
      path: "/admin/kerisi/m/2011",
      name: "kerisi-sf-report-bill-payment-bank",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Bill Payment Data From Bank",
        breadcrumb: "Student Finance / Report / Bill Payment Data From Bank",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Bill Payment Data From Bank" },
    },
    {
      path: "/admin/kerisi/m/2021",
      name: "kerisi-sf-sponsor-advance-process",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Advance Payment Process",
        breadcrumb: "Student Finance / Sponsor / Advance Payment Process",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2020",
        relatedLabel: "Open advance payment",
      },
      meta: { requiresAuth: true, title: "Advance Payment Process" },
    },
    {
      path: "/admin/kerisi/m/2049",
      name: "kerisi-sf-sponsor-advance-journal-approval",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Advance Payment Journal Approval",
        breadcrumb: "Student Finance / Sponsor / Advance Payment Journal Approval",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2020",
        relatedLabel: "Open advance payment",
      },
      meta: { requiresAuth: true, title: "Advance Payment Journal Approval" },
    },
    {
      path: "/admin/kerisi/m/2076",
      name: "kerisi-sf-sponsor-account-statement",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Sponsorship Account Statement",
        breadcrumb: "Student Finance / Sponsor / Sponsorship Account Statement",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1025",
        relatedLabel: "Open sponsor profile",
      },
      meta: { requiresAuth: true, title: "Sponsorship Account Statement" },
    },
    {
      path: "/admin/kerisi/m/2169",
      name: "kerisi-sf-sponsor-journal-knockoff",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Journal Advance Knockoff",
        breadcrumb: "Student Finance / Sponsor / Journal Advance Knockoff",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2020",
        relatedLabel: "Open advance payment",
      },
      meta: { requiresAuth: true, title: "Journal Advance Knockoff" },
    },
    {
      path: "/admin/kerisi/m/2170",
      name: "kerisi-sf-sponsor-journal-knockoff-details",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Journal Advance Knockoff Details",
        breadcrumb: "Student Finance / Sponsor / Journal Advance Knockoff Details",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2020",
        relatedLabel: "Open advance payment",
      },
      meta: { requiresAuth: true, title: "Journal Advance Knockoff Details" },
    },
    {
      path: "/admin/kerisi/m/2354",
      name: "kerisi-sf-sponsor-advance-transfer-student",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Advance Payment Transfer To Student",
        breadcrumb: "Student Finance / Sponsor / Advance Payment Transfer To Student",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2020",
        relatedLabel: "Open advance payment",
      },
      meta: { requiresAuth: true, title: "Advance Payment Transfer To Student" },
    },
    {
      path: "/admin/kerisi/m/2395",
      name: "kerisi-sf-sponsor-journal-approval-one-off",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Student Journal Approval by One Off",
        breadcrumb: "Student Finance / Sponsor / Student Journal Approval by One Off",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2390",
        relatedLabel: "Open student journal approval",
      },
      meta: { requiresAuth: true, title: "Student Journal Approval by One Off" },
    },
    {
      path: "/admin/kerisi/m/2560",
      name: "kerisi-sf-barring-student-info",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Student Barring Information",
        breadcrumb: "Student Finance / Barring / Student Barring Information",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Student Barring Information" },
    },
    {
      path: "/admin/kerisi/m/2602",
      name: "kerisi-sf-barring-registration-info",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Student Registration Barring Information",
        breadcrumb: "Student Finance / Barring / Student Registration Barring Information",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Student Registration Barring Information" },
    },
    {
      path: "/admin/kerisi/m/2646",
      name: "kerisi-sf-barring-report",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Report Barring",
        breadcrumb: "Student Finance / Barring / Report Barring",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Report Barring" },
    },
    {
      path: "/admin/kerisi/m/2745",
      name: "kerisi-sf-barring-report-alt",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Report Barring",
        breadcrumb: "Student Finance / Barring / Report Barring",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Report Barring" },
    },
    {
      path: "/admin/kerisi/m/2764",
      name: "kerisi-sf-barring-report-offered",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Report Barring Student Offered",
        breadcrumb: "Student Finance / Barring / Report Barring Student Offered",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2636",
        relatedLabel: "Open list of offered",
      },
      meta: { requiresAuth: true, title: "Report Barring Student Offered" },
    },
    // HIDDEN_PAGE_LEVEL4 Student Finance (were `kerisi-student-finance-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1512",
      name: "kerisi-sf-view-profile",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "View Profile",
        breadcrumb: "Student Finance / View Profile",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "View Profile" },
    },
    {
      path: "/admin/kerisi/m/1734",
      name: "kerisi-sf-bank-account-workflow",
      component: StudentFinanceLegacyPlaceholderView,
      props: {
        title: "Bank Account Workflow",
        breadcrumb: "Student Finance / Bank Account Workflow",
        description:
          "This legacy Student Finance workflow, letter, or report is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Bank Account Workflow" },
    },
    { path: "/admin/kerisi/m/1877", name: "kerisi-investment-list-of-accrual", component: ListOfAccrualView, meta: { requiresAuth: true, title: "List of Accrual" } },
    { path: "/admin/kerisi/m/2808", name: "kerisi-investment-summary-list", component: SummaryListInvestmentsView, meta: { requiresAuth: true, title: "Summary List of Investments" } },
    { path: "/admin/kerisi/m/1448", name: "kerisi-investment-list", component: ListOfInvestmentsView, meta: { requiresAuth: true, title: "List of Investments" } },
    { path: "/admin/kerisi/m/3485", name: "kerisi-investment-to-be-withdrawn", component: InvestmentToBeWithdrawnView, meta: { requiresAuth: true, title: "Investment to be Withdrawn" } },
    { path: "/admin/kerisi/m/1446", name: "kerisi-investment-accrual", component: InvestmentAccrualView, meta: { requiresAuth: true, title: "Accrual" } },
    { path: "/admin/kerisi/m/1475", name: "kerisi-investment-generate-schedule", component: InvestmentGenerateScheduleView, meta: { requiresAuth: true, title: "Generate Schedule" } },
    { path: "/admin/kerisi/m/1458", name: "kerisi-investment-monitoring", component: InvestmentMonitoringView, meta: { requiresAuth: true, title: "Investment Monitoring" } },
    // HIDDEN_PAGE_LEVEL3 Investment (`HIDDEN_PAGE_LEVEL3.json`; inlined LEVEL4 stubs follow LEVEL3 placeholders in this section).
    {
      path: "/admin/kerisi/m/1406",
      name: "kerisi-investment-setup-institution",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Institution Information",
        breadcrumb: "Investment / Setup / Institution Information",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Institution Information" },
    },
    {
      path: "/admin/kerisi/m/1419",
      name: "kerisi-investment-setup-create-type-gl",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Create Investment Type & GL Code",
        breadcrumb: "Investment / Setup / Create Investment Type & GL Code",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Create Investment Type & GL Code" },
    },
    {
      path: "/admin/kerisi/m/1426",
      name: "kerisi-investment-setup-update-type-gl",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Update Investment Type & GL Code",
        breadcrumb: "Investment / Setup / Update Investment Type & GL Code",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Update Investment Type & GL Code" },
    },
    {
      path: "/admin/kerisi/m/1430",
      name: "kerisi-investment-setup-fund-type",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Fund Type",
        breadcrumb: "Investment / Setup / Fund Type",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Fund Type" },
    },
    {
      path: "/admin/kerisi/m/1434",
      name: "kerisi-investment-setup-gl-code",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "GL Code Setup",
        breadcrumb: "Investment / Setup / GL Code Setup",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "GL Code Setup" },
    },
    {
      path: "/admin/kerisi/m/1447",
      name: "kerisi-investment-setup-update-institution",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Update Institution Information",
        breadcrumb: "Investment / Setup / Update Institution Information",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Update Institution Information" },
    },
    {
      path: "/admin/kerisi/m/1449",
      name: "kerisi-investment-application-new",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "New Application",
        breadcrumb: "Investment / Investment Application / New Application",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "New Application" },
    },
    {
      path: "/admin/kerisi/m/1473",
      name: "kerisi-investment-application-draft",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "List of Investment - Draft",
        breadcrumb: "Investment / Investment Application / List of Investment - Draft",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "List of Investment - Draft" },
    },
    {
      path: "/admin/kerisi/m/1477",
      name: "kerisi-investment-application-rejected",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "List of Investment - Rejected",
        breadcrumb: "Investment / Investment Application / List of Investment - Rejected",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "List of Investment - Rejected" },
    },
    {
      path: "/admin/kerisi/m/3233",
      name: "kerisi-investment-application-approval",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Approval for Investment",
        breadcrumb: "Investment / Investment Application / Approval for Investment",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Approval for Investment" },
    },
    // HIDDEN_PAGE_LEVEL4 Investment (were `kerisi-investment-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1445",
      name: "kerisi-investment-generate-bill",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Generate Bill",
        breadcrumb: "Investment / Generate Bill",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Generate Bill" },
    },
    {
      path: "/admin/kerisi/m/1457",
      name: "kerisi-investment-approval",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Approval for Investment",
        breadcrumb: "Investment / Approval for Investment",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Approval for Investment" },
    },
    {
      path: "/admin/kerisi/m/1483",
      name: "kerisi-investment-approval-withdrawal",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "Approval for Withdrawal",
        breadcrumb: "Investment / Approval for Withdrawal",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1448",
        relatedLabel: "Open list of investments",
      },
      meta: { requiresAuth: true, title: "Approval for Withdrawal" },
    },
    {
      path: "/admin/kerisi/m/1878",
      name: "kerisi-investment-view-accrual",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "View Accrual",
        breadcrumb: "Investment / View Accrual",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1877",
        relatedLabel: "Open list of accrual",
      },
      meta: { requiresAuth: true, title: "View Accrual" },
    },
    {
      path: "/admin/kerisi/m/2820",
      name: "kerisi-investment-view-summary",
      component: InvestmentLegacyPlaceholderView,
      props: {
        title: "View Investment Summary",
        breadcrumb: "Investment / View Investment Summary",
        description:
          "This legacy investment setup, application, or approval screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2808",
        relatedLabel: "Open summary list of investments",
      },
      meta: { requiresAuth: true, title: "View Investment Summary" },
    },
    { path: "/admin/kerisi/m/2056", name: "kerisi-gl-journal-listing", component: JournalListingView, meta: { requiresAuth: true, title: "Journal Listing" } },
    { path: "/admin/kerisi/m/3287", name: "kerisi-gl-year-month", component: GlYearMonthView, meta: { requiresAuth: true, title: "List of Year and Month" } },
    { path: "/admin/kerisi/m/1409", name: "kerisi-gl-posting-to-tb", component: PostingToTbView, meta: { requiresAuth: true, title: "Posting to GL (TB)" } },
    { path: "/admin/kerisi/m/2519", name: "kerisi-gl-listing", component: GeneralLedgerListingView, meta: { requiresAuth: true, title: "General Ledger Listing" } },
    { path: "/admin/kerisi/m/2089", name: "kerisi-gl-manual-journal-listing", component: ManualJournalListingView, meta: { requiresAuth: true, title: "Manual Journal Listing" } },
    // Hidden GL (HIDDEN_PAGE_LEVEL2) — detail/list aliases and legacy placeholders
    { path: "/admin/kerisi/m/1413", name: "kerisi-gl-posting-to-tb-details", component: PostingToTbView, meta: { requiresAuth: true, title: "Posting to TB Details" } },
    { path: "/admin/kerisi/m/2057", name: "kerisi-gl-journal-listing-details", component: JournalListingView, meta: { requiresAuth: true, title: "Journal Listing Details" } },
    {
      path: "/admin/kerisi/m/1513",
      name: "kerisi-gl-manual-journal",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Manual Journal",
        breadcrumb: "General Ledger / Manual Journal",
        description:
          "The legacy manual journal entry form is not ported. Open the manual journal listing for migrated workflows, or use Kerisi Classic to create or edit journals.",
        relatedPath: "/admin/kerisi/m/2089",
        relatedLabel: "Open manual journal listing",
      },
      meta: { requiresAuth: true, title: "Manual Journal" },
    },
    {
      path: "/admin/kerisi/m/1898",
      name: "kerisi-gl-upload-manual-journal",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Upload Manual Journal",
        breadcrumb: "General Ledger / Upload Manual Journal",
        description: "Bulk upload for manual journals is not implemented in Kerisi20. Use Kerisi Classic, or review posted lines in manual journal listing.",
        relatedPath: "/admin/kerisi/m/2089",
        relatedLabel: "Open manual journal listing",
      },
      meta: { requiresAuth: true, title: "Upload Manual Journal" },
    },
    {
      path: "/admin/kerisi/m/2707",
      name: "kerisi-gl-listing-posting-error",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Listing Posting to GL (Error)",
        breadcrumb: "General Ledger / Listing Posting to GL (Error)",
        description:
          "The error-queue listing for failed GL posting is not ported. Use Kerisi Classic to resolve errors, or review posting batches on Posting to GL (TB).",
        relatedPath: "/admin/kerisi/m/1409",
        relatedLabel: "Open posting to GL (TB)",
      },
      meta: { requiresAuth: true, title: "Listing Posting to GL (Error)" },
    },
    {
      path: "/admin/kerisi/m/3043",
      name: "kerisi-gl-transaction-report-ptj",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Transaction Report PTJ",
        breadcrumb: "General Ledger / Transaction Report PTJ",
        description: "This PTJ transaction report is not reproduced in Kerisi20. Use Kerisi Classic for the report, or explore the general ledger listing for account activity.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Transaction Report PTJ" },
    },
    // HIDDEN_PAGE_LEVEL3 Loan (`HIDDEN_PAGE_LEVEL3.json` overlaps; inlined LEVEL4 stubs follow this block).
    {
      path: "/admin/kerisi/m/1586",
      name: "kerisi-loan-portal-profile-form",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Profile Form",
        breadcrumb: "Portal / Loan / Loan Profile Form",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Profile Form" },
    },
    {
      path: "/admin/kerisi/m/1627",
      name: "kerisi-loan-portal-payslip",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Payslip",
        breadcrumb: "Portal / Loan / Payslip",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Payslip" },
    },
    {
      path: "/admin/kerisi/m/1640",
      name: "kerisi-loan-portal-update-information",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Update Loan Information",
        breadcrumb: "Portal / Loan / Update Loan Information",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Update Loan Information" },
    },
    {
      path: "/admin/kerisi/m/2051",
      name: "kerisi-loan-portal-modification",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Modification",
        breadcrumb: "Portal / Loan / Loan Modification",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Modification" },
    },
    {
      path: "/admin/kerisi/m/2052",
      name: "kerisi-loan-portal-modification-workflow",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Modification Workflow",
        breadcrumb: "Portal / Loan / Loan Modification Workflow",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Modification Workflow" },
    },
    {
      path: "/admin/kerisi/m/2484",
      name: "kerisi-loan-cc-student-micro-credit",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Student Micro Credit Loan",
        breadcrumb: "Credit Control / Setup / Student Micro Credit Loan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Student Micro Credit Loan" },
    },
    {
      path: "/admin/kerisi/m/3292",
      name: "kerisi-loan-portal-payment-information",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Form Loan Payment Information",
        breadcrumb: "Portal / Loan / Form Loan Payment Information",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Form Loan Payment Information" },
    },
    // HIDDEN_PAGE_LEVEL4 Loan (were `kerisi-loan-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1230",
      name: "kerisi-loan-payroll-deduction",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Deduction",
        breadcrumb: "Payroll / Staff Profile Information / Deduction / Loan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Deduction" },
    },
    {
      path: "/admin/kerisi/m/1578",
      name: "kerisi-loan-portal-setup-type",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Type",
        breadcrumb: "Portal / Loan / Setup / Loan Type",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Type" },
    },
    {
      path: "/admin/kerisi/m/1579",
      name: "kerisi-loan-portal-setup-blacklist",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Blacklist",
        breadcrumb: "Portal / Loan / Setup / Blacklist",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Blacklist" },
    },
    {
      path: "/admin/kerisi/m/1589",
      name: "kerisi-loan-portal-setup-type-category",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Type Category",
        breadcrumb: "Portal / Loan / Setup / Loan Type Category",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Type Category" },
    },
    {
      path: "/admin/kerisi/m/1596",
      name: "kerisi-loan-portal-setup-item-status",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Item Status",
        breadcrumb: "Portal / Loan / Setup / Item Status",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Item Status" },
    },
    {
      path: "/admin/kerisi/m/1599",
      name: "kerisi-loan-portal-setup-category",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Category",
        breadcrumb: "Portal / Loan / Setup / Loan Category",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Category" },
    },
    {
      path: "/admin/kerisi/m/1600",
      name: "kerisi-loan-portal-setup-new-category",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "New Loan Category",
        breadcrumb: "Portal / Loan / Setup / New Loan Category",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "New Loan Category" },
    },
    {
      path: "/admin/kerisi/m/1603",
      name: "kerisi-loan-portal-report-surat",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Surat",
        breadcrumb: "Portal / Loan / Report / Surat",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Surat" },
    },
    {
      path: "/admin/kerisi/m/1614",
      name: "kerisi-loan-portal-new-application-computer",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "New Application (Computer & Smart Phone)",
        breadcrumb: "Portal / Loan / Loan Application / New Application / Computer & Smart Phone",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "New Application (Computer & Smart Phone)" },
    },
    {
      path: "/admin/kerisi/m/1617",
      name: "kerisi-loan-portal-setup-insurance-type",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Insurance Type",
        breadcrumb: "Portal / Loan / Setup / Insurance Type",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Insurance Type" },
    },
    {
      path: "/admin/kerisi/m/1618",
      name: "kerisi-loan-portal-setup-license-class",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "License Class",
        breadcrumb: "Portal / Loan / Setup / License Class",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "License Class" },
    },
    {
      path: "/admin/kerisi/m/1620",
      name: "kerisi-loan-portal-workflow-computer",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Workflow Loan Application (Computer & Smart Phone)",
        breadcrumb: "Portal / Loan / Loan Application / Workflow Loan Application for Computer & Smart Phone",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Workflow Loan Application (Computer & Smart Phone)" },
    },
    {
      path: "/admin/kerisi/m/1621",
      name: "kerisi-loan-portal-setup-processing-fee",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Processing Fee",
        breadcrumb: "Portal / Loan / Setup / Loan Processing Fee",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Processing Fee" },
    },
    {
      path: "/admin/kerisi/m/1625",
      name: "kerisi-loan-portal-view-application",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "View Loan Application",
        breadcrumb: "Portal / Loan / Loan Application / View Loan Application",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "View Loan Application" },
    },
    {
      path: "/admin/kerisi/m/1629",
      name: "kerisi-loan-portal-workflow-vehicle",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Workflow Loan Application (Vehicle)",
        breadcrumb: "Portal / Loan / Loan Application / Workflow Loan Application for Vehicle",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Workflow Loan Application (Vehicle)" },
    },
    {
      path: "/admin/kerisi/m/1830",
      name: "kerisi-loan-portal-report-bulanan",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Laporan Bulanan",
        breadcrumb: "Portal / Loan / Report / Laporan Bulanan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Laporan Bulanan" },
    },
    {
      path: "/admin/kerisi/m/2095",
      name: "kerisi-loan-portal-report-tahunan",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Laporan Tahunan",
        breadcrumb: "Portal / Loan / Report / Laporan Tahunan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Laporan Tahunan" },
    },
    {
      path: "/admin/kerisi/m/2187",
      name: "kerisi-loan-cc-student-business-setup",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Student Loan Setup",
        breadcrumb: "Credit Control / Setup / Student Business Loan / Student Loan Setup",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Student Loan Setup" },
    },
    {
      path: "/admin/kerisi/m/3132",
      name: "kerisi-loan-portal-report-by-type",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Application by Loan Type",
        breadcrumb: "Portal / Loan / Report / Application by Loan Type",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Application by Loan Type" },
    },
    {
      path: "/admin/kerisi/m/3134",
      name: "kerisi-loan-portal-report-by-committee",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Application Processed by Loan Commitee",
        breadcrumb: "Portal / Loan / Report / Application Processed by Loan Commitee",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Application Processed by Loan Commitee" },
    },
    {
      path: "/admin/kerisi/m/3186",
      name: "kerisi-loan-portal-report-schedule",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "List of Schedule",
        breadcrumb: "Portal / Loan / Report / List of Schedule",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "List of Schedule" },
    },
    {
      path: "/admin/kerisi/m/3193",
      name: "kerisi-loan-portal-report-accrual-posting",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "List of Loan Accrual (Posting)",
        breadcrumb: "Portal / Loan / Report / List of Loan Accrual (Posting)",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "List of Loan Accrual (Posting)" },
    },
    {
      path: "/admin/kerisi/m/3267",
      name: "kerisi-loan-portal-setup-vendor",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Vendor",
        breadcrumb: "Portal / Loan / Setup / Loan Vendor",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Vendor" },
    },
    {
      path: "/admin/kerisi/m/3274",
      name: "kerisi-loan-portal-report-pecahan-caj",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Laporan Pinjaman dan Pecahan Caj Perkhidmatan",
        breadcrumb: "Portal / Loan / Report / Laporan Pinjaman dan Pecahan Caj Perkhidmatan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Laporan Pinjaman dan Pecahan Caj Perkhidmatan" },
    },
    {
      path: "/admin/kerisi/m/3276",
      name: "kerisi-loan-portal-setup-fund",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Fund",
        breadcrumb: "Portal / Loan / Setup / Loan Fund",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Fund" },
    },
    {
      path: "/admin/kerisi/m/3282",
      name: "kerisi-loan-portal-report-komputer-kenderaan",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Laporan Pinjaman (Komputer/Kenderaan)",
        breadcrumb: "Portal / Loan / Report / Laporan Pinjaman <Komputer/Kenderaan /",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Laporan Pinjaman (Komputer/Kenderaan)" },
    },
    {
      path: "/admin/kerisi/m/3293",
      name: "kerisi-loan-portal-report-lampiran-a",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Lampiran A - Penyata Tabung Pusingan",
        breadcrumb: "Portal / Loan / Report / Lampiran A - Penyata Tabung Pusingan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Lampiran A - Penyata Tabung Pusingan" },
    },
    {
      path: "/admin/kerisi/m/3294",
      name: "kerisi-loan-portal-report-lampiran-b",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Lampiran B - Penyata Pengiraan Kelayakan",
        breadcrumb: "Portal / Loan / Report / Lampiran B - Penyata Pengiraan Kelayakan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Lampiran B - Penyata Pengiraan Kelayakan" },
    },
    {
      path: "/admin/kerisi/m/3295",
      name: "kerisi-loan-portal-report-lampiran-c",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Penyata Potongan Gaji (Lampiran C)",
        breadcrumb: "Portal / Loan / Report / Penyata Potongan Gaji (Lampiran C)",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Penyata Potongan Gaji (Lampiran C)" },
    },
    {
      path: "/admin/kerisi/m/3326",
      name: "kerisi-loan-portal-report-balance-dec",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Balance Confirmation as at 31 Dec",
        breadcrumb: "Portal / Loan / Report / Loan Balance Confirmation as at 31 Dec",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Balance Confirmation as at 31 Dec" },
    },
    {
      path: "/admin/kerisi/m/3333",
      name: "kerisi-loan-portal-report-potongan-bulanan",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Laporan Potongan Bulanan",
        breadcrumb: "Portal / Loan / Report / Laporan Potongan Bulanan",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Laporan Potongan Bulanan" },
    },
    {
      path: "/admin/kerisi/m/3334",
      name: "kerisi-loan-portal-report-ageing",
      component: LoanLegacyPlaceholderView,
      props: {
        title: "Loan Ageing",
        breadcrumb: "Portal / Loan / Report / Loan Ageing",
        description:
          "This legacy loan workflow, report, or setup screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin",
        relatedLabel: "Open main dashboard",
      },
      meta: { requiresAuth: true, title: "Loan Ageing" },
    },
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
    // Hidden Setup and Maintenance — HIDDEN_PAGE_LEVEL2–5 (`Menu` starts `Setup and Maintenance>`).
    ...kerisiSetupMaintenanceHiddenRoutes,
    // HIDDEN_PAGE_LEVEL3 Payroll (from `HIDDEN_PAGE_LEVEL3.json`; must stay before LEVEL4 Payroll routes and `...kerisiPayrollHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1216",
      name: "kerisi-payroll-staff-allowance",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Allowance",
        breadcrumb: "Payroll / Staff Profile Information / Allowance",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Allowance" },
    },
    {
      path: "/admin/kerisi/m/1219",
      name: "kerisi-payroll-statutory-information",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Statutory Information",
        breadcrumb: "Payroll / Staff Profile Information / Statutory Information",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Statutory Information" },
    },
    {
      path: "/admin/kerisi/m/1340",
      name: "kerisi-payroll-new-allowance-and-deduction",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "New Allowance And Deduction",
        breadcrumb: "Payroll / Allowance And Deduction / New Allowance And Deduction",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "New Allowance And Deduction" },
    },
    {
      path: "/admin/kerisi/m/1439",
      name: "kerisi-payroll-staff-profile",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Profile",
        breadcrumb: "Payroll / Staff Profile Information / Profile",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Profile" },
    },
    {
      path: "/admin/kerisi/m/1459",
      name: "kerisi-payroll-voucher-registration",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Voucher Registration PY",
        breadcrumb: "Payroll / Salary Crediting / Voucher Registration PY",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Voucher Registration PY" },
    },
    {
      path: "/admin/kerisi/m/1460",
      name: "kerisi-payroll-voucher-registration-details",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Voucher Registration Details PY",
        breadcrumb: "Payroll / Salary Crediting / Voucher Registration Details PY",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Voucher Registration Details PY" },
    },
    {
      path: "/admin/kerisi/m/1466",
      name: "kerisi-payroll-lookup-tax-group",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Tax Group",
        breadcrumb: "Payroll / Lookup / Tax Group",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Tax Group" },
    },
    {
      path: "/admin/kerisi/m/1929",
      name: "kerisi-payroll-journal-new",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Journal New",
        breadcrumb: "Payroll / Salary Crediting / Journal New",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Journal New" },
    },
    {
      path: "/admin/kerisi/m/1931",
      name: "kerisi-payroll-journal-details",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Journal Details",
        breadcrumb: "Payroll / Salary Crediting / Journal Details",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Journal Details" },
    },
    {
      path: "/admin/kerisi/m/1989",
      name: "kerisi-payroll-pcb-calculator",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "PCB Calculator",
        breadcrumb: "Payroll / Report / PCB Calculator",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "PCB Calculator" },
    },
    {
      path: "/admin/kerisi/m/2156",
      name: "kerisi-payroll-report-pengajian-lanjutan",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Penyata Perbelanjaan Pengajian Lanjutan",
        breadcrumb: "Payroll / Report / Penyata Perbelanjaan Pengajian Lanjutan",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Penyata Perbelanjaan Pengajian Lanjutan" },
    },
    {
      path: "/admin/kerisi/m/2503",
      name: "kerisi-payroll-report-integration-allowance",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Integration Allowance",
        breadcrumb: "Payroll / Report / Integration Allowance",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Integration Allowance" },
    },
    {
      path: "/admin/kerisi/m/2541",
      name: "kerisi-payroll-integration-view-other-deduction",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "View Other Deduction",
        breadcrumb: "Payroll / Integration / View Other Deduction",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "View Other Deduction" },
    },
    {
      path: "/admin/kerisi/m/3332",
      name: "kerisi-payroll-payslip-myfis-lite",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Payslip From MyFis Lite",
        breadcrumb: "Payroll / Staff Profile Information / Payslip From MyFis Lite",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Payslip From MyFis Lite" },
    },
    {
      path: "/admin/kerisi/m/3340",
      name: "kerisi-payroll-bonus",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Bonus",
        breadcrumb: "Payroll / Salary Processing / Bonus",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Bonus" },
    },
    {
      path: "/admin/kerisi/m/3444",
      name: "kerisi-payroll-kew8-profile",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Profile",
        breadcrumb: "Payroll / Kew 8 / Profile",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Profile" },
    },
    // HIDDEN_PAGE_LEVEL4 Payroll (were `kerisi-payroll-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1218",
      name: "kerisi-payroll-staff-deduction",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Staff Deduction",
        breadcrumb: "Payroll / Staff Profile Information / Deduction / Staff Deduction",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Staff Deduction" },
    },
    {
      path: "/admin/kerisi/m/1221",
      name: "kerisi-payroll-zakat-pcb",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Zakat/PCB",
        breadcrumb: "Payroll / Staff Profile Information / Deduction / Zakat/PCB",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Zakat/PCB" },
    },
    {
      path: "/admin/kerisi/m/1222",
      name: "kerisi-payroll-others-deduction",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Others Deduction",
        breadcrumb: "Payroll / Staff Profile Information / Deduction / Others Deduction",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Others Deduction" },
    },
    {
      path: "/admin/kerisi/m/1282",
      name: "kerisi-payroll-advance-deduction",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Advance Deduction",
        breadcrumb: "Payroll / Staff Profile Information / Deduction / Advance Deduction",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Advance Deduction" },
    },
    {
      path: "/admin/kerisi/m/2025",
      name: "kerisi-payroll-autopay-bank",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Generate Autopay File - Bank",
        breadcrumb: "Payroll / Salary Crediting / Generate Autopay File / Bank",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Generate Autopay File - Bank" },
    },
    {
      path: "/admin/kerisi/m/2171",
      name: "kerisi-payroll-zakat-waqaf-portal",
      component: PayrollLegacyPlaceholderView,
      props: {
        title: "Zakat / Waqaf Deduction Application Portal",
        breadcrumb:
          "Payroll / Integration / Zakat / Waqaf Deduction Application Portal / Zakat / Waqaf Deduction Application Portal",
        description:
          "This legacy Payroll setup, salary-crediting, integration, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Zakat / Waqaf Deduction Application Portal" },
    },
    ...kerisiPayrollHiddenRoutes,
    // HIDDEN_PAGE_LEVEL3 Portal (from `HIDDEN_PAGE_LEVEL3.json`; must stay before LEVEL4 Portal routes and `...kerisiPortalHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1195",
      name: "kerisi-portal-work-order-instruction",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Instruction Information",
        breadcrumb: "Portal / Work Order / Instruction Information",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Instruction Information" },
    },
    {
      path: "/admin/kerisi/m/1416",
      name: "kerisi-portal-payslip-generate",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Generate Payslip",
        breadcrumb: "Portal / Payslip / Generate Payslip",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Generate Payslip" },
    },
    {
      path: "/admin/kerisi/m/1675",
      name: "kerisi-portal-overtime-edit",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Edit Overtime Claim Application",
        breadcrumb: "Portal / Overtime Claim / Edit Overtime Claim Application",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Edit Overtime Claim Application" },
    },
    {
      path: "/admin/kerisi/m/1712",
      name: "kerisi-portal-overtime-workflow",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Workflow Overtime Claim Application",
        breadcrumb: "Portal / Overtime Claim / Workflow Overtime Claim Application",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Workflow Overtime Claim Application" },
    },
    {
      path: "/admin/kerisi/m/1842",
      name: "kerisi-portal-financial-statement",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Statement of Account",
        breadcrumb: "Portal / Financial Information / Statement of Account",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Open debtors statement",
      },
      meta: { requiresAuth: true, title: "Statement of Account" },
    },
    {
      path: "/admin/kerisi/m/1844",
      name: "kerisi-portal-financial-outstanding",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Outstanding Invoice",
        breadcrumb: "Portal / Financial Information / Outstanding Invoice",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Open debtors statement",
      },
      meta: { requiresAuth: true, title: "Outstanding Invoice" },
    },
    {
      path: "/admin/kerisi/m/1846",
      name: "kerisi-portal-financial-cart",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Cart",
        breadcrumb: "Portal / Financial Information / Cart",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Open debtors statement",
      },
      meta: { requiresAuth: true, title: "Cart" },
    },
    {
      path: "/admin/kerisi/m/1890",
      name: "kerisi-portal-financial-sponsor-details",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Sponsor Details",
        breadcrumb: "Portal / Financial Information / Sponsor Details",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Open debtors statement",
      },
      meta: { requiresAuth: true, title: "Sponsor Details" },
    },
    {
      path: "/admin/kerisi/m/1913",
      name: "kerisi-portal-work-order-list",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "List of Work Order Staff",
        breadcrumb: "Portal / Work Order / List of Work Order Staff",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "List of Work Order Staff" },
    },
    {
      path: "/admin/kerisi/m/2645",
      name: "kerisi-portal-tp1-workflow",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Workflow TP1 Application",
        breadcrumb: "Portal / TP1 / Workflow TP1 Application",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Workflow TP1 Application" },
    },
    {
      path: "/admin/kerisi/m/2914",
      name: "kerisi-portal-stock-new-application-workflow",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "New Application Workflow",
        breadcrumb: "Portal / Stock Application / New Application Workflow",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "New Application Workflow" },
    },
    // HIDDEN_PAGE_LEVEL4 Portal (were `kerisi-portal-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1708",
      name: "kerisi-portal-overtime-setup-limit",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Overtime Limit",
        breadcrumb: "Portal / Overtime Claim / Setup / Overtime Limit",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Overtime Limit" },
    },
    {
      path: "/admin/kerisi/m/1925",
      name: "kerisi-portal-overtime-setup-working-hours",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Working Hours",
        breadcrumb: "Portal / Overtime Claim / Setup / Working Hours",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Working Hours" },
    },
    {
      path: "/admin/kerisi/m/1948",
      name: "kerisi-portal-overtime-setup-position",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Position Allowed to Overtime Claim",
        breadcrumb: "Portal / Overtime Claim / Setup / Position Allowed to Overtime Claim",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Position Allowed to Overtime Claim" },
    },
    {
      path: "/admin/kerisi/m/2420",
      name: "kerisi-portal-advance-staff-form",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Advance Staff Form",
        breadcrumb: "Portal / Advance Staff / Declaration / Manage / Advance Staff Form",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Advance Staff Form" },
    },
    {
      path: "/admin/kerisi/m/2432",
      name: "kerisi-portal-advance-recoupment-approval",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Recoupment Approval",
        breadcrumb: "Portal / Advance Staff / Declaration / Manage / Recoupment Approval",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Recoupment Approval" },
    },
    {
      path: "/admin/kerisi/m/2442",
      name: "kerisi-portal-advance-recoupment-bill",
      component: PortalAdvanceGenerateBillRecoupmentView,
      meta: { requiresAuth: true, title: "Generate Bill Recoupment" },
    },
    {
      path: "/admin/kerisi/m/2467",
      name: "kerisi-portal-advance-generate-accrual",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Generate Accrual",
        breadcrumb: "Portal / Advance Staff / Declaration / Manage / Generate Accrual",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Generate Accrual" },
    },
    {
      path: "/admin/kerisi/m/2539",
      name: "kerisi-portal-advance-new-declaration",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "New Advance Declaration",
        breadcrumb: "Portal / Advance Staff / Declaration / Advance Declaration / New Advance Declaration",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "New Advance Declaration" },
    },
    {
      path: "/admin/kerisi/m/2620",
      name: "kerisi-portal-advance-manual-journal-form",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Manual Journal Form",
        breadcrumb: "Portal / Advance Staff / Declaration / Advance Declaration / Manual Journal Form",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Manual Journal Form" },
    },
    {
      path: "/admin/kerisi/m/2641",
      name: "kerisi-portal-advance-generate-pwr",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Generate PWR",
        breadcrumb: "Portal / Advance Staff / Declaration / Manage / Generate PWR",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Generate PWR" },
    },
    {
      path: "/admin/kerisi/m/2651",
      name: "kerisi-portal-landing-page",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Landing Page",
        breadcrumb: "Portal / Landing Page",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Landing Page" },
    },
    {
      path: "/admin/kerisi/m/2712",
      name: "kerisi-portal-advance-recoupment-details",
      component: PortalAdvanceRecoupmentDetailView,
      props: {
        pageBreadcrumb: "Portal / Advance Staff / Declaration / Manage / Recoupment / Recoup Details",
        cardTitle: "Recoupment details",
      },
      meta: { requiresAuth: true, title: "Recoupment Details" },
    },
    {
      path: "/admin/kerisi/m/2714",
      name: "kerisi-portal-advance-recoupment-status",
      component: PortalAdvanceRecoupmentStatusView,
      meta: { requiresAuth: true, title: "Recoupment Status" },
    },
    {
      path: "/admin/kerisi/m/2716",
      name: "kerisi-portal-advance-recoupment-details-draft",
      component: PortalAdvanceRecoupmentDetailView,
      props: {
        pageBreadcrumb:
          "Portal / Advance Staff / Declaration / Manage / Recoupment / Recoupment Details (Draft)",
        cardTitle: "Recoupment details (draft workflow shell)",
      },
      meta: { requiresAuth: true, title: "Recoupment Details (Draft)" },
    },
    {
      path: "/admin/kerisi/m/2919",
      name: "kerisi-portal-advance-declaration-receipt-form",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Declaration Receipt Form",
        breadcrumb: "Portal / Advance Staff / Declaration / Advance Declaration / Declaration Receipt Form",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Declaration Receipt Form" },
    },
    {
      path: "/admin/kerisi/m/3301",
      name: "kerisi-portal-register-spouse",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Register Information Spouse",
        breadcrumb: "Portal / Register Information Spouse",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Register Information Spouse" },
    },
    {
      path: "/admin/kerisi/m/3305",
      name: "kerisi-portal-register-children",
      component: PortalLegacyPlaceholderView,
      props: {
        title: "Register Information Children",
        breadcrumb: "Portal / Register Information Children",
        description:
          "This legacy Portal workflow, application, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1914",
        relatedLabel: "Open portal staff profile",
      },
      meta: { requiresAuth: true, title: "Register Information Children" },
    },
    ...kerisiPortalHiddenRoutes,
    // HIDDEN_PAGE_LEVEL3 Project Monitoring (`HIDDEN_PAGE_LEVEL3.json`; inlined LEVEL4 stubs follow LEVEL3 placeholders in this section).
    {
      path: "/admin/kerisi/m/1648",
      name: "kerisi-project-monitoring-integration-jpp",
      component: ProjectMonitoringLegacyPlaceholderView,
      props: {
        title: "JPP - List of Project",
        breadcrumb: "Project Monitoring / Integration / JPP",
        description:
          "This legacy Project Monitoring detail or integration screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1544",
        relatedLabel: "List of project",
      },
      meta: { requiresAuth: true, title: "JPP - List of Project" },
    },
    {
      path: "/admin/kerisi/m/1649",
      name: "kerisi-project-monitoring-integration-raiis",
      component: ProjectMonitoringLegacyPlaceholderView,
      props: {
        title: "RAIIS - List of Project",
        breadcrumb: "Project Monitoring / Integration / RAIIS",
        description:
          "This legacy Project Monitoring detail or integration screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1544",
        relatedLabel: "List of project",
      },
      meta: { requiresAuth: true, title: "RAIIS - List of Project" },
    },
    {
      path: "/admin/kerisi/m/1650",
      name: "kerisi-project-monitoring-integration-sam",
      component: ProjectMonitoringLegacyPlaceholderView,
      props: {
        title: "SAM - List of Project",
        breadcrumb: "Project Monitoring / Integration / SAM",
        description:
          "This legacy Project Monitoring detail or integration screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1544",
        relatedLabel: "List of project",
      },
      meta: { requiresAuth: true, title: "SAM - List of Project" },
    },
    {
      path: "/admin/kerisi/m/1692",
      name: "kerisi-project-monitoring-integration-jpp-details",
      component: ProjectMonitoringLegacyPlaceholderView,
      props: {
        title: "JPP Project Details",
        breadcrumb: "Project Monitoring / Integration / JPP Project Details",
        description:
          "This legacy Project Monitoring detail or integration screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1544",
        relatedLabel: "List of project",
      },
      meta: { requiresAuth: true, title: "JPP Project Details" },
    },
    // HIDDEN_PAGE_LEVEL4 Project Monitoring (were `kerisi-project-monitoring-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1597",
      name: "kerisi-project-monitoring-detail",
      component: ProjectMonitoringLegacyPlaceholderView,
      props: {
        title: "Project Detail",
        breadcrumb: "Project Monitoring / Project Detail",
        description:
          "This legacy Project Monitoring detail or integration screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1544",
        relatedLabel: "List of project",
      },
      meta: { requiresAuth: true, title: "Project Detail" },
    },
    {
      path: "/admin/kerisi/m/1641",
      name: "kerisi-project-monitoring-integration-hub",
      component: ProjectMonitoringLegacyPlaceholderView,
      props: {
        title: "Integration",
        breadcrumb: "Project Monitoring / Integration",
        description:
          "This legacy Project Monitoring detail or integration screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1544",
        relatedLabel: "List of project",
      },
      meta: { requiresAuth: true, title: "Integration" },
    },
    // HIDDEN_PAGE_LEVEL3 Account Payable (`HIDDEN_PAGE_LEVEL3.json`; must stay before `...kerisiAccountPayableHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1181",
      name: "kerisi-ap-payment-others-payment",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Others Payment",
        breadcrumb: "Account Payable / Payment / Others Payment",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Others Payment" },
    },
    {
      path: "/admin/kerisi/m/1183",
      name: "kerisi-ap-integration-emergency-fund",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "emergencyFund",
        breadcrumb: "Account Payable / Integration / Emergency Fund",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "emergencyFund" },
    },
    {
      path: "/admin/kerisi/m/1370",
      name: "kerisi-ap-payment-eft-preparation",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "EFT Preparation",
        breadcrumb: "Account Payable / Payment / EFT Preparation",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "EFT Preparation" },
    },
    {
      path: "/admin/kerisi/m/1373",
      name: "kerisi-ap-payment-eft-batching",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "EFT Batching",
        breadcrumb: "Account Payable / Payment / EFT Batching",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "EFT Batching" },
    },
    {
      path: "/admin/kerisi/m/1376",
      name: "kerisi-ap-payment-eft-processing",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Processing EFT",
        breadcrumb: "Account Payable / Payment / EFT PROCESSING",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Processing EFT" },
    },
    {
      path: "/admin/kerisi/m/1380",
      name: "kerisi-ap-payment-eft-cancel",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "EFT Cancel",
        breadcrumb: "Account Payable / Payment / EFT Cancel",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "EFT Cancel" },
    },
    {
      path: "/admin/kerisi/m/1381",
      name: "kerisi-ap-payment-eft-transfer",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "EFT Transfer",
        breadcrumb: "Account Payable / Payment / EFT Transfer",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "EFT Transfer" },
    },
    {
      path: "/admin/kerisi/m/1385",
      name: "kerisi-ap-payment-eft-download-text-file",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "EFT Download Text File",
        breadcrumb: "Account Payable / Payment / EFT Download Text File",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "EFT Download Text File" },
    },
    {
      path: "/admin/kerisi/m/1535",
      name: "kerisi-ap-integration-petty-cash-recoupment-approval",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Petty Cash Recoupment Approval",
        breadcrumb: "Account Payable / Integration / Petty Cash Recoupment Approval",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Petty Cash Recoupment Approval" },
    },
    {
      path: "/admin/kerisi/m/1792",
      name: "kerisi-ap-voucher-voucher-listing-detail",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Voucher Listing Detail",
        breadcrumb: "Account Payable / Voucher / Voucher Listing Detail",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Voucher Listing Detail" },
    },
    {
      path: "/admin/kerisi/m/1970",
      name: "kerisi-ap-report-payment-notification-letter",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Payment Notification Letter",
        breadcrumb: "Account Payable / Report / Payment Notification Letter",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Payment Notification Letter" },
    },
    {
      path: "/admin/kerisi/m/1979",
      name: "kerisi-ap-report-ptj-vot-information",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "PTJ VOT Information",
        breadcrumb: "Account Payable / Report / PTJ VOT Information",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "PTJ VOT Information" },
    },
    {
      path: "/admin/kerisi/m/2005",
      name: "kerisi-ap-report-cash-book",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cash Book",
        breadcrumb: "Account Payable / Report / Cash Book",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cash Book" },
    },
    {
      path: "/admin/kerisi/m/2106",
      name: "kerisi-ap-report-pembayaran-bil-dan-tuntutan",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Pembayaran Bil dan Tuntutan",
        breadcrumb: "Account Payable / Report / Pembayaran Bil dan Tuntutan",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Pembayaran Bil dan Tuntutan" },
    },
    {
      path: "/admin/kerisi/m/2109",
      name: "kerisi-ap-bill-bill-cancellation-knockoff-form",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Bill Cancellation @ Knockoff Form",
        breadcrumb: "Account Payable / Bill / Bill Cancellation @ Knockoff Form",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Bill Cancellation @ Knockoff Form" },
    },
    {
      path: "/admin/kerisi/m/2328",
      name: "kerisi-ap-voucher-voucher-replace-details",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Reverse Journal Details",
        breadcrumb: "Account Payable / Voucher / Voucher Replace Details",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Reverse Journal Details" },
    },
    {
      path: "/admin/kerisi/m/2374",
      name: "kerisi-ap-voucher-voucher-details",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Voucher Details",
        breadcrumb: "Account Payable / Voucher / Voucher Details",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Voucher Details" },
    },
    {
      path: "/admin/kerisi/m/2410",
      name: "kerisi-ap-bill-journal-bill-cancel-details",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Reversal Journal Details",
        breadcrumb: "Account Payable / Bill / Journal Bill Cancel Details",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Reversal Journal Details" },
    },
    {
      path: "/admin/kerisi/m/2418",
      name: "kerisi-ap-voucher-voucher-details-2418",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Voucher Details",
        breadcrumb: "Account Payable / Voucher / Voucher Details",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Voucher Details" },
    },
    {
      path: "/admin/kerisi/m/2461",
      name: "kerisi-ap-bill-bill-view",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Bill View",
        breadcrumb: "Account Payable / Bill / Bill View",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Bill View" },
    },
    {
      path: "/admin/kerisi/m/2719",
      name: "kerisi-ap-integration-activity-recoupment",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Activity Recoupment",
        breadcrumb: "Account Payable / Integration / Activity Recoupment",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Activity Recoupment" },
    },
    {
      path: "/admin/kerisi/m/2720",
      name: "kerisi-ap-integration-activity-recoupment-details",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Activity Recoupment Details",
        breadcrumb: "Account Payable / Integration / Activity Recoupment Details",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Activity Recoupment Details" },
    },
    {
      path: "/admin/kerisi/m/2811",
      name: "kerisi-ap-report-laporan-prestasi-pembayaran-bill",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Laporan Prestasi Pembayaran Bill",
        breadcrumb: "Account Payable / Report / Laporan Prestasi Pembayaran Bill",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Laporan Prestasi Pembayaran Bill" },
    },
    {
      path: "/admin/kerisi/m/3101",
      name: "kerisi-ap-report-laporan-jpka",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Laporan JPKA",
        breadcrumb: "Account Payable / Report / Laporan JPKA",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Laporan JPKA" },
    },
    {
      path: "/admin/kerisi/m/3163",
      name: "kerisi-ap-report-semakan",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Semakan",
        breadcrumb: "Account Payable / Report / Semakan",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Semakan" },
    },
    {
      path: "/admin/kerisi/m/3244",
      name: "kerisi-ap-bill-bill-registration-edisi-2",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Bill Registration Form Edisi 2",
        breadcrumb: "Account Payable / Bill / Bill Registration Edisi 2",
        relatedPath: "/admin/kerisi/m/1718",
        relatedLabel: "Bill Registration Form",
      },
      meta: { requiresAuth: true, title: "Bill Registration Form Edisi 2" },
    },
    {
      path: "/admin/kerisi/m/3265",
      name: "kerisi-ap-credit-note-credit-note-cancellation-form",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Credit Note Cancellation Form",
        breadcrumb: "Account Payable / Credit Note / Credit Note Cancellation Form",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Credit Note Cancellation Form" },
    },
    {
      path: "/admin/kerisi/m/3271",
      name: "kerisi-ap-money-transfer-transfer-form",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Transfer Form",
        breadcrumb: "Account Payable / Money Transfer / Transfer Form",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Transfer Form" },
    },
    // HIDDEN_PAGE_LEVEL4 Account Payable (were `kerisi-account-payable-hidden-routes`; migrated to AP placeholder).
    {
      path: "/admin/kerisi/m/1179",
      name: "kerisi-ap-payment-cheque-cheque-processing-old",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cheque PROCESSING (Old)",
        breadcrumb: "Account Payable / Payment / Cheque / Cheque PROCESSING (Old)",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cheque PROCESSING (Old)" },
    },
    {
      path: "/admin/kerisi/m/1288",
      name: "kerisi-ap-payment-cheque-cheque-book-old",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cheque Book (Old)",
        breadcrumb: "Account Payable / Payment / Cheque / Cheque Book (Old)",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cheque Book (Old)" },
    },
    {
      path: "/admin/kerisi/m/1310",
      name: "kerisi-ap-payment-cheque-cheque-book-new-old",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cheque Book New (Old)",
        breadcrumb: "Account Payable / Payment / Cheque / Cheque Book New (Old)",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cheque Book New (Old)" },
    },
    {
      path: "/admin/kerisi/m/1420",
      name: "kerisi-ap-payment-cheque-cheque-preparation-old",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cheque Preparation (Old)",
        breadcrumb: "Account Payable / Payment / Cheque / Cheque Preparation (Old)",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cheque Preparation (Old)" },
    },
    {
      path: "/admin/kerisi/m/1421",
      name: "kerisi-ap-payment-cheque-cheque-printing-old",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cheque Printing (Old)",
        breadcrumb: "Account Payable / Payment / Cheque / Cheque Printing (Old)",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cheque Printing (Old)" },
    },
    {
      path: "/admin/kerisi/m/1422",
      name: "kerisi-ap-payment-cheque-cheque-update-old",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cheque Update (Old)",
        breadcrumb: "Account Payable / Payment / Cheque / Cheque Update (Old)",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cheque Update (Old)" },
    },
    {
      path: "/admin/kerisi/m/1433",
      name: "kerisi-ap-payment-cheque-cheque-listing-old",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Cheque Listing (Old)",
        breadcrumb: "Account Payable / Payment / Cheque / Cheque Listing (Old)",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Cheque Listing (Old)" },
    },
    {
      path: "/admin/kerisi/m/1920",
      name: "kerisi-ap-payment-payroll-payment-payroll-batching-form",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Payroll Batching Form",
        breadcrumb: "Account Payable / Payment / Payroll Payment / Payroll Batching Form",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Payroll Batching Form" },
    },
    {
      path: "/admin/kerisi/m/2335",
      name: "kerisi-ap-integration-credit-card-credit-card-clearance",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Credit Card Clearance",
        breadcrumb: "Account Payable / Integration / Credit Card / Credit Card Clearance",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Credit Card Clearance" },
    },
    {
      path: "/admin/kerisi/m/2338",
      name: "kerisi-ap-integration-credit-card-payment-process",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Payment Process",
        breadcrumb: "Account Payable / Integration / Credit Card / Payment Process",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Payment Process" },
    },
    {
      path: "/admin/kerisi/m/2339",
      name: "kerisi-ap-integration-credit-card-setup",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Credit Card Setup",
        breadcrumb: "Account Payable / Integration / Credit Card / Setup",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Credit Card Setup" },
    },
    {
      path: "/admin/kerisi/m/2341",
      name: "kerisi-ap-integration-credit-card-list-of-card-holder",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "List of Card Holder",
        breadcrumb: "Account Payable / Integration / Credit Card / List of Card Holder",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "List of Card Holder" },
    },
    {
      path: "/admin/kerisi/m/2345",
      name: "kerisi-ap-integration-credit-card-credit-card-statement",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Credit Card Statement",
        breadcrumb: "Account Payable / Integration / Credit Card / Credit Card Statement",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Credit Card Statement" },
    },
    {
      path: "/admin/kerisi/m/2386",
      name: "kerisi-ap-integration-petty-cash-recoupment-journal-recoupment",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Journal Recoupment",
        breadcrumb: "Account Payable / Integration / Petty Cash Recoupment / Journal Recoupment",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Journal Recoupment" },
    },
    {
      path: "/admin/kerisi/m/2387",
      name: "kerisi-ap-integration-petty-cash-recoupment-voucher-recoupment",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Voucher Recoupment",
        breadcrumb: "Account Payable / Integration / Petty Cash Recoupment / Voucher Recoupment",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Voucher Recoupment" },
    },
    {
      path: "/admin/kerisi/m/2554",
      name: "kerisi-ap-payment-payroll-payment-other-payment",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Other Payment",
        breadcrumb: "Account Payable / Payment / Payroll Payment / Other Payment",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Other Payment" },
    },
    {
      path: "/admin/kerisi/m/2564",
      name: "kerisi-ap-payment-payment-rejected-voucher-details",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Voucher Registration Details",
        breadcrumb: "Account Payable / Payment / Payment Rejected / Voucher Details",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Voucher Registration Details" },
    },
    {
      path: "/admin/kerisi/m/3289",
      name: "kerisi-ap-payment-cheque-overall-bank-cheque-transaction",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Overall Bank Cheque Transaction",
        breadcrumb: "Account Payable / Payment / Cheque / Overall Bank Cheque Transaction",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Overall Bank Cheque Transaction" },
    },
    {
      path: "/admin/kerisi/m/3523",
      name: "kerisi-ap-payment-cheque-release-cheque",
      component: AccountPayableLegacyPlaceholderView,
      props: {
        title: "Release Cheque",
        breadcrumb: "Account Payable / Payment / Cheque / Release Cheque",
        relatedPath: "/admin/kerisi/m/2078",
        relatedLabel: "Account Bank Updated",
      },
      meta: { requiresAuth: true, title: "Release Cheque" },
    },
    ...kerisiAccountPayableHiddenRoutes,
    // HIDDEN_PAGE_LEVEL3 Account Receivable (`HIDDEN_PAGE_LEVEL3.json`; must stay before `...kerisiAccountReceivableHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1653",
      name: "kerisi-ar-cheque-new-cheque-registry",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "New Cheque Registry",
        breadcrumb: "Account Receivable / Cheque / New Cheque Registry",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "New Cheque Registry" },
    },
    {
      path: "/admin/kerisi/m/1735",
      name: "kerisi-ar-cheque-return-status-return-cheque",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "List Of Status",
        breadcrumb: "Account Receivable / Cheque Return / Status Return Cheque",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "List Of Status" },
    },
    {
      path: "/admin/kerisi/m/1738",
      name: "kerisi-ar-receipt-receipt-approval",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Receipt Approval",
        breadcrumb: "Account Receivable / Receipt / Receipt Approval",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Receipt Approval" },
    },
    {
      path: "/admin/kerisi/m/1749",
      name: "kerisi-ar-reports-ar-invoice-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "ARr Invoice Data Listing",
        breadcrumb: "Account Receivable / Reports / AR Invoice Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "ARr Invoice Data Listing" },
    },
    {
      path: "/admin/kerisi/m/1750",
      name: "kerisi-ar-reports-receipt-entry-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Receipt Entry Data Entry",
        breadcrumb: "Account Receivable / Reports / Receipt Entry Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Receipt Entry Data Entry" },
    },
    {
      path: "/admin/kerisi/m/1751",
      name: "kerisi-ar-reports-foreign-currency-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Foreign Currency Data Listing",
        breadcrumb: "Account Receivable / Reports / Foreign Currency Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Foreign Currency Data Listing" },
    },
    {
      path: "/admin/kerisi/m/1752",
      name: "kerisi-ar-reports-card-info-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Card Payment Data Listing",
        breadcrumb: "Account Receivable / Reports / Card Info Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Card Payment Data Listing" },
    },
    {
      path: "/admin/kerisi/m/1754",
      name: "kerisi-ar-reports-bankin-slip-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Bank-In Slip Data Listing",
        breadcrumb: "Account Receivable / Reports / Bankin Slip Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Bank-In Slip Data Listing" },
    },
    {
      path: "/admin/kerisi/m/1759",
      name: "kerisi-ar-reports-invoice-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Invoice Listing",
        breadcrumb: "Account Receivable / Reports / Invoice Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Invoice Listing" },
    },
    {
      path: "/admin/kerisi/m/1772",
      name: "kerisi-ar-cheque-return-list-of-cheque-return",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "List Of Cheque Return",
        breadcrumb: "Account Receivable / Cheque Return / List Of Cheque Return",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "List Of Cheque Return" },
    },
    {
      path: "/admin/kerisi/m/1774",
      name: "kerisi-ar-invoice-invoice-processing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Invoice Processing",
        breadcrumb: "Account Receivable / Invoice / Invoice Processing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Invoice Processing" },
    },
    {
      path: "/admin/kerisi/m/1777",
      name: "kerisi-ar-reports-recurring-invoice-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Recurring Invoice Data Listing",
        breadcrumb: "Account Receivable / Reports / Recurring Invoice Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Recurring Invoice Data Listing" },
    },
    {
      path: "/admin/kerisi/m/1781",
      name: "kerisi-ar-reports-cn-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "CN Report",
        breadcrumb: "Account Receivable / Reports / CN Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "CN Report" },
    },
    {
      path: "/admin/kerisi/m/1785",
      name: "kerisi-ar-reports-dc-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "DC data Listing",
        breadcrumb: "Account Receivable / Reports / DC data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "DC data Listing" },
    },
    {
      path: "/admin/kerisi/m/1788",
      name: "kerisi-ar-reports-dn-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "DN data Listing",
        breadcrumb: "Account Receivable / Reports / DN data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "DN data Listing" },
    },
    {
      path: "/admin/kerisi/m/1865",
      name: "kerisi-ar-reports-open-payment-list",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "List of Open Payment",
        breadcrumb: "Account Receivable / Reports / Open Payment List",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "List of Open Payment" },
    },
    {
      path: "/admin/kerisi/m/1937",
      name: "kerisi-ar-setup-non-invoice-structure-form",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Non - Fee Structure Form",
        breadcrumb: "Account Receivable / Setup / Non-Invoice Structure Form",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Non - Fee Structure Form" },
    },
    {
      path: "/admin/kerisi/m/1964",
      name: "kerisi-ar-reports-unsuccessful-open-payment-list",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Unsuccessful Open Payment List",
        breadcrumb: "Account Receivable / Reports / Unsuccessful Open Payment List",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Unsuccessful Open Payment List" },
    },
    {
      path: "/admin/kerisi/m/2111",
      name: "kerisi-ar-offline-receipt-receipt-collection-entry-form",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Receipt Collection Entry Form",
        breadcrumb: "Account Receivable / Offline Receipt / Receipt Collection Entry Form",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Receipt Collection Entry Form" },
    },
    {
      path: "/admin/kerisi/m/2116",
      name: "kerisi-ar-invoice-revenue-item",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Revenue Item",
        breadcrumb: "Account Receivable / Invoice / Revenue Item",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Revenue Item" },
    },
    {
      path: "/admin/kerisi/m/2119",
      name: "kerisi-ar-setup-invoice-structure-form",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Non - Fee Structure Form",
        breadcrumb: "Account Receivable / Setup / Invoice Structure Form",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Non - Fee Structure Form" },
    },
    {
      path: "/admin/kerisi/m/2129",
      name: "kerisi-ar-invoice-revenue-item-form",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Revenue Item Form",
        breadcrumb: "Account Receivable / Invoice / Revenue Item Form",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Revenue Item Form" },
    },
    {
      path: "/admin/kerisi/m/2140",
      name: "kerisi-ar-offline-receipt-approval-offline-receipt",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Approval Offline Receipt",
        breadcrumb: "Account Receivable / Offline Receipt / Approval Offline Receipt",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Approval Offline Receipt" },
    },
    {
      path: "/admin/kerisi/m/2147",
      name: "kerisi-ar-invoice-revenue-item-account-code",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Revenue Item Account Code",
        breadcrumb: "Account Receivable / Invoice / Revenue Item Account Code",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Revenue Item Account Code" },
    },
    {
      path: "/admin/kerisi/m/2148",
      name: "kerisi-ar-invoice-revenue-item-account-code-form",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Revenue Item Account Code Form",
        breadcrumb: "Account Receivable / Invoice / Revenue Item Account Code Form",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Revenue Item Account Code Form" },
    },
    {
      path: "/admin/kerisi/m/2294",
      name: "kerisi-ar-reports-receipt-cancel-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Receipt Cancel or Delete Data Listing",
        breadcrumb: "Account Receivable / Reports / Receipt Cancel Data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Receipt Cancel or Delete Data Listing" },
    },
    {
      path: "/admin/kerisi/m/2356",
      name: "kerisi-ar-receipt-receipt-on-behalf-approval",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Receipt on Behalf Approval",
        breadcrumb: "Account Receivable / Receipt / Receipt on Behalf Approval",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Receipt on Behalf Approval" },
    },
    {
      path: "/admin/kerisi/m/2385",
      name: "kerisi-ar-offline-receipt-offline-receipt-application-approval",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Offline Receipt Application Approval",
        breadcrumb: "Account Receivable / Offline Receipt / Offline Receipt Application Approval",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Offline Receipt Application Approval" },
    },
    {
      path: "/admin/kerisi/m/2501",
      name: "kerisi-ar-offline-receipt-counter-data-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Counter data Listing",
        breadcrumb: "Account Receivable / Offline Receipt / Counter data Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Counter data Listing" },
    },
    {
      path: "/admin/kerisi/m/3219",
      name: "kerisi-ar-reports-official-receipt-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Official Receipt Listing",
        breadcrumb: "Account Receivable / Reports / Official Receipt Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Official Receipt Listing" },
    },
    {
      path: "/admin/kerisi/m/3418",
      name: "kerisi-ar-reports-receipt-myfis-lite-listing",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Receipt MyFis Lite Entry",
        breadcrumb: "Account Receivable / Reports / Receipt MyFis Lite Listing",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Receipt MyFis Lite Entry" },
    },
    // HIDDEN_PAGE_LEVEL4 Account Receivable (were `kerisi-account-receivable-hidden-routes`; migrated to AR placeholder).
    {
      path: "/admin/kerisi/m/1730",
      name: "kerisi-ar-create-new-return-cheque",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "New Return Cheque",
        breadcrumb: "Account Receivable / Create New Return Cheque",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "New Return Cheque" },
    },
    {
      path: "/admin/kerisi/m/2499",
      name: "kerisi-ar-offline-receipt-cashbook-matching",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Matching",
        breadcrumb: "Account Receivable / Offline Receipt / Cashbook / Matching",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Matching" },
    },
    {
      path: "/admin/kerisi/m/2598",
      name: "kerisi-ar-reset-password-debtor",
      component: AccountReceivableLegacyPlaceholderView,
      props: {
        title: "Reset Password Debtor",
        breadcrumb: "Account Receivable / Reset Password Debtor",
        description:
          "This legacy Account Receivable invoice, receipt, cheque, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1023",
        relatedLabel: "Open invoice listing",
      },
      meta: { requiresAuth: true, title: "Reset Password Debtor" },
    },
    ...kerisiAccountReceivableHiddenRoutes,
    // HIDDEN_PAGE_LEVEL3 Budget — real UIs (LEVEL4 stubs are inlined after this block; see scripts/list-budget-level3-router.mjs).
    { path: "/admin/kerisi/m/1297", name: "kerisi-budget-setup-new-quarter", component: AllocationView, props: { pageHeading: "Budget / Setup / New Quarter" }, meta: { requiresAuth: true, title: "New Quarter" } },
    { path: "/admin/kerisi/m/1333", name: "kerisi-budget-setup-structure-budget", component: StructureBudgetListView, props: { pageHeading: "Budget / Setup / Structure Budget" }, meta: { requiresAuth: true, title: "Structure Budget" } },
    {
      path: "/admin/kerisi/m/1540",
      name: "kerisi-budget-setup-budget-planning-setup",
      component: BudgetPlanningScheduleView,
      props: { pageHeading: "Budget / Setup / Budget Planning Setup" },
      meta: { requiresAuth: true, title: "Budget Planning Setup" },
    },
    {
      path: "/admin/kerisi/m/1572",
      name: "kerisi-budget-planning-planning-listing",
      component: BudgetPlanningListView,
      props: {
        scope: "yearly",
        breadcrumbOverride: "Budget / Planning / Planning Listing",
        panelTitleOverride: "Listing Planning",
      },
      meta: { requiresAuth: true, title: "Listing Planning" },
    },
    {
      path: "/admin/kerisi/m/1573",
      name: "kerisi-budget-planning-budget-planning",
      component: PlanningNewApplicationView,
      props: { pageHeading: "Budget / Planning / Planning" },
      meta: { requiresAuth: true, title: "Planning" },
    },
    {
      path: "/admin/kerisi/m/1580",
      name: "kerisi-budget-planning-edit-application",
      component: BudgetPlanningListView,
      props: {
        scope: "yearly",
        breadcrumbOverride: "Budget / Planning / Edit Application",
        panelTitleOverride: "Edit Application",
      },
      meta: { requiresAuth: true, title: "Edit Application" },
    },
    {
      path: "/admin/kerisi/m/1973",
      name: "kerisi-budget-report-budget-report-listing",
      component: BudgetLegacyReportPlaceholderView,
      props: {
        title: "Budget Report Listing",
        description:
          "Legacy catalog of report shortcuts is not reproduced. In Kerisi20 use Total Allocation Report (/admin/kerisi/m/1968), Laporan Belanjawan (/admin/kerisi/m/3457), or Kerisi Classic for other report packs.",
      },
      meta: { requiresAuth: true, title: "Budget Report Listing" },
    },
    {
      path: "/admin/kerisi/m/1974",
      name: "kerisi-budget-report-expenditure-management-by-object-code-for-the-month",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Expenditure Management by Object Code for the Month" },
      meta: { requiresAuth: true, title: "Expenditure Management by Object Code for the Month" },
    },
    {
      path: "/admin/kerisi/m/2054",
      name: "kerisi-budget-report-budget-by-type",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Budget by Type" },
      meta: { requiresAuth: true, title: "Budget by Type" },
    },
    {
      path: "/admin/kerisi/m/2071",
      name: "kerisi-budget-report-performance-expenses-by-fund",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Performance Expenses by Fund" },
      meta: { requiresAuth: true, title: "Performance Expenses by Fund" },
    },
    {
      path: "/admin/kerisi/m/2316",
      name: "kerisi-budget-report-screening-emolumen-report",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Screening Emolumen Report" },
      meta: { requiresAuth: true, title: "Screening Emolumen Report" },
    },
    {
      path: "/admin/kerisi/m/2477",
      name: "kerisi-budget-planning-budget-planning-v2",
      component: PlanningNewApplicationView,
      props: { pageHeading: "Budget / Planning / Budget Planning V2" },
      meta: { requiresAuth: true, title: "Budget Planning V2" },
    },
    {
      path: "/admin/kerisi/m/2478",
      name: "kerisi-budget-planning-budget-planning-listing",
      component: BudgetPlanningListView,
      props: {
        scope: "yearly",
        breadcrumbOverride: "Budget / Planning / Budget Planning Listing",
        panelTitleOverride: "Budget Planning Listing",
      },
      meta: { requiresAuth: true, title: "Budget Planning Listing" },
    },
    {
      path: "/admin/kerisi/m/2516",
      name: "kerisi-budget-report-budget-emolumen-per-individual",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Budget Emolumen Per Individual" },
      meta: { requiresAuth: true, title: "Budget Emolumen Per Individual" },
    },
    {
      path: "/admin/kerisi/m/3214",
      name: "kerisi-budget-planning-dasar-baru-one-off-form",
      component: BudgetPlanningListView,
      props: {
        scope: "one_off",
        breadcrumbOverride: "Budget / Planning / Dasar Baru / One Off Form",
        panelTitleOverride: "Dasar Baru / One Off Form",
      },
      meta: { requiresAuth: true, title: "Dasar Baru / One Off Form" },
    },
    {
      path: "/admin/kerisi/m/3251",
      name: "kerisi-budget-report-laporan-perbelanjaan-mengikut-bahagian",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Laporan Perbelanjaan Mengikut Bahagian" },
      meta: { requiresAuth: true, title: "Laporan Perbelanjaan Mengikut Bahagian" },
    },
    {
      path: "/admin/kerisi/m/3252",
      name: "kerisi-budget-report-laporan-perbezaan-budget-summary-vs-p-l",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Laporan Perbezaan Budget Summary vs P&L" },
      meta: { requiresAuth: true, title: "Laporan Perbezaan Budget Summary vs P&L" },
    },
    {
      path: "/admin/kerisi/m/3354",
      name: "kerisi-budget-report-commitment",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Commitment" },
      meta: { requiresAuth: true, title: "Commitment" },
    },
    {
      path: "/admin/kerisi/m/3044",
      name: "kerisi-budget-report-total-allocation-umum-allocation-expenditure-balance-of-allocation-by-ptj",
      component: UmumAllocationPtjView,
      meta: { requiresAuth: true, title: "Umum Allocation, Expenditure & Balance of Allocation by PTJ" },
    },
    {
      path: "/admin/kerisi/m/3382",
      name: "kerisi-budget-report-budget-report-by-date-budget-summary-by-date-wbr068",
      component: BudgetV2SummaryReportView,
      props: { menuId: 3382 },
      meta: { requiresAuth: true, title: "Budget Summary By Date (WBR068)" },
    },
    {
      path: "/admin/kerisi/m/3389",
      name: "kerisi-budget-report-budget-report-by-date-budget-variation-by-date-wbr069",
      component: BudgetV2SummaryReportView,
      props: { menuId: 3389 },
      meta: { requiresAuth: true, title: "Budget Variation By Date (WBR069)" },
    },
    {
      path: "/admin/kerisi/m/3393",
      name: "kerisi-budget-report-budget-summary-by-date-budget-summary-by-ptj-wbr071",
      component: BudgetV2SummaryReportView,
      props: { menuId: 3393 },
      meta: { requiresAuth: true, title: "Budget Summary By Date (WBR071) (OLD)" },
    },
    {
      path: "/admin/kerisi/m/3364",
      name: "kerisi-budget-report-perjawatan",
      component: BudgetLegacyReportPlaceholderView,
      props: { title: "Perjawatan" },
      meta: { requiresAuth: true, title: "Perjawatan" },
    },
    // HIDDEN_PAGE_LEVEL4 Budget (were `kerisi-budget-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/3155",
      name: "kerisi-budget-closing-advance-closing-process-advance-closing",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Process Advance Closing",
        breadcrumb: "Budget / Closing / Advance Closing / Process Advance Closing",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "Process Advance Closing" },
    },
    {
      path: "/admin/kerisi/m/3156",
      name: "kerisi-budget-closing-advance-closing-list-of-advance-closing",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "List of Advance Closing",
        breadcrumb: "Budget / Closing / Advance Closing / List of Advance Closing",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "List of Advance Closing" },
    },
    {
      path: "/admin/kerisi/m/3157",
      name: "kerisi-budget-closing-advance-closing-process-advance-closing-allocation-basis",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Process Advance Closing (Allocation Basis)",
        breadcrumb: "Budget / Closing / Advance Closing / Process Advance Closing (Allocation Basis)",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "Process Advance Closing (Allocation Basis)" },
    },
    {
      path: "/admin/kerisi/m/3227",
      name: "kerisi-budget-report-budget-report-by-date-budget-checking-wbr068a",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Budget Checking (WBR068A)",
        breadcrumb: "Budget / Report / Budget Report by Date / Budget Checking (WBR068A)",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "Budget Checking (WBR068A)" },
    },
    {
      path: "/admin/kerisi/m/3237",
      name: "kerisi-budget-report-budget-report-by-date-departmental-budget-wbr070",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Departmental Budget (WBR070)",
        breadcrumb: "Budget / Report / Budget Report by Date / Departmental Budget (WBR070)",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "Departmental Budget (WBR070)" },
    },
    {
      path: "/admin/kerisi/m/3238",
      name: "kerisi-budget-report-budget-report-by-date-budget-summary-by-ptj-wbr073",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Budget Summary by PTJ (WBR073)",
        breadcrumb: "Budget / Report / Budget Report by Date / Budget Summary by PTJ (WBR073)",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "Budget Summary by PTJ (WBR073)" },
    },
    {
      path: "/admin/kerisi/m/3240",
      name: "kerisi-budget-report-budget-report-by-date-departmental-budget-by-ptj-wbr075",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Departmental Budget by PTJ (WBR075)",
        breadcrumb: "Budget / Report / Budget Report by Date / Departmental Budget by PTJ (WBR075)",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "Departmental Budget by PTJ (WBR075)" },
    },
    {
      path: "/admin/kerisi/m/3429",
      name: "kerisi-budget-planning-report-grant-application-summary",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Grant Application Summary",
        breadcrumb: "Budget / Planning / Report / Grant Application Summary",
        description:
          "This legacy Budget setup, planning, closing, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1471",
        relatedLabel: "Budget monitoring",
      },
      meta: { requiresAuth: true, title: "Grant Application Summary" },
    },
    // HIDDEN_PAGE_LEVEL3 Purchasing — shells (same order as kerisi-purchasing-hidden-routes.ts; must stay before `...kerisiPurchasingHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1722",
      name: "kerisi-purch-bank-guarantee-bank-guarantee-details",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Bank Guarantee Details",
        breadcrumb: "Purchasing / Bank Guarantee / Bank Guarantee Details",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Bank Guarantee Details" },
    },
    {
      path: "/admin/kerisi/m/1825",
      name: "kerisi-purch-vendor-vendor-detail",
      component: ListOfVendorView,
      props: {
        pageBreadcrumb: "Purchasing / Vendor / Vendor Detail",
        cardTitle: "Vendor directory",
        listBanner:
          "Vendor master edits and dossier attachments are not ported here yet. Locate a vendor in the grid, then finish changes in Kerisi Classic.",
        exportPageName: "Purchasing - Vendor Detail",
      },
      meta: { requiresAuth: true, title: "Vendor Detail" },
    },
    {
      path: "/admin/kerisi/m/1827",
      name: "kerisi-purch-purchase-order-new-purchase-order",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Purchase Order",
        breadcrumb: "Purchasing / Purchase Order / New Purchase Order",
        description:
          "New PO entry and approval paths are not migrated. Use Kerisi Classic to create or edit purchase orders, or review existing orders under Status PO & PR.",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Purchase Order" },
    },
    {
      path: "/admin/kerisi/m/1837",
      name: "kerisi-purch-good-receive-note-good-receive-note-details",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Good Receive Note",
        breadcrumb: "Purchasing / Good Receive Note / Good Receive Note Details",
        description:
          "GRN detail and matching lines are not migrated. Use Kerisi Classic for goods receipt, or open Status PO & PR for order context.",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Good Receive Note" },
    },
    {
      path: "/admin/kerisi/m/1943",
      name: "kerisi-purch-vendor-list-of-status-blacklist-vendor",
      component: ListOfVendorView,
      props: {
        pageBreadcrumb: "Purchasing / Vendor / List Of Status Blacklist Vendor",
        cardTitle: "Vendor status",
        listBanner:
          "Use the Status column in the directory; blacklist and appeal workflows remain in Kerisi Classic.",
        exportPageName: "Purchasing - List Of Status Vendor",
      },
      meta: { requiresAuth: true, title: "List Of Status Vendor" },
    },
    {
      path: "/admin/kerisi/m/1944",
      name: "kerisi-purch-vendor-info",
      component: ListOfVendorView,
      props: {
        pageBreadcrumb: "Purchasing / Vendor / Information",
        cardTitle: "Vendor information",
        listBanner: "Read-only directory; full vendor information forms are in Kerisi Classic.",
        exportPageName: "Purchasing - Vendor Information",
      },
      meta: { requiresAuth: true, title: "Information" },
    },
    {
      path: "/admin/kerisi/m/2086",
      name: "kerisi-purch-good-receive-note-good-receive-note-cancel-details",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Good Receive Note Cancel Details",
        breadcrumb: "Purchasing / Good Receive Note / Good Receive Note Cancel Details",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Good Receive Note Cancel Details" },
    },
    {
      path: "/admin/kerisi/m/2091",
      name: "kerisi-purch-vendor-reset-password",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Reset Password",
        breadcrumb: "Purchasing / Vendor / Reset Password",
        description:
          "Vendor portal password reset orchestration runs in Kerisi Classic. Vendor directory listing is shown from List of Vendor.",
        relatedPath: "/admin/kerisi/m/1685",
        relatedLabel: "List of Vendor",
      },
      meta: { requiresAuth: true, title: "Reset Password" },
    },
    {
      path: "/admin/kerisi/m/2340",
      name: "kerisi-purch-quotation-tender-opening-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Tender Opening Detail",
        breadcrumb: "Purchasing / Quotation / Tender Opening Detail",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Tender Opening Detail" },
    },
    {
      path: "/admin/kerisi/m/2548",
      name: "kerisi-purch-purchase-requisition-purchase-requisition-cancellation",
      component: StatusPoPrView,
      props: {
        pageHeading: "Purchasing / Purchase Requisition / Purchase Requisition Cancellation",
        panelTitle: "Purchase Requisition Complete Partial",
      },
      meta: { requiresAuth: true, title: "Purchase Requisition Complete Partial" },
    },
    {
      path: "/admin/kerisi/m/2617",
      name: "kerisi-purch-report-po-procurement-management",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Procurement Management",
        breadcrumb: "Purchasing / Report PO / Procurement Management",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Procurement Management" },
    },
    {
      path: "/admin/kerisi/m/2635",
      name: "kerisi-purch-report-po-restricted-tendor",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Restricted Tendor",
        breadcrumb: "Purchasing / Report PO / Restricted Tendor",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Restricted Tendor" },
    },
    {
      path: "/admin/kerisi/m/2723",
      name: "kerisi-purch-tender-quotation-tender-opening-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Tender Detail",
        breadcrumb: "Purchasing / Tender/Quotation / Tender Opening Detail",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Tender Detail" },
    },
    {
      path: "/admin/kerisi/m/2725",
      name: "kerisi-purch-tender-quotation-selection-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Selection Detail",
        breadcrumb: "Purchasing / Tender/Quotation / Selection Detail",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Selection Detail" },
    },
    {
      path: "/admin/kerisi/m/2739",
      name: "kerisi-purch-vendor-renew-vendor",
      component: ListOfVendorView,
      props: {
        pageBreadcrumb: "Purchasing / Vendor / Renew Vendor",
        cardTitle: "Vendor directory",
        listBanner: "Track renewal dates from the grid; renewal submission and approval run in Kerisi Classic.",
        exportPageName: "Purchasing - Renew Vendor",
      },
      meta: { requiresAuth: true, title: "Renew Vendor" },
    },
    {
      path: "/admin/kerisi/m/2925",
      name: "kerisi-purch-advertisement-buka-peti",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Buka peti",
        breadcrumb: "Purchasing / Advertisement / Buka peti",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Buka peti" },
    },
    {
      path: "/admin/kerisi/m/3169",
      name: "kerisi-purch-pre-purchase-requisition-pre-purchase-requisition-list",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Pre - Purchase Requisition List",
        breadcrumb: "Purchasing / Pre - Purchase Requisition / Pre - Purchase Requisition List",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Pre - Purchase Requisition List" },
    },
    {
      path: "/admin/kerisi/m/3171",
      name: "kerisi-purch-pre-purchase-requisition-pre-purchase-requisition-details",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Pre - Purchase Requisition Details",
        breadcrumb: "Purchasing / Pre - Purchase Requisition / Pre - Purchase Requisition Details",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Pre - Purchase Requisition Details" },
    },
    {
      path: "/admin/kerisi/m/3215",
      name: "kerisi-purch-pre-purchase-requisition-pre-pr-to-be-cancel-list",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "PRE - PR To Be Cancel List",
        breadcrumb: "Purchasing / Pre - Purchase Requisition / PRE - PR To Be Cancel List",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "PRE - PR To Be Cancel List" },
    },
    {
      path: "/admin/kerisi/m/3216",
      name: "kerisi-purch-pre-purchase-requisition-pre-pr-to-be-cancel-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "PRE - PR To Be Cancel Detail",
        breadcrumb: "Purchasing / Pre - Purchase Requisition / PRE - PR To Be Cancel Detail",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "PRE - PR To Be Cancel Detail" },
    },
    {
      path: "/admin/kerisi/m/3220",
      name: "kerisi-purch-pre-purchase-requisition-pre-pr-to-be-cancel-partial-list",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "PRE - PR To Be Cancel Partial List",
        breadcrumb: "Purchasing / Pre - Purchase Requisition / PRE - PR To Be Cancel Partial List",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "PRE - PR To Be Cancel Partial List" },
    },
    {
      path: "/admin/kerisi/m/3221",
      name: "kerisi-purch-pre-purchase-requisition-pre-pr-to-be-cancel-partial-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "PRE - PR To Be Cancel Partial Detail",
        breadcrumb: "Purchasing / Pre - Purchase Requisition / PRE - PR To Be Cancel Partial Detail",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "PRE - PR To Be Cancel Partial Detail" },
    },
    {
      path: "/admin/kerisi/m/3273",
      name: "kerisi-purch-tender-quotation-evaluation-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Evaluation Detail",
        breadcrumb: "Purchasing / Tender/Quotation / Evaluation Detail",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Evaluation Detail" },
    },
    {
      path: "/admin/kerisi/m/3373",
      name: "kerisi-purch-tender-quotation-tender-cancellation-detail",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Tender Cancellation Detail",
        breadcrumb: "Purchasing / Tender/Quotation / Tender Cancellation Detail",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Tender Cancellation Detail" },
    },
    {
      path: "/admin/kerisi/m/3417",
      name: "kerisi-purch-tender-quotation-evaluation-criteria",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Evaluation Criteria",
        breadcrumb: "Purchasing / Tender/Quotation / Evaluation Criteria",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Evaluation Criteria" },
    },
    {
      path: "/admin/kerisi/m/3422",
      name: "kerisi-purch-tender-quotation-evaluation-mark",
      component: PurchasingLegacyPlaceholderView,
      props: {
        title: "Evaluation Mark",
        breadcrumb: "Purchasing / Tender/Quotation / Evaluation Mark",
        relatedPath: "/admin/kerisi/m/1841",
        relatedLabel: "Status PO & PR",
      },
      meta: { requiresAuth: true, title: "Evaluation Mark" },
    },
    ...kerisiPurchasingHiddenRoutes,
    // HIDDEN_PAGE_LEVEL3 General Ledger (`HIDDEN_PAGE_LEVEL3.json`; must stay before `...kerisiGeneralLedgerExtraHiddenRoutes`).
    {
      path: "/admin/kerisi/m/1951",
      name: "kerisi-gl-extra-report-view-trial-balance",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "View Trial Balance",
        breadcrumb: "General Ledger / Report / View Trial Balance",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "View Trial Balance" },
    },
    {
      path: "/admin/kerisi/m/2044",
      name: "kerisi-gl-extra-reversal-reversal-journal-details",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Reversal Journal Details",
        breadcrumb: "General Ledger / Reversal / Reversal Journal Details",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Reversal Journal Details" },
    },
    {
      path: "/admin/kerisi/m/2088",
      name: "kerisi-gl-extra-reversal-reverse-journal-details",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Reverse Journal Details",
        breadcrumb: "General Ledger / Reversal / Reverse Journal Details",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Reverse Journal Details" },
    },
    {
      path: "/admin/kerisi/m/2329",
      name: "kerisi-gl-extra-report-trial-balance",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Trial Balance",
        breadcrumb: "General Ledger / Report / Trial Balance",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Trial Balance" },
    },
    {
      path: "/admin/kerisi/m/3122",
      name: "kerisi-gl-extra-financial-statement-cash-flow",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Cash Flow Report",
        breadcrumb: "General Ledger / Financial Statement / Cash Flow",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Cash Flow Report" },
    },
    {
      path: "/admin/kerisi/m/3464",
      name: "kerisi-gl-extra-financial-statement-ppi-position",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Position",
        breadcrumb: "General Ledger / Financial Statement (PPI) / Position",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Position" },
    },
    // HIDDEN_PAGE_LEVEL4 General Ledger — financial statement extras (were `kerisi-general-ledger-extra-hidden-routes`).
    {
      path: "/admin/kerisi/m/2968",
      name: "kerisi-gl-extra-financial-statement-financial-performance-overall",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Performance — Overall",
        breadcrumb: "General Ledger / Financial Statement / Financial Performance / Overall",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Performance — Overall" },
    },
    {
      path: "/admin/kerisi/m/2970",
      name: "kerisi-gl-extra-financial-statement-financial-performance-fund-type",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Performance — Fund Type",
        breadcrumb: "General Ledger / Financial Statement / Financial Performance / Fund Type",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Performance — Fund Type" },
    },
    {
      path: "/admin/kerisi/m/3035",
      name: "kerisi-gl-extra-financial-statement-financial-performance-ptj",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Performance — PTJ",
        breadcrumb: "General Ledger / Financial Statement / Financial Performance / PTJ",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Performance — PTJ" },
    },
    {
      path: "/admin/kerisi/m/3055",
      name: "kerisi-gl-extra-financial-statement-financial-position-overall",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Position — Overall",
        breadcrumb: "General Ledger / Financial Statement / Financial Position / Overall",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Position — Overall" },
    },
    {
      path: "/admin/kerisi/m/3061",
      name: "kerisi-gl-extra-financial-statement-financial-position-fund-type",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Position — Fund Type",
        breadcrumb: "General Ledger / Financial Statement / Financial Position / Fund Type",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Position — Fund Type" },
    },
    {
      path: "/admin/kerisi/m/3062",
      name: "kerisi-gl-extra-financial-statement-financial-position-ptj",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Position — PTJ",
        breadcrumb: "General Ledger / Financial Statement / Financial Position / PTJ",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Position — PTJ" },
    },
    {
      path: "/admin/kerisi/m/3082",
      name: "kerisi-gl-extra-financial-statement-changes-in-equity-overall",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Changes In Equity — Overall",
        breadcrumb: "General Ledger / Financial Statement / Changes In Equity / Overall",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Changes In Equity — Overall" },
    },
    {
      path: "/admin/kerisi/m/3088",
      name: "kerisi-gl-extra-financial-statement-financial-performance-statement",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Performance — Statement",
        breadcrumb: "General Ledger / Financial Statement / Financial Performance / Statement",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Performance — Statement" },
    },
    {
      path: "/admin/kerisi/m/3089",
      name: "kerisi-gl-extra-financial-statement-financial-position-statement",
      component: GeneralLedgerLegacyPlaceholderView,
      props: {
        title: "Financial Position — Statement",
        breadcrumb: "General Ledger / Financial Statement / Financial Position / Statement",
        description:
          "This legacy General Ledger reversal, trial balance, or financial statement screen is not reproduced in Kerisi20. Use Kerisi Classic for the full report.",
        relatedPath: "/admin/kerisi/m/2519",
        relatedLabel: "Open general ledger listing",
      },
      meta: { requiresAuth: true, title: "Financial Position — Statement" },
    },
    ...kerisiGeneralLedgerExtraHiddenRoutes,
    // HIDDEN_PAGE_LEVEL3 Vendor Portal (`HIDDEN_PAGE_LEVEL3.json`; inlined LEVEL4 stubs follow LEVEL3 placeholders in this section).
    {
      path: "/admin/kerisi/m/2950",
      name: "kerisi-vendor-portal-debt-collector-agent-upload-dca",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Upload DCA",
        breadcrumb: "Vendor Portal / Debt Collector Agent / Upload DCA",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Upload DCA" },
    },
    {
      path: "/admin/kerisi/m/2957",
      name: "kerisi-vendor-portal-debt-collector-agent-comment-details",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Comment Details",
        breadcrumb: "Vendor Portal / Debt Collector Agent / Comment Details",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Comment Details" },
    },
    {
      path: "/admin/kerisi/m/2958",
      name: "kerisi-vendor-portal-debt-collector-agent-information",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Information",
        breadcrumb: "Vendor Portal / Debt Collector Agent / Information",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Information" },
    },
    {
      path: "/admin/kerisi/m/2959",
      name: "kerisi-vendor-portal-debt-collector-agent-list-of-upload-dca",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "List of Upload DCA",
        breadcrumb: "Vendor Portal / Debt Collector Agent / List of Upload DCA",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "List of Upload DCA" },
    },
    {
      path: "/admin/kerisi/m/2975",
      name: "kerisi-vendor-portal-debt-collector-agent-payment-history",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Payment History",
        breadcrumb: "Vendor Portal / Debt Collector Agent / Payment History",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Payment History" },
    },
    // HIDDEN_PAGE_LEVEL4 Vendor Portal (were `kerisi-vendor-portal-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1972",
      name: "kerisi-vendor-portal-online-payment-history",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Payment History",
        breadcrumb: "Vendor Portal / Online Payment History",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Payment History" },
    },
    {
      path: "/admin/kerisi/m/1977",
      name: "kerisi-vendor-portal-payment-vendor",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Summary Payment Vendor",
        breadcrumb: "Vendor Portal / Payment Vendor",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Summary Payment Vendor" },
    },
    {
      path: "/admin/kerisi/m/1990",
      name: "kerisi-vendor-portal-vendor-portal",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Vendor Portal",
        breadcrumb: "Vendor Portal / Vendor Portal",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Vendor Portal" },
    },
    {
      path: "/admin/kerisi/m/2769",
      name: "kerisi-vendor-portal-tender-quotation-details",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Tender/Quotation Details",
        breadcrumb: "Vendor Portal / Tender/Quotation Details",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Tender/Quotation Details" },
    },
    {
      path: "/admin/kerisi/m/3281",
      name: "kerisi-vendor-portal-notification",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Notification",
        breadcrumb: "Vendor Portal / Notification",
        description:
          "This legacy Vendor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1961",
        relatedLabel: "Vendor portal",
      },
      meta: { requiresAuth: true, title: "Notification" },
    },
    // HIDDEN_PAGE_LEVEL3 Petty Cash (`HIDDEN_PAGE_LEVEL3.json`; inlined LEVEL4 stubs follow LEVEL3 placeholders in this section).
    {
      path: "/admin/kerisi/m/1527",
      name: "kerisi-petty-cash-setup-account-main",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Account Main",
        breadcrumb: "Petty Cash / Setup / Account Main",
        description:
          "This legacy Petty Cash claim, recoupment, setup, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1490",
        relatedLabel: "List of petty cash application",
      },
      meta: { requiresAuth: true, title: "Account Main" },
    },
    {
      path: "/admin/kerisi/m/1963",
      name: "kerisi-petty-cash-setup-purpose-for-petty-cash",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Purpose for Petty Cash",
        breadcrumb: "Petty Cash / Setup / Purpose for Petty Cash",
        description:
          "This legacy Petty Cash claim, recoupment, setup, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1490",
        relatedLabel: "List of petty cash application",
      },
      meta: { requiresAuth: true, title: "Purpose for Petty Cash" },
    },
    {
      path: "/admin/kerisi/m/2416",
      name: "kerisi-petty-cash-report-report-voucher-panjar-wang-runcit",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Report Voucher Panjar Wang Runcit",
        breadcrumb: "Petty Cash / Report / Report Voucher Panjar Wang Runcit",
        description:
          "This legacy Petty Cash claim, recoupment, setup, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1490",
        relatedLabel: "List of petty cash application",
      },
      meta: { requiresAuth: true, title: "Report Voucher Panjar Wang Runcit" },
    },
    // HIDDEN_PAGE_LEVEL4 Petty Cash (were `kerisi-petty-cash-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/1506",
      name: "kerisi-petty-cash-petty-cash-claim",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Petty Cash Claim",
        breadcrumb: "Petty Cash / Petty Cash Claim",
        description:
          "This legacy Petty Cash claim, recoupment, setup, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1490",
        relatedLabel: "List of petty cash application",
      },
      meta: { requiresAuth: true, title: "Petty Cash Claim" },
    },
    {
      path: "/admin/kerisi/m/2376",
      name: "kerisi-petty-cash-petty-cash-recoupment-details",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Petty Cash Recoupment Details",
        breadcrumb: "Petty Cash / Petty Cash Recoupment Details",
        description:
          "This legacy Petty Cash claim, recoupment, setup, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1490",
        relatedLabel: "List of petty cash application",
      },
      meta: { requiresAuth: true, title: "Petty Cash Recoupment Details" },
    },
    {
      path: "/admin/kerisi/m/2481",
      name: "kerisi-petty-cash-request-petty-cash-details",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Request Petty Cash Details",
        breadcrumb: "Petty Cash / Request Petty Cash Details",
        description:
          "This legacy Petty Cash claim, recoupment, setup, or report screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/1490",
        relatedLabel: "List of petty cash application",
      },
      meta: { requiresAuth: true, title: "Request Petty Cash Details" },
    },
    // HIDDEN_PAGE_LEVEL3 Debtor Portal (`HIDDEN_PAGE_LEVEL3.json`; inlined LEVEL4 stubs follow LEVEL3 placeholders in this section).
    {
      path: "/admin/kerisi/m/2284",
      name: "kerisi-debtor-portal-financial-information-discount-note",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Discount Note",
        breadcrumb: "Debtor Portal / Financial Information / Discount Note",
        description:
          "This legacy Debtor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Debtors statement",
      },
      meta: { requiresAuth: true, title: "Discount Note" },
    },
    {
      path: "/admin/kerisi/m/2310",
      name: "kerisi-debtor-portal-refund-new-application",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "New Application",
        breadcrumb: "Debtor Portal / Refund / New Application",
        description:
          "This legacy Debtor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Debtors statement",
      },
      meta: { requiresAuth: true, title: "New Application" },
    },
    {
      path: "/admin/kerisi/m/2311",
      name: "kerisi-debtor-portal-refund-list-of-refund-application",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "List of Refund Application",
        breadcrumb: "Debtor Portal / Refund / List of Refund Application",
        description:
          "This legacy Debtor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Debtors statement",
      },
      meta: { requiresAuth: true, title: "List of Refund Application" },
    },
    // HIDDEN_PAGE_LEVEL4 Debtor Portal (were `kerisi-debtor-portal-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/2197",
      name: "kerisi-debtor-portal-payment",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Payment",
        breadcrumb: "Debtor Portal / Payment",
        description:
          "This legacy Debtor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Debtors statement",
      },
      meta: { requiresAuth: true, title: "Payment" },
    },
    {
      path: "/admin/kerisi/m/2209",
      name: "kerisi-debtor-portal-update-debtor-information",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Update Debtor Information",
        breadcrumb: "Debtor Portal / Update Debtor Information",
        description:
          "This legacy Debtor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Debtors statement",
      },
      meta: { requiresAuth: true, title: "Update Debtor Information" },
    },
    {
      path: "/admin/kerisi/m/2447",
      name: "kerisi-debtor-portal-advance-payment",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Advance Payment",
        breadcrumb: "Debtor Portal / Advance Payment",
        description:
          "This legacy Debtor Portal screen is not reproduced in Kerisi20. Use Kerisi Classic for the full process.",
        relatedPath: "/admin/kerisi/m/2267",
        relatedLabel: "Debtors statement",
      },
      meta: { requiresAuth: true, title: "Advance Payment" },
    },
    // HIDDEN_PAGE_LEVEL3 EIS (`HIDDEN_PAGE_LEVEL3.json`; inlined LEVEL4 stub follows LEVEL3 placeholders in this section).
    {
      path: "/admin/kerisi/m/3405",
      name: "kerisi-eis-jpka-siberhutang",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Siberhutang",
        breadcrumb: "EIS / JPKA / Siberhutang",
        description: "This legacy EIS screen is not reproduced in Kerisi20.",
        relatedPath: "/admin",
        relatedLabel: "Main dashboard",
      },
      meta: { requiresAuth: true, title: "Siberhutang" },
    },
    {
      path: "/admin/kerisi/m/3415",
      name: "kerisi-eis-jpka-bil-belum-bayar",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Bil Belum Bayar",
        breadcrumb: "EIS / JPKA / Bil Belum Bayar",
        description: "This legacy EIS screen is not reproduced in Kerisi20.",
        relatedPath: "/admin",
        relatedLabel: "Main dashboard",
      },
      meta: { requiresAuth: true, title: "Bil Belum Bayar" },
    },
    // HIDDEN_PAGE_LEVEL4 EIS (were `kerisi-eis-hidden-routes`; inlined).
    {
      path: "/admin/kerisi/m/3404",
      name: "kerisi-eis-staff",
      component: GenericLegacyPlaceholderView,
      props: {
        title: "Staff",
        breadcrumb: "EIS / Staff",
        description: "This legacy EIS screen is not reproduced in Kerisi20.",
        relatedPath: "/admin",
        relatedLabel: "Main dashboard",
      },
      meta: { requiresAuth: true, title: "Staff" },
    },
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
    { path: "/admin/kerisi/m/1334", name: "kerisi-structure-budget-list", component: StructureBudgetListView, meta: { requiresAuth: true, title: "Structure Budget List" } },
    { path: "/admin/kerisi/m/1516", name: "kerisi-planning-new-application", component: PlanningNewApplicationView, meta: { requiresAuth: true, title: "Planning New Application" } },
    { path: "/admin/kerisi/m/1968", name: "kerisi-total-allocation-report", component: TotalAllocationReportView, meta: { requiresAuth: true, title: "Total Allocation Report" } },
    { path: "/admin/kerisi/m/3457", name: "kerisi-laporan-belanjawan", component: LaporanBelanjawanView, meta: { requiresAuth: true, title: "Laporan Belanjawan" } },
    { path: "/admin/kerisi/m/2506", name: "kerisi-planning-dasar-sedia-ada", component: BudgetPlanningListView, props: { scope: "yearly" }, meta: { requiresAuth: true, title: "Dasar Sedia Ada" } },
    { path: "/admin/kerisi/m/3012", name: "kerisi-planning-allocation-2", component: BudgetPlanningListView, props: { scope: "allocation_2" }, meta: { requiresAuth: true, title: "Allocation 2" } },
    { path: "/admin/kerisi/m/3013", name: "kerisi-planning-allocation-3", component: BudgetPlanningListView, props: { scope: "allocation_3" }, meta: { requiresAuth: true, title: "Allocation 3" } },
    { path: "/admin/kerisi/m/3196", name: "kerisi-planning-dasar-baru", component: BudgetPlanningListView, props: { scope: "one_off" }, meta: { requiresAuth: true, title: "Dasar Baru / One Off" } },
    { path: "/admin/kerisi/m/3279", name: "kerisi-planning-to-initial", component: BudgetPlanningListView, props: { scope: "to_initial" }, meta: { requiresAuth: true, title: "Planning to Initial" } },
    { path: "/admin/kerisi/m/:menuId", name: "kerisi-menu", component: ComingSoonView, meta: { requiresAuth: true, title: "KERISI" } },
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
  const site = useSiteStore();
  const pageTitle = (to.meta.title as string) || "Admin";
  site.setDocumentTitle(pageTitle);
});

export default router;
