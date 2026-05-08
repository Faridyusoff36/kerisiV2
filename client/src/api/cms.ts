import { API_BASE_URL } from "@/env";
import { apiRequest, ensureCsrfCookie } from "./client";
import type {
  AccountBankByPayeeGenericRow,
  AccountBankByPayeeInvestmentRow,
  AccountBankByPayeeOptions,
  AccountBankByPayeeSponsorRow,
  AccountBankPayeeType,
  AccountBankUpdatedBillRow,
  AccountBankUpdatedOptions,
  AccountBankUpdatedPayeeType,
  AccountBankUpdatedProcessInput,
  AccountBankUpdatedProcessResult,
  AccountBankUpdatedVoucherRow,
  AuditLog,
  AssetInventoryRow,
  AssetVerificationRow,
  AssetVerificationDetail,
  AssetCancellationAssetRow,
  AssetCancellationJournalRow,
  AssetAccountSetupDetail,
  AssetAccountSetupRow,
  AssetBuildingLocationDetail,
  AssetBuildingLocationInput,
  AssetBuildingLocationRow,
  AssetDamageApplicationHeader,
  AssetDamageApplicationLine,
  AssetDamageListingRow,
  AssetDamageProcessFlowStep,
  AssetDepreciationGroupDetail,
  AssetDepreciationGroupInput,
  AssetDepreciationGroupRow,
  AssetDepreciationSchedulerRow,
  AssetDepreciationTanahDetail,
  AssetDepreciationTanahInput,
  AssetDepreciationTanahRow,
  AssetDisposeMethodInput,
  AssetDisposeMethodRow,
  AssetKewPaGrnRow,
  AssetMaintenanceListRow,
  AssetOrganizationCascadeRoleRow,
  AssetRoomLocationRow,
  AssetVerificationOfficerDetail,
  AssetVerificationOfficerInput,
  AssetVerificationOfficerRow,
  DisposalSecretariatInput,
  DisposalSecretariatRow,
  CapitalProjectProfilePatch,
  ProjectListRow,
  ProjectMonitoringBalance,
  ProjectMonitoringBalanceInput,
  AuditSystemTransactionOptions,
  AuditSystemTransactionRow,
  AuditSystemTransactionSql,
  ActivityGroupRow,
  BankAccountDetail,
  BankAccountInput,
  BankAccountOptions,
  BankAccountRow,
  BankAccountUpdateInput,
  BankMasterInput,
  BankMasterOptions,
  BankMasterRow,
  BankSetupInput,
  BankSetupOptions,
  BankSetupRow,
  CashbookListOptions,
  CashbookListRow,
  CashbookListType,
  CashbookPtjRow,
  AuthorizedReceiptingOptions,
  AuthorizedReceiptingRow,
  CreditNoteFormData,
  CreditNoteRow,
  DebitNoteFormData,
  DebitNoteRow,
  InvoiceLinesResponse,
  LookupOption,
  DebtorSearchOption,
  InvoiceSearchOption,
  SaveCreditNoteResponse,
  SaveDebitNoteResponse,
  DebtorOptions,
  DebtorProfileUpdateRow,
  DebtorRow,
  DiscountNoteRow,
  DiscountInvoiceLinesResponse,
  DiscountNoteFormData,
  DiscountPolicyOption,
  SaveDiscountNoteResponse,
  AuthorizedReceiptingFormData,
  SaveAuthorizedReceiptingResponse,
  CreditControlAgeingRow,
  CreditControlRefundStaffDetailRow,
  CreditControlReminderLookupOption,
  CreditControlReminderStatusRow,
  CcListOfRefundPortalRow,
  CcRefundApplicationAdminRow,
  CcRefundBrIntegrationRow,
  CcRequestRefundStaffRow,
  CurrentStaffProfile,
  ArEventSearchOption,
  ArStaffSearchOption,
  AccountActivityInput,
  AccountActivityRow,
  AccountCodeInput,
  AccountCodeRow,
  AccountCodePpiRow,
  AccountCodePpiOptions,
  ActivitySubgroupRow,
  ActivitySubsiriRow,
  ActivityTypeRow,
  BillsCustomWfInput,
  BillsSetupDetail,
  BillsSetupInput,
  BillsSetupRow,
  BudgetClosingOptions,
  BudgetClosingPayload,
  AllocationInput,
  AllocationOptions,
  AllocationRow,
  BudgetCodeInput,
  BudgetCodeOptions,
  BudgetCodeRow,
  BudgetAdvanceControlledRow,
  BudgetInAdvanceMasterShow,
  BudgetInitialOptions,
  BudgetInitialRow,
  BudgetInitialNewV2DetailRow,
  BudgetInitialNewV2Master,
  BudgetMonitoringOptions,
  BudgetMonitoringRow,
  BudgetMovementFormData,
  BudgetMovementOptions,
  BudgetMovementRow,
  BudgetMovementType,
  BudgetPlanningNewAccount,
  BudgetPlanningNewCreated,
  BudgetPlanningNewInput,
  BudgetPlanningNewOptions,
  BudgetPlanningOptions,
  BudgetPlanningRow,
  BudgetPlanningScope,
  BudgetPlanningScheduleInput,
  BudgetPlanningScheduleOptions,
  BudgetPlanningScheduleRow,
  BudgetStructureSearchForms,
  BudgetStructureSearchOptions,
  Category,
  CategoryInput,
  CascadeStructureInput,
  CascadeStructureRow,
  CheckErrorBillMasterRow,
  CheckErrorPayment2PelikRow,
  CheckErrorPaymentPelikRow,
  CheckErrorResitRow,
  CheckErrorUrlBrfHilangRow,
  CheckErrorVoucherDetailRow,
  CheckErrorVoucherMasterRow,
  CcCustomerOption,
  CcOption,
  CostCentreInput,
  CostCentreRow,
  DepositDetailInput,
  DepositDetailRow,
  DepositFormMaster,
  DepositFormMasterInput,
  DepositOptions,
  DepositRow,
  FundTypeInput,
  FundTypeRow,
  InvoiceBalanceOptions,
  InvoiceBalanceRow,
  JenisCarianDetail,
  ListOfDepositOptions,
  JenisCarianInput,
  JenisCarianRow,
  LetterPhraseDetail,
  LetterPhraseInput,
  LetterPhraseRow,
  Media,
  MediaMetadataInput,
  Page,
  PageInput,
  PayeeRegistrationOptions,
  PayeeRegistrationDetail,
  PayeeRegistrationRow,
  PettyCashApplicationDetail,
  PettyCashApplicationListOptions,
  PettyCashApplicationListRow,
  PettyCashBillRow,
  PettyCashByPtjRow,
  PettyCashClaimAccountCodeSuggestion,
  PettyCashClaimDimensionSuggestion,
  PettyCashClaimForm,
  PettyCashClaimPcmSuggestion,
  PettyCashClaimRequestBySuggestion,
  PettyCashClaimSavePayload,
  PettyCashClaimSaveResponse,
  PettyCashConfirmPaymentRow,
  PettyCashRecoupDetail,
  PettyCashRecoupRow,
  PettyCashReleasePaidApplicationRow,
  PettyCashReleasePaidReceiptRow,
  PettyCashRequestListRow,
  PettyCashVoucherListOptions,
  PettyCashVoucherListRow,
  PtjCodeInput,
  PtjCodeRow,
  Post,
  PostInput,
  PublicSiteSettings,
  Role,
  RoleInput,
  SemiStrictInput,
  SettingsPayload,
  StorefrontMenuItem,
  DebtorReminderRow,
  DebtorStatementFooter,
  DebtorStatementRow,
  GlListingOptions,
  GlListingRow,
  GlYearMonthDetail,
  GlYearMonthInput,
  GlYearMonthOptions,
  GlYearMonthRow,
  ProfileFloatingPointRow,
  JournalListingHeader,
  JournalListingLine,
  JournalListingOptions,
  JournalListingRow,
  LaporanBelanjawanOptions,
  LaporanBelanjawanRow,
  LaporanBelanjawanTotals,
  ManualJournalDetail,
  ManualJournalListingPdfPayload,
  ManualJournalOptions,
  ManualJournalRow,
  ManualInvoiceDetail,
  ManualInvoiceFooter,
  ManualInvoiceLineInput,
  ManualInvoiceOptions,
  ManualInvoiceRow,
  PostingToTbHeader,
  PostingToTbLine,
  PostingToTbOptions,
  PostingToTbRow,
  BankAccountUpdateOptions,
  BankAccountUpdateRow,
  LedgerOptions,
  LedgerRow,
  OfferedStudentOptions,
  OfferedStudentRow,
  StudentInsuranceListingOptions,
  StudentInsuranceListingRow,
  InvestmentAccrualOptions,
  InvestmentAccrualPostResult,
  InvestmentAccrualRow,
  InvestmentGenerateScheduleResult,
  InvestmentGenerateScheduleRow,
  InvestmentMonitoringBatchRow,
  InvestmentMonitoringInvestmentRow,
  InvestmentMonitoringSummaryPdfPayload,
  ListOfAccrualOptions,
  ListOfAccrualRow,
  InvestmentToBeWithdrawnModalData,
  InvestmentToBeWithdrawnOptions,
  InvestmentToBeWithdrawnRow,
  ListOfInvestmentOptions,
  ListOfInvestmentRow,
  SummaryListInvestmentOptions,
  SummaryListInvestmentRow,
  InvoiceDetails,
  InvoiceFooter,
  InvoiceOptions,
  InvoiceRow,
  PtptnDataDetail,
  PtptnDataHeader,
  PtptnDataRow,
  AdvancePaymentOptions,
  AdvancePaymentRow,
  SponsorInvoiceGenerationFooter,
  SponsorInvoiceGenerationOptions,
  SponsorInvoiceGenerationRow,
  SponsorListRow,
  SponsorProfileOptions,
  SponsorProfileRow,
  SponsorPtptnOptions,
  SponsorPtptnRow,
  StudentJournalApprovalDetail,
  StudentJournalApprovalFooter,
  StudentJournalApprovalRow,
  SubsidiaryLedgerAllOptions,
  SubsidiaryLedgerAllRow,
  StatusPoPrOptions,
  StatusPoPrRow,
  PurchasingVendorRow,
  PostDatedChequeRow,
  PortalAdvanceGenerateBillBatchRow,
  PortalAdvanceRecoupBillRow,
  PortalAdvanceRecoupDebitLineRow,
  PortalAdvanceRecoupHeader,
  EmergencyFundApprovedListingRow,
  EmergencyFundReminderReportRow,
  EmergencyFundAccrualRow,
  EmergencyFundReleaseQueueRow,
  StructureBudgetListOptions,
  StructureBudgetListRow,
  TotalAllocationOptions,
  TotalAllocationRow,
  TotalAllocationTotals,
  BudgetV2BudgetSummaryRow,
  UmumAllocationPtjFooter,
  UmumAllocationPtjOptions,
  UmumAllocationPtjRow,
  StudentInvoiceGenerationGenerateInput,
  StudentInvoiceGenerationGenerateResult,
  StudentInvoiceGenerationOptions,
  StudentInvoiceGenerationRow,
  StudentInvoiceGenerationSearchInput,
  StudentInvoiceGenerationSearchMeta,
  TenderQuotationRow,
  UtilityRegistrationDetail,
  UtilityRegistrationInput,
  UtilityRegistrationRow,
  UserDetail,
  UserInput,
  VcTncDetail,
  VcTncOptions,
  VcTncRow,
  VendorBillingRow,
  VendorPaymentRow,
  VendorPoStatusRow,
  VendorPortalAccountRow,
  VendorPortalAddressRow,
  VendorPortalCategoryRow,
  VendorPortalJobscopeRow,
  VendorPortalLicenceRow,
  VendorPortalLookups,
  VendorPortalOtherLicenceRow,
  VendorPortalProfile,
  VendorPortalProfileInput,
  VendorRegistrationFeeRow,
  VendorStatusCheck,
  VendorVoucherRow,
  SponsorLetterCatalogRow,
  SponsorLetterHistoryRow,
  StaffProfileAddress,
  StaffProfileAddressInput,
  StaffProfileChildRow,
  StaffProfileMaritalStatusInput,
  StaffProfileMaster,
  StaffProfileOptions,
  StaffProfileSpouseRow,
  IntegrationPtjRow,
  IntegrationPtjPromoteInput,
  IntegrationCostCentreRow,
  IntegrationCostCentrePromoteInput,
  IntegrationProfileRow,
  IntegrationActivityRow,
  BudgetNotExistsRow,
  ListOfCurrencyRow,
  ListOfCurrencyInput,
  ListOfCurrencyUpdate,
  CountryOption,
  AgRateRow,
  AgRateLine,
  AgRateCurrencyOption,
  AgRateOptions,
  AgRateEntryInput,
} from "@/types";
import type { AdminMenuPrefs } from "@/config/admin-menu";

export async function fetchDashboardSummary() {
  return apiRequest<{ data: { counts: { posts: number; pages: number; media: number }; recent: { posts: Post[]; pages: Page[] } } }>(
    "/api/dashboard/summary",
  );
}

export async function listPosts(params = "") {
  return apiRequest<{ data: Post[]; meta: Record<string, unknown> }>(`/api/posts${params}`);
}

export async function getPost(id: number) {
  return apiRequest<{ data: Post }>(`/api/posts/${id}`);
}

export async function createPost(input: PostInput) {
  return apiRequest<{ data: Post }>("/api/posts", { method: "POST", body: JSON.stringify(input) });
}

export async function updatePost(id: number, input: PostInput) {
  return apiRequest<{ data: Post }>(`/api/posts/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function deletePost(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/posts/${id}`, { method: "DELETE" });
}

// Categories
export async function listCategories(params = "") {
  return apiRequest<{ data: Category[]; meta: Record<string, unknown> }>(`/api/categories${params}`);
}

export async function getCategory(id: number) {
  return apiRequest<{ data: Category }>(`/api/categories/${id}`);
}

export async function createCategory(input: CategoryInput) {
  return apiRequest<{ data: Category }>("/api/categories", { method: "POST", body: JSON.stringify(input) });
}

export async function updateCategory(id: number, input: CategoryInput) {
  return apiRequest<{ data: Category }>(`/api/categories/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function deleteCategory(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/categories/${id}`, { method: "DELETE" });
}

export async function listPages(params = "") {
  return apiRequest<{ data: Page[]; meta: Record<string, unknown> }>(`/api/pages${params}`);
}

export async function getPage(id: number) {
  return apiRequest<{ data: Page }>(`/api/pages/${id}`);
}

export async function createPage(input: PageInput) {
  return apiRequest<{ data: Page }>("/api/pages", { method: "POST", body: JSON.stringify(input) });
}

export async function updatePage(id: number, input: PageInput) {
  return apiRequest<{ data: Page }>(`/api/pages/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function deletePage(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/pages/${id}`, { method: "DELETE" });
}

export async function listMedia() {
  return apiRequest<{ data: Media[] }>("/api/media");
}

export async function uploadMedia(file: File) {
  const formData = new FormData();
  formData.append("file", file);
  return apiRequest<{ data: Media }>("/api/media/upload", { method: "POST", body: formData });
}

export async function removeMedia(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/media/${id}`, { method: "DELETE" });
}

export async function updateMediaMetadata(id: number, input: MediaMetadataInput) {
  return apiRequest<{ data: Media }>(`/api/media/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function getSettings() {
  return apiRequest<{ data: SettingsPayload }>("/api/settings");
}

export async function updateSettings(payload: SettingsPayload) {
  return apiRequest<{ data: SettingsPayload }>("/api/settings", {
    method: "PUT",
    body: JSON.stringify(payload),
  });
}

export async function getAdminMenuPrefs() {
  return apiRequest<{ data: AdminMenuPrefs | null }>("/api/settings/admin-menu-prefs");
}

export async function saveAdminMenuPrefs(prefs: AdminMenuPrefs) {
  return apiRequest<{ data: AdminMenuPrefs }>("/api/settings/admin-menu-prefs", {
    method: "PUT",
    body: JSON.stringify(prefs),
  });
}

export async function getStorefrontMenu() {
  return apiRequest<{ data: StorefrontMenuItem[] }>("/api/settings/storefront-menu");
}

export async function saveStorefrontMenu(items: StorefrontMenuItem[]) {
  return apiRequest<{ data: StorefrontMenuItem[] }>("/api/settings/storefront-menu", {
    method: "PUT",
    body: JSON.stringify(items),
  });
}

export async function getPublicSiteSettings() {
  return apiRequest<{ data: PublicSiteSettings }>("/api/public/site");
}

export async function getPublicFrontPage() {
  return apiRequest<{ data: Page; meta?: { source?: string } }>("/api/public/pages/frontpage");
}

export async function getPublicPageBySlug(slug: string) {
  return apiRequest<{ data: Page }>(`/api/public/pages/${encodeURIComponent(slug)}`);
}

// Users
export async function listUsers() {
  return apiRequest<{ data: UserDetail[] }>("/api/users");
}

export async function getUser(id: number) {
  return apiRequest<{ data: UserDetail }>(`/api/users/${id}`);
}

export async function createUser(input: UserInput) {
  return apiRequest<{ data: UserDetail }>("/api/users", { method: "POST", body: JSON.stringify(input) });
}

export async function updateUser(id: number, input: UserInput) {
  return apiRequest<{ data: UserDetail }>(`/api/users/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function deleteUser(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/users/${id}`, { method: "DELETE" });
}

// Roles
export async function listRoles() {
  return apiRequest<{ data: Role[] }>("/api/roles");
}

export async function getRole(id: number) {
  return apiRequest<{ data: Role }>(`/api/roles/${id}`);
}

export async function createRole(input: RoleInput) {
  return apiRequest<{ data: Role }>("/api/roles", { method: "POST", body: JSON.stringify(input) });
}

export async function updateRole(id: number, input: RoleInput) {
  return apiRequest<{ data: Role }>(`/api/roles/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function deleteRole(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/roles/${id}`, { method: "DELETE" });
}

// Audit Logs
export async function listAuditLogs(params = "") {
  return apiRequest<{ data: AuditLog[]; meta: Record<string, unknown> }>(`/api/audit-logs${params}`);
}

// Developers Guide
export async function getDevelopersGuide() {
  return apiRequest<{ data: { content: string; syncFiles: { filename: string; path?: string; exists: boolean; inSync: boolean; readOnly?: boolean; role?: "canonical" | "mirror" }[] } }>("/api/developers-guide");
}

export async function updateDevelopersGuide(content: string) {
  return apiRequest<{ data: { success: boolean; syncFiles: { filename: string; path?: string; exists: boolean; inSync: boolean; readOnly?: boolean; role?: "canonical" | "mirror" }[] } }>("/api/developers-guide", {
    method: "PUT",
    body: JSON.stringify({ content }),
  });
}

export async function listFundTypes(params = "") {
  return apiRequest<{ data: FundTypeRow[]; meta: Record<string, unknown> }>(`/api/fund-types${params}`);
}

export async function getFundType(id: number) {
  return apiRequest<{ data: { id: number; ftyFundType: string; ftyFundDesc: string; ftyFundDescEng: string | null; ftyBasis: string; ftyStatus: number; ftyRemark: string | null } }>(`/api/fund-types/${id}`);
}

export async function createFundType(input: FundTypeInput) {
  return apiRequest<{ data: { id: number } }>("/api/fund-types", { method: "POST", body: JSON.stringify(input) });
}

export async function updateFundType(id: number, input: FundTypeInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/fund-types/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function listAssetDisposeMethods(params = "") {
  return apiRequest<{ data: AssetDisposeMethodRow[]; meta: Record<string, unknown> }>(`/api/asset/dispose-methods${params}`);
}

export async function getAssetDisposeMethod(id: number) {
  return apiRequest<{ data: { adtId: number; adtCode: string; adtName: string; adtStatus: number } }>(`/api/asset/dispose-methods/${id}`);
}

export async function createAssetDisposeMethod(input: AssetDisposeMethodInput) {
  return apiRequest<{ data: { id: number; adtId: number } }>("/api/asset/dispose-methods", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateAssetDisposeMethod(id: number, input: AssetDisposeMethodInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/dispose-methods/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function listAssetBuildingLocations(params = "") {
  return apiRequest<{ data: AssetBuildingLocationRow[]; meta: Record<string, unknown> }>(`/api/asset/building-locations${params}`);
}

export async function getAssetBuildingLocation(id: number) {
  return apiRequest<{ data: AssetBuildingLocationDetail }>(`/api/asset/building-locations/${id}`);
}

export async function createAssetBuildingLocation(input: AssetBuildingLocationInput) {
  return apiRequest<{ data: { id: number; bdlId: number } }>("/api/asset/building-locations", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateAssetBuildingLocation(id: number, input: AssetBuildingLocationInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/building-locations/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteAssetBuildingLocation(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/building-locations/${id}`, {
    method: "DELETE",
  });
}

export async function listAssetRoomLocations(params = "") {
  return apiRequest<{ data: AssetRoomLocationRow[]; meta: Record<string, unknown> }>(`/api/asset/room-location-listing${params}`);
}

export async function listAssetDamageRegister(params = "") {
  return apiRequest<{ data: AssetDamageListingRow[]; meta: Record<string, unknown> }>(`/api/asset/damage-listing${params}`);
}

export async function getAssetDamageApplication(drmId: number) {
  return apiRequest<{
    data: {
      header: AssetDamageApplicationHeader;
      lines: AssetDamageApplicationLine[];
      processFlow: AssetDamageProcessFlowStep[];
    };
  }>(`/api/asset/damage-application/${drmId}`);
}

export async function listAssetMaintenanceRegister(params = "") {
  return apiRequest<{ data: AssetMaintenanceListRow[]; meta: Record<string, unknown> }>(`/api/asset/maintenance-listing${params}`);
}

export async function listAssetGoodsReceiveKewpa(params = "") {
  return apiRequest<{ data: AssetKewPaGrnRow[]; meta: Record<string, unknown> }>(`/api/asset/goods-receive-kewpa${params}`);
}

export async function getDisposalSecretariatOptions(params = "") {
  return apiRequest<{
    data: { itemSubcats: { value: string; label: string }[]; staff: { value: string; label: string }[] };
  }>(`/api/asset/disposal-secretariat/options${params}`);
}

export async function listDisposalSecretariats(params = "") {
  return apiRequest<{ data: DisposalSecretariatRow[]; meta: Record<string, unknown> }>(`/api/asset/disposal-secretariat${params}`);
}

export async function getDisposalSecretariat(id: number) {
  return apiRequest<{
    data: {
      astId: number;
      iscType: string;
      stfStaffId: string;
      stfStaffIdSuperior: string;
      stfStaffIdHod: string;
      astStatus: number;
    };
  }>(`/api/asset/disposal-secretariat/${id}`);
}

export async function createDisposalSecretariat(input: DisposalSecretariatInput) {
  return apiRequest<{ data: { id: number; astId: number } }>("/api/asset/disposal-secretariat", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateDisposalSecretariat(id: number, input: DisposalSecretariatInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/disposal-secretariat/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteDisposalSecretariat(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/disposal-secretariat/${id}`, {
    method: "DELETE",
  });
}

export async function listAssetDepreciationGroups(params = "") {
  return apiRequest<{ data: AssetDepreciationGroupRow[]; meta: Record<string, unknown> }>(`/api/asset/depreciation-groups${params}`);
}

export async function getAssetDepreciationGroup(id: number) {
  return apiRequest<{ data: AssetDepreciationGroupDetail }>(`/api/asset/depreciation-groups/${id}`);
}

export async function createAssetDepreciationGroup(input: AssetDepreciationGroupInput) {
  return apiRequest<{ data: { ldeId: number } }>("/api/asset/depreciation-groups", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateAssetDepreciationGroup(id: number, input: AssetDepreciationGroupInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/depreciation-groups/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteAssetDepreciationGroup(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/depreciation-groups/${id}`, { method: "DELETE" });
}

export async function listAssetDepreciationTanah(params = "") {
  return apiRequest<{ data: AssetDepreciationTanahRow[]; meta: Record<string, unknown> }>(`/api/asset/depreciation-tanah${params}`);
}

export async function getAssetDepreciationTanah(id: number) {
  return apiRequest<{ data: AssetDepreciationTanahDetail }>(`/api/asset/depreciation-tanah/${id}`);
}

export async function createAssetDepreciationTanah(input: AssetDepreciationTanahInput) {
  return apiRequest<{ data: { adtDeprId: number } }>("/api/asset/depreciation-tanah", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateAssetDepreciationTanah(id: number, input: AssetDepreciationTanahInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/depreciation-tanah/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteAssetDepreciationTanah(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/depreciation-tanah/${id}`, { method: "DELETE" });
}

export async function listAssetDepreciationScheduler(params = "") {
  return apiRequest<{ data: AssetDepreciationSchedulerRow[]; meta: Record<string, unknown> }>(`/api/asset/depreciation-scheduler${params}`);
}

export async function updateAssetDepreciationScheduler(id: number, input: { adscDay: string; adscIsopen: string }) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/depreciation-scheduler/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function listAssetAccountSetup(params = "") {
  return apiRequest<{ data: AssetAccountSetupRow[]; meta: Record<string, unknown> }>(`/api/asset/account-setup${params}`);
}

export async function getAssetAccountSetup(id: number) {
  return apiRequest<{ data: AssetAccountSetupDetail }>(`/api/asset/account-setup/${id}`);
}

export async function listAssetVerificationOfficers(params = "") {
  return apiRequest<{ data: AssetVerificationOfficerRow[]; meta: Record<string, unknown> }>(`/api/asset/verification-officers${params}`);
}

export async function getAssetVerificationOfficer(id: number) {
  return apiRequest<{ data: AssetVerificationOfficerDetail }>(`/api/asset/verification-officers/${id}`);
}

export async function createAssetVerificationOfficer(input: AssetVerificationOfficerInput) {
  return apiRequest<{ data: { id: number } }>("/api/asset/verification-officers", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateAssetVerificationOfficer(id: number, input: AssetVerificationOfficerInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/verification-officers/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteAssetVerificationOfficer(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/verification-officers/${id}`, { method: "DELETE" });
}

export async function listAssetOrganizationCascadeRoles(params = "") {
  return apiRequest<{ data: AssetOrganizationCascadeRoleRow[]; meta: Record<string, unknown> }>(`/api/asset/organization-cascade-roles${params}`);
}

export async function getFundTypeOptions() {
  return apiRequest<{
    data: {
      smartFilter: {
        fundType: { id: string; label: string }[];
        basis: { id: string; label: string }[];
        status: { id: string; label: string }[];
      };
      popupModal: {
        basis: { id: string; label: string }[];
        status: { id: number; label: string }[];
      };
    };
  }>("/api/fund-types/options");
}

export async function listActivityCodeLevel(params = "") {
  return apiRequest<{ data: ActivityGroupRow[] | ActivitySubgroupRow[] | ActivitySubsiriRow[] | ActivityTypeRow[] }>(
    `/api/setup/activity-code${params}`,
  );
}

export async function createActivityGroup(input: { activityGroupCode: string; activityGroupDesc: string }) {
  return apiRequest<{ data: { success: boolean } }>("/api/setup/activity-code/group", { method: "POST", body: JSON.stringify(input) });
}

export async function updateActivityGroup(code: string, input: { activityGroupDesc: string }) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/group/${encodeURIComponent(code)}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteActivityGroup(code: string) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/group/${encodeURIComponent(code)}`, { method: "DELETE" });
}

export async function createActivitySubgroup(input: { activityGroupCode: string; activitySubgroupCode: string; activitySubgroupDesc: string }) {
  return apiRequest<{ data: { success: boolean } }>("/api/setup/activity-code/subgroup", { method: "POST", body: JSON.stringify(input) });
}

export async function updateActivitySubgroup(code: string, input: { activityGroupCode: string; activitySubgroupDesc: string }) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/subgroup/${encodeURIComponent(code)}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteActivitySubgroup(code: string, activityGroupCode: string) {
  const params = new URLSearchParams({ activityGroupCode });
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/subgroup/${encodeURIComponent(code)}?${params.toString()}`, { method: "DELETE" });
}

export async function createActivitySubsiri(input: {
  activityGroup: string;
  activitySubgroupCode: string;
  activitySubsiriCode: string;
  activitySubsiriDesc: string;
  activitySubsiriDescEng?: string;
}) {
  return apiRequest<{ data: { success: boolean } }>("/api/setup/activity-code/subsiri", { method: "POST", body: JSON.stringify(input) });
}

export async function updateActivitySubsiri(
  code: string,
  input: { activityGroup: string; activitySubgroupCode: string; activitySubsiriDesc: string; activitySubsiriDescEng?: string },
) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/subsiri/${encodeURIComponent(code)}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteActivitySubsiri(code: string, activityGroup: string, activitySubgroupCode: string) {
  const params = new URLSearchParams({ activityGroup, activitySubgroupCode });
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/subsiri/${encodeURIComponent(code)}?${params.toString()}`, { method: "DELETE" });
}

export async function createActivityType(input: {
  activityGroupCode: string;
  activitySubgroupCode: string;
  activitySubsiriCode: string;
  atActivityCode: string;
  atActivityDescriptionBm: string;
  atActivityDescriptionEn?: string;
  atStatus: "ACTIVE" | "INACTIVE";
}) {
  return apiRequest<{ data: { id: number } }>("/api/setup/activity-code/activity-type", { method: "POST", body: JSON.stringify(input) });
}

export async function updateActivityType(
  id: number,
  input: { atActivityDescriptionBm: string; atActivityDescriptionEn?: string; atStatus: "ACTIVE" | "INACTIVE" },
) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/activity-type/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function deleteActivityType(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/activity-code/activity-type/${id}`, { method: "DELETE" });
}

export async function listPtjCodeLevel(params = "") {
  return apiRequest<{ data: PtjCodeRow[] }>(`/api/setup/ptj-code${params}`);
}

export async function createPtjCode(input: PtjCodeInput) {
  return apiRequest<{ data: { ounId: number; ounCode: string } }>("/api/setup/ptj-code", { method: "POST", body: JSON.stringify(input) });
}

export async function updatePtjCode(code: string, input: Partial<PtjCodeInput>) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/ptj-code/${encodeURIComponent(code)}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deletePtjCode(code: string) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/ptj-code/${encodeURIComponent(code)}`, { method: "DELETE" });
}

export async function listAccountCodeLevel(params = "") {
  return apiRequest<{ data: AccountActivityRow[] | AccountCodeRow[] }>(`/api/setup/account-code${params}`);
}

export async function createAccountCode(input: AccountCodeInput) {
  return apiRequest<{ data: { success: boolean } }>("/api/setup/account-code", { method: "POST", body: JSON.stringify(input) });
}

export async function updateAccountCode(code: string, input: Partial<AccountCodeInput>) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/account-code/${encodeURIComponent(code)}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteAccountCode(code: string) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/account-code/${encodeURIComponent(code)}`, { method: "DELETE" });
}

export async function createAccountActivity(input: AccountActivityInput) {
  return apiRequest<{ data: { ldeId: number } }>("/api/setup/account-code/activity", { method: "POST", body: JSON.stringify(input) });
}

export async function updateAccountActivity(id: number, input: AccountActivityInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/account-code/activity/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteAccountActivity(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/account-code/activity/${id}`, { method: "DELETE" });
}

export async function listAccountCodePpi(params = "") {
  return apiRequest<{ data: AccountCodePpiRow[]; meta: Record<string, unknown> }>(`/api/setup/account-code-ppi${params}`);
}

export async function getAccountCodePpiOptions() {
  return apiRequest<{ data: AccountCodePpiOptions }>("/api/setup/account-code-ppi/options");
}

export async function listCostCentres(params = "") {
  return apiRequest<{ data: CostCentreRow[]; meta: Record<string, unknown> }>(`/api/setup/cost-centre${params}`);
}

export async function getCostCentre(id: number) {
  return apiRequest<{ data: CostCentreRow }>(`/api/setup/cost-centre/${id}`);
}

export async function createCostCentre(input: CostCentreInput) {
  return apiRequest<{ data: { id: number } }>("/api/setup/cost-centre", { method: "POST", body: JSON.stringify(input) });
}

export async function updateCostCentre(id: number, input: CostCentreInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/cost-centre/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

export async function getCostCentreOptions() {
  return apiRequest<{
    data: {
      smartFilter: { costCentre: { id: string; label: string }[]; ptjCode: { id: string; label: string }[]; status: { id: string; label: string }[] };
      popupModal: {
        ptjCode: { id: string; label: string }[];
        status: { id: string; label: string }[];
        flagSalary: { id: string; label: string }[];
      };
    };
  }>("/api/setup/cost-centre/options");
}

export async function listCascadeStructures(params = "") {
  return apiRequest<{ data: CascadeStructureRow[]; meta: Record<string, unknown> }>(`/api/setup/cascade-structure${params}`);
}

export async function getCascadeStructure(id: number) {
  return apiRequest<{ data: CascadeStructureRow }>(`/api/setup/cascade-structure/${id}`);
}

export async function createCascadeStructure(input: CascadeStructureInput) {
  return apiRequest<{ data: { id: number } }>("/api/setup/cascade-structure", { method: "POST", body: JSON.stringify(input) });
}

export async function updateCascadeStructure(id: number, input: CascadeStructureInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/cascade-structure/${id}`, { method: "PUT", body: JSON.stringify(input) });
}

// FIMS Budget (Increment / Decrement / Virement) — list + read-only movement form payload.
export async function listBudgetMovements(type: BudgetMovementType, params = "") {
  return apiRequest<{ data: BudgetMovementRow[]; meta: Record<string, unknown> }>(
    `/api/budget/movements/${encodeURIComponent(type)}${params}`,
  );
}

export async function getBudgetMovement(id: string | number) {
  return apiRequest<{ data: BudgetMovementRow }>(`/api/budget/movements/show/${encodeURIComponent(String(id))}`);
}

export async function getBudgetMovementForm(type: BudgetMovementType, id: string | number) {
  return apiRequest<{ data: BudgetMovementFormData }>(
    `/api/budget/movements/${encodeURIComponent(type)}/${encodeURIComponent(String(id))}/form`,
  );
}

export async function getBudgetMovementOptions(type: BudgetMovementType) {
  return apiRequest<{ data: BudgetMovementOptions }>(`/api/budget/movements/${encodeURIComponent(type)}/options`);
}

// FIMS Budget Monitoring (PAGEID 1201 / MENUID 1471) — read-only aggregated list.
export async function listBudgetMonitoring(params = "") {
  return apiRequest<{ data: BudgetMonitoringRow[]; meta: Record<string, unknown> }>(
    `/api/budget/monitoring${params}`,
  );
}

export async function getBudgetMonitoringOptions() {
  return apiRequest<{ data: BudgetMonitoringOptions }>("/api/budget/monitoring/options");
}

export async function listBudgetMonitoringListing(params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/budget/monitoring/listing${params}`,
  );
}

// FIMS Budget / Budget Advance Controlled (PAGEID 1784 / MENUID 2160).
export async function listBudgetAdvanceControlled(params = "") {
  return apiRequest<{ data: BudgetAdvanceControlledRow[]; meta: Record<string, unknown> }>(
    `/api/budget/advance-controlled${params}`,
  );
}

/** Budget In Advance full list (PAGEID 1737 / MENUID 2098). */
export async function listBudgetInAdvance(params = "") {
  return apiRequest<{ data: BudgetAdvanceControlledRow[]; meta: Record<string, unknown> }>(
    `/api/budget/in-advance${params}`,
  );
}

export async function getBudgetInAdvanceMaster(id: string | number) {
  return apiRequest<{ data: BudgetInAdvanceMasterShow }>(`/api/budget/in-advance/${encodeURIComponent(String(id))}`);
}

// FIMS Budget Initial listing (PAGEID 1264 / MENUID 1541).
export async function listBudgetInitial(params = "") {
  return apiRequest<{ data: BudgetInitialRow[]; meta: Record<string, unknown> }>(
    `/api/budget/initial${params}`,
  );
}

export async function getBudgetInitialNewV2Master(bamId: number) {
  return apiRequest<{ data: BudgetInitialNewV2Master }>(`/api/budget/initial-new-v2/master/${bamId}`);
}

export async function listBudgetInitialNewV2Details(params = "") {
  return apiRequest<{
    data: BudgetInitialNewV2DetailRow[];
    meta: Record<string, unknown>;
  }>(`/api/budget/initial-new-v2/details${params}`);
}

export async function getBudgetInitialOptions() {
  return apiRequest<{ data: BudgetInitialOptions }>("/api/budget/initial/options");
}

// FIMS Budget Closing (PAGEID 1953) — options + process/reverse stubs.
export async function getBudgetClosingOptions() {
  return apiRequest<{ data: BudgetClosingOptions }>("/api/budget/closing/options");
}

export async function budgetClosingProcess(payload: BudgetClosingPayload) {
  return apiRequest<{ data: unknown }>("/api/budget/closing/process", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function budgetClosingReverse(payload: BudgetClosingPayload) {
  return apiRequest<{ data: unknown }>("/api/budget/closing/reverse", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function getCascadeStructureOptions(ptjCode = "") {
  const params = ptjCode ? `?ptjCode=${encodeURIComponent(ptjCode)}` : "";
  return apiRequest<{
    data: {
      smartFilter: {
        fund: { id: string; label: string }[];
        activity: { id: string; label: string }[];
        ptj: { id: string; label: string }[];
        costCenter: { id: string; label: string }[];
        status: { id: string; label: string }[];
      };
      popupModal: {
        fund: { id: string; label: string }[];
        activity: { id: string; label: string }[];
        ptj: { id: string; label: string }[];
        costCenter: { id: string; label: string }[];
        status: { id: string; label: string }[];
      };
    };
  }>(`/api/setup/cascade-structure/options${params}`);
}

// Letter Phrase setup (PAGEID 2911 / MENUID 3506). Read-only listing with
// an edit-only popup modal — legacy BL never exposed add or delete.
export async function listLetterPhrases(params = "") {
  return apiRequest<{ data: LetterPhraseRow[]; meta: Record<string, unknown> }>(
    `/api/setup/letter-phrase${params}`,
  );
}

export async function getLetterPhrase(lpmValue: string) {
  return apiRequest<{ data: LetterPhraseDetail }>(
    `/api/setup/letter-phrase/${encodeURIComponent(lpmValue)}`,
  );
}

export async function updateLetterPhrase(lpmValue: string, input: LetterPhraseInput) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/setup/letter-phrase/${encodeURIComponent(lpmValue)}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

// Petty Cash Recoup list (PAGEID 1255 / MENUID 1532).
export async function listPettyCashRecoups(params = "") {
  return apiRequest<{ data: PettyCashRecoupRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/recoup${params}`,
  );
}

// Petty Cash Recoup form view (PAGEID 1256 / MENUID 1534).
export async function getPettyCashRecoup(pcbId: number) {
  return apiRequest<{ data: PettyCashRecoupDetail }>(`/api/petty-cash/recoup/${pcbId}`);
}

export async function getPettyCashApplicationListOptions() {
  return apiRequest<{ data: PettyCashApplicationListOptions }>("/api/petty-cash/applications/options");
}

export async function listPettyCashApplications(params = "") {
  return apiRequest<{ data: PettyCashApplicationListRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/applications${params}`,
  );
}

export async function getPettyCashApplication(id: number) {
  return apiRequest<{ data: PettyCashApplicationDetail }>(`/api/petty-cash/applications/${id}`);
}

// Petty Cash Claim Form (PAGEID 1544 / MENUID 1872). Legacy BL
// MM_API_PETTYCASH_PETTYCASHCLAIMFORM.
export async function getPettyCashClaim(id: number) {
  return apiRequest<{ data: PettyCashClaimForm }>(`/api/petty-cash/claim-form/${id}`);
}

export async function suggestPettyCashClaimRequestBy(q: string, limit = 20) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  return apiRequest<{ data: PettyCashClaimRequestBySuggestion[] }>(
    `/api/petty-cash/claim-form/request-by/suggest?${params.toString()}`,
  );
}

export async function suggestPettyCashClaimPcm(q: string, ptjCode = "", limit = 20) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  if (ptjCode) params.set("ptj_code", ptjCode);
  return apiRequest<{ data: PettyCashClaimPcmSuggestion[] }>(
    `/api/petty-cash/claim-form/pcm/suggest?${params.toString()}`,
  );
}

export async function suggestPettyCashClaimAccountCode(q: string, fundType = "", limit = 20) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  if (fundType) params.set("fund_type", fundType);
  return apiRequest<{ data: PettyCashClaimAccountCodeSuggestion[] }>(
    `/api/petty-cash/claim-form/account-code/suggest?${params.toString()}`,
  );
}

export async function suggestPettyCashClaimFundType(q = "", limit = 50) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  return apiRequest<{ data: PettyCashClaimDimensionSuggestion[] }>(
    `/api/petty-cash/claim-form/fund-type/suggest?${params.toString()}`,
  );
}

export async function suggestPettyCashClaimActivityCode(
  q = "",
  fundType = "",
  limit = 50,
) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  if (fundType) params.set("fund_type", fundType);
  return apiRequest<{ data: PettyCashClaimDimensionSuggestion[] }>(
    `/api/petty-cash/claim-form/activity-code/suggest?${params.toString()}`,
  );
}

export async function suggestPettyCashClaimOun(
  q = "",
  opts: { fundType?: string; activityCode?: string } = {},
  limit = 50,
) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  if (opts.fundType) params.set("fund_type", opts.fundType);
  if (opts.activityCode) params.set("activity_code", opts.activityCode);
  return apiRequest<{ data: PettyCashClaimDimensionSuggestion[] }>(
    `/api/petty-cash/claim-form/oun/suggest?${params.toString()}`,
  );
}

export async function suggestPettyCashClaimCostCentre(
  q = "",
  opts: { fundType?: string; activityCode?: string; ounCode?: string } = {},
  limit = 50,
) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  if (opts.fundType) params.set("fund_type", opts.fundType);
  if (opts.activityCode) params.set("activity_code", opts.activityCode);
  if (opts.ounCode) params.set("oun_code", opts.ounCode);
  return apiRequest<{ data: PettyCashClaimDimensionSuggestion[] }>(
    `/api/petty-cash/claim-form/cost-centre/suggest?${params.toString()}`,
  );
}

export async function getPettyCashClaimNextSeq() {
  return apiRequest<{ data: { pcdId: number } }>(`/api/petty-cash/claim-form/next-seq`);
}

export async function savePettyCashClaim(payload: PettyCashClaimSavePayload) {
  return apiRequest<{ data: PettyCashClaimSaveResponse }>("/api/petty-cash/claim-form", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function submitPettyCashClaim(id: number) {
  return apiRequest<{ data: { status: string; pmsStatus: string; workflowStub: boolean; message: string } }>(
    `/api/petty-cash/claim-form/${id}/submit`,
    { method: "POST", body: JSON.stringify({}) },
  );
}

export async function cancelPettyCashClaim(id: number, cancelReason: string) {
  return apiRequest<{ data: { status: string; pmsStatus: string; message: string } }>(
    `/api/petty-cash/claim-form/${id}/cancel`,
    { method: "POST", body: JSON.stringify({ cancelReason }) },
  );
}

export async function getPettyCashClaimProcessFlow(id: number) {
  return apiRequest<{ data: unknown[]; meta?: Record<string, unknown> }>(
    `/api/petty-cash/claim-form/${id}/process-flow`,
  );
}

// List Petty Cash by PTJ (PAGEID 1963 / MENUID 2399).
export async function listPettyCashByPtj(params = "") {
  return apiRequest<{ data: PettyCashByPtjRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/by-ptj${params}`,
  );
}

// Bill Petty Cash (PAGEID 1964 / MENUID 2400).
export async function listPettyCashBills(params = "") {
  return apiRequest<{ data: PettyCashBillRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/bills${params}`,
  );
}

// Confirmation Payment — Petty Cash (PAGEID 1982 / MENUID 2424).
export async function listPettyCashConfirmPaymentAwaiting(params = "") {
  return apiRequest<{ data: PettyCashConfirmPaymentRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/confirm-payment/awaiting${params}`,
  );
}

export async function listPettyCashConfirmPaymentConfirmed(params = "") {
  return apiRequest<{ data: PettyCashConfirmPaymentRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/confirm-payment/confirmed${params}`,
  );
}

// Request Petty Cash list (PAGEID 2010 / MENUID 2456).
export async function listPettyCashRequests(params = "") {
  return apiRequest<{ data: PettyCashRequestListRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/requests${params}`,
  );
}

// List of Release Paid — Petty Cash (PAGEID 2273 / MENUID 2761).
export async function listPettyCashReleasePaidApplications(params = "") {
  return apiRequest<{ data: PettyCashReleasePaidApplicationRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/release-paid/applications${params}`,
  );
}

export async function listPettyCashReleasePaidReceipts(params = "") {
  return apiRequest<{ data: PettyCashReleasePaidReceiptRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/release-paid/receipts${params}`,
  );
}

// List of Voucher Petty Cash (PAGEID 2774 / MENUID 3344).
export async function getPettyCashVoucherListOptions() {
  return apiRequest<{ data: PettyCashVoucherListOptions }>("/api/petty-cash/vouchers/options");
}

export async function listPettyCashVouchers(params = "") {
  return apiRequest<{ data: PettyCashVoucherListRow[]; meta: Record<string, unknown> }>(
    `/api/petty-cash/vouchers${params}`,
  );
}

// HOD, VC & TNC setup (PAGEID 1715 / MENUID 2073).
export async function listVcTnc(params = "") {
  return apiRequest<{ data: VcTncRow[]; meta: Record<string, unknown> }>(
    `/api/setup/vc-tnc${params}`,
  );
}

export async function getVcTnc(id: number) {
  return apiRequest<{ data: VcTncDetail }>(`/api/setup/vc-tnc/${id}`);
}

export async function getVcTncOptions() {
  return apiRequest<{ data: VcTncOptions }>("/api/setup/vc-tnc/options");
}

export async function updateVcTnc(id: number, input: { stStaffIdSuperior: string }) {
  return apiRequest<{ data: { success: boolean } }>(`/api/setup/vc-tnc/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

// "Cek yang mungkin error" (PAGEID 2253 / MENUID 2740).
export async function listCheckErrorBillMaster(params = "") {
  return apiRequest<{ data: CheckErrorBillMasterRow[]; meta: Record<string, unknown> }>(
    `/api/setup/check-error/bill-master${params}`,
  );
}

export async function listCheckErrorVoucherDetail(params = "") {
  return apiRequest<{ data: CheckErrorVoucherDetailRow[]; meta: Record<string, unknown> }>(
    `/api/setup/check-error/voucher-detail${params}`,
  );
}

export async function listCheckErrorVoucherMaster(params = "") {
  return apiRequest<{ data: CheckErrorVoucherMasterRow[]; meta: Record<string, unknown> }>(
    `/api/setup/check-error/voucher-master${params}`,
  );
}

export async function listCheckErrorPaymentPelik(params = "") {
  return apiRequest<{ data: CheckErrorPaymentPelikRow[]; meta: Record<string, unknown> }>(
    `/api/setup/check-error/payment-record-pelik${params}`,
  );
}

export async function listCheckErrorPayment2Pelik(params = "") {
  return apiRequest<{ data: CheckErrorPayment2PelikRow[]; meta: Record<string, unknown> }>(
    `/api/setup/check-error/payment-record-pelik2${params}`,
  );
}

export async function listCheckErrorUrlBrfHilang(params = "") {
  return apiRequest<{ data: CheckErrorUrlBrfHilangRow[]; meta: Record<string, unknown> }>(
    `/api/setup/check-error/url-brf-hilang${params}`,
  );
}

export async function listCheckErrorResit(params = "") {
  return apiRequest<{ data: CheckErrorResitRow[]; meta: Record<string, unknown> }>(
    `/api/setup/check-error/resit-no-allocate${params}`,
  );
}

// Setup Carian Structure Budget (PAGEID 2664 / MENUID 3224).
export async function getBudgetStructureSearchOptions() {
  return apiRequest<{ data: BudgetStructureSearchOptions }>(
    "/api/setup/budget-structure-search/options",
  );
}

export async function getBudgetStructureSearchForms() {
  return apiRequest<{ data: BudgetStructureSearchForms }>(
    "/api/setup/budget-structure-search/forms",
  );
}

export async function listJenisCarian(params = "") {
  return apiRequest<{ data: JenisCarianRow[]; meta: Record<string, unknown> }>(
    `/api/setup/budget-structure-search/jenis-carian${params}`,
  );
}

export async function getJenisCarian(id: number) {
  return apiRequest<{ data: JenisCarianDetail }>(
    `/api/setup/budget-structure-search/jenis-carian/${id}`,
  );
}

export async function updateJenisCarian(id: number, input: JenisCarianInput) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/setup/budget-structure-search/jenis-carian/${id}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function listBillsSetup(params = "") {
  return apiRequest<{ data: BillsSetupRow[]; meta: Record<string, unknown> }>(
    `/api/setup/budget-structure-search/bills-setup${params}`,
  );
}

export async function getBillsSetup(id: number) {
  return apiRequest<{ data: BillsSetupDetail }>(
    `/api/setup/budget-structure-search/bills-setup/${id}`,
  );
}

export async function updateBillsSetup(id: number, input: BillsSetupInput) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/setup/budget-structure-search/bills-setup/${id}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function saveSemiStrict(input: SemiStrictInput) {
  return apiRequest<{ data: { success: boolean } }>(
    "/api/setup/budget-structure-search/semi-strict",
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function saveBillsCustomWf(input: BillsCustomWfInput) {
  return apiRequest<{ data: { success: boolean } }>(
    "/api/setup/budget-structure-search/custom-wf",
    { method: "PUT", body: JSON.stringify(input) },
  );
}

// ─── FIMS Cashbook ─────────────────────────────────────────────────────────
// Bank Setup (PAGEID 2680 / MENUID 3246)
export async function listBankSetup(params = "") {
  return apiRequest<{ data: BankSetupRow[]; meta: Record<string, unknown> }>(`/api/cashbook/bank-setup${params}`);
}

export async function getBankSetupOptions() {
  return apiRequest<{ data: BankSetupOptions }>("/api/cashbook/bank-setup/options");
}

export async function getBankSetup(code: string) {
  return apiRequest<{ data: { lbmBankCode: string; lbmBankName: string; isBankMain: "Y" | "N" | null; lbmStatus: number } }>(
    `/api/cashbook/bank-setup/${encodeURIComponent(code)}`,
  );
}

export async function createBankSetup(input: BankSetupInput) {
  return apiRequest<{ data: { lbmBankCode: string } }>("/api/cashbook/bank-setup", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateBankSetup(code: string, input: BankSetupInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/cashbook/bank-setup/${encodeURIComponent(code)}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

// Bank Master (PAGEID 1682 / MENUID 2036)
export async function listBankMaster(params = "") {
  return apiRequest<{ data: BankMasterRow[]; meta: Record<string, unknown> }>(`/api/cashbook/bank-master${params}`);
}

export async function getBankMasterOptions() {
  return apiRequest<{ data: BankMasterOptions }>("/api/cashbook/bank-master/options");
}

export async function getBankMaster(id: number) {
  return apiRequest<{ data: BankMasterRow & { bnmAddressCountry: string | null; bnmAddressPostcode: string | null } }>(
    `/api/cashbook/bank-master/${id}`,
  );
}

export async function createBankMaster(input: BankMasterInput) {
  return apiRequest<{ data: { bnmBankId: number } }>("/api/cashbook/bank-master", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateBankMaster(id: number, input: BankMasterInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/cashbook/bank-master/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

// Bank Account (PAGEID 1736 / MENUID 2097)
export async function listBankAccount(params = "") {
  return apiRequest<{ data: BankAccountRow[]; meta: Record<string, unknown> }>(`/api/cashbook/bank-account${params}`);
}

export async function getBankAccountOptions() {
  return apiRequest<{ data: BankAccountOptions }>("/api/cashbook/bank-account/options");
}

export async function getBankAccount(id: number) {
  return apiRequest<{ data: BankAccountDetail }>(`/api/cashbook/bank-account/${id}`);
}

export async function createBankAccount(input: BankAccountInput) {
  return apiRequest<{ data: { bndBankDetlId: number } }>("/api/cashbook/bank-account", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateBankAccount(id: number, input: BankAccountUpdateInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/cashbook/bank-account/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

// List Of Cashbook DAILY|MONTHLY (PAGEID 1397/2024 / MENUID 1702/2471)
export async function listCashbookList(type: CashbookListType, params = "") {
  return apiRequest<{ data: CashbookListRow[]; meta: Record<string, unknown> }>(
    `/api/cashbook/list/${type.toLowerCase()}${params}`,
  );
}

export async function getCashbookListOptions(type: CashbookListType) {
  return apiRequest<{ data: CashbookListOptions }>(`/api/cashbook/list/${type.toLowerCase()}/options`);
}

// ─── FIMS Account Payable ───────────────────────────────────────────────────
// Payee Registration (Others) — PAGEID 1403 / MENUID 1711 (read-only listing).
export async function listPayeeRegistration(params = "") {
  return apiRequest<{ data: PayeeRegistrationRow[]; meta: Record<string, unknown> }>(
    `/api/account-payable/payee-registration${params}`,
  );
}

export async function getPayeeRegistrationOptions() {
  return apiRequest<{ data: PayeeRegistrationOptions }>("/api/account-payable/payee-registration/options");
}

/** Payee Registration detail (hidden MENUID 1713). */
export async function getPayeeRegistrationDetail(id: string | number) {
  return apiRequest<{ data: PayeeRegistrationDetail }>(
    `/api/account-payable/payee-registration/${encodeURIComponent(String(id))}`,
  );
}

// Utility Registration — PAGEID 2881 / MENUID 3466 (list + inline add/edit).
export async function listUtilityRegistration(params = "") {
  return apiRequest<{ data: UtilityRegistrationRow[]; meta: Record<string, unknown> }>(
    `/api/account-payable/utility-registration${params}`,
  );
}

export async function getUtilityRegistration(id: string | number) {
  return apiRequest<{ data: UtilityRegistrationDetail }>(`/api/account-payable/utility-registration/${id}`);
}

export async function createUtilityRegistration(input: UtilityRegistrationInput) {
  return apiRequest<{ data: { vcsId: string; vcsVendorCode: string } }>(
    "/api/account-payable/utility-registration",
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function updateUtilityRegistration(id: string | number, input: UtilityRegistrationInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/account-payable/utility-registration/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

// Account Bank by Payee — PAGEID 2262 / MENUID 2751 (read-only, payee-type driven).
export async function getAccountBankByPayeeOptions(payeeType?: AccountBankPayeeType) {
  const suffix = payeeType ? `?payee_type=${payeeType}` : "";
  return apiRequest<{ data: AccountBankByPayeeOptions }>(
    `/api/account-payable/account-bank-by-payee/options${suffix}`,
  );
}

export async function listAccountBankByPayee(params = "") {
  return apiRequest<{
    data:
      | AccountBankByPayeeGenericRow[]
      | AccountBankByPayeeSponsorRow[]
      | AccountBankByPayeeInvestmentRow[];
    meta: Record<string, unknown>;
  }>(`/api/account-payable/account-bank-by-payee${params}`);
}

// Account Bank Updated — PAGEID 1719 / MENUID 2078. Bills + vouchers whose
// line-level bank account drifts from the payee master, with bulk resync.
export async function getAccountBankUpdatedOptions(payeeType?: AccountBankUpdatedPayeeType) {
  const suffix = payeeType ? `?payee_type=${payeeType}` : "";
  return apiRequest<{ data: AccountBankUpdatedOptions }>(
    `/api/account-payable/account-bank-updated/options${suffix}`,
  );
}

export async function listAccountBankUpdatedBills(params = "") {
  return apiRequest<{ data: AccountBankUpdatedBillRow[]; meta: Record<string, unknown> }>(
    `/api/account-payable/account-bank-updated/bills${params}`,
  );
}

export async function listAccountBankUpdatedVouchers(params = "") {
  return apiRequest<{ data: AccountBankUpdatedVoucherRow[]; meta: Record<string, unknown> }>(
    `/api/account-payable/account-bank-updated/vouchers${params}`,
  );
}

export async function processAccountBankUpdatedBills(input: AccountBankUpdatedProcessInput) {
  return apiRequest<{ data: AccountBankUpdatedProcessResult }>(
    "/api/account-payable/account-bank-updated/bills/process",
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function processAccountBankUpdatedVouchers(input: AccountBankUpdatedProcessInput) {
  return apiRequest<{ data: AccountBankUpdatedProcessResult }>(
    "/api/account-payable/account-bank-updated/vouchers/process",
    { method: "POST", body: JSON.stringify(input) },
  );
}

// ─── FIMS Account Receivable ────────────────────────────────────────────────
// Debtor — PAGEID 1415 / MENUID 1727 (datatable + smart filter + delete).
export async function getDebtorOptions() {
  return apiRequest<{ data: DebtorOptions }>("/api/account-receivable/debtor/options");
}

export async function listDebtors(params = "") {
  return apiRequest<{ data: DebtorRow[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/debtor${params}`,
  );
}

export async function deleteDebtor(id: number | string) {
  return apiRequest<{ data: { success: boolean } }>(`/api/account-receivable/debtor/${id}`, {
    method: "DELETE",
  });
}

// Cashbook PTJ — PAGEID 2048 / MENUID 1049 (read-only listing).
export async function listCashbookPtj(params = "") {
  return apiRequest<{ data: CashbookPtjRow[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/cashbook-ptj${params}`,
  );
}

// AR Note listings — Credit / Debit / Discount (MENUID 1041 / 1042 / 1043).
// The `meta` payload includes the `footer` aggregate used to render the
// legacy totals row under the datatable.
export async function listCreditNotes(params = "") {
  return apiRequest<{ data: CreditNoteRow[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/credit-note${params}`,
  );
}

export async function deleteCreditNote(id: number | string) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/account-receivable/credit-note/${id}`,
    { method: "DELETE" },
  );
}

export async function listDebitNotes(params = "") {
  return apiRequest<{ data: DebitNoteRow[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/debit-note${params}`,
  );
}

export async function deleteDebitNote(id: number | string) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/account-receivable/debit-note/${id}`,
    { method: "DELETE" },
  );
}

export async function listDiscountNotes(params = "") {
  return apiRequest<{ data: DiscountNoteRow[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/discount-note${params}`,
  );
}

export async function deleteDiscountNote(id: number | string) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/account-receivable/discount-note/${id}`,
    { method: "DELETE" },
  );
}

// Authorized Receipting — PAGEID 1613 / MENUID 1952.
export async function getAuthorizedReceiptingOptions() {
  return apiRequest<{ data: AuthorizedReceiptingOptions }>(
    "/api/account-receivable/authorized-receipting/options",
  );
}

export async function listAuthorizedReceipting(params = "") {
  return apiRequest<{ data: AuthorizedReceiptingRow[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/authorized-receipting${params}`,
  );
}

export async function deleteAuthorizedReceipting(id: number | string) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/account-receivable/authorized-receipting/${id}`,
    { method: "DELETE" },
  );
}

// ─── AR Credit Note Form — MENUID 1782 ──────────────────────────────────────
// Workflow-ish endpoints (submit / cancel / process-flow) return the same
// envelope as the save endpoints but carry `workflow_stub: true` on the
// data payload to signal that no real workflow task was created — see
// CreditNoteFormController for the rationale.

/**
 * Shared AR lookup: `CUSTOMER_TYPE` dropdown options used by Credit
 * Note / Debit Note / Discount Note forms. The `value` field is the
 * `CODE#Label` composite stored in the master `*_cust_type` column.
 */
export async function listDebtorTypes() {
  return apiRequest<{ data: LookupOption[] }>(
    `/api/account-receivable/lookup/customer-type`,
  );
}

/**
 * Autosuggest: search customers by code or name for the
 * `Customer / Debtor Name *` combobox on AR note forms.
 *
 * Optional `custType` mirrors the legacy `BL_AUTOSUGGEST_RECC_FEE` split:
 * `C` returns creditors (`vcs_iscreditor='Y'`), anything else (D / B / A /
 * F / G / U / blank) returns debtors (`vcs_isdebtor='Y'`). Accepts bare
 * code (`D`) or legacy composite (`D#DEBTOR`).
 */
export async function searchArDebtors(q: string, custType = "", limit = 20) {
  const params = new URLSearchParams({ q, limit: String(limit) });
  if (custType) {
    params.set("cust_type", custType);
  }
  return apiRequest<{ data: DebtorSearchOption[] }>(
    `/api/account-receivable/credit-note-form/search-debtor?${params.toString()}`,
  );
}

/**
 * Autosuggest: search `APPROVE` invoices for the chosen debtor/cust id, for
 * the `Invoice No *` combobox. By default the API requires open balance
 * (`cim_bal_amt > 0`); pass `{ requireBalance: false }` (Credit/Debit note
 * forms) to include zero-balance approved invoices. Mirrors legacy
 * `sddInvoiceNo` composite `invoiceId#invoiceNo`.
 */
export async function searchArInvoicesByDebtor(
  custId: string,
  q = "",
  limit = 20,
  opts?: { requireBalance?: boolean },
) {
  const params = new URLSearchParams({ cust_id: custId, q, limit: String(limit) });
  if (opts?.requireBalance === false) {
    params.set("require_balance", "0");
  }
  return apiRequest<{ data: InvoiceSearchOption[] }>(
    `/api/account-receivable/credit-note-form/search-invoice?${params.toString()}`,
  );
}

export async function getCreditNoteFormInvoiceLines(invoiceId: string) {
  return apiRequest<{ data: InvoiceLinesResponse }>(
    `/api/account-receivable/credit-note-form/invoice-lines?invoice_id=${encodeURIComponent(invoiceId)}`,
  );
}

export async function getCreditNoteForm(id: string | number) {
  return apiRequest<{ data: CreditNoteFormData }>(
    `/api/account-receivable/credit-note-form/${id}`,
  );
}

export async function saveCreditNoteForm(input: unknown) {
  return apiRequest<{ data: SaveCreditNoteResponse }>(
    `/api/account-receivable/credit-note-form`,
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function submitCreditNoteForm(id: string | number) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/credit-note-form/${id}/submit`,
    { method: "POST", body: JSON.stringify({}) },
  );
}

export async function cancelCreditNoteForm(
  id: string | number,
  reason: string,
) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/credit-note-form/${id}/cancel`,
    { method: "POST", body: JSON.stringify({ cancelReason: reason }) },
  );
}

export async function getCreditNoteFormProcessFlow(id: string | number) {
  return apiRequest<{ data: unknown[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/credit-note-form/${id}/process-flow`,
  );
}

// ─── AR Debit Note Form — MENUID 1783 ───────────────────────────────────────
export async function getDebitNoteFormInvoiceLines(invoiceId: string) {
  return apiRequest<{ data: InvoiceLinesResponse }>(
    `/api/account-receivable/debit-note-form/invoice-lines?invoice_id=${encodeURIComponent(invoiceId)}`,
  );
}

export async function getDebitNoteForm(id: string | number) {
  return apiRequest<{ data: DebitNoteFormData }>(
    `/api/account-receivable/debit-note-form/${id}`,
  );
}

export async function saveDebitNoteForm(input: unknown) {
  return apiRequest<{ data: SaveDebitNoteResponse }>(
    `/api/account-receivable/debit-note-form`,
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function submitDebitNoteForm(id: string | number) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/debit-note-form/${id}/submit`,
    { method: "POST", body: JSON.stringify({}) },
  );
}

export async function cancelDebitNoteForm(id: string | number, reason: string) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/debit-note-form/${id}/cancel`,
    { method: "POST", body: JSON.stringify({ cancelReason: reason }) },
  );
}

export async function getDebitNoteFormProcessFlow(id: string | number) {
  return apiRequest<{ data: unknown[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/debit-note-form/${id}/process-flow`,
  );
}

// ─── AR Discount Note Form — MENUID 1784 ────────────────────────────────────

/**
 * Lookup for the `Discount Policy *` combobox on Discount Note Form.
 * Mirrors legacy BL `DT_AR_DISCOUNT_NOTE_FORM` ~line 495.
 */
export async function listDiscountPolicies(q = "") {
  const params = new URLSearchParams();
  if (q) params.set("q", q);
  const qs = params.toString();
  return apiRequest<{ data: DiscountPolicyOption[] }>(
    `/api/account-receivable/discount-note-form/discount-policies${qs ? `?${qs}` : ""}`,
  );
}

export async function getDiscountNoteFormInvoiceLines(
  invoiceId: string,
  policyId: string,
) {
  return apiRequest<{ data: DiscountInvoiceLinesResponse }>(
    `/api/account-receivable/discount-note-form/invoice-lines?invoice_id=${encodeURIComponent(invoiceId)}&policy_id=${encodeURIComponent(policyId)}`,
  );
}

export async function getDiscountNoteForm(id: string | number) {
  return apiRequest<{ data: DiscountNoteFormData }>(
    `/api/account-receivable/discount-note-form/${id}`,
  );
}

export async function saveDiscountNoteForm(input: unknown) {
  return apiRequest<{ data: SaveDiscountNoteResponse }>(
    `/api/account-receivable/discount-note-form`,
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function submitDiscountNoteForm(id: string | number) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/discount-note-form/${id}/submit`,
    { method: "POST", body: JSON.stringify({}) },
  );
}

export async function cancelDiscountNoteForm(
  id: string | number,
  reason: string,
) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/discount-note-form/${id}/cancel`,
    { method: "POST", body: JSON.stringify({ cancelReason: reason }) },
  );
}

export async function getDiscountNoteFormProcessFlow(id: string | number) {
  return apiRequest<{ data: unknown[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/discount-note-form/${id}/process-flow`,
  );
}

// ─── AR Authorized Receipting Form — MENUID 1953 ────────────────────────────
export async function getAuthorizedReceiptingForm(id: string | number) {
  return apiRequest<{ data: AuthorizedReceiptingFormData }>(
    `/api/account-receivable/authorized-receipting-form/${id}`,
  );
}

export async function saveAuthorizedReceiptingForm(input: unknown) {
  return apiRequest<{ data: SaveAuthorizedReceiptingResponse }>(
    `/api/account-receivable/authorized-receipting-form`,
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function submitAuthorizedReceiptingForm(
  id: string | number,
  input: unknown,
) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/authorized-receipting-form/${id}/submit`,
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function cancelAuthorizedReceiptingForm(
  id: string | number,
  reason: string,
) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/account-receivable/authorized-receipting-form/${id}/cancel`,
    { method: "POST", body: JSON.stringify({ cancelReason: reason }) },
  );
}

export async function getAuthorizedReceiptingFormProcessFlow(
  id: string | number,
) {
  return apiRequest<{ data: unknown[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/authorized-receipting-form/${id}/process-flow`,
  );
}

/**
 * Fetch the logged-in user's staff profile for the Details card at the top
 * of the Authorized Receipting Form. Replaces the legacy `$_USER` session
 * superglobal lookups. Returns `resolved=false` (plus best-effort name/email
 * only) when the Laravel user cannot be matched against `staff` /
 * `staff_service`.
 */
export async function getCurrentStaffProfile() {
  return apiRequest<{ data: CurrentStaffProfile }>(
    `/api/account-receivable/authorized-receipting-form/current-staff`,
  );
}

/**
 * Autosuggest for the "Event" combobox on the Authorized Receipting Form
 * (visible when Collection Type = EVENT). Backs `capital_project` rows
 * flagged as EVENT and still within their valid window. Pass the current
 * staff id to restrict to the user's own / PTJ-matching events — matches
 * legacy `autoSuggestProject` behaviour.
 */
export async function searchArEvents(
  query = "",
  staffId = "",
  limit = 20,
) {
  const params = new URLSearchParams();
  if (query) params.set("q", query);
  if (staffId) params.set("stf_staff_id", staffId);
  params.set("limit", String(limit));
  const qs = params.toString();
  return apiRequest<{ data: ArEventSearchOption[] }>(
    `/api/account-receivable/authorized-receipting-form/search-event${qs ? `?${qs}` : ""}`,
  );
}

/**
 * Autosuggest for the "+ New" authorized-staff modal. Filters
 * `staff` + `staff_service` to active staff (job status 1/2/4, service
 * status 1/6/B). When `oun` (PTJ) is provided, limits to that PTJ — matches
 * legacy `autoSuggestAuthorized` behaviour.
 */
export async function searchArAuthorizedStaff(
  query = "",
  oun = "",
  limit = 20,
) {
  const params = new URLSearchParams();
  if (query) params.set("q", query);
  if (oun) params.set("oun_code", oun);
  params.set("limit", String(limit));
  const qs = params.toString();
  return apiRequest<{ data: ArStaffSearchOption[] }>(
    `/api/account-receivable/authorized-receipting-form/search-staff${qs ? `?${qs}` : ""}`,
  );
}

// ─── FIMS Credit Control ────────────────────────────────────────────────────

/**
 * Deposit listing (MENUID 1809). Accepts query string already built by the
 * caller so tables / smart filters / sort params are passed through 1:1 to
 * `DepositController@index`.
 */
export async function listDeposits(query = "") {
  return apiRequest<{ data: DepositRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/deposit${query}`,
  );
}

export async function fetchDepositOptions() {
  return apiRequest<{ data: DepositOptions }>(
    `/api/credit-control/deposit/options`,
  );
}

/**
 * Smart-filter combobox autosuggest for the Deposit listing. `field` matches
 * the controller's switch (deposit_no | vendor_code | ref_no | acct_code |
 * amount | fund_type).
 */
export async function autosuggestDeposit(
  field: string,
  query = "",
  limit = 20,
) {
  const params = new URLSearchParams({ field, limit: String(limit) });
  if (query) params.set("q", query);
  return apiRequest<{ data: { id: string; label: string }[] }>(
    `/api/credit-control/deposit/autosuggest?${params.toString()}`,
  );
}

/**
 * List of Deposit (MENUID 3066). Restricted to account_main rows flagged as
 * subsidiary+deposit and exposes the customer-type / customer-id / PTJ
 * top filters defined in the legacy BL.
 */
export async function listOfDeposit(query = "") {
  return apiRequest<{ data: DepositRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/list-of-deposit${query}`,
  );
}

export async function fetchListOfDepositOptions() {
  return apiRequest<{ data: ListOfDepositOptions }>(
    `/api/credit-control/list-of-deposit/options`,
  );
}

export async function searchListOfDepositCustomer(query = "", limit = 20) {
  const params = new URLSearchParams({ limit: String(limit) });
  if (query) params.set("q", query);
  return apiRequest<{ data: CcCustomerOption[] }>(
    `/api/credit-control/list-of-deposit/search-customer?${params.toString()}`,
  );
}

/**
 * Invoice Balance (MENUID 3388) — read-only aggregated view. `tf_end_date`
 * is required by the BL; backend defaults to today when omitted.
 */
export async function listInvoiceBalance(query = "") {
  return apiRequest<{ data: InvoiceBalanceRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/invoice-balance${query}`,
  );
}

export async function fetchInvoiceBalanceOptions() {
  return apiRequest<{ data: InvoiceBalanceOptions }>(
    `/api/credit-control/invoice-balance/options`,
  );
}

export async function searchInvoiceBalanceCustomer(
  query = "",
  customerType = "",
  limit = 20,
) {
  const params = new URLSearchParams({ limit: String(limit) });
  if (query) params.set("q", query);
  if (customerType) params.set("customer_type", customerType);
  return apiRequest<{ data: CcCustomerOption[] }>(
    `/api/credit-control/invoice-balance/search-customer?${params.toString()}`,
  );
}

/** Subsidiary Ledger All / Subsidiary Statement — legacy SNA_API_CC_SUBSLEDGER_ALL. */
export async function getSubsidiaryLedgerAllOptions() {
  return apiRequest<{ data: SubsidiaryLedgerAllOptions }>(
    "/api/credit-control/subsidiary-ledger-all/options",
  );
}

export async function listSubsidiaryLedgerAll(query = "") {
  return apiRequest<{ data: SubsidiaryLedgerAllRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/subsidiary-ledger-all${query}`,
  );
}

export async function searchInvoiceBalanceInvoice(
  query = "",
  customerType = "",
  customerId = "",
  limit = 20,
) {
  const params = new URLSearchParams({ limit: String(limit) });
  if (query) params.set("q", query);
  if (customerType) params.set("customer_type", customerType);
  if (customerId) params.set("customer_id", customerId);
  return apiRequest<{ data: CcOption[] }>(
    `/api/credit-control/invoice-balance/search-invoice?${params.toString()}`,
  );
}

/**
 * Detail of Deposit (MENUID 3397) — master form + detail datatable + popup.
 */
export async function getDepositForm(id: number | string) {
  return apiRequest<{ data: DepositFormMaster }>(
    `/api/credit-control/deposit-form/${id}`,
  );
}

export async function listDepositFormDetails(
  id: number | string,
  query = "",
) {
  return apiRequest<{ data: DepositDetailRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/deposit-form/${id}/details${query}`,
  );
}

export async function updateDepositFormMaster(
  id: number | string,
  input: DepositFormMasterInput,
) {
  return apiRequest<{ data: DepositFormMaster }>(
    `/api/credit-control/deposit-form/${id}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function updateDepositFormDetail(
  id: number | string,
  detailId: number | string,
  input: DepositDetailInput,
) {
  return apiRequest<{ data: Record<string, unknown> }>(
    `/api/credit-control/deposit-form/${id}/detail/${detailId}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function searchDepositFormCustomer(query = "", limit = 20) {
  const params = new URLSearchParams({ limit: String(limit) });
  if (query) params.set("q", query);
  return apiRequest<{ data: CcCustomerOption[] }>(
    `/api/credit-control/deposit-form/search-customer?${params.toString()}`,
  );
}

// ─── FIMS Portal ───────────────────────────────────────────────────────────
// Read-only self-service listings for vendors/debtors logged into the Portal.

// Debtor Portal > List of Profile Update Application (MENUID 2608)
export async function listDebtorProfileUpdates(params = "") {
  return apiRequest<{ data: DebtorProfileUpdateRow[]; meta: Record<string, unknown> }>(
    `/api/portal/debtor/profile-update-applications${params}`,
  );
}

// Vendor Portal > Tender/Quotation List (MENUID 2767)
export async function listPortalTenders(params = "") {
  return apiRequest<{ data: TenderQuotationRow[]; meta: Record<string, unknown> }>(
    `/api/portal/vendor/tenders${params}`,
  );
}

export async function checkPortalVendorStatus() {
  return apiRequest<{ data: VendorStatusCheck }>("/api/portal/vendor/tenders/check-status");
}

// Vendor Portal > Online Registration Fee History (MENUID 2003)
export async function listPortalRegistrationFees(params = "") {
  return apiRequest<{ data: VendorRegistrationFeeRow[]; meta: Record<string, unknown> }>(
    `/api/portal/vendor/registration-fees${params}`,
  );
}

// Debtor Portal > Financial Information > Reminder (MENUID 2584).
export async function listDebtorReminders(params = "") {
  return apiRequest<{ data: DebtorReminderRow[]; meta: Record<string, unknown> }>(
    `/api/portal/debtor/reminders${params}`,
  );
}

// Debtor Portal > Financial Information > Debtors Statement (MENUID 2267).
export async function listDebtorStatement(params = "") {
  return apiRequest<{
    data: DebtorStatementRow[];
    meta: Record<string, unknown> & { footer?: DebtorStatementFooter };
  }>(`/api/portal/debtor/statement${params}`);
}

// Student Finance > PTPTN Data (PAGEID 857 / MENUID 1031).
export async function listPtptnData(params = "") {
  return apiRequest<{ data: PtptnDataRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/ptptn-data${params}`,
  );
}

export async function getPtptnData(id: number) {
  return apiRequest<{ data: { header: PtptnDataHeader; details: PtptnDataDetail[] } }>(
    `/api/student-finance/ptptn-data/${id}`,
  );
}

export async function deletePtptnData(id: number) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/student-finance/ptptn-data/${id}`,
    { method: "DELETE" },
  );
}

// Student Finance > Student Profile or Ledger (PAGEID 1232 / MENUID 1509).
// Legacy BL `V2_SFSP_LEDGER_API`.
export async function listLedger(params = "") {
  return apiRequest<{ data: LedgerRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/ledger${params}`,
  );
}

export async function getLedgerOptions() {
  return apiRequest<{ data: LedgerOptions }>("/api/student-finance/ledger/options");
}

// Student Finance > Manual Invoice Listing (PAGEID 2343 / MENUID 2897).
// Legacy BL `DT_SF_MANUAL_INV_LISTING`.
export async function listManualInvoices(params = "") {
  return apiRequest<{
    data: ManualInvoiceRow[];
    meta: Record<string, unknown> & { footer?: ManualInvoiceFooter };
  }>(`/api/student-finance/manual-invoice${params}`);
}

export async function getManualInvoiceOptions() {
  return apiRequest<{ data: ManualInvoiceOptions }>(
    "/api/student-finance/manual-invoice/options",
  );
}

export async function getManualInvoice(id: number) {
  return apiRequest<{ data: ManualInvoiceDetail }>(
    `/api/student-finance/manual-invoice/${id}`,
  );
}

/** Insert a cust_invoice_details row (legacy +Add). Returns refreshed invoice detail. */
export async function addManualInvoiceLine(invoiceId: number, input: ManualInvoiceLineInput) {
  return apiRequest<{ data: ManualInvoiceDetail }>(
    `/api/student-finance/manual-invoice/${invoiceId}/lines`,
    { method: "POST", body: JSON.stringify(input) },
  );
}

/** Remove one line (DRAFT only). Returns refreshed invoice detail. */
export async function removeManualInvoiceLine(invoiceId: number, lineId: number) {
  return apiRequest<{ data: ManualInvoiceDetail }>(
    `/api/student-finance/manual-invoice/${invoiceId}/lines/${lineId}`,
    { method: "DELETE" },
  );
}

export async function deleteManualInvoice(id: number) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/student-finance/manual-invoice/${id}`,
    { method: "DELETE" },
  );
}

// Student Finance > List of Offered (PAGEID 2181 / MENUID 2636).
// Legacy BL `MZ_BL_SF_OFFEREDLIST`.
export async function listOfferedStudents(params = "") {
  return apiRequest<{ data: OfferedStudentRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/offered${params}`,
  );
}

export async function getOfferedStudentOptions() {
  return apiRequest<{ data: OfferedStudentOptions }>(
    "/api/student-finance/offered/options",
  );
}

/** Registry shell for PAGE_MENUID1019_LEVEL3 menus — {@link KerisiSfLevel3Controller}. */
export async function listKerisiSfLevel3Data(menuId: number, params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/student-finance/kerisi-level3/${menuId}${params}`,
  );
}

// Insurance lists — MENUID 1039 / 2797 / 2799. {@link StudentInsuranceListingController}.
export async function listStudentInsuranceListing(params = "") {
  return apiRequest<{ data: StudentInsuranceListingRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/insurance-student-list${params}`,
  );
}

export async function getStudentInsuranceListingOptions() {
  return apiRequest<{ data: StudentInsuranceListingOptions }>(
    "/api/student-finance/insurance-student-list/options",
  );
}

// Legacy BLs `DT_SF_INVOICE` (main listing) + `DT_DEBIT_LIST`
// (per-invoice debit detail drilldown).
export async function listInvoices(params = "") {
  return apiRequest<{
    data: InvoiceRow[];
    meta: Record<string, unknown> & { footer?: InvoiceFooter };
  }>(`/api/student-finance/invoice${params}`);
}

export async function getInvoiceOptions() {
  return apiRequest<{ data: InvoiceOptions }>(
    "/api/student-finance/invoice/options",
  );
}

export async function getInvoiceDetails(id: number) {
  return apiRequest<{ data: InvoiceDetails }>(
    `/api/student-finance/invoice/${id}/details`,
  );
}

// Student Finance > Invoice Generation (PAGEID 970 / MENUID 1231).
// Maps directly to legacy CALL_PROC_STUDENT_INVOICE actions:
//   options  -> dropdown lookups (no legacy equivalent — pure SETUP read)
//   search   -> find=1     (CALL invoiceCheckingByBatch)
//   generate -> generate=1 (CALL invoiceCreationByBatch + wf_task URL rewrite)
//   exportCsv      -> csv=1   (legacy listing CSV by uniqueKey)
//   exportMatchCsv -> match=1 (post-generate match CSV by uniqueKey)
export async function getStudentInvoiceGenerationOptions() {
  return apiRequest<{ data: StudentInvoiceGenerationOptions }>(
    "/api/student-finance/invoice-generation/options",
  );
}

export async function searchStudentInvoiceGeneration(
  input: StudentInvoiceGenerationSearchInput,
) {
  return apiRequest<{
    data: StudentInvoiceGenerationRow[];
    meta: StudentInvoiceGenerationSearchMeta;
  }>("/api/student-finance/invoice-generation/search", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function generateStudentInvoice(
  input: StudentInvoiceGenerationGenerateInput,
) {
  return apiRequest<{ data: StudentInvoiceGenerationGenerateResult }>(
    "/api/student-finance/invoice-generation/generate",
    {
      method: "POST",
      body: JSON.stringify(input),
    },
  );
}

// CSV exports stream binary blobs (text/csv) so they cannot reuse
// `apiRequest`, which always parses JSON. We still respect the same
// session/CSRF rules: cookies via `credentials: "include"` and the
// XSRF-TOKEN cookie copied to the X-XSRF-TOKEN header. POST is used
// instead of GET because the uniqueKey + legacy label fields drive
// the CSV header text and that contract is too large for a query
// string. Routes match `routes/api.php`.
async function streamStudentInvoiceCsv(
  path: string,
  input: Record<string, unknown>,
): Promise<Blob> {
  const csrfMatch = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
  const csrfToken = csrfMatch ? decodeURIComponent(csrfMatch[1]) : "";
  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: "POST",
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
      Accept: "text/csv",
      "X-Requested-With": "XMLHttpRequest",
      ...(csrfToken ? { "X-XSRF-TOKEN": csrfToken } : {}),
    },
    body: JSON.stringify(input),
  });
  if (!response.ok) {
    let message = `Export failed (${response.status})`;
    try {
      const payload = await response.clone().json();
      message = payload?.error?.message ?? message;
    } catch {
      // Non-JSON failure — leave the generic message.
    }
    throw new Error(message);
  }
  return response.blob();
}

export async function exportStudentInvoiceGenerationCsv(input: {
  uniqueKey: string;
  semesterDesc?: string;
  programLevelDesc?: string;
  studentTypeDesc?: string;
  feeTypeDesc?: string;
  intakeCaseDesc?: string;
}): Promise<Blob> {
  return streamStudentInvoiceCsv(
    "/api/student-finance/invoice-generation/export/csv",
    input,
  );
}

export async function exportStudentInvoiceGenerationMatchCsv(input: {
  uniqueKey: string;
}): Promise<Blob> {
  return streamStudentInvoiceCsv(
    "/api/student-finance/invoice-generation/export/match-csv",
    input,
  );
}

// Student Finance > Bank Account Update (PAGEID 977 / MENUID 1081).
// Legacy BL `DT_BANK_ACC_UPDATE`. Read-only listing.
export async function listBankAccountUpdates(params = "") {
  return apiRequest<{ data: BankAccountUpdateRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/bank-account-update${params}`,
  );
}

export async function getBankAccountUpdateOptions() {
  return apiRequest<{ data: BankAccountUpdateOptions }>(
    "/api/student-finance/bank-account-update/options",
  );
}

// Investment > List Of Accrual (PAGEID 1548 / MENUID 1877).
// Legacy BL `API_LIST_OF_ACCRUAL` (action=listing_all_dt).
export async function listListOfAccrual(params = "") {
  return apiRequest<{ data: ListOfAccrualRow[]; meta: Record<string, unknown> }>(
    `/api/investment/list-of-accrual${params}`,
  );
}

export async function getListOfAccrualOptions() {
  return apiRequest<{ data: ListOfAccrualOptions }>(
    "/api/investment/list-of-accrual/options",
  );
}

// Investment > Summary List of Investments (PAGEID 2316 / MENUID 2808).
// Legacy BL `API_SUMMARY_LIST_OF_NEW_INVESTMENT` (action=listing_all_dt).
export async function listSummaryListInvestments(params = "") {
  return apiRequest<{
    data: SummaryListInvestmentRow[];
    meta: Record<string, unknown>;
  }>(`/api/investment/summary-list${params}`);
}

export async function getSummaryListInvestmentOptions() {
  return apiRequest<{ data: SummaryListInvestmentOptions }>(
    "/api/investment/summary-list/options",
  );
}

// Investment > List of Investments (PAGEID 1174 / MENUID 1448).
// Legacy BL `API_LIST_OF_NEW_INVESTMENT` (action=listing_all_dt).
export async function listInvestments(params = "") {
  return apiRequest<{
    data: ListOfInvestmentRow[];
    meta: Record<string, unknown>;
  }>(`/api/investment/list${params}`);
}

export async function getListOfInvestmentOptions() {
  return apiRequest<{ data: ListOfInvestmentOptions }>(
    "/api/investment/list/options",
  );
}

// Investment > Investment to be Withdrawn (PAGEID 2895 / MENUID 3485).
// Legacy BL `API_INV_WITHDRAWN` (action=listing_all_dt / getDataModal /
// edit_investment).
export async function listInvestmentsToBeWithdrawn(params = "") {
  return apiRequest<{
    data: InvestmentToBeWithdrawnRow[];
    meta: Record<string, unknown>;
  }>(`/api/investment/withdrawn${params}`);
}

export async function getInvestmentToBeWithdrawnOptions() {
  return apiRequest<{ data: InvestmentToBeWithdrawnOptions }>(
    "/api/investment/withdrawn/options",
  );
}

export async function getInvestmentWithdrawModalData(id: number) {
  return apiRequest<{ data: InvestmentToBeWithdrawnModalData }>(
    `/api/investment/withdrawn/${id}/modal`,
  );
}

export async function withdrawInvestment(id: number) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/investment/withdrawn/${id}/withdraw`,
    { method: "POST", body: JSON.stringify({}) },
  );
}

// Investment > Accrual (PAGEID 1175 / MENUID 1446).
// Legacy BL `API_INVESTMENT_ACCRUAL`.
export async function listInvestmentAccrual(params = "") {
  return apiRequest<{
    data: InvestmentAccrualRow[];
    meta: Record<string, unknown>;
  }>(`/api/investment/accrual${params}`);
}

export async function getInvestmentAccrualOptions() {
  return apiRequest<{ data: InvestmentAccrualOptions }>(
    "/api/investment/accrual/options",
  );
}

// Legacy INSERT_UPDATE_INVESTMENT_ACCRUAL default branch — inserts
// posting_master / posting_details rows and calls the
// getTableSequenceNum / getRefNoByCurrentYear stored procs for each
// selected accrual. Response returns per-iac_id success/failure.
export async function postInvestmentAccrualToTb(accrualIds: number[]) {
  return apiRequest<{ data: InvestmentAccrualPostResult }>(
    "/api/investment/accrual/post-to-tb",
    {
      method: "POST",
      body: JSON.stringify({ accrualIds }),
    },
  );
}

// Investment > Generate Schedule (PAGEID 1206 / MENUID 1475).
// Legacy BL `API_INVESTMENT_GENERATE_ACCRUAL`.
export async function listInvestmentGenerateSchedule(params = "") {
  return apiRequest<{
    data: InvestmentGenerateScheduleRow[];
    meta: Record<string, unknown>;
  }>(`/api/investment/generate-schedule${params}`);
}

// Legacy INSERT_UPDATE_INVESTMENT_ACCRUAL mode=generateScheduleAccrual.
// Fans `CALL investment_accrual(?)` per number; response includes
// per-number success/failure breakdown.
export async function generateInvestmentSchedules(investmentNumbers: string[]) {
  return apiRequest<{ data: InvestmentGenerateScheduleResult }>(
    "/api/investment/generate-schedule/generate",
    {
      method: "POST",
      body: JSON.stringify({ investmentNumbers }),
    },
  );
}

// Investment > Monitoring (PAGEID 1183 / MENUID 1458).
// Legacy BL `ATR_INVESTMENT_MONITORING`. Two-level drill-down.
export async function listInvestmentMonitoringBatches(params = "") {
  return apiRequest<{
    data: InvestmentMonitoringBatchRow[];
    meta: Record<string, unknown>;
  }>(`/api/investment/monitoring/batches${params}`);
}

export async function listInvestmentMonitoringInvestments(params = "") {
  return apiRequest<{
    data: InvestmentMonitoringInvestmentRow[];
    meta: Record<string, unknown>;
  }>(`/api/investment/monitoring/investments${params}`);
}

// Backs the "Investment Summary" batch-level PDF report — migrated
// from the legacy `investmentSummary_pdf.php`. Returns the full
// batch payload (rows + totals + generated timestamp) for
// `downloadInvestmentMonitoringSummaryPdf` to render client-side.
export async function getInvestmentMonitoringSummaryPdf(params = "") {
  return apiRequest<{ data: InvestmentMonitoringSummaryPdfPayload }>(
    `/api/investment/monitoring/summary-pdf${params}`,
  );
}

// Purchasing > Status PO & PR (PAGEID 1520 / MENUID 1841).
export async function listStatusPoPr(params = "") {
  return apiRequest<{ data: StatusPoPrRow[]; meta: Record<string, unknown> }>(
    `/api/purchasing/status-po-pr${params}`,
  );
}

export async function getStatusPoPrOptions() {
  return apiRequest<{ data: StatusPoPrOptions }>(
    "/api/purchasing/status-po-pr/options",
  );
}

export async function listPurchasingVendors(params = "") {
  return apiRequest<{ data: PurchasingVendorRow[]; meta: Record<string, unknown> }>(
    `/api/purchasing/vendors${params}`,
  );
}

export async function listPostDatedCheques(params = "") {
  return apiRequest<{ data: PostDatedChequeRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/post-dated-cheques${params}`,
  );
}

// Credit Control — Emergency Fund / Report / Listing (PAGEID 1683 / MENUID 2038).
export async function listEmergencyFundApprovedListing(params = "") {
  return apiRequest<{ data: EmergencyFundApprovedListingRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/emergency-fund-approved-listing${params}`,
  );
}

// Credit Control — Emergency Fund / Report / Reminder (PAGEID 1686 / MENUID 2037).
export async function listEmergencyFundReminderReport(params = "") {
  return apiRequest<{ data: EmergencyFundReminderReportRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/emergency-fund-reminder-report${params}`,
  );
}

/** PAGEID 1637 — ZR_CREDITCTRL_EMERGENCYFUND_ACCRUAL_API (dt_emergencyFundAccrual). */
export async function listEmergencyFundAccrualListing(params = "") {
  return apiRequest<{ data: EmergencyFundAccrualRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/emergency-fund-accrual-listing${params}`,
  );
}

/** PAGEID 1676 & 2182 — NAD_API_CC_EF_RELEASE (dt_emergencyFundRelease). */
export async function listEmergencyFundReleaseQueueListing(params = "") {
  return apiRequest<{ data: EmergencyFundReleaseQueueRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/emergency-fund-release-queue-listing${params}`,
  );
}

/** Credit Control Level 4 — ageing & AP-style bucket reports (secondary DB). */
export async function listCreditControlAgeingReports(params = "") {
  return apiRequest<{ data: CreditControlAgeingRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/ageing-reports${params}`,
  );
}

/** MENUID 2289 — non-approved staff refund BRI rows. */
export async function listCreditControlRefundBrIntegration(params = "") {
  return apiRequest<{ data: CcRefundBrIntegrationRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/refund-br-integration${params}`,
  );
}

/** MENUID 2290 — refund process line listing. */
export async function listCreditControlRefundStaffDetail(params = "") {
  return apiRequest<{ data: CreditControlRefundStaffDetailRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/refund-staff-detail-listing${params}`,
  );
}

/** MENUID 2604 — portal refund applications. */
export async function listCreditControlListOfRefundPortal(params = "") {
  return apiRequest<{ data: CcListOfRefundPortalRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/list-of-refund-portal${params}`,
  );
}

/** Legacy `checkReject` — selections must map to one `tra_application_no`. */
export async function checkCreditControlRefundPortalSubmit(body: { tra_ids: number[] }) {
  return apiRequest<{
    data: { ok: boolean; distinct_application_count: number; tra_application_no: string | null };
  }>("/api/credit-control/list-of-refund-portal/submit-check", {
    method: "POST",
    body: JSON.stringify(body),
  });
}

/** Staff batch submit (`tra_process` / `tra_status_process` draft). */
export async function submitCreditControlRefundPortalBatch(body: { tra_ids: number[] }) {
  return apiRequest<{ data: { updated: number; tra_application_no: string | null } }>(
    "/api/credit-control/list-of-refund-portal/submit",
    { method: "POST", body: JSON.stringify(body) },
  );
}

/** Staff batch reject (`tra_status` REJECT + `tra_reason_reject`). */
export async function rejectCreditControlRefundPortalBatch(body: { tra_ids: number[]; remark: string }) {
  return apiRequest<{ data: { updated: number; tra_application_no: string | null } }>(
    "/api/credit-control/list-of-refund-portal/reject",
    { method: "POST", body: JSON.stringify(body) },
  );
}

/** MENUID 2286 — list payment in advance / refund application (Classic {@code dt_listpayinadvstaff}). */
export async function listCreditControlRefundApplication(params = "") {
  return apiRequest<{ data: CcRefundApplicationAdminRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/refund-application${params}`,
  );
}

/** Top-filter account lookup (deposit × account_main), Classic `dt_listapply` parity. */
export type CcRefundDepositAccountOption = { id: string; text: string };

export async function listRefundApplicationDepositAccounts(params = "") {
  return apiRequest<{ data: CcRefundDepositAccountOption[] }>(
    `/api/credit-control/refund-application/deposit-account-options${params}`,
  );
}

/** Staff ID options (Apply queue vend codes) for MENUID 2286 TopFilter. */
export async function listRefundApplicationPayToVendors(params = "") {
  return apiRequest<{ data: CcRefundDepositAccountOption[] }>(
    `/api/credit-control/refund-application/pay-to-vendor-options${params}`,
  );
}

export type CcRefundApplicationSubmitBody = {
  tra_ids: number[];
  fty_fund_type?: string;
  acm_acct_code?: string;
  /** When true, backend matches {@code tra.acm_acct_code} exactly (dropdown selection). */
  acm_acct_exact?: boolean;
  bill_reg_integration_type?: string;
  /** Staff / vendor {@code tra.vcs_vendor_code} when narrowing the APPLY list. */
  vcs_vendor_code?: string;
  vcs_vendor_exact?: boolean;
};

export async function checkCreditControlRefundApplicationSubmit(body: CcRefundApplicationSubmitBody) {
  return apiRequest<{
    data: { ok: boolean; distinct_application_count: number; tra_application_no: string | null };
  }>("/api/credit-control/refund-application/submit-check", {
    method: "POST",
    body: JSON.stringify(body),
  });
}

export async function submitCreditControlRefundApplicationBatch(body: CcRefundApplicationSubmitBody) {
  return apiRequest<{ data: { updated: number; tra_application_no: string | null } }>(
    "/api/credit-control/refund-application/submit",
    { method: "POST", body: JSON.stringify(body) },
  );
}

/** MENUID 2291 — Request Refund (`SNA_API_CREDITCONTROL_REQUESTREFUNDSTAFF` / `dt_listapply`). */
export async function listCreditControlRequestRefundStaff(params = "") {
  return apiRequest<{ data: CcRequestRefundStaffRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/request-refund-staff${params}`,
  );
}

export async function getCreditControlReminderDebtorCreditorTypes() {
  return apiRequest<{ data: CreditControlReminderLookupOption[] }>(
    "/api/credit-control/reminder-status/types",
  );
}

export async function getCreditControlReminderBusinessTypes(type: string) {
  const q = new URLSearchParams({ type });
  return apiRequest<{ data: CreditControlReminderLookupOption[] }>(
    `/api/credit-control/reminder-status/business-types?${q.toString()}`,
  );
}

export async function listCreditControlReminderStatus(params = "") {
  return apiRequest<{ data: CreditControlReminderStatusRow[]; meta: Record<string, unknown> }>(
    `/api/credit-control/reminder-status${params}`,
  );
}

// General Ledger > Journal Listing (PAGEID 1700 / MENUID 2056).
export async function listJournalListing(params = "") {
  return apiRequest<{ data: JournalListingRow[]; meta: Record<string, unknown> }>(
    `/api/general-ledger/journal-listing${params}`,
  );
}

export async function getJournalListing(id: number) {
  return apiRequest<{
    data: {
      header: JournalListingHeader;
      debit: JournalListingLine[];
      credit: JournalListingLine[];
    };
  }>(`/api/general-ledger/journal-listing/${id}`);
}

export async function deleteJournalListing(id: number) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/general-ledger/journal-listing/${id}`,
    { method: "DELETE" },
  );
}

export async function getJournalListingOptions() {
  return apiRequest<{ data: JournalListingOptions }>(
    "/api/general-ledger/journal-listing/options",
  );
}

// General Ledger > Manual Journal Listing (PAGEID 1729 / MENUID 2089).
// Source: FIMS BL `V2_GL_JOURNAL_API` (?listing=1 + ?listing_delete=1).
// Read list + DRAFT-only delete. Type-of-Journal is a fixed dropdown
// hard-coded on the legacy page (General / InterOU / Intercompany) and is
// REQUIRED by the backend — index returns an empty page when missing, as
// the legacy BL does.
export async function listManualJournal(params = "") {
  return apiRequest<{ data: ManualJournalRow[]; meta: Record<string, unknown> }>(
    `/api/general-ledger/manual-journal${params}`,
  );
}

export async function deleteManualJournal(id: number) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/general-ledger/manual-journal/${id}`,
    { method: "DELETE" },
  );
}

export async function getManualJournalOptions() {
  return apiRequest<{ data: ManualJournalOptions }>(
    "/api/general-ledger/manual-journal/options",
  );
}

// Backs the toolbar "Download PDF" button — fetches ALL rows matching the
// current filters (same contract as listManualJournal, no pagination).
// Mirrors `custom/report/Manual Journal/downloadListPDF.php`.
export async function getManualJournalListingPdf(params = "") {
  return apiRequest<{ data: ManualJournalListingPdfPayload }>(
    `/api/general-ledger/manual-journal/listing-pdf${params}`,
  );
}

// Backs the per-row "PDF" row action — header + GL lines + workflow signers.
// Mirrors `custom/report/Manual Journal/downloadPDFmj.php`.
export async function getManualJournalDetail(id: number) {
  return apiRequest<{ data: ManualJournalDetail }>(
    `/api/general-ledger/manual-journal/${id}`,
  );
}

// General Ledger > List of Year and Month (PAGEID 2721 / MENUID 3287).
export async function listGlYearMonth(params = "") {
  return apiRequest<{ data: GlYearMonthRow[]; meta: Record<string, unknown> }>(
    `/api/general-ledger/year-month${params}`,
  );
}

// Setup & Maintenance > Floating Point for Profile Setup (PAGEID 1943 / MENUID 2375).
export async function listProfileFloatingPointListing(params = "") {
  return apiRequest<{ data: ProfileFloatingPointRow[]; meta: Record<string, unknown> }>(
    `/api/general-ledger/profile-floating-point-listing${params}`,
  );
}

export async function getGlYearMonth(id: number) {
  return apiRequest<{ data: GlYearMonthDetail }>(`/api/general-ledger/year-month/${id}`);
}

export async function getGlYearMonthOptions() {
  return apiRequest<{ data: GlYearMonthOptions }>(
    "/api/general-ledger/year-month/options",
  );
}

export async function createGlYearMonth(input: GlYearMonthInput) {
  return apiRequest<{ data: GlYearMonthDetail }>("/api/general-ledger/year-month", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateGlYearMonth(id: number, input: GlYearMonthInput) {
  return apiRequest<{ data: GlYearMonthDetail }>(
    `/api/general-ledger/year-month/${id}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

// General Ledger > Posting to GL (TB) (PAGEID 1139 / MENUID 1409).
// Source: FIMS BL `POSTING_TO_TB`. List returns grouped master+document
// rows with aggregated DR/CR sums; show returns the master header plus
// debit + credit line sub-tables in one payload for the in-page modal.
export async function listPostingToTb(params = "") {
  return apiRequest<{ data: PostingToTbRow[]; meta: Record<string, unknown> }>(
    `/api/general-ledger/posting-to-tb${params}`,
  );
}

export async function getPostingToTb(id: number) {
  return apiRequest<{
    data: { header: PostingToTbHeader; debit: PostingToTbLine[]; credit: PostingToTbLine[] };
  }>(`/api/general-ledger/posting-to-tb/${id}`);
}

export async function getPostingToTbOptions() {
  return apiRequest<{ data: PostingToTbOptions }>(
    "/api/general-ledger/posting-to-tb/options",
  );
}

// General Ledger > General Ledger Listing (PAGEID 2068 / MENUID 2519).
// Source: FIMS BL `NAD_API_GL_LISTINGPOSTINGTOGL`. Read-only line-level
// datatable with a single consolidated smart filter modal.
export async function listGlListing(params = "") {
  return apiRequest<{ data: GlListingRow[]; meta: Record<string, unknown> }>(
    `/api/general-ledger/general-ledger-listing${params}`,
  );
}

export async function getGlListingOptions() {
  return apiRequest<{ data: GlListingOptions }>(
    "/api/general-ledger/general-ledger-listing/options",
  );
}

// Audit Trail > System Transaction (PAGEID 3 / MENUID 5).
// Source: FIMS BL `V2_AUDIT_SYSTEM_TRANSACTION_API`. Read-only listing of
// the legacy `fims_audit.system_transaction` ledger.
export async function listAuditSystemTransactions(params = "") {
  return apiRequest<{ data: AuditSystemTransactionRow[]; meta: Record<string, unknown> }>(
    `/api/audit/system-transactions${params}`,
  );
}

export async function getAuditSystemTransactionOptions() {
  return apiRequest<{ data: AuditSystemTransactionOptions }>(
    "/api/audit/system-transactions/options",
  );
}

export async function getAuditSystemTransactionSql(auditId: number) {
  return apiRequest<{ data: AuditSystemTransactionSql }>(
    `/api/audit/system-transactions/${auditId}/sql`,
  );
}

// Vendor Portal > Purchase Order Status (PAGEID 1664 / MENUID 2015).
// Source: FIMS BL `NF_BL_VENDOR_PO_STATUS`.
export async function listVendorPoStatus(params = "") {
  return apiRequest<{ data: VendorPoStatusRow[]; meta: Record<string, unknown> }>(
    `/api/portal/vendor/po-status${params}`,
  );
}

// Vendor Portal > Financial Status (PAGEID 1714 / MENUID 2072).
// Source: FIMS BL `NF_BL_PURCHASING_FINANCIAL_STATUS`.
export async function listVendorBillings(params = "") {
  return apiRequest<{ data: VendorBillingRow[]; meta: Record<string, unknown> }>(
    `/api/portal/vendor/financial-status/billings${params}`,
  );
}
export async function listVendorVouchers(params = "") {
  return apiRequest<{ data: VendorVoucherRow[]; meta: Record<string, unknown> }>(
    `/api/portal/vendor/financial-status/vouchers${params}`,
  );
}
export async function listVendorPayments(params = "") {
  return apiRequest<{ data: VendorPaymentRow[]; meta: Record<string, unknown> }>(
    `/api/portal/vendor/financial-status/payments${params}`,
  );
}

// Portal > List of Letter (PAGEID 2330 / MENUID 2823).
// Source: FIMS BL `IKA_LETTER_LIST_API`. Read-only catalog + history.
export async function listSponsorLetterCatalog(params = "") {
  return apiRequest<{ data: SponsorLetterCatalogRow[]; meta: Record<string, unknown> }>(
    `/api/portal/letter/catalog${params}`,
  );
}
export async function listSponsorLetterHistory(params = "") {
  return apiRequest<{ data: SponsorLetterHistoryRow[]; meta: Record<string, unknown> }>(
    `/api/portal/letter/history${params}`,
  );
}
export async function downloadSponsorLetter(letterId: string) {
  return apiRequest<{ data: { reportUrl: string } }>(
    `/api/portal/letter/${encodeURIComponent(letterId)}/download`,
    { method: "POST", body: JSON.stringify({}) },
  );
}

// Asset > List of Asset (PAGEID 1271 / MENUID 1548).
// Source: FIMS BL `API_ASSET_INVENTORY_LISTOFASSET`.
export async function listAssetInventory(params = "") {
  return apiRequest<{ data: AssetInventoryRow[]; meta: Record<string, unknown> }>(
    `/api/asset/list-of-asset${params}`,
  );
}

export async function listAssetVerification(params = "") {
  return apiRequest<{ data: AssetVerificationRow[]; meta: Record<string, unknown> }>(
    `/api/asset/verification${params}`,
  );
}

export async function getAssetVerification(assetId: number) {
  return apiRequest<{ data: AssetVerificationDetail }>(`/api/asset/verification/${assetId}`);
}

export async function updateAssetVerification(
  assetId: number,
  body: {
    realCurBuilding?: string | null;
    realCurRoom?: string | null;
    realCurBuildingDesc?: string | null;
    realCurRoomDesc?: string | null;
    assetStatus?: string | null;
  },
) {
  await ensureCsrfCookie();
  return apiRequest<{ data: { success: boolean } }>(`/api/asset/verification/${assetId}`, {
    method: "PUT",
    body: JSON.stringify(body),
  });
}

export async function listAssetCancellationAssets(params = "") {
  return apiRequest<{ data: AssetCancellationAssetRow[]; meta: Record<string, unknown> }>(
    `/api/asset/cancellation/assets${params}`,
  );
}

export async function listAssetCancellationJournals(params = "") {
  return apiRequest<{ data: AssetCancellationJournalRow[]; meta: Record<string, unknown> }>(
    `/api/asset/cancellation/journals${params}`,
  );
}

// Project Monitoring > List of Project (MENUID 1544).
export async function listProjectMonitoringProjects(params = "") {
  return apiRequest<{ data: ProjectListRow[]; meta: Record<string, unknown> }>(
    `/api/project-monitoring/projects${params}`,
  );
}

export async function getProjectMonitoringProject(cpaProjectNo: string) {
  return apiRequest<{ data: ProjectListRow }>(
    `/api/project-monitoring/projects/${encodeURIComponent(cpaProjectNo)}`,
  );
}

export async function patchProjectMonitoringProject(cpaProjectNo: string, body: CapitalProjectProfilePatch) {
  await ensureCsrfCookie();
  return apiRequest<{ data: ProjectListRow }>(
    `/api/project-monitoring/projects/${encodeURIComponent(cpaProjectNo)}`,
    { method: "PATCH", body: JSON.stringify(body) },
  );
}

// Project Monitoring > Updated Balance (MENUID 2065). Form-driven:
//   - search: autosuggest typed search (Project ID dropdown). Joined
//             select over capital_project / fund_type / costcentre /
//             activity_type / structure_budget / organization_unit /
//             budget (latest bdg_year per project).
//   - get:    same payload, scoped to a single cpa_project_no.
//   - save:   POST {info, bal} → updates cpa_ytd_balance_amt +
//             bdg_topup_amt in a transaction, mirroring legacy
//             SNA_API_UPDATEDBALANCE_PM?updateAmount=1.
export async function searchProjectMonitoringProjects(params = "") {
  return apiRequest<{ data: ProjectMonitoringBalance[] }>(
    `/api/project-monitoring/updated-balance/search${params}`,
  );
}

export async function getProjectMonitoringBalance(cpaProjectNo: string) {
  return apiRequest<{ data: ProjectMonitoringBalance }>(
    `/api/project-monitoring/updated-balance/${encodeURIComponent(cpaProjectNo)}`,
  );
}

export async function saveProjectMonitoringBalance(
  input: ProjectMonitoringBalanceInput,
) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/project-monitoring/updated-balance`,
    { method: "POST", body: JSON.stringify(input) },
  );
}

// Portal > Advance Staff / Recoupment (LEVEL5 menus 2442, 2714, 2712, 2716).

export async function listPortalAdvanceGenerateBillBatches(params = "") {
  return apiRequest<{ data: PortalAdvanceGenerateBillBatchRow[]; meta: Record<string, unknown> }>(
    `/api/portal/advance-recoup/generate-bill-batches${params}`,
  );
}

export async function listPortalAdvanceRecoupBills(params = "") {
  return apiRequest<{
    data: PortalAdvanceRecoupBillRow[];
    meta: Record<string, unknown> & { section?: string };
  }>(`/api/portal/advance-recoup/recoup-bills${params}`);
}

export async function getPortalAdvanceRecoupHeader(bimBillsId: string) {
  return apiRequest<{ data: PortalAdvanceRecoupHeader }>(
    `/api/portal/advance-recoup/recoup-bills/${encodeURIComponent(bimBillsId)}/header`,
  );
}

export async function listPortalAdvanceRecoupDebitLines(bimBillsId: string, params = "") {
  return apiRequest<{ data: PortalAdvanceRecoupDebitLineRow[]; meta: Record<string, unknown> }>(
    `/api/portal/advance-recoup/recoup-bills/${encodeURIComponent(bimBillsId)}/debit-lines${params}`,
  );
}

// Portal > Staff Profile (PAGEID 1581 / MENUID 1914).
// Backend: StaffProfileController.

export async function getStaffProfileMaster() {
  return apiRequest<{ data: StaffProfileMaster }>(`/api/portal/staff-profile`);
}

export async function getStaffProfileOptions() {
  return apiRequest<{ data: StaffProfileOptions }>(
    `/api/portal/staff-profile/options`,
  );
}

export async function getStaffProfileAddress() {
  return apiRequest<{ data: StaffProfileAddress }>(
    `/api/portal/staff-profile/address`,
  );
}

export async function updateStaffProfileAddress(
  input: StaffProfileAddressInput,
) {
  return apiRequest<{ data: { success: boolean } }>(
    `/api/portal/staff-profile/address`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function updateStaffProfileMaritalStatus(
  input: StaffProfileMaritalStatusInput,
) {
  return apiRequest<{
    data: {
      success: boolean;
      maritalStatus: string;
      maritalstatusDesc: string;
    };
  }>(`/api/portal/staff-profile/marital-status`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function listStaffProfileChildren(params = "") {
  return apiRequest<{
    data: StaffProfileChildRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/staff-profile/children${params}`);
}

export async function listStaffProfileSpouses(params = "") {
  return apiRequest<{
    data: StaffProfileSpouseRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/staff-profile/spouses${params}`);
}

export async function listStaffProfileSpouseChildren(seq: string, params = "") {
  return apiRequest<{
    data: StaffProfileChildRow[];
    meta: Record<string, unknown>;
  }>(
    `/api/portal/staff-profile/spouses/${encodeURIComponent(seq)}/children${params}`,
  );
}

// ----- Vendor Portal (PAGEID 1622 / MENUID 1961) -------------------------

export async function getVendorPortalProfile(params = "") {
  return apiRequest<{ data: VendorPortalProfile }>(`/api/portal/vendor/profile${params}`);
}

export async function updateVendorPortalProfile(
  input: VendorPortalProfileInput,
  params = "",
) {
  return apiRequest<{ data: VendorPortalProfile }>(
    `/api/portal/vendor/profile${params}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function getVendorPortalLookups(params = "") {
  return apiRequest<{ data: VendorPortalLookups }>(
    `/api/portal/vendor/lookups${params}`,
  );
}

export async function listVendorPortalCategories(params = "") {
  return apiRequest<{
    data: VendorPortalCategoryRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/vendor/categories${params}`);
}

export async function listVendorPortalAccounts(params = "") {
  return apiRequest<{
    data: VendorPortalAccountRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/vendor/accounts${params}`);
}

export async function listVendorPortalAddresses(params = "") {
  return apiRequest<{
    data: VendorPortalAddressRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/vendor/addresses${params}`);
}

export async function listVendorPortalJobscopes(params = "") {
  return apiRequest<{
    data: VendorPortalJobscopeRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/vendor/jobscopes${params}`);
}

export async function listVendorPortalSsmLicences(params = "") {
  return apiRequest<{
    data: VendorPortalLicenceRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/vendor/ssm-licences${params}`);
}

export async function listVendorPortalMofLicences(params = "") {
  return apiRequest<{
    data: VendorPortalLicenceRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/vendor/mof-licences${params}`);
}

export async function listVendorPortalOtherLicences(params = "") {
  return apiRequest<{
    data: VendorPortalOtherLicenceRow[];
    meta: Record<string, unknown>;
  }>(`/api/portal/vendor/other-licences${params}`);
}

// Setup and Maintenance > Integration > Integration - PTJ (PAGEID 1860 / MENUID 2277).
export async function listIntegrationPtj(params = "") {
  return apiRequest<{ data: IntegrationPtjRow[]; meta: Record<string, unknown> }>(
    `/api/integration/ptj${params}`,
  );
}

export async function getIntegrationPtjOptions() {
  return apiRequest<{ data: { levels: { id: string; label: string }[] } }>(
    "/api/integration/ptj/options",
  );
}

export async function getIntegrationPtjParents(params = "") {
  return apiRequest<{ data: { id: string; label: string }[] }>(
    `/api/integration/ptj/parents${params}`,
  );
}

export async function getIntegrationPtjRow(id: number) {
  return apiRequest<{ data: IntegrationPtjRow }>(`/api/integration/ptj/${id}`);
}

export async function promoteIntegrationPtj(id: number, input: IntegrationPtjPromoteInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/integration/ptj/${id}/promote`, {
    method: "POST",
    body: JSON.stringify(input),
  });
}

// Setup and Maintenance > Integration > Integration - Cost center (PAGEID 1861 / MENUID 2278).
export async function listIntegrationCostCentres(params = "") {
  return apiRequest<{ data: IntegrationCostCentreRow[]; meta: Record<string, unknown> }>(
    `/api/integration/cost-centre${params}`,
  );
}

export async function getIntegrationCostCentre(id: number) {
  return apiRequest<{ data: IntegrationCostCentreRow }>(`/api/integration/cost-centre/${id}`);
}

export async function promoteIntegrationCostCentre(id: number, input: IntegrationCostCentrePromoteInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/integration/cost-centre/${id}/promote`, {
    method: "POST",
    body: JSON.stringify(input),
  });
}

// Setup and Maintenance > Integration > Integration - Profile (PAGEID 2000 / MENUID 2443).
export async function listIntegrationProfiles(params = "") {
  return apiRequest<{ data: IntegrationProfileRow[]; meta: Record<string, unknown> }>(
    `/api/integration/profile${params}`,
  );
}

export async function getIntegrationProfile(id: number) {
  return apiRequest<{ data: IntegrationProfileRow }>(`/api/integration/profile/${id}`);
}

// Setup and Maintenance > Integration > Integration - Activity (PAGEID 2003 / MENUID 2444).
export async function listIntegrationActivities(params = "") {
  return apiRequest<{ data: IntegrationActivityRow[]; meta: Record<string, unknown> }>(
    `/api/integration/activity${params}`,
  );
}

export async function getIntegrationActivity(id: number) {
  return apiRequest<{ data: IntegrationActivityRow }>(`/api/integration/activity/${id}`);
}

// General Ledger > Budget Not Exists (PAGEID 2200 / MENUID 2657).
export async function listBudgetNotExists(params = "") {
  return apiRequest<{ data: BudgetNotExistsRow[]; meta: Record<string, unknown> }>(
    `/api/general-ledger/budget-not-exists${params}`,
  );
}

// Setup and Maintenance > Global > List of Currency (PAGEID 2636 / MENUID 3198).
export async function listCurrencies(params = "") {
  return apiRequest<{ data: ListOfCurrencyRow[]; meta: Record<string, unknown> }>(
    `/api/global/currencies${params}`,
  );
}

export async function searchCurrencyCountries(params = "") {
  return apiRequest<{ data: CountryOption[] }>(`/api/global/currencies/countries${params}`);
}

export async function getCurrency(id: number) {
  return apiRequest<{ data: ListOfCurrencyRow }>(`/api/global/currencies/${id}`);
}

export async function createCurrency(input: ListOfCurrencyInput) {
  return apiRequest<{ data: { cymCurrencyId: number; cymCurrencyCode: string } }>(
    "/api/global/currencies",
    { method: "POST", body: JSON.stringify(input) },
  );
}

export async function updateCurrency(id: number, input: ListOfCurrencyUpdate) {
  return apiRequest<{ data: { success: boolean } }>(`/api/global/currencies/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function deleteCurrency(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/global/currencies/${id}`, {
    method: "DELETE",
  });
}

// Setup and Maintenance > Global > AG Rate (PAGEID 2647 / MENUID 3199).
export async function listAgRates(params = "") {
  return apiRequest<{ data: AgRateRow[]; meta: Record<string, unknown> }>(
    `/api/global/ag-rate${params}`,
  );
}

export async function getAgRateOptions() {
  return apiRequest<{ data: AgRateOptions }>("/api/global/ag-rate/options");
}

export async function searchAgRateCurrencies(params = "") {
  return apiRequest<{ data: AgRateCurrencyOption[] }>(`/api/global/ag-rate/currencies${params}`);
}

export async function listAgRateLines(params = "") {
  return apiRequest<{ data: AgRateLine[] }>(`/api/global/ag-rate/lines${params}`);
}

export async function checkAgRateExist(params = "") {
  return apiRequest<{ data: { exists: boolean } }>(`/api/global/ag-rate/check-exist${params}`);
}

export async function saveAgRateEntry(input: AgRateEntryInput) {
  return apiRequest<{ data: { success: boolean } }>("/api/global/ag-rate", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function deleteAgRatePeriod(year: number, month: string) {
  const qs = new URLSearchParams({ cyd_year: String(year), cyd_month: month }).toString();
  return apiRequest<{ data: { success: boolean } }>(`/api/global/ag-rate?${qs}`, {
    method: "DELETE",
  });
}

// Budget > Setup > Budget Code (PAGEID 1475 / MENUID 1796).
export async function listBudgetCodes(params = "") {
  return apiRequest<{ data: BudgetCodeRow[]; meta: Record<string, unknown> }>(`/api/budget/budget-code${params}`);
}

export async function getBudgetCode(id: number) {
  return apiRequest<{ data: BudgetCodeRow }>(`/api/budget/budget-code/${id}`);
}

export async function createBudgetCode(input: BudgetCodeInput) {
  return apiRequest<{ data: { id: number } }>("/api/budget/budget-code", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateBudgetCode(id: number, input: BudgetCodeInput) {
  return apiRequest<{ data: { success: boolean } }>(`/api/budget/budget-code/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function getBudgetCodeOptions() {
  return apiRequest<{ data: BudgetCodeOptions }>("/api/budget/budget-code/options");
}

// Budget > Setup > Budget Planning Schedule (PAGEID 2872 / MENUID 3456).
export async function listBudgetPlanningSchedules(params = "") {
  return apiRequest<{ data: BudgetPlanningScheduleRow[]; meta: Record<string, unknown> }>(
    `/api/budget/planning-schedule${params}`,
  );
}

export async function getBudgetPlanningSchedule(id: number) {
  return apiRequest<{ data: BudgetPlanningScheduleRow }>(`/api/budget/planning-schedule/${id}`);
}

export async function createBudgetPlanningSchedule(input: BudgetPlanningScheduleInput) {
  return apiRequest<{ data: { id: number; successMessage?: string } }>("/api/budget/planning-schedule", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updateBudgetPlanningSchedule(id: number, input: BudgetPlanningScheduleInput) {
  return apiRequest<{ data: { success: boolean; successMessage?: string } }>(
    `/api/budget/planning-schedule/${id}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function deleteBudgetPlanningSchedule(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/budget/planning-schedule/${id}`, {
    method: "DELETE",
  });
}

export async function getBudgetPlanningScheduleOptions() {
  return apiRequest<{ data: BudgetPlanningScheduleOptions }>("/api/budget/planning-schedule/options");
}

// Budget > Setup > Allocation (PAGEID 1035 / MENUID 1294).
// Legacy BL: SWS_DT_SETUP_QUARTER + popup-modal update.
export async function listAllocations(params = "") {
  return apiRequest<{ data: AllocationRow[]; meta: Record<string, unknown> }>(
    `/api/budget/allocation${params}`,
  );
}

export async function getAllocation(id: string) {
  return apiRequest<{ data: AllocationRow }>(`/api/budget/allocation/${encodeURIComponent(id)}`);
}

export async function updateAllocation(id: string, input: AllocationInput) {
  return apiRequest<{ data: { qbuQuarterId: string; successMessage?: string } }>(
    `/api/budget/allocation/${encodeURIComponent(id)}`,
    { method: "PUT", body: JSON.stringify(input) },
  );
}

export async function getAllocationOptions() {
  return apiRequest<{ data: AllocationOptions }>("/api/budget/allocation/options");
}

// Budget > Structure Budget List (PAGEID 1071 / MENUID 1334).
export async function listStructureBudgetList(params = "") {
  return apiRequest<{ data: StructureBudgetListRow[]; meta: Record<string, unknown> }>(
    `/api/budget/structure-list${params}`,
  );
}

export async function getStructureBudgetListOptions() {
  return apiRequest<{ data: StructureBudgetListOptions }>("/api/budget/structure-list/options");
}

// Budget Planning suite (shared list controller, scoped by ?scope=).
export async function listBudgetPlanning(scope: BudgetPlanningScope, params = "") {
  const sep = params.startsWith("?") ? "&" : params ? "&" : "?";
  const trimmed = params.startsWith("?") ? params.slice(1) : params;
  const query = `?scope=${encodeURIComponent(scope)}${trimmed ? sep + trimmed : ""}`;
  return apiRequest<{
    data: BudgetPlanningRow[];
    meta: Record<string, unknown>;
  }>(`/api/budget/planning-list${query}`);
}

export async function getBudgetPlanningOptions(scope: BudgetPlanningScope) {
  return apiRequest<{ data: BudgetPlanningOptions }>(
    `/api/budget/planning-list/options?scope=${encodeURIComponent(scope)}`,
  );
}

export async function deleteBudgetPlanning(id: number) {
  return apiRequest<{ data: { success: boolean } }>(`/api/budget/planning-list/${id}`, {
    method: "DELETE",
  });
}

export async function duplicateBudgetPlanning(id: number) {
  return apiRequest<{
    data: { bpmId: number; bpmPlanningNo: string | null; successMessage?: string };
  }>(`/api/budget/planning-list/${id}/duplicate`, { method: "POST" });
}

// Budget > Planning > New Application (PAGEID 1236 / MENUID 1516).
export async function getBudgetPlanningNewOptions() {
  return apiRequest<{ data: BudgetPlanningNewOptions }>("/api/budget/planning-new/options");
}

export async function listBudgetPlanningNewAccounts(fund: string, activity = "") {
  const params = new URLSearchParams({ fund });
  if (activity) params.set("activity", activity);
  return apiRequest<{
    data: BudgetPlanningNewAccount[];
    meta: { fund: string; activity: string; total: number };
  }>(`/api/budget/planning-new/accounts?${params.toString()}`);
}

export async function createBudgetPlanningNew(input: BudgetPlanningNewInput) {
  return apiRequest<{ data: BudgetPlanningNewCreated }>("/api/budget/planning-new", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

// Budget > Reports > Total Allocation Report (PAGEID 1626 / MENUID 1968).
export async function listTotalAllocationReport(params = "") {
  return apiRequest<{
    data: TotalAllocationRow[];
    meta: Record<string, unknown> & { totals?: TotalAllocationTotals };
  }>(`/api/budget/report/total-allocation${params}`);
}

export async function getTotalAllocationReportOptions() {
  return apiRequest<{ data: TotalAllocationOptions }>(
    "/api/budget/report/total-allocation/options",
  );
}

// Umum Allocation, Expenditure & Balance by PTJ (PAGEID 2515 / MENUID 3044 — HIDDEN_PAGE_LEVEL4).
export async function listUmumAllocationPtj(params = "") {
  return apiRequest<{
    data: UmumAllocationPtjRow[];
    meta: Record<string, unknown> & { footer?: UmumAllocationPtjFooter };
  }>(`/api/budget/report/umum-allocation-ptj${params}`);
}

export async function getUmumAllocationPtjOptions() {
  return apiRequest<{ data: UmumAllocationPtjOptions }>("/api/budget/report/umum-allocation-ptj/options");
}

// Budget Summary By Date / Variation / By PTJ OLD (menus 3382, 3389, 3393 — legacy `V2_BUDGET_SUMMARY_API`).
export async function postBudgetV2BudgetSummaryListing(body: Record<string, string>) {
  return apiRequest<{
    data: BudgetV2BudgetSummaryRow[];
    meta?: { aggregateExpensesPercent?: string | null };
  }>("/api/budget/report/v2-budget-summary/listing", {
    method: "POST",
    body: JSON.stringify(body),
  });
}

// Budget > Reports > Laporan Belanjawan (PAGEID 2873 / MENUID 3457).
export async function listLaporanBelanjawan(params = "") {
  return apiRequest<{
    data: LaporanBelanjawanRow[];
    meta: Record<string, unknown> & { totals?: LaporanBelanjawanTotals };
  }>(`/api/budget/report/laporan-belanjawan${params}`);
}

export async function getLaporanBelanjawanOptions() {
  return apiRequest<{ data: LaporanBelanjawanOptions }>(
    "/api/budget/report/laporan-belanjawan/options",
  );
}

// Student Finance > Sponsor > Advance Payment (PAGEID 1669 / MENUID 2020).
// Legacy BL `V2_SAP_LIST_API`.
export async function listAdvancePayments(params = "") {
  return apiRequest<{ data: AdvancePaymentRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/advance-payment${params}`,
  );
}

export async function getAdvancePaymentOptions() {
  return apiRequest<{ data: AdvancePaymentOptions }>(
    "/api/student-finance/advance-payment/options",
  );
}

// Student Finance > Sponsor > PTPTN (PAGEID 1231 / MENUID 1507).
// Legacy BL `V2_PTPTN_API`.
export async function listSponsorPtptn(params = "") {
  return apiRequest<{ data: SponsorPtptnRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/sponsor-ptptn${params}`,
  );
}

export async function getSponsorPtptnOptions() {
  return apiRequest<{ data: SponsorPtptnOptions }>(
    "/api/student-finance/sponsor-ptptn/options",
  );
}

// Student Finance > Sponsor > Profile (PAGEID 845 / MENUID 1025).
// Legacy BL `V2_SFSP_SPONSOR_API`.
export async function listSponsorProfile(params = "") {
  return apiRequest<{ data: SponsorProfileRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/sponsor-profile${params}`,
  );
}

export async function getSponsorProfileOptions() {
  return apiRequest<{ data: SponsorProfileOptions }>(
    "/api/student-finance/sponsor-profile/options",
  );
}

// Student Finance > Sponsor > Report > List of Sponsor (PAGEID 1583 / MENUID 1916).
// Legacy BL `API_SF_SPONSOR_LISTOFSPONSOR`.
export async function listReportListOfSponsor(params = "") {
  return apiRequest<{ data: SponsorListRow[]; meta: Record<string, unknown> }>(
    `/api/student-finance/report/list-of-sponsor${params}`,
  );
}

// Student Finance > Sponsor > Invoice Generation (PAGEID 1218 / MENUID 1491).
// Legacy BL `V2_SFSI_API` (?listing=2).
export async function listSponsorInvoiceGeneration(params = "") {
  return apiRequest<{
    data: SponsorInvoiceGenerationRow[];
    meta: Record<string, unknown> & { footer?: SponsorInvoiceGenerationFooter; totalStudent?: number };
  }>(`/api/student-finance/sponsor-invoice-generation${params}`);
}

export async function getSponsorInvoiceGenerationOptions() {
  return apiRequest<{ data: SponsorInvoiceGenerationOptions }>(
    "/api/student-finance/sponsor-invoice-generation/options",
  );
}

// Student Finance > Sponsor > Student Journal Approval (PAGEID 1954 / MENUID 2390).
// Legacy BL `MZ_BL_SF_APPROVAL`.
export async function getStudentJournalApprovalDetail(id: number) {
  return apiRequest<{ data: StudentJournalApprovalDetail }>(
    `/api/student-finance/student-journal-approval/${id}`,
  );
}

export async function listStudentJournalApprovalCredit(id: number, params = "") {
  return apiRequest<{
    data: StudentJournalApprovalRow[];
    meta: Record<string, unknown> & { footer?: StudentJournalApprovalFooter };
  }>(`/api/student-finance/student-journal-approval/${id}/credit${params}`);
}

export async function listStudentJournalApprovalDebit(id: number, params = "") {
  return apiRequest<{
    data: StudentJournalApprovalRow[];
    meta: Record<string, unknown> & { footer?: StudentJournalApprovalFooter };
  }>(`/api/student-finance/student-journal-approval/${id}/debit${params}`);
}

// Account Receivable — PAGE_MENUID1024_LEVEL3 shell endpoint
export async function listKerisiArData(menuId: number, params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/account-receivable/kerisi-ar/${menuId}${params}`,
  );
}

export async function listKerisiPayrollData(menuId: number, params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/payroll/kerisi/${menuId}${params}`,
  );
}

export async function listKerisiRemainingData(menuId: number, params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/kerisi/remaining/${menuId}${params}`,
  );
}

/** Purchasing / Work Progress Note Cancel (2082) — legacy processcancelwpn_entry */
export async function kerisiWpnCancel(payload: { selectedId: string }) {
  return apiRequest<{
    data: { status: string; successMessage?: string; wpnNo?: string };
  }>("/api/kerisi/remaining/wpn-cancel", { method: "POST", body: JSON.stringify(payload) });
}

/** Purchasing / List of PR To Be Cancel (3038) — Details PR grid linked to PR no / id */
export async function getKerisiPrToCancelDetails(query: string) {
  const qs = query.startsWith("?") ? query : `?${query}`;
  return apiRequest<{ data: Record<string, unknown>[] }>(`/api/kerisi/remaining/pr-to-cancel/details${qs}`);
}

/** Purchasing / Setup / Item Main (menu 1820) — mysql_secondary cascading lists */
export type PurchasingItemMainGroupOpt = { value: string; label: string };

export async function purchasingItemMainGroups() {
  return apiRequest<{ data: PurchasingItemMainGroupOpt[] }>("/api/purchasing/item-main/groups");
}

export async function purchasingItemMainMainCategories(params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/purchasing/item-main/main-categories${params}`,
  );
}

export async function purchasingItemMainSubcategories(params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/purchasing/item-main/subcategories${params}`,
  );
}

export async function purchasingItemMainSubsiri(params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/purchasing/item-main/subsiri${params}`,
  );
}

export async function purchasingItemMainItemLines(params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(
    `/api/purchasing/item-main/item-lines${params}`,
  );
}

/** Purchasing / Setup / List Of Jobscope (menu 1932). */

export type JobscopeDropdownOpt = { value: string; label: string };

export async function purchasingJobscopeFormOptions() {
  return apiRequest<{
    data: {
      levels: JobscopeDropdownOpt[];
      categories: JobscopeDropdownOpt[];
      smartCategories: JobscopeDropdownOpt[];
      statuses: JobscopeDropdownOpt[];
    };
  }>("/api/purchasing/jobscope/form-options");
}

export async function purchasingJobscopeParentOptions(params = "") {
  return apiRequest<{ data: JobscopeDropdownOpt[] }>(`/api/purchasing/jobscope/parent-options${params}`);
}

export async function listPurchasingJobscope(params = "") {
  return apiRequest<{ data: Record<string, unknown>[]; meta: Record<string, unknown> }>(`/api/purchasing/jobscope${params}`);
}

export async function getPurchasingJobscope(id: number) {
  return apiRequest<{ data: Record<string, unknown> }>(`/api/purchasing/jobscope/${id}`);
}

export async function createPurchasingJobscope(input: {
  level: string;
  category: string;
  parent?: string;
  code: string;
  name: string;
  status: string;
}) {
  return apiRequest<{ data: { id: number } }>("/api/purchasing/jobscope", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updatePurchasingJobscope(
  id: number,
  input: {
    level: string;
    category: string;
    parent?: string;
    code: string;
    name: string;
    status: string;
  },
) {
  return apiRequest<{ data: Record<string, unknown> }>(`/api/purchasing/jobscope/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

/** Purchasing / New Purchase Requisition (MENUID 1771) — `requisition_master` header + lookups on mysql_secondary. */
export type PurchasingPrDropdownRow = { value: string; label: string };

export type PurchasingPrOptionsPayload = {
  requestBy: PurchasingPrDropdownRow[];
  contactPerson: PurchasingPrDropdownRow[];
  ptj: PurchasingPrDropdownRow[];
  costCentres: PurchasingPrDropdownRow[];
  fund: PurchasingPrDropdownRow[];
  activity: PurchasingPrDropdownRow[];
  vendor: PurchasingPrDropdownRow[];
  agreementYesNo: PurchasingPrDropdownRow[];
  agreementNo: PurchasingPrDropdownRow[];
  foreignCurrencyCode: PurchasingPrDropdownRow[];
  rateType: PurchasingPrDropdownRow[];
  requisitionType: PurchasingPrDropdownRow[];
  purchaseMethod: PurchasingPrDropdownRow[];
  /** Code SO / `kod_so` (+ historic PR strings) — may be empty. */
  soCode: PurchasingPrDropdownRow[];
  /** Next workflow recipient — staff list mirroring legacy (not persisted unless workflow is ported). */
  nextReceiver: PurchasingPrDropdownRow[];
};

export async function purchasingPurchaseRequisitionOptions() {
  return apiRequest<{ data: PurchasingPrOptionsPayload }>("/api/purchasing/purchase-requisition/options");
}

export async function purchasingPurchaseRequisitionCostCentres(ounCode: string) {
  const p =
    typeof ounCode === "string" && ounCode.trim() !== ""
      ? `?oun_code=${encodeURIComponent(ounCode.trim())}`
      : "";
  return apiRequest<{ data: { costCentres: PurchasingPrDropdownRow[] } }>(
    `/api/purchasing/purchase-requisition/cost-centres${p}`,
  );
}

export async function getPurchasingPurchaseRequisition(id: number) {
  return apiRequest<{ data: Record<string, unknown> }>(`/api/purchasing/purchase-requisition/${id}`);
}

export async function purchasingPurchaseRequisitionCreate(input: Record<string, unknown>) {
  return apiRequest<{ data: Record<string, unknown> }>(`/api/purchasing/purchase-requisition`, {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export async function updatePurchasingPurchaseRequisition(id: number, input: Record<string, unknown>) {
  return apiRequest<{ data: Record<string, unknown> }>(`/api/purchasing/purchase-requisition/${id}`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}

export async function listPurchasingPurchaseRequisitionLines(id: number) {
  return apiRequest<{ data: Record<string, unknown>[] }>(`/api/purchasing/purchase-requisition/${id}/lines`);
}

/** Purchasing PR Cancel Partial — GRN / WPN / Bill rows tied to PO for this requisition (`pom_requisition_no`). */
export async function listPurchasingPrPartialExistingDocs(id: number) {
  return apiRequest<{ data: Record<string, unknown>[] }>(
    `/api/purchasing/purchase-requisition/${id}/partial-existing-docs`,
  );
}

/** Purchasing PR Cancel — save master + mandatory cancel reason (`requisition_master.rqm_cancel_remark`). */
export async function updatePurchasingPrCancel(id: number, input: Record<string, unknown>) {
  return apiRequest<{ data: Record<string, unknown> }>(`/api/purchasing/purchase-requisition/${id}/cancel`, {
    method: "PUT",
    body: JSON.stringify(input),
  });
}
