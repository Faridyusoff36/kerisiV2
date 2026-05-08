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
import SubsidiaryLedgerAllView from "@/views/SubsidiaryLedgerAllView.vue";
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
import CcAgeingReportView from "@/views/CcAgeingReportView.vue";
import CcRefundBrIntegrationView from "@/views/CcRefundBrIntegrationView.vue";
import CcRefundStaffDetailListingView from "@/views/CcRefundStaffDetailListingView.vue";
import CcListOfRefundPortalView from "@/views/CcListOfRefundPortalView.vue";
import CcRefundApplicationAdminView from "@/views/CcRefundApplicationAdminView.vue";
import CcRequestRefundStaffView from "@/views/CcRequestRefundStaffView.vue";
import CcReminderStatusView from "@/views/CcReminderStatusView.vue";
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
import AssetBuildingLocationView from "@/views/AssetBuildingLocationView.vue";
import AssetDisposeMethodView from "@/views/AssetDisposeMethodView.vue";
import DisposalSecretariatSetupView from "@/views/DisposalSecretariatSetupView.vue";
import AssetAccountSetupView from "@/views/AssetAccountSetupView.vue";
import AssetDepreciationGroupView from "@/views/AssetDepreciationGroupView.vue";
import AssetDepreciationTanahView from "@/views/AssetDepreciationTanahView.vue";
import AssetDepreciationSchedulerView from "@/views/AssetDepreciationSchedulerView.vue";
import AssetRoleListingView from "@/views/AssetRoleListingView.vue";
import AssetVerificationOfficerView from "@/views/AssetVerificationOfficerView.vue";
import AssetVerificationView from "@/views/AssetVerificationView.vue";
import AssetCancellationAssetsView from "@/views/AssetCancellationAssetsView.vue";
import AssetCancellationJournalListingView from "@/views/AssetCancellationJournalListingView.vue";
import AssetDamageApplicationView from "@/views/AssetDamageApplicationView.vue";
import AssetDamageListingView from "@/views/AssetDamageListingView.vue";
import AssetKewPaGrnListingView from "@/views/AssetKewPaGrnListingView.vue";
import AssetMaintenanceListingView from "@/views/AssetMaintenanceListingView.vue";
import AssetRoomLocationListingView from "@/views/AssetRoomLocationListingView.vue";
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
    // Shortcut → MENUID 2604 (sidebar: Credit Control / Refund / Refund (Staff) / …).
    {
      path: "/admin/credit-control/list-of-refund-application-portal",
      redirect: "/admin/kerisi/m/2604",
    },
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
    { path: "/admin/kerisi/m/1564", name: "kerisi-asset-dispose-method", component: AssetDisposeMethodView, meta: { requiresAuth: true, title: "Asset Dispose Method" } },
    { path: "/admin/kerisi/m/3118", name: "kerisi-disposal-secretariat-setup", component: DisposalSecretariatSetupView, meta: { requiresAuth: true, title: "Disposal Secretariat Setup" } },
    { path: "/admin/kerisi/m/1645", name: "kerisi-asset-account-setup", component: AssetAccountSetupView, meta: { requiresAuth: true, title: "Asset Account Setup" } },
    { path: "/admin/kerisi/m/2455", name: "kerisi-asset-depr-group", component: AssetDepreciationGroupView, meta: { requiresAuth: true, title: "Depreciation Group" } },
    { path: "/admin/kerisi/m/2483", name: "kerisi-asset-depr-tanah", component: AssetDepreciationTanahView, meta: { requiresAuth: true, title: "Depreciation Setup (Tanah)" } },
    { path: "/admin/kerisi/m/3338", name: "kerisi-asset-depr-scheduler", component: AssetDepreciationSchedulerView, meta: { requiresAuth: true, title: "Asset Depreciation Scheduler" } },
    { path: "/admin/kerisi/m/3148", name: "kerisi-asset-role-listing", component: AssetRoleListingView, meta: { requiresAuth: true, title: "Role Listing" } },
    { path: "/admin/kerisi/m/3471", name: "kerisi-asset-verification-officer", component: AssetVerificationOfficerView, meta: { requiresAuth: true, title: "Asset Verification Officer" } },
    { path: "/admin/kerisi/m/2293", name: "kerisi-asset-item-main", component: PurchasingItemMainView, meta: { requiresAuth: true, title: "Item Main", kerisiMenuId: 2293 } },
    {
      path: "/admin/kerisi/m/1562",
      name: "kerisi-asset-location-building",
      component: AssetBuildingLocationView,
      meta: { requiresAuth: true, title: "Location" },
    },
    {
      path: "/admin/kerisi/m/2746",
      name: "kerisi-asset-location-listing",
      component: AssetRoomLocationListingView,
      meta: { requiresAuth: true, title: "Location Listing" },
    },
    {
      path: "/admin/kerisi/m/1563",
      name: "kerisi-asset-item-listing",
      component: PurchasingItemMainView,
      meta: { requiresAuth: true, title: "Item Listing", kerisiMenuId: 1563 },
    },
    {
      path: "/admin/kerisi/m/3470",
      name: "kerisi-asset-damage-report-list",
      component: AssetDamageListingView,
      meta: { requiresAuth: true, title: "Damage Report List", damageListingScope: "reports" },
    },
    {
      path: "/admin/kerisi/m/3481",
      name: "kerisi-asset-damage-application",
      component: AssetDamageApplicationView,
      meta: { requiresAuth: true, title: "Damage Asset Application" },
    },
    {
      path: "/admin/kerisi/m/3492",
      name: "kerisi-asset-preventive-maint-list",
      component: AssetMaintenanceListingView,
      meta: { requiresAuth: true, title: "Preventive Maintenance Listing", maintenanceListing: "preventive" },
    },
    {
      path: "/admin/kerisi/m/3493",
      name: "kerisi-asset-preventive-maint-form-draft",
      component: AssetMaintenanceListingView,
      meta: {
        requiresAuth: true,
        title: "Preventive Maintenance Form",
        maintenanceListing: "preventive",
        maintenanceDraftOnly: true,
      },
    },
    {
      path: "/admin/kerisi/m/3502",
      name: "kerisi-asset-schedule-maint-list",
      component: AssetMaintenanceListingView,
      meta: { requiresAuth: true, title: "Schedule Maintenance Listing", maintenanceListing: "schedule" },
    },
    {
      path: "/admin/kerisi/m/3498",
      name: "kerisi-asset-corrective-maint-list",
      component: AssetMaintenanceListingView,
      meta: { requiresAuth: true, title: "Corrective Maintenance List", maintenanceListing: "corrective" },
    },
    {
      path: "/admin/kerisi/m/3499",
      name: "kerisi-asset-corrective-maint-form-draft",
      component: AssetMaintenanceListingView,
      meta: {
        requiresAuth: true,
        title: "Corrective Maintenance Form",
        maintenanceListing: "corrective",
        maintenanceDraftOnly: true,
      },
    },
    {
      path: "/admin/kerisi/m/2589",
      name: "kerisi-asset-kew-pa-receipt",
      component: AssetKewPaGrnListingView,
      meta: { requiresAuth: true, title: "Kew PA 1 – Penerimaan Aset", kewPaVariant: "receive" },
    },
    {
      path: "/admin/kerisi/m/2597",
      name: "kerisi-asset-kew-pa-reject",
      component: AssetKewPaGrnListingView,
      meta: { requiresAuth: true, title: "Kew PA 2 – Penolakan Aset", kewPaVariant: "reject" },
    },
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
    // Student Finance note listings (1529 / 1575 / 1570) live under `studentFinanceKerisiRoutes.ts`.
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
      path: "/admin/kerisi/m/2286",
      name: "kerisi-cc-refund-application-admin",
      component: CcRefundApplicationAdminView,
      meta: {
        requiresAuth: true,
        title: "Refund Type",
      },
    },
    {
      path: "/admin/kerisi/m/2287",
      name: "kerisi-cc-list-refund-staff-admin",
      component: CcListOfRefundPortalView,
      props: {
        pageHeading: "Credit Control / Refund / Refund (Staff) / Admin / List of Refund",
        cardTitle: "List of Refund",
        datatablePageName: "List of Refund (Staff Admin)",
        excelFileLabel: "CC_List_of_Refund",
        excelSheetName: "List of refund",
        emptyStateTitle: "No refund applications in this list",
        emptyStateHint:
          "Try another search. Rows use the same APPLY queue and refund-prefix scope as the portal listing.",
      },
      meta: { requiresAuth: true, title: "List of Refund" },
    },
    {
      path: "/admin/kerisi/m/2291",
      name: "kerisi-cc-request-refund-staff",
      component: CcRequestRefundStaffView,
      meta: { requiresAuth: true, title: "Request Refund" },
    },
    {
      path: "/admin/kerisi/m/2289",
      name: "kerisi-cc-refund-bri-list",
      component: CcRefundBrIntegrationView,
      meta: { requiresAuth: true, title: "List Of Refund Bill" },
    },
    {
      path: "/admin/kerisi/m/2290",
      name: "kerisi-cc-refund-staff-report",
      component: CcRefundStaffDetailListingView,
      meta: { requiresAuth: true, title: "Detail Listing Of Refund Process" },
    },
    {
      path: "/admin/kerisi/m/2604",
      name: "kerisi-cc-list-refund-portal",
      component: CcListOfRefundPortalView,
      meta: {
        requiresAuth: true,
        title: "List Of Refund Application (Portal)",
      },
    },
    {
      path: "/admin/kerisi/m/2669",
      name: "kerisi-cc-reminder-success",
      component: CcReminderStatusView,
      meta: { requiresAuth: true, title: "Successful Generated Reminder" },
    },
    {
      path: "/admin/kerisi/m/3370",
      name: "kerisi-cc-ageing-creditor-summary",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Creditor Ageing / Creditor Ageing Summary",
        cardTitle: "Creditor Ageing Summary",
        ageingKind: "creditor_summary",
      },
      meta: { requiresAuth: true, title: "Creditor Ageing Summary" },
    },
    {
      path: "/admin/kerisi/m/3445",
      name: "kerisi-cc-ageing-creditor-summary-6y",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Creditor Ageing / Creditor Ageing Summary ( > 6 Years )",
        cardTitle: "Creditor Ageing Summary ( > 6 Years )",
        ageingKind: "creditor_summary_ext",
      },
      meta: { requiresAuth: true, title: "Creditor Ageing Summary ( > 6 Years )" },
    },
    {
      path: "/admin/kerisi/m/3371",
      name: "kerisi-cc-ageing-creditor-details",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Creditor Ageing / Creditor Ageing Details",
        cardTitle: "Creditor Ageing Details",
        ageingKind: "creditor_details",
      },
      meta: { requiresAuth: true, title: "Creditor Ageing Details" },
    },
    {
      path: "/admin/kerisi/m/3443",
      name: "kerisi-cc-ageing-creditor-details-6y",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Creditor Ageing / Creditor Ageing Details( > 6 Years)",
        cardTitle: "Creditor Ageing Details( > 6 Years)",
        ageingKind: "creditor_details_ext",
      },
      meta: { requiresAuth: true, title: "Creditor Ageing Details( > 6 Years)" },
    },
    {
      path: "/admin/kerisi/m/3375",
      name: "kerisi-cc-ageing-creditor-ap",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Creditor Ageing / Creditor AP Listing",
        cardTitle: "Creditor AP Listing",
        ageingKind: "creditor_ap_listing",
      },
      meta: { requiresAuth: true, title: "Creditor AP Listing" },
    },
    {
      path: "/admin/kerisi/m/3447",
      name: "kerisi-cc-ageing-debtor-summary-6y",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Debtor Ageing / Debtor Ageing Summary ( > 6 Years)",
        cardTitle: "Debtor Ageing Summary ( > 6 Years)",
        ageingKind: "debtor_summary_ext",
      },
      meta: { requiresAuth: true, title: "Debtor Ageing Summary ( > 6 Years)" },
    },
    {
      path: "/admin/kerisi/m/3446",
      name: "kerisi-cc-ageing-debtor-details-6y",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Debtor Ageing / Debtor Ageing Details ( > 6 Years )",
        cardTitle: "Debtor Ageing Details ( > 6 Years )",
        ageingKind: "debtor_details_ext",
      },
      meta: { requiresAuth: true, title: "Debtor Ageing Details ( > 6 Years )" },
    },
    {
      path: "/admin/kerisi/m/3409",
      name: "kerisi-cc-ageing-advance-listing",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Advance Ageing / Advance Listing",
        cardTitle: "Advance Listing",
        ageingKind: "advance_listing",
      },
      meta: { requiresAuth: true, title: "Advance Listing" },
    },
    {
      path: "/admin/kerisi/m/3448",
      name: "kerisi-cc-ageing-advance-summary-6y",
      component: CcAgeingReportView,
      props: {
        pageBreadcrumb: "Credit Control / Advance Ageing / Advance Ageing Summary (> 6 Years)",
        cardTitle: "Advance Ageing Summary (> 6 Years)",
        ageingKind: "advance_listing",
      },
      meta: { requiresAuth: true, title: "Advance Ageing Summary (> 6 Years)" },
    },
    {
      path: "/admin/kerisi/m/3380",
      name: "kerisi-cc-subsidiary-gl-individual",
      component: GeneralLedgerListingView,
      props: {
        pageBreadcrumb: "Credit Control / Subsidiary Ledger / Individual Subsidiary Ledger",
        cardTitle: "Individual Subsidiary Ledger",
        creditControlSubsidiary: true,
      },
      meta: { requiresAuth: true, title: "Individual Subsidiary Ledger" },
    },
    {
      path: "/admin/kerisi/m/3381",
      name: "kerisi-cc-subsidiary-gl-all",
      component: SubsidiaryLedgerAllView,
      props: {
        pageBreadcrumb: "Credit Control / Subsidiary Ledger / All Subsidiary Ledger",
        cardTitle: "All Subsidiary Ledger",
        legacyMenuId: "3381",
      },
      meta: { requiresAuth: true, title: "All Subsidiary Ledger" },
    },
    {
      path: "/admin/kerisi/m/2667",
      name: "kerisi-cc-subsidiary-statement",
      component: SubsidiaryLedgerAllView,
      props: {
        pageBreadcrumb: "Credit Control / Report / Subsidiary Statement",
        cardTitle: "Subsidiary Statement",
        legacyMenuId: "2667",
      },
      meta: { requiresAuth: true, title: "Subsidiary Statement" },
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
      redirect: (to) => ({ path: "/admin/kerisi/m/2289", query: to.query }),
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
      redirect: (to) => ({ path: "/admin/kerisi/m/2289", query: to.query }),
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
    // FIMS Asset Level 4 — PAGEID 2123 (MENUID 2574), 2139 (2591), 2221 (2697), 2267 (2756).
    { path: "/admin/kerisi/m/2574", name: "kerisi-asset-verification", component: AssetVerificationView, meta: { requiresAuth: true, title: "Asset / Asset Verification" } },
    {
      path: "/admin/kerisi/m/2591",
      name: "kerisi-asset-cancellation",
      component: AssetCancellationAssetsView,
      props: { selectionMode: true },
      meta: { requiresAuth: true, title: "Asset / Asset Cancellation" },
    },
    {
      path: "/admin/kerisi/m/2697",
      name: "kerisi-asset-cancellation-verify",
      component: AssetCancellationAssetsView,
      props: { selectionMode: false },
      meta: { requiresAuth: true, title: "Asset / Asset Cancellation For Verification" },
    },
    {
      path: "/admin/kerisi/m/2756",
      name: "kerisi-asset-cancellation-listing",
      component: AssetCancellationJournalListingView,
      meta: { requiresAuth: true, title: "Asset / Asset Cancellation Listing" },
    },
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
      meta: { requiresAuth: true, title: "Item Main", kerisiMenuId: 1820 },
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
