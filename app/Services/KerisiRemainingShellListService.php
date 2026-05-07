<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shell list service for all remaining FIMS modules (14 JSON files, 271 menus).
 *
 * Table corrections applied (from live DB inspection):
 *  - staff_transfer_setup  → staff_service        (all sts_* columns)
 *  - kew8_master           → payroll_kew8         (pkw_* columns)
 *
 * Every query method aliases its columns to match the registry dtKey values
 * so the shell view renders correctly without blank cells.
 */
class KerisiRemainingShellListService
{
    public function fetch(int $menuId, Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        return match ($menuId) {

            // ── FILE 1228: Portal / DCA ──────────────────────────────────
            2949 => $this->dcaListAssign($request, $page, $limit, $q),
            2951 => $this->dcaCompletedList($request, $page, $limit, $q),

            // ── FILE 1229: Debtor Portal ─────────────────────────────────
            2279 => $this->debtorPortalReceipt($request, $page, $limit, $q),
            2282 => $this->debtorPortalCreditNote($request, $page, $limit, $q),
            2283 => $this->debtorPortalDebitNote($request, $page, $limit, $q),

            // ── FILE 1404: Investment ────────────────────────────────────
            1408 => $this->investmentInstitution($request, $page, $limit, $q),
            1415 => $this->investmentByBatch($request, $page, $limit, $q),
            1417 => $this->investmentBank($request, $page, $limit, $q),
            1418 => $this->investmentType($request, $page, $limit, $q),
            1431 => $this->investmentTenure($request, $page, $limit, $q),
            1465 => $this->investmentWithdrawLetter($request, $page, $limit, $q),
            1472 => $this->investmentWithdrawApplication($request, $page, $limit, $q),
            1487 => $this->investmentFdSummary($request, $page, $limit, $q),
            1488 => $this->investmentFdDetails($request, $page, $limit, $q),
            1493 => $this->investmentWithdrawRejected($request, $page, $limit, $q),
            1499 => $this->investmentWithdrawList($request, $page, $limit, $q),
            1517 => $this->investmentMonthlySchedule($request, $page, $limit, $q),
            1971 => $this->investmentAccrualReport($request, $page, $limit, $q),
            3188 => $this->investmentAccrualPosting($request, $page, $limit, $q),
            3191 => $this->investmentAccrualNoPosting($request, $page, $limit, $q),
            3226 => $this->investmentNewApplication($request, $page, $limit, $q),
            3231 => $this->investmentNewAppDraft($request, $page, $limit, $q),
            3232 => $this->investmentNewAppRejected($request, $page, $limit, $q),
            3268 => $this->investmentInstructionLetter($request, $page, $limit, $q),
            3312 => $this->investmentList($request, $page, $limit, $q),
            3313 => $this->investmentJournalDetails($request, $page, $limit, $q),
            3319 => $this->investmentReport($request, $page, $limit, $q),
            3438 => $this->investmentRegisterReport($request, $page, $limit, $q),

            // ── FILE 1489: Petty Cash Reports ────────────────────────────
            1528 => $this->pettyCashPersonnel($request, $page, $limit, $q),
            1881 => $this->pettyCashRecoupReport($request, $page, $limit, $q),
            2417 => $this->pettyCashBaucarRuncit($request, $page, $limit, $q),
            2502 => $this->pettyCashBaucarRuncit($request, $page, $limit, $q),
            3350 => $this->pettyCashBukuTunai($request, $page, $limit, $q),

            // ── FILE 1533: Multi-module (Loan/OT/Emergency/Store/etc.) ───
            1157 => $this->pettyCashList($request, $page, $limit, $q),
            1175 => $this->emergencyFundList($request, $page, $limit, $q),
            1194 => $this->workOrderInstruction($request, $page, $limit, $q),
            1196 => $this->otClaimList($request, $page, $limit, $q),
            1220 => $this->workOrderInstructionList($request, $page, $limit, $q),
            1249 => $this->otClaimNotification($request, $page, $limit, $q),
            1585 => $this->loanProfile($request, $page, $limit, $q),
            1591 => $this->loanDisburse($request, $page, $limit, $q),
            1592 => $this->loanGenerateSchedule($request, $page, $limit, $q),
            1593 => $this->loanAccrualMonthly($request, $page, $limit, $q),
            1619 => $this->loanDeleteSchedule($request, $page, $limit, $q),
            1639 => $this->loanInformation($request, $page, $limit, $q),
            1666 => $this->workOrderList($request, $page, $limit, $q),
            1667 => $this->otClaimApplication($request, $page, $limit, $q),
            1733 => $this->loanProfile($request, $page, $limit, $q),
            1737 => $this->loanFinancialStatus($request, $page, $limit, $q),
            1834 => $this->staffAccountBank($request, $page, $limit, $q),
            1843 => $this->fsCreditNote($request, $page, $limit, $q),
            1847 => $this->fsPaymentHistory($request, $page, $limit, $q),
            1857 => $this->fsDiscountNote($request, $page, $limit, $q),
            1858 => $this->fsSponsor($request, $page, $limit, $q),
            1859 => $this->fsReceipt($request, $page, $limit, $q),
            1860 => $this->fsPaymentInfo($request, $page, $limit, $q),
            1875 => $this->fsDebitNote($request, $page, $limit, $q),
            1876 => $this->fsVoucherInfo($request, $page, $limit, $q),
            1892 => $this->fsAdvancePayment($request, $page, $limit, $q),
            1957 => $this->otHierarchySetup($request, $page, $limit, $q),
            1981 => $this->otCollectedHours($request, $page, $limit, $q),
            1985 => $this->loanNewApplication($request, $page, $limit, $q),
            1993 => $this->emergencyFundListAll($request, $page, $limit, $q),
            2017 => $this->loanSpecialApplication($request, $page, $limit, $q),
            2046 => $this->refundApplication($request, $page, $limit, $q),
            2053 => $this->refundList($request, $page, $limit, $q),
            2059 => $this->refundApplication($request, $page, $limit, $q),
            2060 => $this->refundListStudent($request, $page, $limit, $q),
            2067 => $this->loanRegenerateSchedule($request, $page, $limit, $q),
            2139 => $this->vehiclePortal($request, $page, $limit, $q),
            2158 => $this->storeApplication($request, $page, $limit, $q),
            2159 => $this->storeApplicationList($request, $page, $limit, $q),
            2366 => $this->statementOfAccount($request, $page, $limit, $q),
            2593 => $this->tp1Application($request, $page, $limit, $q),
            2594 => $this->pcbList($request, $page, $limit, $q),
            2708 => $this->emergencyFundPaid($request, $page, $limit, $q),
            2709 => $this->emergencyFundUnblock($request, $page, $limit, $q),
            2736 => $this->recoupReceivedList($request, $page, $limit, $q),
            2752 => $this->pcbWorkflowList($request, $page, $limit, $q),
            2754 => $this->emergencyFundCashbook($request, $page, $limit, $q),
            2765 => $this->loanMonthly($request, $page, $limit, $q),
            2768 => $this->emergencyFundVerify($request, $page, $limit, $q),
            2818 => $this->loanChangeStatusComplete($request, $page, $limit, $q),
            2819 => $this->loanStopAccrual($request, $page, $limit, $q),
            2833 => $this->otRosterSchedule($request, $page, $limit, $q),
            2842 => $this->otShiftDayOff($request, $page, $limit, $q),
            2868 => $this->loanNewApplication($request, $page, $limit, $q),
            2915 => $this->storeApplicationList($request, $page, $limit, $q),
            3015 => $this->loanNewApplication($request, $page, $limit, $q),
            3060 => $this->loanAdvancePayment($request, $page, $limit, $q),
            3290 => $this->loanUpdatePayment($request, $page, $limit, $q),
            3307 => $this->loanStatusCancel($request, $page, $limit, $q),

            // ── FILE 1543: Project Monitoring ────────────────────────────
            2110 => $this->projectBudget($request, $page, $limit, $q),
            2115 => $this->projectBudgetList($request, $page, $limit, $q),
            2673 => $this->allocationReceiveLog($request, $page, $limit, $q),
            2963 => $this->projectMonitoring($request, $page, $limit, $q),
            3077 => $this->auditorWipReport($request, $page, $limit, $q),

            // ── FILE 1547: Asset ─────────────────────────────────────────
            1622 => $this->assetDepreciationProcess($request, $page, $limit, $q),
            1628 => $this->assetDepreciationAccrual($request, $page, $limit, $q),
            1647 => $this->assetDisposalApplication($request, $page, $limit, $q),
            1674 => $this->assetDisposalListing($request, $page, $limit, $q),
            1683 => $this->assetDepreciationListing($request, $page, $limit, $q),
            1835 => $this->assetGrn($request, $page, $limit, $q),
            1946 => $this->assetJournal($request, $page, $limit, $q),
            1959 => $this->assetApprovalGrn($request, $page, $limit, $q),
            2151 => $this->assetBill($request, $page, $limit, $q),
            2397 => $this->assetDisposalReqList($request, $page, $limit, $q),
            2431 => $this->assetByDepartment($request, $page, $limit, $q),
            2495 => $this->assetDisposalRegistration($request, $page, $limit, $q),
            2543 => $this->assetTransferList($request, $page, $limit, $q),
            2544 => $this->assetTransferApplication($request, $page, $limit, $q),
            /** Purchasing / Advertisement / New Tender/Quotation — `tender_master` form shell (PAGE 2165 / menu 2618). */
            2618 => $this->purchasingAdvertisementRequest2618($request, $page, $limit, $q),
            /** Purchasing / Vendor Assessment / Good Receive Note — `goods_receive_master` + `vendor_assessment_master` (PAGE 2170 / menu 2624). */
            2624 => $this->purchasingGrnVendorAssessment2624($request, $page, $limit, $q),
            /** Purchasing / Vendor Assessment / Work Progress Note — `work_progress_master` (PAGE 2172 / menu 2626). */
            2626 => $this->purchasingWpnVendorAssessment2626($request, $page, $limit, $q),
            /** Purchasing / Vendor / List of Vendor — `vend_customer_supplier` (PAGE 2205 / menu 2663). */
            2663 => $this->purchasingListingOfVendor($request, $page, $limit, $q),
            2664 => $this->assetMaintenanceMasterList($request, $page, $limit, $q),
            2665 => $this->assetDamageList($request, $page, $limit, $q),
            2667 => $this->assetAdjustmentList($request, $page, $limit, $q),
            3086 => $this->assetJournalAdjListing($request, $page, $limit, $q),
            3087 => $this->assetJournalAdjForm($request, $page, $limit, $q),
            3302 => $this->assetDisposalInfo($request, $page, $limit, $q),
            3374 => $this->assetLostApplication($request, $page, $limit, $q),
            3385 => $this->assetOpeningList($request, $page, $limit, $q),
            3387 => $this->assetScheduleMaintenance($request, $page, $limit, $q),
            3400 => $this->assetDisposalActualMethod($request, $page, $limit, $q),

            // ── FILE 1651: Credit Control ────────────────────────────────
            1663 => $this->ccSetupExecution($request, $page, $limit, $q),
            1665 => $this->ccBadDebt($request, $page, $limit, $q),
            2336 => $this->ccAdvanceMonitoring($request, $page, $limit, $q),
            2364 => $this->ccReminderReport($request, $page, $limit, $q),
            2627 => $this->ccDebtorBucketAgeing($request, $page, $limit, $q),
            // 2633 is routed as Account Payable / Payment / Payment Notice in the migrated menu.
            3064 => $this->ccDebtorReminder($request, $page, $limit, $q),
            3069 => $this->ccBucketAgeingReport($request, $page, $limit, $q),
            3226 => $this->investmentNewApplication($request, $page, $limit, $q), // reuse
            3330 => $this->ccStudentBond($request, $page, $limit, $q),
            3349 => $this->ccBadDebtDetail($request, $page, $limit, $q),
            3495 => $this->ccDoubtfulList($request, $page, $limit, $q),
            3496 => $this->ccBadDebtSetup($request, $page, $limit, $q),
            3504 => $this->ccScheduleBoc($request, $page, $limit, $q),
            3505 => $this->ccDebtType($request, $page, $limit, $q),

            // ── FILE 1684: Purchasing ────────────────────────────────────
            1660 => $this->purchasingSetupItem($request, $page, $limit, $q),
            1771 => $this->purchasingPrList($request, $page, $limit, $q),
            /** Purchasing / Purchase Requisition / Purchase Requisition List — `requisition_master` (PAGE 1773). */
            1773 => $this->purchasingPurchaseRequisitionList($request, $page, $limit, $q),
            1820 => $this->purchasingItemMainSetupShell($request, $page, $limit, $q),
            1932 => $this->purchasingListOfJobscopeShell($request, $page, $limit, $q),
            /** Purchasing / Setup / Assessment Question (PAGE 1708) — `vendor_assessment_setup`. */
            2066 => $this->purchasingAssessmentQuestionShell($request, $page, $limit, $q),
            /** Purchasing / Vendor / Vendor Profile — `vend_customer_supplier` (PAGE 1507 / menu 1828). */
            1828 => $this->purchasingVendorProfile($request, $page, $limit, $q),
            1829 => $this->purchasingItemMainListing($request, $page, $limit, $q),
            /** Purchasing / Purchase Order List — `purchase_order_master` (PAGE 1512 / menu 1833). */
            1833 => $this->purchasingPurchaseOrderMenu1833($request, $page, $limit, $q),
            /** Purchasing / Work Progress Note Detail — `work_progress_master` + grids (PAGE 1517 / menu 1838). */
            1838 => $this->purchasingWorkProgressNoteDetail1838($request, $page, $limit, $q),
            /** Purchasing / Good Receive Note / Good Receive Note List — `goods_receive_master` (PAGE 1518 / menu 1839). */
            1839 => $this->purchasingGoodReceiveNoteList1839($request, $page, $limit, $q),
            /** Purchasing / Work Progress Note List — `work_progress_master` (PAGE 1519 / menu 1840). */
            1840 => $this->purchasingWorkProgressNoteList1840($request, $page, $limit, $q),
            /** Purchasing / Vendor / Bank Account No For Updated — vendor status/payment update shell (PAGE 1616 / menu 1955). */
            1955 => $this->purchasingVendorBankAccountUpdated1955($request, $page, $limit, $q),
            1856 => $this->purchasingPrForm($request, $page, $limit, $q),
            1858 => $this->purchasingGrnForm($request, $page, $limit, $q),
            /** Purchasing / Purchase Order Cancellation — POCANCEL_STATUS (PAGE 1684 / menu 2039). */
            2039 => $this->purchasingPoCancellationStatus2039($request, $page, $limit, $q),
            /** Purchasing / PO Report Print / Bendahari (PAGE 1602 / menu 1939). */
            1939 => $this->purchasingPoReportPrint1939($request, $page, $limit, $q),
            /** Purchasing / PO Report Print / PTJ (PAGE 1605 / menu 1941). */
            1941 => $this->purchasingPoReportPrint1941($request, $page, $limit, $q),
            2041 => $this->purchasingPoClosing($request, $page, $limit, $q),
            2042 => $this->purchasingPoUpdate($request, $page, $limit, $q),
            /** Purchasing / Work Progress Note Cancel List (PAGE 1723 / menu 2082). */
            2082 => $this->purchasingWorkProgressNoteCancel2082($request, $page, $limit, $q),
            /** Purchasing / Good Receive Note / Good Receive Note Cancel — `goods_receive_master` (PAGE 1726 / menu 2085). */
            2085 => $this->purchasingGrnCancel2085($request, $page, $limit, $q),
            2320 => $this->purchasingPurchaseRequisitionCancellationList($request, $page, $limit, $q),
            2333 => $this->purchasingTenderEvaluation($request, $page, $limit, $q),
            2361 => $this->purchasingVendorListByItem($request, $page, $limit, $q),
            2642 => $this->purchasingListingOfVendor($request, $page, $limit, $q),
            2706 => $this->purchasingPrintSab($request, $page, $limit, $q),
            2708 => $this->purchasingOtherPayment($request, $page, $limit, $q),
            2721 => $this->purchasingAdvertisementReqList($request, $page, $limit, $q),
            2724 => $this->purchasingQuotationSelection($request, $page, $limit, $q),
            2762 => $this->purchasingAdvertisementComplete($request, $page, $limit, $q),
            2827 => $this->purchasingTenderCancel($request, $page, $limit, $q),
            2828 => $this->purchasingAdvertisementTime($request, $page, $limit, $q),
            2845 => $this->purchasingOfferLetter($request, $page, $limit, $q),
            2846 => $this->purchasingVoListAll($request, $page, $limit, $q),
            2847 => $this->purchasingTenderParticipant($request, $page, $limit, $q),
            2848 => $this->purchasingNewVo($request, $page, $limit, $q),
            /** List of PR To Be Cancel — `requisition_master` (PAGE 3038). */
            3038 => $this->purchasingListOfPrToBeCancel($request, $page, $limit, $q),
            3039 => $this->purchasingPrCancelForm($request, $page, $limit, $q),
            3041 => $this->purchasingPrToCancelPartial($request, $page, $limit, $q),
            3042 => $this->purchasingPrCancelPartialForm($request, $page, $limit, $q),
            3100 => $this->purchasingVendorAssessmentReport($request, $page, $limit, $q),
            3106 => $this->purchasingVendorAssessmentWpn($request, $page, $limit, $q),
            3272 => $this->purchasingEvaluationList($request, $page, $limit, $q),
            3306 => $this->purchasingCommitteeReport($request, $page, $limit, $q),
            3320 => $this->purchasingAgreementListAll($request, $page, $limit, $q),
            3323 => $this->purchasingNewVo($request, $page, $limit, $q),

            // ── FILE 1701: Cashbook / Bank Statement Reports ─────────────
            1871 => $this->bankStatementList($request, $page, $limit, $q),
            2094 => $this->bankStatementList($request, $page, $limit, $q),
            2099 => $this->bankStatementMonthlyList($request, $page, $limit, $q),
            2100 => $this->bankStatementMonthlyList($request, $page, $limit, $q),
            2263 => $this->bankStatementAttachment($request, $page, $limit, $q, 3),
            2264 => $this->bankStatementAttachment($request, $page, $limit, $q, 2),
            2269 => $this->bankStatementAttachment($request, $page, $limit, $q, 1),
            2280 => $this->bankStatementUnidentifiedEntry($request, $page, $limit, $q),
            2281 => $this->bankStatementMatchingReport($request, $page, $limit, $q),
            2331 => $this->bankReconStatement($request, $page, $limit, $q),
            2479 => $this->cashbookClosing($request, $page, $limit, $q),
            2533 => $this->cashbookClosingAttachment($request, $page, $limit, $q, 1),
            2534 => $this->cashbookClosingAttachment($request, $page, $limit, $q, 2),
            2535 => $this->cashbookClosingAttachment($request, $page, $limit, $q, 3),
            2542 => $this->cashbookMasterFile($request, $page, $limit, $q),
            2553 => $this->cashbookMonthlyList($request, $page, $limit, $q),
            2664 => $this->cashbookDailySummary($request, $page, $limit, $q),
            2665 => $this->cashbookMonthlySummary($request, $page, $limit, $q),
            2670 => $this->cashbookBalanceByFund($request, $page, $limit, $q),
            3009 => $this->bankReconStatement($request, $page, $limit, $q),
            3256 => $this->bankStatementUnmatched($request, $page, $limit, $q),

            // ── FILE 1709: Account Payable ───────────────────────────────
            1187 => $this->apLoanDisburse($request, $page, $limit, $q),
            1716 => $this->apBillRegistrationList($request, $page, $limit, $q),
            1823 => $this->apVoucherRegistration($request, $page, $limit, $q),
            1897 => $this->apOtherPayment($request, $page, $limit, $q),
            1928 => $this->apRefund($request, $page, $limit, $q),
            1986 => $this->apPayeeReport($request, $page, $limit, $q),
            /** Purchasing / Purchase Order / New PO Cancellation — PO_REJECT (PAGE 1678 / menu 2030). */
            2030 => $this->purchasingPoNewCancellation2030($request, $page, $limit, $q),
            2107 => $this->apBillCancelKnockoff($request, $page, $limit, $q),
            2166 => $this->apBillDaysReport($request, $page, $limit, $q),
            2198 => $this->apVoucherReport($request, $page, $limit, $q),
            2297 => $this->apVoucherListing($request, $page, $limit, $q),
            2298 => $this->apVoucherCancel($request, $page, $limit, $q),
            2337 => $this->apVoucherReplace($request, $page, $limit, $q),
            2355 => $this->apBillCancelListing($request, $page, $limit, $q),
            2409 => $this->apJournalBillCancel($request, $page, $limit, $q),
            2411 => $this->apIdentifiedReceiptsReport($request, $page, $limit, $q),
            2412 => $this->apVoucherCancelJournal($request, $page, $limit, $q),
            2533 => $this->cashbookClosingAttachment($request, $page, $limit, $q, 1),
            2633 => $this->apPaymentNotice($request, $page, $limit, $q),
            2706 => $this->apPrintSabBatch($request, $page, $limit, $q),
            2717 => $this->apPaymentListing($request, $page, $limit, $q),
            2766 => $this->apPayeeListing($request, $page, $limit, $q),
            2817 => $this->apDownloadVoucherBatch($request, $page, $limit, $q),
            2878 => $this->apSendEmail($request, $page, $limit, $q),
            2879 => $this->apHistoricalSender($request, $page, $limit, $q),
            2895 => $this->apBillReport($request, $page, $limit, $q),
            2974 => $this->apTransactionHistory($request, $page, $limit, $q),
            3133 => $this->apPayeeReportByPtj($request, $page, $limit, $q),
            3242 => $this->apCreditNoteForm($request, $page, $limit, $q),
            3243 => $this->apCreditNoteListing($request, $page, $limit, $q),
            3254 => $this->apJournalRevaluation($request, $page, $limit, $q),
            3264 => $this->apCreditNoteCancel($request, $page, $limit, $q),
            3270 => $this->apMoneyTransferList($request, $page, $limit, $q),
            3345 => $this->apOtherPaymentUpdate($request, $page, $limit, $q),
            3346 => $this->apGenerateVoucherDraft($request, $page, $limit, $q),
            3348 => $this->apVoucherMoneyTransferList($request, $page, $limit, $q),
            3372 => $this->apBukuDaftarTerimaan($request, $page, $limit, $q),
            3387 => $this->assetScheduleMaintenance($request, $page, $limit, $q),
            3461 => $this->apDirectVoucher($request, $page, $limit, $q),
            3526 => $this->apUpdateBankAccount($request, $page, $limit, $q),
            3529 => $this->apUpdateBankAccount($request, $page, $limit, $q),
            3534 => $this->apDownloadVoucherByRef($request, $page, $limit, $q),
            3535 => $this->apVoucherProcess($request, $page, $limit, $q),
            3538 => $this->apPaymentRejectBatch($request, $page, $limit, $q),
            3543 => $this->apDebitNoteForm($request, $page, $limit, $q),
            3546 => $this->apVoucherInfoCreditor($request, $page, $limit, $q),
            3548 => $this->apDebitNoteForm($request, $page, $limit, $q),
            3549 => $this->apDebitNoteCancel($request, $page, $limit, $q),
            3550 => $this->apDebitNoteCancelForm($request, $page, $limit, $q),
            3558 => $this->apPaymentRecord($request, $page, $limit, $q),

            // ── FILE 3401: Parliament/Budget Setup ───────────────────────
            3414 => $this->parliamentGroupSetup($request, $page, $limit, $q),
            3426 => $this->parliamentGroupSetup($request, $page, $limit, $q),

            // ── FILE 3454: Treatment (Medical) ───────────────────────────
            3460 => $this->treatmentList($request, $page, $limit, $q),
            3472 => $this->treatmentLedgerByYear($request, $page, $limit, $q),
            3473 => $this->treatmentNotificationLetter($request, $page, $limit, $q),
            3474 => $this->treatmentOverallLedger($request, $page, $limit, $q),
            3475 => $this->treatmentStatementByStaff($request, $page, $limit, $q),

            // ── FILE 1052: GL Reports ────────────────────────────────────
            1514 => $this->glReversal($request, $page, $limit, $q),
            1882 => $this->glOpening($request, $page, $limit, $q),
            2081 => $this->glReverseJournalListing($request, $page, $limit, $q),
            2221 => $this->glUnidentifiedReceiptList($request, $page, $limit, $q),
            2225 => $this->glManualJournalEntry($request, $page, $limit, $q),
            2280 => $this->glUnidentifiedReceiptEntry($request, $page, $limit, $q),
            2411 => $this->glIdentifiedReceiptsReport($request, $page, $limit, $q),
            3026 => $this->glYearlyClosing($request, $page, $limit, $q),
            3086 => $this->glJournalAdjustmentListing($request, $page, $limit, $q),
            3087 => $this->glJournalAdjustmentForm($request, $page, $limit, $q),
            3428 => $this->glTrialBalanceSummary($request, $page, $limit, $q),
            3430 => $this->glDetailsTransaction($request, $page, $limit, $q),
            3487 => $this->glCombineReport($request, $page, $limit, $q),
            3508 => $this->glTrialBalance($request, $page, $limit, $q),
            3516 => $this->glBsPl($request, $page, $limit, $q),
            3582 => $this->glReportItemSetup($request, $page, $limit, $q),

            default => ['rows' => [], 'total' => 0, 'connector' => 'remaining_shell_preview'],
        };
    }

    /**
     * Legacy SNA_API_PURCHASING_WPN_CANCEL `processcancelwpn_entry` (workflowSubmit commented out there).
     *
     * @return array{success: bool, successMessage?: string, wpnNo?: string, errorMsg?: string}
     */
    public function processWpnCancelEntry(string $cboxKey, string $username): array
    {
        $cboxKey = trim($cboxKey);
        if ($cboxKey === '') {
            return ['success' => false, 'errorMsg' => 'Please choose one WPN row.'];
        }

        if (! preg_match('/^(\d+)_(.+)$/', $cboxKey, $m)) {
            return ['success' => false, 'errorMsg' => 'Invalid selection reference.'];
        }

        $seqId = (int) $m[1];
        $progressNo = trim((string) $m[2]);
        if ($seqId < 1 || $progressNo === '') {
            return ['success' => false, 'errorMsg' => 'Invalid selection reference.'];
        }

        $cx = $this->conn();

        try {
            $cx->transaction(function () use ($cx, $seqId, $progressNo, $username): void {
                /** @var object|null $row */
                $row = $cx->table('work_progress_master')->where('wpm_progress_id', $seqId)->lockForUpdate()->first();
                if (! $row) {
                    throw new \RuntimeException('Work Progress Note not found.');
                }
                if (trim((string) ($row->wpm_progress_no ?? '')) !== $progressNo) {
                    throw new \RuntimeException('Selection does not match this WPN record.');
                }
                if (! in_array((string) ($row->wpm_status ?? ''), ['ENDORSE', 'APPROVE'], true)) {
                    throw new \RuntimeException('This WPN is not in a cancellable status (ENDORSE/APPROVE).');
                }

                $hasApproveBill = $cx->table('bills_master')
                    ->where('bim_status', 'APPROVE')
                    ->whereRaw('IFNULL(grm_receive_no, ?) = ?', ['', $progressNo])
                    ->exists();
                if ($hasApproveBill) {
                    throw new \RuntimeException('This WPN has an approved bill and cannot be cancelled from this list.');
                }

                $now = now();

                $n = $cx->table('work_progress_master')
                    ->where('wpm_progress_id', $seqId)
                    ->update([
                        'wpm_status' => 'CANCEL',
                        'wpm_cancel_by' => $username,
                        'wpm_cancel_date' => $now,
                        'updateddate' => $now,
                        'updatedby' => $username,
                    ]);
                if ($n < 1) {
                    throw new \RuntimeException('Failed to update Work Progress Note master.');
                }

                $cx->table('work_progress_details')
                    ->where('wpm_progress_id', $seqId)
                    ->update([
                        'wpd_status' => 'CANCEL',
                        'updateddate' => $now,
                        'updatedby' => $username,
                    ]);
            });
        } catch (\RuntimeException $e) {
            return ['success' => false, 'errorMsg' => $e->getMessage()];
        } catch (\Throwable $e) {
            report($e);

            return ['success' => false, 'errorMsg' => 'WPN cancel failed.'];
        }

        return [
            'success' => true,
            'successMessage' => 'Work Progress Note successfully submitted to be cancelled. WPN No: '.$progressNo,
            'wpnNo' => $progressNo,
        ];
    }

    /* ── helpers ──────────────────────────────────────────────────────── */
    private function conn(): Connection
    {
        return DB::connection('mysql_secondary');
    }

    private function likeEscape(string $q): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';
    }

    private function paginate(Builder $base, int $page, int $limit): array
    {
        $total = (clone $base)->count();
        $rows = $base->skip(($page - 1) * $limit)->take($limit)->get()->map(fn ($r) => (array) $r)->toArray();

        return ['rows' => $rows, 'total' => $total];
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1228 — Portal / DCA
     * ══════════════════════════════════════════════════════════════════ */
    private function dcaListAssign(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_assign_dcapanel as ca')
            ->leftJoin('debt_collector_agent as da', 'da.vcs_vendor_code', '=', 'ca.cad_flag_panel')
            ->select(['ca.cad_id', 'ca.cad_debtor_no', 'ca.cad_debtor_name', 'ca.cad_amount', 'ca.cad_reference_date', 'ca.cad_flag_dca', 'da.vcs_vendor_name']);
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(ca.cad_debtor_name,''), IFNULL(ca.cad_debtor_no,''))) LIKE ?", [$like]);
        }
        $base->orderByDesc('ca.cad_reference_date');

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'dca_list_assign']);
    }

    private function dcaCompletedList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_assign_history as ch')
            ->leftJoin('debt_collector_agent as da', 'da.dca_id', '=', 'ch.dca_id')
            ->select(['ch.cah_id', 'ch.cm_id', 'ch.dca_id', 'da.vcs_vendor_name', 'ch.cah_assign_date', 'ch.cah_status']);
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(da.vcs_vendor_name,''), IFNULL(ch.cah_assign_no,''))) LIKE ?", [$like]);
        }
        $base->orderByDesc('ch.cah_assign_date');

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'dca_completed_list']);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1229 — Debtor Portal
     * ══════════════════════════════════════════════════════════════════ */
    private function debtorPortalReceipt(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('receipt_master as rm')
            ->select(['rm.rma_receipt_master_id', 'rm.rma_receipt_no', 'rm.rma_cust_id', 'rm.rma_total_amt', 'rm.rma_enter_date', 'rm.rma_status'])
            ->orderByDesc('rm.rma_receipt_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(rm.rec_no,''), IFNULL(rm.debtor_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'debtor_portal_receipt']);
    }

    private function debtorPortalCreditNote(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('credit_note_master as cn')
            ->select(['cn.cnm_credit_note_master_id', 'cn.cnm_crnote_no', 'cn.cnm_cust_id', 'cn.cnm_cn_total_amount', 'cn.cnm_crnote_date', 'cn.cnm_status_cd'])
            ->orderByDesc('cn.cnm_credit_note_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cn.cn_no,''), IFNULL(cn.debtor_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'debtor_portal_credit_note']);
    }

    private function debtorPortalDebitNote(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('debit_note_master as dn')
            ->select(['dn.dnm_debit_note_master_id', 'dn.dnm_dnnote_no', 'dn.dnm_cust_id', 'dn.dnm_dn_total_amount', 'dn.dnm_dnnote_date', 'dn.dnm_status_cd'])
            ->orderByDesc('dn.dnm_debit_note_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(dn.dn_no,''), IFNULL(dn.debtor_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'debtor_portal_debit_note']);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1404 — Investment
     * ══════════════════════════════════════════════════════════════════ */
    private function investmentList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('advance_main as am')
            ->select(['am.am_id', 'am.am_payto_id', 'am.am_payto_name', 'am.am_opening',
                'am.am_receive_date', 'am.updateddate', 'am.am_type'])
            ->orderByDesc('am.am_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(am.am_payto_id,''), IFNULL(am.am_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'investment_list']);
    }

    private function investmentInstitution(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('bank_master as bm')
            ->select(['bm.bnm_bank_id', 'bm.bnm_bank_desc', 'bm.bnm_bank_code', 'bm.bnm_status'])
            ->orderBy('bm.bnm_bank_desc');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bm.bnm_bank_desc,''), IFNULL(bm.bnm_bank_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'investment_institution']);
    }

    private function investmentByBatch(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('advance_main as am')
            ->select(['am.am_id', 'am.am_payto_id', 'am.am_payto_name', 'am.am_opening', 'am.am_type'])
            ->orderByDesc('am.am_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(am.am_payto_id,''), IFNULL(am.am_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'investment_by_batch']);
    }

    private function investmentBank(Request $r, int $page, int $limit, string $q): array
    {
        return $this->investmentInstitution($r, $page, $limit, $q);
    }

    private function investmentType(Request $r, int $page, int $limit, string $q): array
    {
        // grant_type: gty_grant_code (PK), gty_grant_name, gty_status (verified)
        $base = $this->conn()->table('grant_type as gt')
            ->select(['gt.gty_grant_code', 'gt.gty_grant_name', 'gt.gty_status'])
            ->orderBy('gt.gty_grant_name');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(gt.gty_grant_code,''), IFNULL(gt.gty_grant_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'investment_type']);
    }

    private function investmentTenure(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_tenure');
    }

    private function investmentWithdrawLetter(Request $r, int $page, int $limit, string $q): array
    {
        return $this->investmentList($r, $page, $limit, $q);
    }

    private function investmentWithdrawApplication(Request $r, int $page, int $limit, string $q): array
    {
        return $this->investmentList($r, $page, $limit, $q);
    }

    private function investmentFdSummary(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_fd_summary');
    }

    private function investmentFdDetails(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_fd_details');
    }

    private function investmentWithdrawRejected(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('advance_main as am')
            ->where('am.am_type', 'REJECTED')
            ->select(['am.am_id', 'am.am_payto_id', 'am.am_payto_name', 'am.am_opening', 'am.am_type'])
            ->orderByDesc('am.am_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(am.am_payto_id,''), IFNULL(am.am_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'investment_withdraw_rejected']);
    }

    private function investmentWithdrawList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->investmentList($r, $page, $limit, $q);
    }

    private function investmentMonthlySchedule(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_monthly_schedule');
    }

    private function investmentAccrualReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_accrual_report');
    }

    private function investmentAccrualPosting(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_accrual_posting');
    }

    private function investmentAccrualNoPosting(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_accrual_no_posting');
    }

    private function investmentNewApplication(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_new_application');
    }

    private function investmentNewAppDraft(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('advance_main as am')
            ->where('am.am_type', 'DRAFT')
            ->select(['am.am_id', 'am.am_payto_id', 'am.am_payto_name', 'am.am_opening', 'am.am_type'])
            ->orderByDesc('am.am_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(am.am_payto_id,''), IFNULL(am.am_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'investment_new_app_draft']);
    }

    private function investmentNewAppRejected(Request $r, int $page, int $limit, string $q): array
    {
        return $this->investmentWithdrawRejected($r, $page, $limit, $q);
    }

    private function investmentInstructionLetter(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_instruction_letter');
    }

    private function investmentJournalDetails(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_journal_details');
    }

    private function investmentReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_report');
    }

    private function investmentRegisterReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('investment_register_report');
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1489 — Petty Cash Reports
     * ══════════════════════════════════════════════════════════════════ */
    private function pettyCashPersonnel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('petty_cash_master as pc')
            ->select(['pc.pms_id', 'pc.pms_application_no', 'pc.pms_pay_to_id', 'pc.pms_total_amt', 'pc.pms_status', 'pc.pms_request_date'])
            ->orderByDesc('pc.pms_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(pc.pms_application_no,''), IFNULL(pc.pms_pay_to_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'petty_cash_personnel']);
    }

    private function pettyCashRecoupReport(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('petty_cash_main as pcm')
            ->select(['pcm.pcm_id', 'pcm.pcm_running_no', 'pcm.pcm_balance', 'pcm.updateddate', 'pcm.pcm_status'])
            ->orderByDesc('pcm.pcm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(pcm.pcm_running_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'petty_cash_recoup_report']);
    }

    private function pettyCashBaucarRuncit(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('petty_cash_details as pcd')
            ->leftJoin('petty_cash_main as pcm', 'pcm.pcm_id', '=', 'pcd.pcm_id')
            ->select(['pcd.pcd_id', 'pcd.pcm_id', 'pcm.pcm_running_no', 'pcd.pcd_trans_amt', 'pcd.pcd_trans_desc'])
            ->orderByDesc('pcd.pcd_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(pcm.pcm_running_no,''), IFNULL(pcd.pcd_trans_desc,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'petty_cash_baucar_runcit']);
    }

    private function pettyCashBukuTunai(Request $r, int $page, int $limit, string $q): array
    {
        return $this->pettyCashRecoupReport($r, $page, $limit, $q);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1533 — Loan / OT / Emergency Fund / Store / FS
     * ══════════════════════════════════════════════════════════════════ */
    private function pettyCashList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('petty_cash_main as pcm')
            ->select(['pcm.pcm_id', 'pcm.pcm_running_no', 'pcm.pcm_balance', 'pcm.updateddate', 'pcm.pcm_status'])
            ->orderByDesc('pcm.pcm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(pcm.pcm_running_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'petty_cash_list']);
    }

    private function emergencyFundList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('emergency_fund_main as ef')

            ->select(['ef.efm_fund_id', 'ef.oun_code', 'ef.efm_balance', 'ef.createddate', 'ef.efm_status'])
            ->orderByDesc('ef.efm_fund_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(ef.org_code,''), IFNULL(ef.oun_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'emergency_fund_list']);
    }

    private function emergencyFundListAll(Request $r, int $page, int $limit, string $q): array
    {
        return $this->emergencyFundList($r, $page, $limit, $q);
    }

    private function emergencyFundPaid(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('emergency_fund_main as ef')

            ->where('ef.efm_status', 'PAID')
            ->select(['ef.efm_fund_id', 'ef.oun_code', 'ef.efm_balance', 'ef.createddate', 'ef.efm_status'])
            ->orderByDesc('ef.efm_fund_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(ef.org_code,''), IFNULL(ef.oun_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'emergency_fund_paid']);
    }

    private function emergencyFundUnblock(Request $r, int $page, int $limit, string $q): array
    {
        return $this->emergencyFundList($r, $page, $limit, $q);
    }

    private function emergencyFundVerify(Request $r, int $page, int $limit, string $q): array
    {
        return $this->emergencyFundList($r, $page, $limit, $q);
    }

    private function emergencyFundCashbook(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('emergency_fund_cashbook as efc')
            ->select(['efc.ecb_id', 'efc.ecb_reference', 'efc.ecb_receive_amount', 'efc.ecb_date', 'efc.ecb_paid_amount'])
            ->orderByDesc('efc.ecb_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(efc.ecb_reference,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'emergency_fund_cashbook']);
    }

    private function workOrderInstruction(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('work_order_master as wom')
            ->select(['wom.wom_workorder_id', 'wom.wom_workorder_no', 'wom.wom_description', 'wom.wom_status', 'wom.createddate'])
            ->orderByDesc('wom.wom_workorder_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(wom.wom_workorder_no,''), IFNULL(wom.wo_description,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'work_order_instruction']);
    }

    private function workOrderInstructionList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->workOrderInstruction($r, $page, $limit, $q);
    }

    private function workOrderList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->workOrderInstruction($r, $page, $limit, $q);
    }

    private function otClaimList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_overtime_master as om')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'om.stf_staff_id')
            ->select(['om.otm_overtime_master_id', 'om.stf_staff_id', 'om.otm_apply_date', 'om.otm_total_hours', 'om.otm_status'])
            ->orderByDesc('om.otm_overtime_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(om.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ot_claim_list']);
    }

    private function otClaimNotification(Request $r, int $page, int $limit, string $q): array
    {
        return $this->otClaimList($r, $page, $limit, $q);
    }

    private function otClaimApplication(Request $r, int $page, int $limit, string $q): array
    {
        return $this->otClaimList($r, $page, $limit, $q);
    }

    private function otHierarchySetup(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_overtime_hierarchy as oh')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'oh.stf_staff_id')
            ->select(['oh.soh_id', 'oh.stf_staff_id', 'oh.soh_head_of_unit', 'oh.createddate'])
            ->orderBy('oh.soh_head_of_unit');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(oh.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ot_hierarchy_setup']);
    }

    private function otCollectedHours(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_overtime_collect_hours as oc')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'oc.stf_staff_id')
            ->select(['oc.otc_collect_hours_id', 'oc.stf_staff_id', 'oc.otd_transfer_date', 'oc.otc_total_days'])
            ->orderByDesc('oc.otc_collect_hours_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(oc.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ot_collected_hours']);
    }

    private function otRosterSchedule(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_roster_schedule as rs')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'rs.stf_staff_id')
            ->select(['rs.src_id', 'rs.stf_staff_id', 'rs.src_start_date', 'rs.src_schedule_in'])
            ->orderByDesc('rs.src_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(rs.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ot_roster_schedule']);
    }

    private function otShiftDayOff(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ot_shift_day_off');
    }

    private function loanProfile(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('advance_main as am')

            ->select(['am.am_id', 'am.am_payto_id', 'am.am_payto_id', 'am.am_payto_name',
                'am.am_opening', 'am.am_type'])
            ->orderByDesc('am.am_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(am.am_payto_id,''), IFNULL(am.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'loan_profile']);
    }

    private function loanDisburse(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanGenerateSchedule(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanAccrualMonthly(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('accrual_log as al')
            ->select(['al.acl_id', 'al.acl_bl_name', 'al.createddate', 'al.createddate', 'al.acl_bl_name'])
            ->orderByDesc('al.acl_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(al.acl_bl_name,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'loan_accrual_monthly']);
    }

    private function loanDeleteSchedule(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanInformation(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanFinancialStatus(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanNewApplication(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('loan_new_application');
    }

    private function loanSpecialApplication(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanRegenerateSchedule(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanChangeStatusComplete(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('advance_main as am')

            ->where('am.am_type', 'COMPLETE')
            ->select(['am.am_id', 'am.am_payto_id', 'am.am_payto_id', 'am.am_payto_name',
                'am.am_opening', 'am.am_type'])
            ->orderByDesc('am.am_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(am.am_payto_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'loan_change_status_complete']);
    }

    private function loanStopAccrual(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanMonthly(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanAdvancePayment(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanUpdatePayment(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanProfile($r, $page, $limit, $q);
    }

    private function loanStatusCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('advance_main as am')

            ->where('am.am_type', 'CANCEL')
            ->select(['am.am_id', 'am.am_payto_id', 'am.am_payto_id', 'am.am_payto_name',
                'am.am_opening', 'am.am_type'])
            ->orderByDesc('am.am_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(am.am_payto_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'loan_status_cancel']);
    }

    private function staffAccountBank(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_account as sa')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'sa.stf_staff_id')
            ->select(['sa.sta_staff_acct_id', 'sa.stf_staff_id',
                'sa.sta_branch', 'sa.sta_acct_no', 'sa.sta_status'])
            ->orderByDesc('sa.sta_staff_acct_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(sa.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(sa.sta_acct_no,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'staff_account_bank']);
    }

    private function fsCreditNote(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('credit_note_master as cn')
            ->select(['cn.cnm_credit_note_master_id', 'cn.cnm_crnote_no', 'cn.cnm_cust_id', 'cn.cnm_cn_total_amount', 'cn.cnm_crnote_date', 'cn.cnm_status_cd'])
            ->orderByDesc('cn.cnm_credit_note_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cn.cn_no,''), IFNULL(cn.debtor_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'fs_credit_note']);
    }

    private function fsDebitNote(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('debit_note_master as dn')
            ->select(['dn.dnm_debit_note_master_id', 'dn.dnm_dnnote_no', 'dn.dnm_cust_id', 'dn.dnm_dn_total_amount', 'dn.dnm_dnnote_date', 'dn.dnm_status_cd'])
            ->orderByDesc('dn.dnm_debit_note_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(dn.dn_no,''), IFNULL(dn.debtor_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'fs_debit_note']);
    }

    private function fsDiscountNote(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('discount_note_master as dm')
            ->select(['dm.dcm_discount_note_master_id', 'dm.dcm_dcnote_no', 'dm.dcm_cust_id', 'dm.dcm_dc_total_amount', 'dm.dcm_dcnote_date', 'dm.dcm_status_cd'])
            ->orderByDesc('dm.dcm_discount_note_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(dm.disc_no,''), IFNULL(dm.dcm_cust_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'fs_discount_note']);
    }

    private function fsSponsor(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('sponsor as sp')
            ->select(['sp.spn_sponsor_id', 'sp.spn_sponsor_code', 'sp.spn_sponsor_name', 'sp.spn_sponsor_type', 'sp.spn_status_cd'])
            ->orderBy('sp.spn_sponsor_name');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(sp.spon_code,''), IFNULL(sp.spon_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'fs_sponsor']);
    }

    private function fsReceipt(Request $r, int $page, int $limit, string $q): array
    {
        return $this->debtorPortalReceipt($r, $page, $limit, $q);
    }

    private function fsPaymentInfo(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('receipt_master as rm')
            ->select(['rm.rma_receipt_master_id', 'rm.rma_receipt_no', 'rm.rma_cust_id', 'rm.rma_total_amt', 'rm.rma_enter_date', 'rm.rma_status'])
            ->orderByDesc('rm.rma_receipt_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(rm.rec_no,''), IFNULL(rm.debtor_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'fs_payment_info']);
    }

    private function fsPaymentHistory(Request $r, int $page, int $limit, string $q): array
    {
        return $this->fsPaymentInfo($r, $page, $limit, $q);
    }

    private function fsVoucherInfo(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('voucher_master as vm')
            ->select(['vm.vma_voucher_id', 'vm.vma_voucher_no', 'vm.vma_payto_id', 'vm.vma_total_amt', 'vm.createddate', 'vm.vma_vch_status'])
            ->orderByDesc('vm.vma_voucher_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_id,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'fs_voucher_info']);
    }

    private function fsAdvancePayment(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('fs_advance_payment');
    }

    private function refundApplication(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('temp_refund_application as ra')
            ->select(['ra.tra_id', 'ra.tra_application_no', 'ra.tra_amt', 'ra.createddate', 'ra.tra_status'])
            ->orderByDesc('ra.tra_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(ra.ref_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'refund_application']);
    }

    private function refundList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->refundApplication($r, $page, $limit, $q);
    }

    private function refundListStudent(Request $r, int $page, int $limit, string $q): array
    {
        return $this->refundApplication($r, $page, $limit, $q);
    }

    private function vehiclePortal(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_vehicle as sv')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'sv.stf_staff_id')
            ->select(['sv.sve_id', 'sv.stf_staff_id', 'sv.sve_registration_no', 'sv.sve_vehicle_type', 'sv.sve_status'])
            ->orderByDesc('sv.sve_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(sv.stf_staff_id,''), IFNULL(s.stf_staff_name,''), IFNULL(sv.sv_plate,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'vehicle_portal']);
    }

    private function storeApplication(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('store_request_master as srm')
            ->select(['srm.srm_store_request_id', 'srm.srm_store_request_no', 'srm.createddate', 'srm.srm_status'])
            ->orderByDesc('srm.srm_store_request_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(srm.srm_store_request_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'store_application']);
    }

    private function storeApplicationList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->storeApplication($r, $page, $limit, $q);
    }

    private function statementOfAccount(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('statement_of_account');
    }

    private function tp1Application(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('tp1_application');
    }

    private function pcbList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('staff_tax_head as th')
            ->leftJoin('staff as s', 's.stf_staff_id', '=', 'th.stf_staff_id')
            ->select(['th.sth_staff_tax_id', 'th.stf_staff_id', 'th.sth_tax_year', 'th.sth_status'])
            ->orderByDesc('th.sth_staff_tax_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(th.stf_staff_id,''), IFNULL(s.stf_staff_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'pcb_list']);
    }

    private function pcbWorkflowList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->pcbList($r, $page, $limit, $q);
    }

    private function recoupReceivedList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->pettyCashRecoupReport($r, $page, $limit, $q);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1543 — Project Monitoring
     * ══════════════════════════════════════════════════════════════════ */
    private function projectBudget(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('budget_project_monitoring as bp')
            ->select(['bp.bpm_id', 'bp.bpm_reference_no', 'bp.bpm_total_amt', 'bp.createddate', 'bp.bpm_status'])
            ->orderByDesc('bp.bpm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(bp.bpm_reference_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'project_budget']);
    }

    private function projectBudgetList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->projectBudget($r, $page, $limit, $q);
    }

    private function allocationReceiveLog(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('budget_allocation_master as bam')
            ->select(['bam.bam_id', 'bam.bam_allocation_no', 'bam.bam_total', 'bam.createddate', 'bam.bam_status_cd'])
            ->orderByDesc('bam.bam_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(bam.bam_allocation_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'allocation_receive_log']);
    }

    private function projectMonitoring(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('capital_project as cp')
            ->select(['cp.cpa_project_id', 'cp.cpa_project_no', 'cp.cpa_project_desc', 'cp.cpa_budget_approved', 'cp.cpa_project_status'])
            ->orderByDesc('cp.cpa_project_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cp.cpa_project_no,''), IFNULL(cp.cpa_project_desc,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'project_monitoring']);
    }

    private function auditorWipReport(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('contractor_wip as cw')
            ->select(['cw.cwp_id', 'cw.agg_no', 'cw.vcs_vendor_name', 'cw.cwp_contractor_amt', 'cw.cwp_vendor_status'])
            ->orderByDesc('cw.cwp_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cw.agg_no,''), IFNULL(cw.vcs_vendor_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'auditor_wip_report']);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1547 — Asset
     * ══════════════════════════════════════════════════════════════════ */
    private function assetList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_inventory_main as ai')
            ->select(['ai.aim_asset_id', 'ai.aim_tag_status', 'ai.aim_asset_desc', 'ai.aim_category',
                'ai.aim_nett_value', 'ai.aim_status'])
            ->orderByDesc('ai.aim_asset_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(ai.aim_tag_status,''), IFNULL(ai.aim_asset_desc,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_list']);
    }

    private function assetGrn(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('goods_receive_master as grm')
            ->select(['grm.grm_receive_id', 'grm.grm_receive_no', 'grm.grm_receive_date', 'grm.vcs_vendor_code', 'grm.grm_status'])
            ->orderByDesc('grm.grm_receive_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(grm.grm_receive_no,''), IFNULL(grm.vcs_vendor_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_grn']);
    }

    private function assetApprovalGrn(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetGrn($r, $page, $limit, $q);
    }

    private function assetBill(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('bills_master as bm')
            ->select(['bm.bim_bills_id', 'bm.bim_bills_no', 'bm.createddate', 'bm.bim_bill_amt', 'bm.bim_status'])
            ->orderByDesc('bm.bim_bills_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bm.bill_no,''), IFNULL(bm.bill_status,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_bill']);
    }

    private function assetJournal(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('asset_journal');
    }

    private function assetDepreciationProcess(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_depreciation as ad')
            ->select(['ad.adp_asset_depreciation_id', 'ad.aim_asset_code', 'ad.adp_year', 'ad.adp_month', 'ad.adp_depr_amt', 'ad.adp_status'])
            ->orderByDesc('ad.adp_asset_depreciation_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(ad.asset_id,''), IFNULL(ad.depr_year,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_depreciation_process']);
    }

    private function assetDepreciationAccrual(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetDepreciationProcess($r, $page, $limit, $q);
    }

    private function assetDepreciationListing(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetDepreciationProcess($r, $page, $limit, $q);
    }

    private function assetDisposalApplication(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_dispose_req_master as adrm')
            ->select(['adrm.arm_dispose_id', 'adrm.arm_disposal_req_no', 'adrm.arm_apply_date', 'adrm.arm_status'])
            ->orderByDesc('adrm.arm_dispose_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(adrm.disp_req_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_disposal_application']);
    }

    private function assetDisposalListing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_dispose_master as adm')
            ->select(['adm.adm_dispose_master_id', 'adm.adm_disposal_no', 'adm.createddate', 'adm.adm_type', 'adm.adm_status'])
            ->orderByDesc('adm.adm_dispose_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(adm.disp_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_disposal_listing']);
    }

    private function assetDisposalReqList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetDisposalApplication($r, $page, $limit, $q);
    }

    private function assetDisposalRegistration(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetDisposalListing($r, $page, $limit, $q);
    }

    private function assetDisposalInfo(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetDisposalListing($r, $page, $limit, $q);
    }

    private function assetDisposalActualMethod(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_dispose_actual_method as adam')
            ->select(['adam.ada_id', 'adam.ada_code', 'adam.ada_desc', 'adam.createddate'])
            ->orderBy('adam.ada_desc');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(adam.dam_code,''), IFNULL(adam.ada_desc,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_disposal_actual_method']);
    }

    private function assetTransferList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_transfer_master as atm')
            ->select(['atm.atm_asset_transfer_id', 'atm.atm_asset_transfer_no', 'atm.createddate', 'atm.atm_description', 'atm.atm_type', 'atm.atm_status'])
            ->orderByDesc('atm.atm_asset_transfer_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(atm.trans_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_transfer_list']);
    }

    private function assetTransferApplication(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetTransferList($r, $page, $limit, $q);
    }

    private function assetVerification(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_verification as av')
            ->select(['av.avm_asset_verify_id', 'av.avm_asset_verify_no', 'av.avm_verify_date', 'av.avm_status'])
            ->orderByDesc('av.avm_asset_verify_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(av.avm_asset_verify_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_verification']);
    }

    private function assetByDepartment(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetList($r, $page, $limit, $q);
    }

    private function assetLostListing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_lost_master as alm')
            ->select(['alm.alm_id', 'alm.alm_report_no', 'alm.alm_qty_asset', 'alm.createddate', 'alm.alm_status'])
            ->orderByDesc('alm.alm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(alm.alm_report_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_lost_listing']);
    }

    private function assetLostApplication(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetLostListing($r, $page, $limit, $q);
    }

    private function assetMaintenanceList(Request $r, int $page, int $limit, string $q): array
    {
        // asset_maintenance_details: amd_maintain_detl_id, amt_maintain_id, aim_asset_code, amd_total_cost (verified)
        $base = $this->conn()->table('asset_maintenance_details as amd')
            ->select([
                'amd.amd_maintain_detl_id',
                'amd.amt_maintain_id',
                'amd.aim_asset_code',
                'amd.amd_current_date',
                'amd.amd_next_date',
                'amd.amd_total_cost',
            ])
            ->orderByDesc('amd.amd_maintain_detl_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(amd.aim_asset_code,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_maintenance_list']);
    }

    private function assetMaintenanceMasterList(Request $r, int $page, int $limit, string $q): array
    {
        // asset_maintenance_master: amt_maintain_id, amt_maintain_no, aim_asset_code, amt_total_cost (verified)
        $base = $this->conn()->table('asset_maintenance_master as amm')
            ->select([
                'amm.amt_maintain_id',
                'amm.amt_maintain_no',
                'amm.aim_asset_code',
                'amm.amt_description',
                'amm.amt_maintenance_type',
                'amm.amt_total_cost',
                'amm.vcs_vendor_code',
            ])
            ->orderByDesc('amm.amt_maintain_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(amm.amt_maintain_no,''), IFNULL(amm.aim_asset_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_maintenance_master_list']);
    }

    private function assetDamageList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_damage_master as adm')
            ->select(['adm.drm_id', 'adm.drm_report_no', 'adm.drm_qty_asset', 'adm.createddate', 'adm.drm_status'])
            ->orderByDesc('adm.drm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(adm.drm_report_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_damage_list']);
    }

    private function assetAdjustmentList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_adjustment_detl as aad')
            ->select(['aad.aad_id', 'aad.aim_asset_code', 'aad.createddate', 'aad.aad_trans_amt', 'aad.aad_type'])
            ->orderByDesc('aad.aad_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(aad.aim_asset_code,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_adjustment_list']);
    }

    private function assetJournalAdjListing(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('asset_journal_adj_listing');
    }

    private function assetJournalAdjForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('asset_journal_adj_form');
    }

    private function assetOpeningList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_opening as ao')
            ->select(['ao.aop_year', 'ao.acm_acct_code', 'ao.aop_year', 'ao.aop_cost_bal', 'ao.createddate'])
            ->orderByDesc('ao.aop_year');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(ao.acm_acct_code,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_opening_list']);
    }

    private function assetScheduleMaintenance(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('asset_schedule_maintenance as asm')
            ->select(['asm.asm_id', 'asm.asm_id', 'asm.asm_start_date', 'asm.asm_schedule_method', 'asm.asm_status'])
            ->orderByDesc('asm.asm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(asm.asm_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'asset_schedule_maintenance']);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1651 — Credit Control
     * ══════════════════════════════════════════════════════════════════ */
    private function ccSetupExecution(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_setup as cs')
            ->select(['cs.cs_id', 'cs.cs_code', 'cs.cs_description', 'cs.cs_type', 'cs.cs_status'])
            ->orderBy('cs.cs_description');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cs.setup_code,''), IFNULL(cs.cs_description,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_setup_execution']);
    }

    private function ccBadDebt(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_bad_debt as cbd')
            ->select(['cbd.cbd_batch_id', 'cbd.cbd_badebt_no', 'cbd.cbd_baddebt_amt', 'cbd.createddate', 'cbd.cbd_status'])
            ->orderByDesc('cbd.cbd_batch_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(cbd.cbd_badebt_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_bad_debt']);
    }

    private function ccBadDebtDetail(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_bad_debt_detail as cbdd')
            ->select(['cbdd.bdd_seq_id', 'cbdd.cbd_batch_id', 'cbdd.bdd_baddebt_amt', 'cbdd.createddate'])
            ->orderByDesc('cbdd.bdd_seq_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(cbdd.cbd_batch_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_bad_debt_detail']);
    }

    private function ccBadDebtSetup(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('baddebt_setup as bs')
            ->select(['bs.bds_id', 'bs.bds_type', 'bs.bds_type', 'bs.bds_status'])
            ->orderBy('bs.bds_type');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bs.bds_type,''), IFNULL(bs.bds_type,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_bad_debt_setup']);
    }

    private function ccAdvanceMonitoring(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_master as cm')
            ->select(['cm.cm_id', 'cm.cm_debtor_creditor', 'cm.createddate', 'cm.createddate', 'cm.cm_status'])
            ->orderByDesc('cm.cm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(cm.cm_debtor_creditor,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_advance_monitoring']);
    }

    private function ccReminderReport(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_reminder as cr')
            ->select(['cr.crm_id', 'cr.crm_debtor_id', 'cr.crm_reminder_date', 'cr.crm_notification_methd', 'cr.crm_confirm_status'])
            ->orderByDesc('cr.crm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(cr.crm_debtor_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_reminder_report']);
    }

    private function ccDebtorBucketAgeing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_bucket_aging as cba')
            ->select(['cba.cba_id', 'cba.cba_bucket_code', 'cba.cba_end_days', 'cba.cba_bucket_code', 'cba.createddate'])
            ->orderByDesc('cba.cba_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(cba.cba_bucket_code,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_debtor_bucket_ageing']);
    }

    private function ccDebtMovementReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->ccAdvanceMonitoring($r, $page, $limit, $q);
    }

    private function ccDebtorReminder(Request $r, int $page, int $limit, string $q): array
    {
        return $this->ccReminderReport($r, $page, $limit, $q);
    }

    private function ccBucketAgeingReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->ccDebtorBucketAgeing($r, $page, $limit, $q);
    }

    private function ccStudentBond(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_student_bond as csb')
            ->select(['csb.csb_id', 'csb.csb_invoice_no', 'csb.csb_receipt_date', 'csb.csb_study_status', 'csb.csb_status'])
            ->orderByDesc('csb.csb_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(shb.shb_pay_month,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_student_bond']);
    }

    private function ccDoubtfulList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_doubtful as cd')
            ->select(['cd.cdb_id', 'cd.cm_id', 'cd.cdb_doubtful_amt', 'cd.createddate', 'cd.cdb_status'])
            ->orderByDesc('cd.cdb_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(cd.cm_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_doubtful_list']);
    }

    private function ccScheduleBoc(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('ccontroller_schedule_boc as shb')
            ->select(['shb.shb_id', 'shb.cm_id', 'shb.shb_pay_month', 'shb.shb_bal_end_mth', 'shb.shb_status'])
            ->orderByDesc('shb.shb_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(shb.shb_pay_month,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_schedule_boc']);
    }

    private function ccDebtType(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('cc_debt_type as cdt')
            ->select(['cdt.cdt_id', 'cdt.cdt_type', 'cdt.cdt_type', 'cdt.cdt_status'])
            ->orderBy('cdt.cdt_type');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cdt.cdt_type,''), IFNULL(cdt.cdt_type,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cc_debt_type']);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1684 — Purchasing
     * ══════════════════════════════════════════════════════════════════ */
    private function purchasingPrList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('requisition_master as rm')
            ->select(['rm.rqm_requisition_id', 'rm.rqm_requisition_no', 'rm.rqm_request_date', 'rm.oun_code', 'rm.rqm_amount', 'rm.rqm_status'])
            ->orderByDesc('rm.rqm_requisition_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', IFNULL(rm.rqm_requisition_no,\'\'), IFNULL(rm.oun_code,\'\'))) LIKE ?',
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_pr_list']);
    }

    /**
     * Purchasing / Purchase Requisition / Purchase Requisition List (menu 1773).
     *
     * Registry `dtKey` columns: rqm_requisition_no, rqm_requisition_title, rqm_amount,
     * rqm_agg_no, rqm_status, updateddate —
     * must match SELECT aliases so CamelCase outbound keys bind in the SPA grid.
     */
    private function purchasingPurchaseRequisitionList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('requisition_master as rm')
            ->select([
                'rm.rqm_requisition_id',
                'rm.rqm_requisition_no',
                'rm.rqm_requisition_title',
                'rm.rqm_amount',
                'rm.rqm_agg_no',
                'rm.rqm_status',
                'rm.updateddate',
            ])
            ->orderByDesc('rm.rqm_requisition_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', IFNULL(rm.rqm_requisition_no,\'\'), IFNULL(rm.rqm_requisition_title,\'\'), IFNULL(rm.rqm_status,\'\'), IFNULL(rm.rqm_agg_no,\'\'))) LIKE ?',
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_purchase_requisition_list']);
    }

    /**
     * Purchasing / Purchase Requisition / List of Purchase Requisition Cancellation (menu 2320).
     *
     * Shell registry `dtKey`: rqm_requisition_no, rqm_requisition_title, rqm_amount, org_code, oun_code,
     * fty_fund_type, ccr_costcentre, rqm_status, rqm_wflow_sts.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function purchasingPurchaseRequisitionCancellationList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('requisition_master as rm')
            ->select([
                'rm.rqm_requisition_id',
                'rm.rqm_requisition_no',
                'rm.rqm_requisition_title',
                'rm.rqm_amount',
                'rm.org_code',
                'rm.oun_code',
                'rm.fty_fund_type',
                'rm.ccr_costcentre',
                'rm.rqm_status',
                'rm.rqm_wflow_sts',
            ])
            ->orderByDesc('rm.rqm_requisition_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                ."IFNULL(rm.rqm_requisition_no,''), IFNULL(rm.rqm_requisition_title,''), "
                ."IFNULL(rm.rqm_status,''), IFNULL(rm.rqm_wflow_sts,''), IFNULL(rm.org_code,''), IFNULL(rm.oun_code,''), "
                ."IFNULL(rm.fty_fund_type,''), IFNULL(rm.ccr_costcentre,''), IFNULL(CAST(rm.rqm_amount AS CHAR),''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_pr_cancellation_list']);
    }

    /**
     * Purchasing / Purchase Requisition / List of PR To Be Cancel (menu 3038).
     *
     * Registry `dtKey`: rqm_requisition_id, rqm_requisition_no, rqm_request_by, fty_fund_type, ccr_costcentre,
     * at_activity_code, rqm_requisition_title, rqm_amount, rqm_status.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    private function purchasingListOfPrToBeCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('requisition_master as rm')
            ->select([
                'rm.rqm_requisition_id',
                'rm.rqm_requisition_no',
                'rm.rqm_request_by',
                'rm.fty_fund_type',
                'rm.ccr_costcentre',
                'rm.at_activity_code',
                'rm.rqm_requisition_title',
                'rm.rqm_amount',
                'rm.rqm_status',
            ])
            ->orderByDesc('rm.rqm_requisition_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                ."IFNULL(rm.rqm_requisition_no,''), IFNULL(rm.rqm_request_by,''), IFNULL(rm.rqm_requisition_title,''), "
                ."IFNULL(rm.rqm_status,''), IFNULL(rm.fty_fund_type,''), IFNULL(rm.ccr_costcentre,''), "
                ."IFNULL(rm.at_activity_code,''), IFNULL(CAST(rm.rqm_amount AS CHAR),''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_pr_to_be_cancel_list']);
    }

    /**
     * Purchasing / List of PR To Be Cancel — detail grid (menu 3038 DT1).
     *
     * Registry dtKeys: nogn, grnstatus, nowpm, wpnstatus, nopor, postatus, nobill, statusbill
     * Join path: purchase_order_details.rqm_requisition_no → PO → goods_receive_master (driver),
     * correlated subqueries for first WPN per PO and first bill per GRN.
     *
     * @return list<array<string, mixed>>
     */
    public function purchasingPrToCancelDetailRows(string $rqmRequisitionNo): array
    {
        $rqmRequisitionNo = trim($rqmRequisitionNo);
        if ($rqmRequisitionNo === '') {
            return [];
        }

        $cx = $this->conn();

        $pomNos = $cx->table('purchase_order_details as pod')
            ->join('purchase_order_master as pom', 'pod.pom_order_id', '=', 'pom.pom_order_id')
            ->where('pod.rqm_requisition_no', $rqmRequisitionNo)
            ->distinct()
            ->pluck('pom.pom_order_no');

        if ($pomNos->isEmpty()) {
            return [];
        }

        $wpnNoSql = '(SELECT w2.wpm_progress_no FROM work_progress_master w2 WHERE w2.pom_order_no = pom.pom_order_no ORDER BY w2.wpm_progress_id ASC LIMIT 1)';
        $wpnStsSql = '(SELECT w2.wpm_status FROM work_progress_master w2 WHERE w2.pom_order_no = pom.pom_order_no ORDER BY w2.wpm_progress_id ASC LIMIT 1)';
        $billNoSql = '(SELECT b2.bim_bills_no FROM bills_master b2 WHERE b2.grm_receive_no = gm.grm_receive_no ORDER BY b2.bim_bills_id ASC LIMIT 1)';
        $billStsSql = '(SELECT b2.bim_status FROM bills_master b2 WHERE b2.grm_receive_no = gm.grm_receive_no ORDER BY b2.bim_bills_id ASC LIMIT 1)';
        $billNoPomSql = '(SELECT b2.bim_bills_no FROM bills_master b2 WHERE b2.pom_order_no = pom.pom_order_no ORDER BY b2.bim_bills_id ASC LIMIT 1)';
        $billStsPomSql = '(SELECT b2.bim_status FROM bills_master b2 WHERE b2.pom_order_no = pom.pom_order_no ORDER BY b2.bim_bills_id ASC LIMIT 1)';

        $gr = $cx->table('goods_receive_master as gm')
            ->join('purchase_order_master as pom', 'gm.pom_order_no', '=', 'pom.pom_order_no')
            ->whereIn('gm.pom_order_no', $pomNos)
            ->select([
                'gm.grm_receive_no as nogn',
                'gm.grm_status as grnstatus',
                DB::raw($wpnNoSql.' as nowpm'),
                DB::raw($wpnStsSql.' as wpnstatus'),
                'pom.pom_order_no as nopor',
                'pom.pom_order_status as postatus',
                DB::raw($billNoSql.' as nobill'),
                DB::raw($billStsSql.' as statusbill'),
            ])
            ->orderBy('gm.grm_receive_id');

        $payload = array_map(static fn (\stdClass $row) => (array) $row, $gr->get()->all());

        if ($payload !== []) {
            return $payload;
        }

        // PO exists for this PR but no GRN rows yet — one row per PO with PO-level WPN/bill hints.
        $fallback = $cx->table('purchase_order_master as pom')
            ->join('purchase_order_details as pod', 'pod.pom_order_id', '=', 'pom.pom_order_id')
            ->where('pod.rqm_requisition_no', $rqmRequisitionNo)
            ->groupBy('pom.pom_order_id', 'pom.pom_order_no', 'pom.pom_order_status')
            ->select([
                DB::raw('NULL as nogn'),
                DB::raw('NULL as grnstatus'),
                DB::raw($wpnNoSql.' as nowpm'),
                DB::raw($wpnStsSql.' as wpnstatus'),
                'pom.pom_order_no as nopor',
                'pom.pom_order_status as postatus',
                DB::raw($billNoPomSql.' as nobill'),
                DB::raw($billStsPomSql.' as statusbill'),
            ])
            ->orderBy('pom.pom_order_no');

        return array_map(static fn (\stdClass $row) => (array) $row, $fallback->get()->all());
    }

    /**
     * Shared query for Purchasing PO Kerisi grids (menus 1833, 2030, 2039).
     *
     * @param  'all'|'eligible_cancel'|'cancellation_log'  $scenario
     */
    private function purchaseOrderKerisiBase(Request $request, string $q, string $scenario): Builder
    {
        $subOu = $this->conn()->table('purchase_order_details')
            ->select([
                'pom_order_id',
                DB::raw('MIN(IFNULL(TRIM(`oun_code`), \'\')) AS oun_from_pod'),
            ])
            ->groupBy('pom_order_id');

        $base = $this->conn()->table('purchase_order_master AS pom')
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'pom.vcs_vendor_code')
            ->leftJoinSub($subOu, 'pou', function (JoinClause $join) {
                $join->on('pou.pom_order_id', '=', 'pom.pom_order_id');
            })
            ->select([
                'pom.pom_order_id',
                'pom.pom_order_no',
                'pom.pom_description',
                DB::raw("IFNULL(TRIM(`pou`.`oun_from_pod`),'') AS `oun_code`"),
                'pom.vcs_vendor_code',
                'vc.vcs_vendor_name',
                DB::raw('pom.pom_order_amt_rm AS pom_order_amt_rm'),
                DB::raw('pom.pom_order_amt_rm AS pom_order_amt'),
                'pom.pom_order_status',
                DB::raw("IFNULL(TRIM(`pom`.`pom_requisition_no`),'') AS `prlno`"),
                'pom.pom_wflow_sts',
                'pom.pom_cancel_remark',
                'pom.updateddate AS createddate',
            ])
            ->orderByDesc('pom.pom_order_id');

        if ($scenario === 'eligible_cancel') {
            $base->whereRaw("UPPER(TRIM(IFNULL(pom.pom_order_status,''))) = 'APPROVE'")
                ->where(function ($w) {
                    $w->whereNull('pom.pom_cancel_remark')
                        ->orWhereRaw("TRIM(IFNULL(pom.pom_cancel_remark,'')) = ''");
                });
        } elseif ($scenario === 'cancellation_log') {
            $base->where(function ($w) {
                $w->whereRaw("UPPER(TRIM(IFNULL(pom.pom_order_status,''))) = 'CANCEL'")
                    ->orWhereRaw("IFNULL(TRIM(pom.pom_cancel_remark),'') <> ''")
                    ->orWhereNotNull('pom.pom_cancel_date');
            });
        }

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pom.pom_order_no,''), IFNULL(pom.pom_description,''), IFNULL(pom.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(pom.pom_order_status,''), IFNULL(pom.pom_requisition_no,''), IFNULL(pom.pom_cancel_remark,''), IFNULL(pom.pom_wflow_sts,''), IFNULL(`pou`.`oun_from_pod`,''))) LIKE ?",
                [$like]
            );
        }

        return $base;
    }

    /** Purchasing / Purchase Order List (menu 1833). */
    private function purchasingPurchaseOrderMenu1833(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->purchaseOrderKerisiBase($r, $q, 'all');

        /** @phpstan-ignore-next-line Laravel sum casts */
        $grand = round((float) (clone $base)->sum('pom.pom_order_amt_rm'), 2);

        $out = array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_purchase_order_list_1833']);
        $out['grand_total_pom_order_amt_rm'] = $grand;

        return $out;
    }

    /** Purchasing / New PO Cancellation (menu 2030). */
    private function purchasingPoNewCancellation2030(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->purchaseOrderKerisiBase($r, $q, 'eligible_cancel');

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_po_new_cancel_2030']);
    }

    /** Purchasing / List of Purchase Order Cancellation (menu 2039). */
    private function purchasingPoCancellationStatus2039(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->purchaseOrderKerisiBase($r, $q, 'cancellation_log');

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_po_cancel_status_2039']);
    }

    /** Purchasing / PO Report Print / Bendahari (menu 1939). */
    private function purchasingPoReportPrint1939(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->purchaseOrderPoReportPrintBase($r, $q);

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_po_report_print_bendahari_1939',
        ]);
    }

    /** Purchasing / PO Report Print / PTJ (menu 1941). */
    private function purchasingPoReportPrint1941(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->purchaseOrderPoReportPrintBase($r, $q);

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_po_report_print_ptj_1941',
        ]);
    }

    /**
     * Approved PO listing for PO Report Print (menus 1939, 1941). Registry dtKeys: pom_*, vcs_*, APPROVE_UPDATEDATE → approve_updatedate.
     */
    private function purchaseOrderPoReportPrintBase(Request $r, string $q): Builder
    {
        $base = $this->conn()->table('purchase_order_master AS pom')
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'pom.vcs_vendor_code')
            ->select([
                'pom.pom_order_id',
                'pom.pom_order_no',
                'pom.pom_description',
                DB::raw('pom.pom_order_amt_rm AS pom_order_amt'),
                'pom.vcs_vendor_code',
                'vc.vcs_vendor_name',
                'pom.pom_order_status',
                DB::raw('pom.updateddate AS approve_updatedate'),
            ])
            ->whereRaw("UPPER(TRIM(IFNULL(pom.pom_order_status,''))) = 'APPROVE'")
            ->orderByDesc('pom.pom_order_id');

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pom.pom_order_no,''), IFNULL(pom.pom_description,''), IFNULL(pom.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(pom.pom_order_status,''))) LIKE ?",
                [$like]
            );
        }

        return $base;
    }

    /**
     * Purchasing / Work Progress Note List (menu 1840) — legacy MM_API_PURCHASING_WORKPROGRESSNOTELIST.
     */
    private function purchasingWorkProgressNoteList1840(Request $r, int $page, int $limit, string $q): array
    {
        $taxSum = $this->conn()->table('work_progress_details')
            ->select([
                'wpm_progress_id',
                DB::raw('COALESCE(SUM(IFNULL(wpd_taxamt, 0)), 0) AS sum_wpd_taxamt'),
            ])
            ->groupBy('wpm_progress_id');

        $vamSql = '(SELECT v2.vam_status FROM vendor_assessment_master AS v2 WHERE v2.vcs_vendor_code = wpm.vcs_vendor_code ORDER BY v2.vam_assessment_id DESC LIMIT 1)';

        $base = $this->conn()->table('work_progress_master AS wpm')
            ->leftJoin('purchase_order_master AS pom', 'pom.pom_order_no', '=', 'wpm.pom_order_no')
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'wpm.vcs_vendor_code')
            ->leftJoinSub($taxSum, 'tx', 'tx.wpm_progress_id', '=', 'wpm.wpm_progress_id')
            ->select([
                'wpm.wpm_progress_id',
                'wpm.wpm_progress_no',
                'wpm.pom_order_no',
                'wpm.vcs_vendor_code',
                'vc.vcs_vendor_name',
                DB::raw("IFNULL(NULLIF(TRIM(pom.pom_description), ''), '') AS pom_description"),
                DB::raw('IFNULL(tx.sum_wpd_taxamt, 0) AS sum_wpd_taxamt'),
                'wpm.wpm_total_amt',
                'wpm.wpm_status',
                DB::raw($vamSql.' AS vam_status'),
            ])
            ->orderByDesc('wpm.wpm_progress_id');

        $sf = trim((string) $r->input('sf_0', ''));
        if ($sf !== '') {
            $base->where('wpm.wpm_status', $sf);
        }

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(wpm.wpm_progress_no,''), IFNULL(wpm.pom_order_no,''), IFNULL(wpm.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(pom.pom_description,''), IFNULL(wpm.wpm_status,''))) LIKE ?",
                [$like]
            );
        }

        $statusOpts = $this->conn()->table('work_progress_master')
            ->selectRaw('DISTINCT TRIM(wpm_status) AS st')
            ->whereRaw("TRIM(IFNULL(wpm_status,'')) <> ''")
            ->orderBy('st')
            ->pluck('st')
            ->map(fn ($s) => ['value' => (string) $s, 'label' => (string) $s])
            ->values()
            ->all();

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_work_progress_note_list_1840',
            'smart_filter_options' => ['sf_0' => $statusOpts],
        ]);
    }

    /**
     * Purchasing / Good Receive Note List (menu 1839) — registry MM_API_PURCHASING_GOODRECEIVENOTELIST GRN columns.
     */
    private function purchasingGoodReceiveNoteList1839(Request $r, int $page, int $limit, string $q): array
    {
        $taxSum = $this->conn()->table('goods_receive_details')
            ->select([
                'grm_receive_id',
                DB::raw('COALESCE(SUM(IFNULL(grd_taxamt, 0)), 0) AS sum_grd_taxamt'),
            ])
            ->groupBy('grm_receive_id');

        /** PTJ / OU from PO lines — same derivation as {@see purchaseOrderKerisiBase} (`pom_master` has no `oun_code`). */
        $subOu = $this->conn()->table('purchase_order_details')
            ->select([
                'pom_order_id',
                DB::raw('MIN(IFNULL(TRIM(`oun_code`), \'\')) AS oun_from_pod'),
            ])
            ->groupBy('pom_order_id');

        $billSql = '(SELECT b2.bim_bills_no FROM bills_master AS b2 WHERE b2.grm_receive_no = grm.grm_receive_no ORDER BY b2.bim_bills_id ASC LIMIT 1)';
        $vamSql = '(SELECT v2.vam_status FROM vendor_assessment_master AS v2 WHERE v2.vcs_vendor_code = grm.vcs_vendor_code ORDER BY v2.vam_assessment_id DESC LIMIT 1)';

        $base = $this->conn()->table('goods_receive_master AS grm')
            ->leftJoin('purchase_order_master AS pom', 'pom.pom_order_no', '=', 'grm.pom_order_no')
            ->leftJoinSub($subOu, 'pou', function (JoinClause $join) {
                $join->on('pou.pom_order_id', '=', 'pom.pom_order_id');
            })
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'grm.vcs_vendor_code')
            ->leftJoinSub($taxSum, 'tx', 'tx.grm_receive_id', '=', 'grm.grm_receive_id')
            ->select([
                'grm.grm_receive_id',
                'grm.grm_receive_no',
                'grm.pom_order_no',
                'grm.vcs_vendor_code',
                'vc.vcs_vendor_name',
                DB::raw("IFNULL(NULLIF(TRIM(pom.pom_description), ''), '') AS pom_description"),
                DB::raw('IFNULL(tx.sum_grd_taxamt, 0) AS sum_grd_taxamt'),
                DB::raw('CAST(IFNULL(grm.grm_total_amt, 0) AS DECIMAL(15, 2)) AS grm_total_amt'),
                'grm.grm_status',
                DB::raw($billSql.' AS bim_bills_no'),
                DB::raw($vamSql.' AS vam_status'),
                DB::raw('grm.grm_receive_date AS createddate'),
            ])
            ->orderByDesc('grm.grm_receive_id');

        $sfPtj = trim((string) $r->input('sf_0', ''));
        if ($sfPtj !== '') {
            $base->whereRaw('IFNULL(TRIM(`pou`.`oun_from_pod`), \'\') = ?', [$sfPtj]);
        }

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(grm.grm_receive_no,''), IFNULL(grm.pom_order_no,''), IFNULL(grm.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(pom.pom_description,''), IFNULL(grm.grm_status,''))) LIKE ?",
                [$like]
            );
        }

        $ptjOpts = $this->conn()->table('purchase_order_details')
            ->selectRaw('DISTINCT TRIM(IFNULL(oun_code, \'\')) AS ou')
            ->whereRaw("TRIM(IFNULL(oun_code,'')) <> ''")
            ->orderBy('ou')
            ->pluck('ou')
            ->map(fn ($s) => ['value' => (string) $s, 'label' => (string) $s])
            ->values()
            ->all();

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_good_receive_note_list_1839',
            'smart_filter_options' => ['sf_0' => $ptjOpts],
        ]);
    }

    /**
     * Purchasing / Good Receive Note Cancel (menu 2085) — legacy SNA_API_PURCHASING_GRN_CANCEL list keys (NOGRN → no_grn, etc.).
     */
    private function purchasingGrnCancel2085(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('goods_receive_master AS grm')
            ->leftJoin('purchase_order_master AS pom', 'pom.pom_order_no', '=', 'grm.pom_order_no')
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'grm.vcs_vendor_code')
            ->whereIn('grm.grm_status', ['ENDORSE', 'APPROVE'])
            ->whereRaw(
                'grm.grm_receive_no NOT IN (SELECT IFNULL(grm_receive_no, ?) FROM bills_master WHERE bim_status = ?)',
                ['', 'APPROVE']
            )
            ->select([
                'grm.grm_receive_id',
                DB::raw('grm.grm_receive_no AS no_grn'),
                DB::raw('grm.pom_order_no AS purchase_no'),
                DB::raw('grm.vcs_vendor_code AS kod_vendor'),
                DB::raw("IFNULL(vc.vcs_vendor_name, '') AS nama_vendor"),
                DB::raw("IFNULL(NULLIF(TRIM(pom.pom_description), ''), '') AS keterangan_po"),
                DB::raw('DATE(grm.grm_receive_date) AS tarikh_grn'),
                DB::raw('CAST(IFNULL(grm.grm_total_amt, 0) AS DECIMAL(15, 2)) AS amaun'),
                DB::raw("CONCAT(grm.grm_receive_id, '_', grm.grm_receive_no) AS cbox"),
                DB::raw("CONCAT('/admin/kerisi/m/1858?grm_receive_id=', grm.grm_receive_id) AS url_view"),
            ])
            ->orderByDesc('grm.grm_receive_id');

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(grm.grm_receive_no,''), IFNULL(grm.pom_order_no,''), IFNULL(grm.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(pom.pom_description,''), IFNULL(grm.grm_receive_date,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_grn_cancel_2085']);
    }

    /**
     * Base query: GRNs split by whether `vendor_assessment_master` has a row for the vendor (registry dt_without/dt_with).
     */
    private function grnVendorAssessmentQuery(Request $r, bool $withAssessment, string $q): Builder
    {
        unset($r);
        $billSql = '(SELECT b2.bim_bills_no FROM bills_master AS b2 WHERE b2.grm_receive_no = grm.grm_receive_no ORDER BY b2.bim_bills_id ASC LIMIT 1)';
        $vamSql = '(SELECT v2.vam_status FROM vendor_assessment_master AS v2 WHERE v2.vcs_vendor_code = grm.vcs_vendor_code ORDER BY v2.vam_assessment_id DESC LIMIT 1)';

        $base = $this->conn()->table('goods_receive_master AS grm')
            ->leftJoin('purchase_order_master AS pom', 'pom.pom_order_no', '=', 'grm.pom_order_no')
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'grm.vcs_vendor_code')
            ->select([
                'grm.grm_receive_id',
                'grm.grm_receive_no',
                'grm.pom_order_no',
                'grm.vcs_vendor_code',
                'vc.vcs_vendor_name',
                DB::raw("IFNULL(NULLIF(TRIM(pom.pom_description), ''), '') AS pom_description"),
                DB::raw('CAST(IFNULL(grm.grm_total_amt, 0) AS DECIMAL(15, 2)) AS grm_total_amt'),
                'grm.grm_status',
                DB::raw($billSql.' AS bim_bills_no'),
                DB::raw($vamSql.' AS vam_status'),
            ]);

        if ($withAssessment) {
            $base->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('vendor_assessment_master AS vam')
                    ->whereColumn('vam.vcs_vendor_code', 'grm.vcs_vendor_code');
            });
        } else {
            $base->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('vendor_assessment_master AS vam')
                    ->whereColumn('vam.vcs_vendor_code', 'grm.vcs_vendor_code');
            });
        }

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(grm.grm_receive_no,''), IFNULL(grm.pom_order_no,''), IFNULL(grm.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(pom.pom_description,''))) LIKE ?",
                [$like]
            );
        }

        return $base->orderByDesc('grm.grm_receive_id');
    }

    /**
     * Purchasing / Vendor Assessment / Good Receive Note (menu 2624) — two grids: without / with vendor assessment.
     */
    private function purchasingGrnVendorAssessment2624(Request $r, int $page, int $limit, string $q): array
    {
        $without = $this->paginate($this->grnVendorAssessmentQuery($r, false, $q), $page, $limit);
        $with = $this->paginate($this->grnVendorAssessmentQuery($r, true, $q), $page, $limit);

        return array_merge($without, [
            'extra_datatable_rows' => [$with['rows']],
            'secondary_total' => $with['total'],
            'connector' => 'purchasing_grn_vendor_assessment_2624',
        ]);
    }

    /**
     * Base query: WPNs split by vendor assessment presence (registry 2626).
     */
    private function wpnVendorAssessmentQuery(Request $r, bool $withAssessment, string $q): Builder
    {
        unset($r);
        $vamSql = '(SELECT v2.vam_status FROM vendor_assessment_master AS v2 WHERE v2.vcs_vendor_code = wpm.vcs_vendor_code ORDER BY v2.vam_assessment_id DESC LIMIT 1)';

        $base = $this->conn()->table('work_progress_master AS wpm')
            ->leftJoin('purchase_order_master AS pom', 'pom.pom_order_no', '=', 'wpm.pom_order_no')
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'wpm.vcs_vendor_code')
            ->select([
                'wpm.wpm_progress_id',
                'wpm.wpm_progress_no',
                'wpm.pom_order_no',
                'wpm.vcs_vendor_code',
                'vc.vcs_vendor_name',
                DB::raw("IFNULL(NULLIF(TRIM(pom.pom_description), ''), '') AS pom_description"),
                DB::raw('CAST(IFNULL(wpm.wpm_total_amt, IFNULL(wpm.wpm_total_amt_rm, 0)) AS DECIMAL(15, 2)) AS wpm_total_amt'),
                'wpm.wpm_status',
                DB::raw($vamSql.' AS vam_status'),
            ]);

        if ($withAssessment) {
            $base->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('vendor_assessment_master AS vam')
                    ->whereColumn('vam.vcs_vendor_code', 'wpm.vcs_vendor_code');
            });
        } else {
            $base->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('vendor_assessment_master AS vam')
                    ->whereColumn('vam.vcs_vendor_code', 'wpm.vcs_vendor_code');
            });
        }

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(wpm.wpm_progress_no,''), IFNULL(wpm.pom_order_no,''), IFNULL(wpm.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(pom.pom_description,''), IFNULL(wpm.wpm_status,''))) LIKE ?",
                [$like]
            );
        }

        return $base->orderByDesc('wpm.wpm_progress_id');
    }

    /**
     * Purchasing / Vendor Assessment / Work Progress Note (menu 2626) — two grids.
     */
    private function purchasingWpnVendorAssessment2626(Request $r, int $page, int $limit, string $q): array
    {
        $without = $this->paginate($this->wpnVendorAssessmentQuery($r, false, $q), $page, $limit);
        $with = $this->paginate($this->wpnVendorAssessmentQuery($r, true, $q), $page, $limit);

        return array_merge($without, [
            'extra_datatable_rows' => [$with['rows']],
            'secondary_total' => $with['total'],
            'connector' => 'purchasing_wpn_vendor_assessment_2626',
        ]);
    }

    /**
     * Purchasing / Work Progress Note Cancel List (menu 2082) — legacy `SNA_API_PURCHASING_WPN_CANCEL?dt_wpnCancel=1`.
     *
     * Same eligibility as cancel submit: ENDORSE/APPROVE, no approved bill with grm_receive_no = wpm_progress_no.
     */
    private function purchasingWorkProgressNoteCancel2082(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->wpnCancel2082BaseQuery()
            ->select([
                'wpm.wpm_progress_id',
                'wpm.wpm_progress_no',
                'wpm.pom_order_no',
                'wpm.wpm_receive_date',
                DB::raw('IFNULL(wpm.wpm_total_amt_rm, IFNULL(wpm.wpm_total_amt, 0)) AS wpm_total_amt_rm'),
                DB::raw("CONCAT(wpm.wpm_progress_id, '_', wpm.wpm_progress_no) AS cbox"),
            ])
            ->orderByDesc('wpm.wpm_progress_id');

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(wpm.wpm_progress_id,''),
                    IFNULL(wpm.wpm_progress_no,''),
                    IFNULL(wpm.pom_order_no,''),
                    IFNULL(wpm.wpm_receive_date,''),
                    IFNULL(wpm.wpm_total_amt,''),
                    IFNULL(wpm.wpm_total_amt_rm,''),
                    IFNULL(wpm.wpm_status,''),
                    CONCAT(IFNULL(wpm.wpm_progress_id,''), '_', IFNULL(wpm.wpm_progress_no,''))
                )) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_wpn_cancel_list_2082']);
    }

    /** Base query for menu 2082 list + cancel eligibility (legacy dt_wpnCancel NOT IN approved bill). */
    private function wpnCancel2082BaseQuery(): Builder
    {
        return $this->conn()->table('work_progress_master AS wpm')
            ->whereIn('wpm.wpm_status', ['ENDORSE', 'APPROVE'])
            ->whereRaw(
                'wpm.wpm_progress_no NOT IN (SELECT IFNULL(grm_receive_no, ?) FROM bills_master WHERE bim_status = ?)',
                ['', 'APPROVE']
            );
    }

    /**
     * Purchasing / Work Progress Note Detail (menu 1838) — header form options + two grids.
     *
     * Pass ?wpm_progress_id=… to load grids; otherwise grids are empty (legacy empty-POST parity).
     *
     * @return array<string, mixed>
     */
    private function purchasingWorkProgressNoteDetail1838(Request $r, int $page, int $limit, string $q): array
    {
        unset($page, $limit);
        $formOptions = $this->workProgressNoteFormOptions();
        $wpmId = (int) $r->input('wpm_progress_id', $r->input('wpmProgressId', 0));

        if ($wpmId < 1) {
            return [
                'rows' => [],
                'total' => 0,
                'connector' => 'purchasing_wpn_detail_1838',
                'form_options' => $formOptions,
                'extra_datatable_rows' => [[]],
            ];
        }

        $wpm = $this->conn()->table('work_progress_master')->where('wpm_progress_id', $wpmId)->first();
        if (! $wpm) {
            return [
                'rows' => [],
                'total' => 0,
                'connector' => 'purchasing_wpn_detail_1838',
                'form_options' => $formOptions,
                'extra_datatable_rows' => [[]],
                'shellError' => 'Work Progress Note not found.',
            ];
        }

        $pomNo = trim((string) ($wpm->pom_order_no ?? ''));
        $existing = $pomNo !== '' ? $this->workProgressNoteExistingPrPoRowsForPom($pomNo) : [];
        $qTrim = trim($q);
        if ($qTrim !== '') {
            $needle = mb_strtolower($qTrim, 'UTF-8');
            $existing = array_values(array_filter($existing, static function (array $row) use ($needle) {
                $hay = mb_strtolower(implode('|', array_map(static fn ($v) => (string) $v, $row)), 'UTF-8');

                return str_contains($hay, $needle);
            }));
        }

        $detailRows = $this->workProgressNoteWpnDetailRows($wpmId);

        return [
            'rows' => $existing,
            'total' => count($existing),
            'connector' => 'purchasing_wpn_detail_1838',
            'form_options' => $formOptions,
            'extra_datatable_rows' => [$detailRows],
            'form_values' => [
                'wpm_progress_no' => (string) ($wpm->wpm_progress_no ?? ''),
                'wpm_type' => (string) ($wpm->wpm_type ?? ''),
                'pom_order_no' => $pomNo,
                'vcs_vendor_code' => (string) ($wpm->vcs_vendor_code ?? ''),
                'vcs_vendor_name' => $this->vendorNameForCode((string) ($wpm->vcs_vendor_code ?? '')),
                'pom_description' => $this->pomDescriptionForOrder($pomNo),
                'wpm_currency_code' => (string) ($wpm->wpm_currency_code ?? ''),
                'wpm_receive_date' => $wpm->wpm_receive_date ?? null,
                'wpm_reference_doc' => (string) ($wpm->wpm_reference_doc ?? ''),
                'wpm_status' => (string) ($wpm->wpm_status ?? ''),
                'wpm_cancel_remark' => (string) ($wpm->wpm_cancel_remark ?? ''),
            ],
        ];
    }

    /**
     * Lookup + static options for WPN Information form (menu 1838).
     *
     * @return array<string, mixed>
     */
    private function workProgressNoteFormOptions(): array
    {
        $cx = $this->conn();
        $po = $cx->table('purchase_order_master')
            ->orderByDesc('pom_order_id')
            ->limit(800)
            ->pluck('pom_order_no')
            ->filter(static fn ($n) => trim((string) $n) !== '')
            ->unique()
            ->map(static fn ($n) => ['value' => (string) $n, 'label' => (string) $n])
            ->values()
            ->all();

        $vendors = $cx->table('vend_customer_supplier')
            ->orderBy('vcs_vendor_code')
            ->limit(3000)
            ->get(['vcs_vendor_code', 'vcs_vendor_name'])
            ->map(static function ($r) {
                $c = trim((string) ($r->vcs_vendor_code ?? ''));
                $n = trim((string) ($r->vcs_vendor_name ?? ''));

                return ['value' => $c, 'label' => $c !== '' && $n !== '' ? $c.' — '.$n : $c];
            })
            ->filter(static fn ($o) => $o['value'] !== '')
            ->values()
            ->all();

        $currencies = $cx->table('lookup_details')
            ->where('lma_code_name', 'CURRENCY')
            ->where('lde_status', '1')
            ->orderBy('lde_sorting')
            ->orderBy('lde_value')
            ->get(['lde_value'])
            ->map(static fn ($r) => ['value' => (string) ($r->lde_value ?? ''), 'label' => (string) ($r->lde_value ?? '')])
            ->filter(static fn ($o) => $o['value'] !== '')
            ->values()
            ->all();

        if ($currencies === []) {
            $currencies = $cx->table('work_progress_master')
                ->selectRaw('DISTINCT TRIM(wpm_currency_code) AS cu')
                ->whereRaw("TRIM(IFNULL(wpm_currency_code, '')) <> ''")
                ->orderBy('cu')
                ->pluck('cu')
                ->map(static fn ($c) => ['value' => (string) $c, 'label' => (string) $c])
                ->values()
                ->all();
        }

        return [
            'wpn_type' => [
                ['value' => 'PR', 'label' => 'PR'],
                ['value' => 'PO', 'label' => 'PO'],
            ],
            'po_pr_no' => $po,
            'vendor' => $vendors,
            'currency' => $currencies,
        ];
    }

    private function vendorNameForCode(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return '';
        }
        $name = $this->conn()->table('vend_customer_supplier')
            ->where('vcs_vendor_code', $code)
            ->value('vcs_vendor_name');

        return trim((string) ($name ?? ''));
    }

    private function pomDescriptionForOrder(string $pomOrderNo): string
    {
        $pomOrderNo = trim($pomOrderNo);
        if ($pomOrderNo === '') {
            return '';
        }
        $d = $this->conn()->table('purchase_order_master')
            ->where('pom_order_no', $pomOrderNo)
            ->value('pom_description');

        return trim((string) ($d ?? ''));
    }

    /**
     * Rows for "List of Existing PR / PO in WPN / GRN" (menu 1838 DT0).
     *
     * @return list<array<string, mixed>>
     */
    private function workProgressNoteExistingPrPoRowsForPom(string $pomOrderNo): array
    {
        $pomOrderNo = trim($pomOrderNo);
        if ($pomOrderNo === '') {
            return [];
        }
        $cx = $this->conn();
        $pom = $cx->table('purchase_order_master')->where('pom_order_no', $pomOrderNo)->first();
        if (! $pom) {
            return [];
        }
        $rqmNo = trim((string) ($pom->pom_requisition_no ?? ''));
        if ($rqmNo === '') {
            return [];
        }

        $grn = $cx->table('goods_receive_master as grm')
            ->join('purchase_order_master as pom', 'pom.pom_order_no', '=', 'grm.pom_order_no')
            ->where('pom.pom_requisition_no', $rqmNo)
            ->selectRaw('grm.grm_receive_no AS application_no')
            ->selectRaw('grm.pom_order_no AS pom_order_no')
            ->selectRaw('COALESCE(grm.vcs_vendor_code, pom.vcs_vendor_code) AS vcs_vendor_code')
            ->selectRaw('pom.pom_description AS pom_description')
            ->selectRaw('grm.grm_total_amt AS grm_total_amt')
            ->selectRaw('(SELECT SUM(grd.grd_receive_amt) FROM goods_receive_details grd WHERE grd.grm_receive_id = grm.grm_receive_id) AS grd_receive_amt')
            ->selectRaw('grm.grm_status AS application_status')
            ->selectRaw("'GRN' AS from_table")
            ->get();

        $wpn = $cx->table('work_progress_master as wpm')
            ->join('purchase_order_master as pom', 'pom.pom_order_no', '=', 'wpm.pom_order_no')
            ->where('pom.pom_requisition_no', $rqmNo)
            ->selectRaw('wpm.wpm_progress_no AS application_no')
            ->selectRaw('wpm.pom_order_no AS pom_order_no')
            ->selectRaw('COALESCE(wpm.vcs_vendor_code, pom.vcs_vendor_code) AS vcs_vendor_code')
            ->selectRaw('pom.pom_description AS pom_description')
            ->selectRaw('wpm.wpm_total_amt AS grm_total_amt')
            ->selectRaw('COALESCE(wpm.wpm_receive_amt_rm, wpm.wpm_total_amt_rm) AS grd_receive_amt')
            ->selectRaw('wpm.wpm_status AS application_status')
            ->selectRaw("'WPN' AS from_table")
            ->get();

        $bill = $cx->table('bills_master as bim')
            ->join('purchase_order_master as pom', 'pom.pom_order_no', '=', 'bim.pom_order_no')
            ->where('pom.pom_requisition_no', $rqmNo)
            ->selectRaw('bim.bim_bills_no AS application_no')
            ->selectRaw('bim.pom_order_no AS pom_order_no')
            ->selectRaw('COALESCE(bim.vcs_vendor_code, pom.vcs_vendor_code) AS vcs_vendor_code')
            ->selectRaw("COALESCE(NULLIF(TRIM(bim.bim_bills_desc), ''), pom.pom_description) AS pom_description")
            ->selectRaw('bim.bim_bill_amt AS grm_total_amt')
            ->selectRaw('bim.bim_ent_amt AS grd_receive_amt')
            ->selectRaw('bim.bim_status AS application_status')
            ->selectRaw("'BILL' AS from_table")
            ->get();

        $out = [];
        foreach (array_merge($grn->all(), $wpn->all(), $bill->all()) as $row) {
            $a = (array) $row;
            if (trim((string) ($a['application_no'] ?? '')) === '') {
                continue;
            }
            $out[] = [
                'application_no' => $a['application_no'],
                'pom_order_no' => $a['pom_order_no'],
                'vcs_vendor_code' => $a['vcs_vendor_code'],
                'pom_description' => $a['pom_description'],
                'grm_total_amt' => $a['grm_total_amt'],
                'grd_receive_amt' => $a['grd_receive_amt'],
                'application_status' => $a['application_status'],
                'from_table' => $a['from_table'],
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function workProgressNoteWpnDetailRows(int $wpmProgressId): array
    {
        $cx = $this->conn();
        $rows = $cx->table('work_progress_details AS wpd')
            ->leftJoin('purchase_order_master AS pomd', 'pomd.pom_order_no', '=', 'wpd.pom_order_no')
            ->leftJoin('purchase_order_details AS pod', function ($j) {
                $j->whereColumn('pod.pom_order_id', 'pomd.pom_order_id')
                    ->whereColumn('pod.pod_line_no', 'wpd.pod_line_no');
            })
            ->leftJoin('item_main AS im', 'im.itm_item_code', '=', 'pod.itm_item_code')
            ->where('wpd.wpm_progress_id', $wpmProgressId)
            ->orderBy('wpd.wpd_line_no')
            ->select([
                'wpd.wpd_wp_details_id',
                'wpd.wpd_line_no',
                DB::raw('IFNULL(NULLIF(TRIM(pod.itm_item_code), \'\'), IFNULL(im.itm_item_code, \'\')) AS itm_item_code'),
                DB::raw('COALESCE(NULLIF(TRIM(pod.pod_item_spec), \'\'), NULLIF(TRIM(wpd.wpd_item_desc), \'\')) AS pod_item_spec'),
                'wpd.acm_acct_code',
                DB::raw('IFNULL(pod.bdg_budget_code, \'\') AS bdg_budget_code'),
                'wpd.wpd_order_qty',
                'wpd.wpd_unit_price',
                'wpd.wpd_order_amt',
                'wpd.wpd_taxcode',
                'wpd.wpd_taxpct',
                'wpd.wpd_taxamt',
                'wpd.wpd_receive_amt',
            ])
            ->get();

        $list = [];
        foreach ($rows as $r) {
            $list[] = (array) $r;
        }

        return $list;
    }

    private function purchasingGrnList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetGrn($r, $page, $limit, $q);
    }

    private function purchasingGrnForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->assetGrn($r, $page, $limit, $q);
    }

    private function purchasingTenderList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tender_master as tm')
            ->select(['tm.tdm_tender_id', 'tm.tdm_tender_no', 'tm.createddate', 'tm.tdm_title', 'tm.tdm_status'])
            ->orderByDesc('tm.tdm_tender_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(tm.tender_no,''), IFNULL(tm.tender_title,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_tender_list']);
    }

    /**
     * Purchasing / Advertisement / New Tender/Quotation (menu 2618).
     *
     * The legacy form is mostly client-side until Save; this endpoint supplies the
     * live dropdown lists and existing child rows when a `tdm_tender_id` is opened.
     */
    private function purchasingAdvertisementRequest2618(Request $r, int $page, int $limit, string $q): array
    {
        unset($page, $limit, $q);

        $tenderId = (int) $r->input('tdm_tender_id', $r->input('tdmTenderId', $r->input('id', 0)));
        $jobscopeRows = [];
        $tarafRows = [];
        $flowRows = [];
        $formValues = [
            'tdm_tender_no' => 'Auto Assigned',
            'tdm_requestdate' => now()->toDateString(),
            'tdm_status' => 'DRAFT',
            'tdm_tender_type' => '',
        ];

        if ($tenderId > 0) {
            $master = $this->conn()->table('tender_master')->where('tdm_tender_id', $tenderId)->first();
            if ($master) {
                $formValues = array_merge($formValues, (array) $master);
            }

            $jobscopeRows = $this->advertisementJobscopeRows($tenderId);
            $tarafRows = $this->advertisementTarafRows($tenderId);
        }

        return [
            'rows' => $jobscopeRows,
            'total' => count($jobscopeRows),
            'connector' => 'purchasing_advertisement_request_2618',
            'form_options' => $this->advertisementRequestOptions(),
            'form_values' => $formValues,
            'extra_datatable_rows' => [$tarafRows, $flowRows],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function advertisementJobscopeRows(int $tenderId): array
    {
        return $this->conn()->table('tender_jobscope AS tj')
            ->leftJoin('jobscope AS js', 'js.jbs_jobscope_code', '=', 'tj.tjs_jobscope_code')
            ->leftJoin('jobscope_category AS jc', 'jc.jbc_category', '=', 'tj.tjs_jobscope_category')
            ->leftJoin('lkp_logic_jobscope AS lj', 'lj.llj_code', '=', 'tj.tjs_logic_code')
            ->where('tj.tdm_tender_id', $tenderId)
            ->orderBy('tj.tjs_id_ai')
            ->select([
                'tj.tjs_id_ai',
                'tj.tdm_tender_id',
                'tj.tjs_jobscope_code',
                DB::raw("IFNULL(js.jbs_job_name, '') AS tjs_jobscope_desc"),
                'tj.tjs_jobscope_category',
                DB::raw("IFNULL(jc.jbc_desc, '') AS tjs_jobscope_category_desc"),
                'tj.tjs_logic_code',
                DB::raw("IFNULL(lj.llj_desc, '') AS tjs_logic_desc"),
            ])
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function advertisementTarafRows(int $tenderId): array
    {
        return $this->conn()->table('tender_bumi_status AS tb')
            ->leftJoin('lookup_details AS ld', function (JoinClause $join) {
                $join->on('ld.lde_value', '=', 'tb.trf_bumi_status')
                    ->where('ld.lma_code_name', '=', 'TARAF_VENDOR');
            })
            ->where('tb.tdm_tender_id', $tenderId)
            ->orderBy('tb.trf_id_ai')
            ->select([
                'tb.trf_id_ai',
                'tb.tdm_tender_id',
                'tb.trf_bumi_status',
                DB::raw("IFNULL(ld.lde_description, '') AS trf_bumi_status_desc"),
            ])
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function advertisementRequestOptions(): array
    {
        $cx = $this->conn();

        $option = static fn ($value, $label): array => [
            'value' => (string) $value,
            'label' => (string) $label,
        ];

        $requisitions = $cx->table('requisition_master')
            ->orderByDesc('rqm_requisition_id')
            ->limit(500)
            ->get(['rqm_requisition_no', 'rqm_requisition_title'])
            ->filter(fn ($r) => trim((string) ($r->rqm_requisition_no ?? '')) !== '')
            ->map(fn ($r) => $option(
                $r->rqm_requisition_no,
                trim((string) ($r->rqm_requisition_title ?? '')) !== ''
                    ? $r->rqm_requisition_no.' - '.$r->rqm_requisition_title
                    : $r->rqm_requisition_no
            ))
            ->values()
            ->all();

        $staff = $cx->table('staff')
            ->orderBy('stf_staff_name')
            ->limit(1000)
            ->get(['stf_staff_id', 'stf_staff_name'])
            ->map(fn ($r) => $option(
                $r->stf_staff_id,
                trim((string) ($r->stf_staff_name ?? '')) !== ''
                    ? $r->stf_staff_id.' - '.$r->stf_staff_name
                    : $r->stf_staff_id
            ))
            ->values()
            ->all();

        $tenderMethods = $cx->table('lookup_details')
            ->where('lma_code_name', 'TENDERTYPE')
            ->orderBy('lde_sorting')
            ->orderBy('lde_description')
            ->get(['lde_description'])
            ->filter(fn ($r) => trim((string) ($r->lde_description ?? '')) !== '')
            ->map(fn ($r) => $option($r->lde_description, $r->lde_description))
            ->values()
            ->all();

        $ptj = $cx->table('organization_unit')
            ->whereRaw("TRIM(IFNULL(oun_code,'')) <> ''")
            ->orderBy('oun_code')
            ->limit(2000)
            ->get(['oun_code', 'oun_desc'])
            ->map(fn ($r) => $option($r->oun_code, $r->oun_code.' - '.$r->oun_desc))
            ->values()
            ->all();

        $costCentres = $cx->table('costcentre')
            ->whereRaw("TRIM(IFNULL(ccr_costcentre,'')) <> ''")
            ->orderBy('ccr_costcentre')
            ->limit(3000)
            ->get(['ccr_costcentre', 'ccr_costcentre_desc', 'oun_code'])
            ->map(fn ($r) => [
                'value' => (string) $r->ccr_costcentre,
                'label' => (string) ($r->ccr_costcentre.' - '.$r->ccr_costcentre_desc),
                'ounCode' => (string) ($r->oun_code ?? ''),
            ])
            ->values()
            ->all();

        $fundTypes = $cx->table('fund_type')
            ->whereRaw("TRIM(IFNULL(fty_fund_type,'')) <> ''")
            ->orderBy('fty_fund_type')
            ->get(['fty_fund_type', 'fty_fund_desc'])
            ->map(fn ($r) => $option($r->fty_fund_type, $r->fty_fund_type.' - '.$r->fty_fund_desc))
            ->values()
            ->all();

        $activities = $cx->table('activity_type')
            ->whereRaw("TRIM(IFNULL(at_activity_code,'')) <> ''")
            ->orderBy('at_activity_code')
            ->limit(3000)
            ->get(['at_activity_code', 'at_activity_description_bm'])
            ->map(fn ($r) => $option($r->at_activity_code, $r->at_activity_code.' - '.$r->at_activity_description_bm))
            ->values()
            ->all();

        $soCodes = $cx->table('requisition_master')
            ->selectRaw('DISTINCT TRIM(IFNULL(so_code, \'\')) AS so_code')
            ->whereRaw("TRIM(IFNULL(so_code,'')) <> ''")
            ->orderBy('so_code')
            ->pluck('so_code')
            ->map(fn ($s) => $option($s, $s))
            ->values()
            ->all();

        $categories = $cx->table('jobscope_category')
            ->whereRaw("TRIM(IFNULL(jbc_category,'')) <> ''")
            ->orderBy('jbc_category')
            ->get(['jbc_category', 'jbc_desc'])
            ->map(fn ($r) => $option($r->jbc_category, strtoupper((string) $r->jbc_desc)))
            ->values()
            ->all();

        $jobscopes = $cx->table('jobscope')
            ->whereRaw("TRIM(IFNULL(jbs_jobscope_code,'')) <> ''")
            ->whereRaw("UPPER(TRIM(IFNULL(jbs_status,''))) NOT IN ('N','INACTIVE','DISABLE')")
            ->orderBy('jbs_jobscope_code')
            ->get(['jbs_jobscope_code', 'jbs_job_name', 'jbc_category'])
            ->map(fn ($r) => [
                'value' => (string) $r->jbs_jobscope_code,
                'label' => trim((string) ($r->jbs_job_name ?? '')) !== ''
                    ? $r->jbs_jobscope_code.' - '.$r->jbs_job_name
                    : (string) $r->jbs_jobscope_code,
                'category' => (string) ($r->jbc_category ?? ''),
            ])
            ->values()
            ->all();

        $logic = $cx->table('lkp_logic_jobscope')
            ->where('llj_status', '1')
            ->orderBy('llj_code')
            ->get(['llj_code', 'llj_desc'])
            ->map(fn ($r) => $option($r->llj_code, $r->llj_desc))
            ->values()
            ->all();

        $taraf = $cx->table('lookup_details')
            ->where('lma_code_name', 'TARAF_VENDOR')
            ->orderBy('lde_sorting')
            ->orderBy('lde_value')
            ->get(['lde_value', 'lde_description'])
            ->map(fn ($r) => $option(
                $r->lde_value,
                trim((string) ($r->lde_description ?? '')) !== ''
                    ? $r->lde_value.' - '.strtoupper((string) $r->lde_description)
                    : $r->lde_value
            ))
            ->values()
            ->all();

        return [
            'purchaseRequisitions' => $requisitions,
            'requestBy' => $staff,
            'staff' => $staff,
            'tenderMethods' => $tenderMethods,
            'tenderTypes' => [$option('TENDER', 'TENDER'), $option('QUOTATION', 'QUOTATION')],
            'ptj' => $ptj,
            'costCentres' => $costCentres,
            'fundTypes' => $fundTypes,
            'activities' => $activities,
            'soCodes' => $soCodes,
            'nextReceivers' => $staff,
            'jobscopeCategories' => $categories,
            'jobscopes' => $jobscopes,
            'jobscopeLogic' => $logic,
            'taraf' => $taraf,
        ];
    }

    /**
     * Purchasing / Setup / Item Main Listing (menu 1829).
     * Legacy: ZR_PURCHASING_ITEMMAINLISTING_API — item_main, LEFT JOIN lookup_details ON lde_value = itm_category_code,
     * itm_status = '1', top filters category / subcategory / accountCode, Category column from asset_depr_setup + STORE/OTHERS.
     */
    private function purchasingItemMainListing(Request $r, int $page, int $limit, string $q): array
    {
        $cat = trim((string) $r->input('tf_0', $r->input('category', '')));
        $sub = trim((string) $r->input('tf_1', $r->input('subcategory', '')));
        $acct = trim((string) $r->input('tf_2', $r->input('account_code', $r->input('accountCode', ''))));

        $categoryExpr = "(SELECT IFNULL((SELECT MAX(ads.ads_type) FROM asset_depr_setup ads WHERE ads.acm_acct_code = im.acm_acct_code), IF(ld.lde_group = 'STORE', ld.lde_group, 'OTHERS')))";

        $base = $this->conn()->table('item_main as im')
            ->leftJoin('lookup_details as ld', function ($j) {
                $j->whereColumn('ld.lde_value', 'im.itm_category_code');
            })
            ->where('im.itm_status', '1');

        if ($cat !== '') {
            $base->where('im.itm_category_code', $cat);
        }
        if ($sub !== '') {
            $base->where('im.isc_subcategory_code', $sub);
        }
        if ($acct !== '') {
            $base->where('im.acm_acct_code', $acct);
        }

        for ($i = 0; $i <= 4; $i++) {
            $v = trim((string) $r->input('sf_'.$i, ''));
            if ($v === '') {
                continue;
            }
            $like = $this->likeEscape(mb_strtolower($v, 'UTF-8'));
            match ($i) {
                0 => $base->whereRaw('LOWER(IFNULL(im.itm_item_code, \'\')) LIKE ?', [$like]),
                1 => $base->whereRaw('LOWER(IFNULL(im.itm_item_desc, \'\')) LIKE ?', [$like]),
                2 => $base->whereRaw('LOWER(IFNULL(ld.lde_description, \'\')) LIKE ?', [$like]),
                3 => $base->whereRaw('LOWER(IFNULL(im.iss_subsiri_code, \'\')) LIKE ?', [$like]),
                4 => $base->whereRaw('LOWER(IFNULL(CAST(im.itm_level AS CHAR), \'\')) LIKE ?', [$like]),
                default => null,
            };
        }

        $base->select([
            'im.itm_item_code',
            'im.itm_item_desc',
            'im.acm_acct_code',
            'im.itm_category_code',
            'ld.lde_description',
            'im.isc_subcategory_code',
            'im.iss_subsiri_code',
            'im.itm_level',
            DB::raw($categoryExpr.' AS `Category`'),
        ])
            ->distinct()
            ->orderBy('im.itm_item_code');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(im.itm_item_code,''),
                    IFNULL(im.itm_item_desc,''),
                    IFNULL(im.acm_acct_code,''),
                    IFNULL(im.itm_category_code,''),
                    IFNULL(ld.lde_description,''),
                    IFNULL(im.isc_subcategory_code,''),
                    IFNULL(im.iss_subsiri_code,''),
                    IFNULL({$categoryExpr},''),
                    IFNULL(CAST(im.itm_level AS CHAR),''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_item_main_listing',
            'top_filter_options' => $this->purchasingItemMainListingTopFilterOptions($r),
        ]);
    }

    /**
     * Top filter options from legacy autosuggest SQL (cascading: subcategory needs category; account needs both).
     */
    private function purchasingItemMainListingTopFilterOptions(Request $r): array
    {
        $c = $this->conn();

        $cat = trim((string) $r->input('tf_0', $r->input('category', '')));
        $sub = trim((string) $r->input('tf_1', $r->input('subcategory', '')));

        $catRows = $c->table('item_main as im')
            ->join('lookup_details as ld', function ($j) {
                $j->whereColumn('ld.lde_value', 'im.itm_category_code');
            })
            ->where('im.itm_status', '1')
            ->whereNotNull('im.itm_category_code')
            ->where('im.itm_category_code', '!=', '')
            ->selectRaw('DISTINCT im.itm_category_code AS code, ld.lde_description AS descr')
            ->orderBy('im.itm_category_code')
            ->get();

        $tf0 = [];
        foreach ($catRows as $row) {
            $code = (string) $row->code;
            $d = $row->descr !== null ? (string) $row->descr : '';
            $tf0[] = [
                'value' => $code,
                'label' => $d !== '' ? $code.' — '.$d : $code,
            ];
        }

        $tf1 = [];
        if ($cat !== '') {
            $subRows = $c->table('item_main as im')
                ->join('item_subcategory as isc', function ($j) {
                    $j->whereColumn('im.itm_item_code_parent', 'isc.isc_subcategory_code');
                })
                ->where('im.itm_status', '1')
                ->where('isc.isc_category_code', $cat)
                ->selectRaw('DISTINCT im.isc_subcategory_code AS code, isc.isc_subcategory_desc AS descr')
                ->orderBy('im.isc_subcategory_code')
                ->get();
            foreach ($subRows as $row) {
                $code = (string) $row->code;
                $d = $row->descr !== null ? (string) $row->descr : '';
                $tf1[] = [
                    'value' => $code,
                    'label' => $d !== '' ? $code.' — '.$d : $code,
                ];
            }
        }

        $tf2 = [];
        if ($cat !== '' && $sub !== '') {
            $acctRows = $c->table('account_main as am')
                ->join('item_main as im', 'im.acm_acct_code', '=', 'am.acm_acct_code')
                ->where('am.acm_acct_status', 1)
                ->where('im.itm_status', '1')
                ->where('im.isc_subcategory_code', $sub)
                ->where('im.itm_category_code', $cat)
                ->selectRaw('DISTINCT am.acm_acct_code AS code, am.acm_acct_desc AS descr')
                ->orderBy('am.acm_acct_code')
                ->get();
            foreach ($acctRows as $row) {
                $code = (string) $row->code;
                $d = $row->descr !== null ? (string) $row->descr : '';
                $tf2[] = [
                    'value' => $code,
                    'label' => $d !== '' ? $code.' — '.$d : $code,
                ];
            }
        }

        return [
            'tf_0' => $tf0,
            'tf_1' => $tf1,
            'tf_2' => $tf2,
        ];
    }

    /**
     * Purchasing / Setup / Item Main (menu 1820) — aligned with PurchasingItemMainService / controller.
     */
    private function purchasingItemMainSetupShell(Request $r, int $page, int $limit, string $q): array
    {
        $svc = app(PurchasingItemMainService::class);
        $pack = $svc->mainCategories($r, $page, $limit, $q);
        try {
            $groups = $svc->groupLookupOptions();
        } catch (\Throwable) {
            $groups = [];
        }

        return [
            'rows' => $pack['rows'],
            'total' => $pack['total'],
            'connector' => 'purchasing_item_main_setup',
            'top_filter_options' => [
                'tf_0' => $groups,
            ],
        ];
    }

    /**
     * Purchasing / Setup / List Of Jobscope (menu 1932) — `jobscope` list for shell preview.
     */
    private function purchasingListOfJobscopeShell(Request $r, int $page, int $limit, string $q): array
    {
        return app(PurchasingJobscopeService::class)->listForShell($r, $page, $limit, $q);
    }

    /**
     * List Of Assessment Question (menu 2066) — aligns with registry dtKey on `vendor_assessment_setup`.
     */
    private function purchasingAssessmentQuestionShell(Request $r, int $page, int $limit, string $q): array
    {
        try {
            $base = $this->conn()->table('vendor_assessment_setup as vas')
                ->select([
                    'vas.vas_assessment_item_no',
                    'vas.vas_assessment_item_code',
                    'vas.vas_assessment_item_desc',
                    DB::raw(
                        'CASE UPPER(TRIM(IFNULL(vas.vas_yn_flag,\'\'))) '.
                            "WHEN 'Y' THEN 'YES' WHEN 'N' THEN 'NO' ELSE IFNULL(vas.vas_yn_flag,'') END AS vas_yn_flag"
                    ),
                ]);
            if ($q !== '') {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $base->whereRaw(
                    'LOWER(CONCAT_WS("|",
                        IFNULL(vas.vas_assessment_item_code,\'\'),
                        IFNULL(vas.vas_assessment_item_desc,\'\'),
                        IFNULL(vas.vas_yn_flag,\'\'))) LIKE ?',
                    [$like]
                );
            }
            $base->orderBy('vas.vas_assessment_item_code');

            return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_assessment_question']);
        } catch (\Throwable) {
            return ['rows' => [], 'total' => 0, 'connector' => 'purchasing_assessment_question'];
        }
    }

    private function purchasingVendorList(Request $r, int $page, int $limit, string $q): array
    {
        $bankStatus = $this->conn()->table('vend_supplier_account')
            ->select([
                'vcs_vendor_code',
                DB::raw("CASE WHEN MAX(CASE WHEN vsa_status = '1' THEN 1 ELSE 0 END) = 1 THEN 'ACTIVE' WHEN MAX(CASE WHEN vsa_status = '0' THEN 1 ELSE 0 END) = 1 THEN 'INACTIVE' ELSE MAX(vsa_status) END AS vsa_status"),
            ])
            ->groupBy('vcs_vendor_code');

        $base = $this->conn()->table('vend_customer_supplier as vcs')
            ->leftJoinSub($bankStatus, 'vsa', 'vsa.vcs_vendor_code', '=', 'vcs.vcs_vendor_code')
            ->select([
                'vcs.vcs_id',
                'vcs.vcs_vendor_code',
                'vcs.vcs_vendor_name',
                DB::raw("CONCAT_WS(', ', NULLIF(TRIM(vcs.vcs_address), ''), NULLIF(TRIM(vcs.vcs_address2), ''), NULLIF(TRIM(vcs.vcs_address3), ''), NULLIF(TRIM(vcs.vcs_postcode), ''), NULLIF(TRIM(vcs.vcs_town), ''), NULLIF(TRIM(vcs.vcs_state), '')) AS vcs_address"),
                'vcs.vcs_registration_no',
                'vcs.vcs_reg_date',
                'vcs.vcs_reg_exp_date',
                'vcs.vcs_kk_regno',
                'vcs.vcs_kk_expired_date',
                'vcs.vcs_unv_reg_date',
                'vcs.vcs_unv_req_exp_date',
                'vcs.vcs_bumi_status',
                'vcs.vcs_company_category',
                DB::raw('CAST(IFNULL(vcs.vcs_authorize_capital, 0) AS DECIMAL(15, 2)) AS vcs_authorize_capital'),
                DB::raw('CAST(IFNULL(vcs.vcs_paid_up_capital, 0) AS DECIMAL(15, 2)) AS vcs_paid_up_capital'),
                'vcs.vcs_tel_no',
                'vcs.vcs_fax_no',
                'vcs.vcs_contact_person',
                'vcs.vcs_iscreditor',
                'vcs.vcs_isdebtor',
                DB::raw("IFNULL(vsa.vsa_status, '') AS vsa_status"),
                'vcs.vcs_vendor_status',
            ])
            ->orderBy('vcs.vcs_vendor_name')
            ->orderBy('vcs.vcs_vendor_code');

        $vendorCode = trim((string) $r->input('sf_0', ''));
        if ($vendorCode !== '') {
            $like = $this->likeEscape(mb_strtolower($vendorCode, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(vcs.vcs_vendor_code,'')) LIKE ?", [$like]);
        }

        $vendorName = trim((string) $r->input('sf_1', ''));
        if ($vendorName !== '') {
            $like = $this->likeEscape(mb_strtolower($vendorName, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(vcs.vcs_vendor_name,'')) LIKE ?", [$like]);
        }

        $debtor = trim((string) $r->input('sf_2', ''));
        if ($debtor !== '') {
            $base->where('vcs.vcs_isdebtor', $debtor);
        }

        $vendorStatus = trim((string) $r->input('sf_3', ''));
        if ($vendorStatus !== '') {
            $base->where('vcs.vcs_vendor_status', $vendorStatus);
        }

        $state = trim((string) $r->input('sf_5', ''));
        if ($state !== '') {
            $base->where('vcs.vcs_state', $state);
        }

        $registrationCategory = trim((string) $r->input('sf_6', ''));
        if ($registrationCategory !== '') {
            $base->where('vcs.vcs_company_category', $registrationCategory);
        }

        $codeSsm = trim((string) $r->input('sf_8', ''));
        if ($codeSsm !== '') {
            $base->where('vcs.vcs_registration_no', $codeSsm);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vcs.vcs_vendor_code,''), IFNULL(vcs.vcs_vendor_name,''), IFNULL(vcs.vcs_registration_no,''), IFNULL(vcs.vcs_kk_regno,''), IFNULL(vcs.vcs_contact_person,''), IFNULL(vcs.vcs_tel_no,''), IFNULL(vcs.vcs_vendor_status,''))) LIKE ?",
                [$like]
            );
        }

        $stateOptions = $this->conn()->table('lookup_details')
            ->where('lma_code_name', 'STATE')
            ->whereRaw("TRIM(IFNULL(lde_description,'')) <> ''")
            ->orderBy('lde_description')
            ->pluck('lde_description')
            ->unique()
            ->map(fn ($value) => ['value' => (string) $value, 'label' => (string) $value])
            ->values()
            ->all();

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_vendor_list',
            'smart_filter_options' => [
                'sf_2' => [
                    ['value' => 'Y', 'label' => 'YES'],
                    ['value' => 'N', 'label' => 'NO'],
                ],
                'sf_3' => [
                    ['value' => '0', 'label' => 'INACTIVE'],
                    ['value' => '1', 'label' => 'ACTIVE'],
                    ['value' => 'BLACKLIST', 'label' => 'BLACKLIST'],
                ],
                'sf_5' => $stateOptions,
                'sf_6' => [
                    ['value' => 'INDIVIDUAL', 'label' => 'INDIVIDUAL'],
                    ['value' => 'REGISTERED', 'label' => 'REGISTERED'],
                    ['value' => 'UNQUALIFIED TO REGISTERED', 'label' => 'UNQUALIFIED TO REGISTERED'],
                ],
            ],
        ]);
    }

    private function purchasingVendorProfile(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    private function purchasingVendorBankAccountUpdated1955(Request $r, int $page, int $limit, string $q): array
    {
        $vendorCode = $this->resolveVendorCode($r);

        if ($vendorCode === '') {
            return [
                'rows' => [],
                'total' => 0,
                'connector' => 'purchasing_vendor_bank_account_updated_1955',
                'form_values' => $this->blankVendorBankUpdatedValues(),
                'form_options' => $this->vendorBankUpdatedOptions(),
                'extra_datatable_rows' => [[], [], [], [], [], [], []],
            ];
        }

        $bankBase = $this->conn()->table('vend_supplier_account as vsa')
            ->leftJoin('bank_master as bm', 'bm.bnm_bank_code', '=', 'vsa.vsa_vendor_bank')
            ->where('vsa.vcs_vendor_code', $vendorCode)
            ->select([
                'vsa.vsa_vend_acct_id',
                'vsa.vcs_vendor_code',
                DB::raw("COALESCE(NULLIF(CONCAT_WS(' - ', NULLIF(TRIM(vsa.vsa_vendor_bank), ''), NULLIF(TRIM(bm.bnm_bank_desc), '')), ''), vsa.vsa_vendor_bank) AS vsa_vendor_bank"),
                'vsa.vsa_bank_accno',
                'vsa.vsa_reason',
                DB::raw("CASE WHEN vsa.vsa_status = '1' THEN 'ACTIVE' WHEN vsa.vsa_status = '0' THEN 'INACTIVE' ELSE IFNULL(vsa.vsa_status, '') END AS vsa_status"),
                'vsa.createddate',
            ])
            ->orderByDesc('vsa.createddate');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $bankBase->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vsa.vsa_vendor_bank,''), IFNULL(bm.bnm_bank_desc,''), IFNULL(vsa.vsa_bank_accno,''), IFNULL(vsa.vsa_reason,''), IFNULL(vsa.vsa_status,''))) LIKE ?",
                [$like]
            );
        }

        $bankPack = $this->paginate($bankBase, $page, $limit);

        return array_merge($bankPack, [
            'connector' => 'purchasing_vendor_bank_account_updated_1955',
            'form_values' => $this->vendorBankUpdatedFormValues($vendorCode),
            'form_options' => $this->vendorBankUpdatedOptions(),
            'extra_datatable_rows' => [
                $this->vendorCategoryRows($vendorCode),
                $this->vendorAccountRows($vendorCode),
                $this->vendorAddressRows($vendorCode),
                $this->vendorJobscopeRows($vendorCode),
                $this->vendorLicenceRows('vend_licence_ssm', 'vls_id', 'vls_licence_code', $vendorCode),
                $this->vendorLicenceRows('vend_licence_mof', 'vlm_id', 'vlm_licence_code', $vendorCode),
                $this->vendorOtherLicenceRows($vendorCode),
            ],
        ]);
    }

    private function resolveVendorCode(Request $r): string
    {
        foreach (['Code', 'code', 'vcs_vendor_code', 'vcsVendorCode', 'vendorCode'] as $key) {
            $value = trim((string) $r->input($key, ''));
            if ($value !== '') {
                return $value;
            }
        }

        $id = trim((string) $r->input('ID', $r->input('id', '')));
        if ($id === '') {
            return '';
        }

        return (string) ($this->conn()
            ->table('vend_customer_supplier')
            ->where('vcs_id', $id)
            ->value('vcs_vendor_code') ?? '');
    }

    private function blankVendorBankUpdatedValues(): array
    {
        return [
            'vendorCode' => '',
            'vendorName' => '',
            'icNo' => '',
            'telNo' => '',
            'creditor' => '',
            'faxNo' => '',
            'contactPerson' => '',
            'vendorStatus' => '',
            'debtor' => '',
            'taraf' => '',
            'gstNo' => '',
            'email' => '',
            'registrationDate' => '',
            'expiryDate' => '',
            'kwspNo' => '',
            'socsoNo' => '',
            'companyCategory' => '',
            'registrationNoSsm' => '',
            'registrationNoMof' => '',
            'registrationDateSsm' => '',
            'registrationExpiryDateMof' => '',
            'registrationExpiryDateSsm' => '',
            'registrationNoMotac' => '',
            'registrationExpiredDateMotac' => '',
            'registrationDateMotac' => '',
            'rosNo' => '',
            'approvalName' => '',
            'approvalPosition' => '',
            'approvalDate' => now()->format('d/m/Y'),
            'approvalStatus' => '',
            'approvalRemark' => '',
        ];
    }

    private function vendorBankUpdatedFormValues(string $vendorCode): array
    {
        $row = $this->conn()->table('vend_customer_supplier')
            ->where('vcs_vendor_code', $vendorCode)
            ->first([
                'vcs_vendor_code',
                'vcs_vendor_name',
                'vcs_ic_no',
                'vcs_tel_no',
                'vcs_iscreditor',
                'vcs_fax_no',
                'vcs_contact_person',
                'vcs_vendor_status',
                'vcs_isdebtor',
                'vcs_bumi_status',
                'vcs_tax_regno',
                'vcs_email_address',
                'vcs_unv_reg_date',
                'vcs_unv_req_exp_date',
                'vcs_epf_no',
                'vcs_socso_no',
                'vcs_company_category',
                'vcs_registration_no',
                'vcs_kk_regno',
                'vcs_reg_date',
                'vcs_kk_expired_date',
                'vcs_reg_exp_date',
                'vcs_reg_no_kpm',
                'vcs_reg_expdate_kpm',
                'vcs_reg_date_kpm',
                'vcs_ros_no',
                'vcs_position',
            ]);

        if (! $row) {
            return $this->blankVendorBankUpdatedValues();
        }

        return array_merge($this->blankVendorBankUpdatedValues(), [
            'vendorCode' => (string) $row->vcs_vendor_code,
            'vendorName' => (string) $row->vcs_vendor_name,
            'icNo' => (string) ($row->vcs_ic_no ?? ''),
            'telNo' => (string) ($row->vcs_tel_no ?? ''),
            'creditor' => (string) ($row->vcs_iscreditor ?? ''),
            'faxNo' => (string) ($row->vcs_fax_no ?? ''),
            'contactPerson' => (string) ($row->vcs_contact_person ?? ''),
            'vendorStatus' => (string) ($row->vcs_vendor_status ?? ''),
            'debtor' => (string) ($row->vcs_isdebtor ?? ''),
            'taraf' => (string) ($row->vcs_bumi_status ?? ''),
            'gstNo' => (string) ($row->vcs_tax_regno ?? ''),
            'email' => (string) ($row->vcs_email_address ?? ''),
            'registrationDate' => $this->formatSqlDate($row->vcs_unv_reg_date),
            'expiryDate' => $this->formatSqlDate($row->vcs_unv_req_exp_date),
            'kwspNo' => (string) ($row->vcs_epf_no ?? ''),
            'socsoNo' => (string) ($row->vcs_socso_no ?? ''),
            'companyCategory' => (string) ($row->vcs_company_category ?? ''),
            'registrationNoSsm' => (string) ($row->vcs_registration_no ?? ''),
            'registrationNoMof' => (string) ($row->vcs_kk_regno ?? ''),
            'registrationDateSsm' => $this->formatSqlDate($row->vcs_reg_date),
            'registrationExpiryDateMof' => $this->formatSqlDate($row->vcs_kk_expired_date),
            'registrationExpiryDateSsm' => $this->formatSqlDate($row->vcs_reg_exp_date),
            'registrationNoMotac' => (string) ($row->vcs_reg_no_kpm ?? ''),
            'registrationExpiredDateMotac' => $this->formatSqlDate($row->vcs_reg_expdate_kpm),
            'registrationDateMotac' => $this->formatSqlDate($row->vcs_reg_date_kpm),
            'rosNo' => (string) ($row->vcs_ros_no ?? ''),
            'approvalPosition' => (string) ($row->vcs_position ?? ''),
        ]);
    }

    private function vendorBankUpdatedOptions(): array
    {
        $taraf = $this->conn()->table('lookup_details')
            ->where('lma_code_name', 'TARAF_VENDOR')
            ->orderBy('lde_sorting')
            ->orderBy('lde_description')
            ->get(['lde_value', 'lde_description'])
            ->map(fn ($r) => [
                'value' => (string) $r->lde_value,
                'label' => strtoupper((string) $r->lde_description),
            ])
            ->all();

        return [
            'yesNo' => [
                ['value' => 'Y', 'label' => 'YES'],
                ['value' => 'N', 'label' => 'NO'],
            ],
            'taraf' => $taraf,
            'approvalStatus' => [
                ['value' => 'APPROVE', 'label' => 'APPROVE'],
                ['value' => 'REJECT', 'label' => 'REJECT'],
            ],
        ];
    }

    private function vendorCategoryRows(string $vendorCode): array
    {
        return $this->conn()->table('vend_category')
            ->where('vcs_vendor_code', $vendorCode)
            ->orderByDesc('createddate')
            ->get([
                DB::raw('vc_id AS VendorID'),
                DB::raw('vcs_vendor_code AS VendorCode'),
                DB::raw('vc_category_code AS CategoryCode'),
            ])
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function vendorAccountRows(string $vendorCode): array
    {
        return $this->conn()->table('vend_supplier_account as vsa')
            ->leftJoin('bank_master as bm', 'bm.bnm_bank_code', '=', 'vsa.vsa_vendor_bank')
            ->where('vsa.vcs_vendor_code', $vendorCode)
            ->orderByDesc('vsa.createddate')
            ->get([
                DB::raw('vsa.vsa_vend_acct_id AS AccountIndex'),
                DB::raw('vsa.vcs_vendor_code AS VendorCode'),
                DB::raw("COALESCE(NULLIF(CONCAT_WS(' - ', NULLIF(TRIM(vsa.vsa_vendor_bank), ''), NULLIF(TRIM(bm.bnm_bank_desc), '')), ''), vsa.vsa_vendor_bank) AS VendorBank"),
                DB::raw('vsa.vsa_bank_accno AS BankAccountNo'),
                DB::raw("CASE WHEN vsa.vsa_status = '1' THEN 'ACTIVE' WHEN vsa.vsa_status = '0' THEN 'INACTIVE' ELSE IFNULL(vsa.vsa_status, '') END AS Status"),
            ])
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function vendorAddressRows(string $vendorCode): array
    {
        return $this->conn()->table('vendor_address as va')
            ->leftJoin('lookup_details as ld', function (JoinClause $join) {
                $join->on('ld.lde_value', '=', 'va.vdd_address_type')
                    ->where('ld.lma_code_name', '=', 'ADDRESS_TYPE');
            })
            ->where('va.vcs_vendor_code', $vendorCode)
            ->orderByDesc('va.createddate')
            ->get([
                DB::raw('va.vdd_address_id AS AddressId'),
                DB::raw('va.vcs_vendor_code AS VendorCode'),
                DB::raw("COALESCE(NULLIF(ld.lde_description2, ''), NULLIF(ld.lde_description, ''), va.vdd_address_type) AS AddressType"),
                'va.vdd_address1',
                'va.vdd_address2',
                'va.vdd_address3',
                DB::raw('va.vdd_pcode AS Postcode'),
                DB::raw('va.vdd_city AS City'),
                DB::raw('va.vdd_state AS State'),
                DB::raw('va.vdd_country AS Country'),
                DB::raw("DATE_FORMAT(va.createddate, '%d/%m/%Y') AS CreateDate"),
            ])
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function vendorJobscopeRows(string $vendorCode): array
    {
        return $this->conn()->table('vendor_jobscope as vj')
            ->leftJoin('jobscope as js', function (JoinClause $join) {
                $join->on('js.jbs_jobscope_code', '=', 'vj.jbs_jobscope_code')
                    ->on('js.jbc_category', '=', 'vj.jbc_category');
            })
            ->where('vj.vcs_vendor_code', $vendorCode)
            ->orderByDesc('vj.createddate')
            ->get([
                DB::raw('vj.vjb_id AS VendorID'),
                DB::raw('vj.vcs_vendor_code AS VendorCode'),
                DB::raw("COALESCE(NULLIF(CONCAT_WS(' - ', NULLIF(TRIM(vj.jbs_jobscope_code), ''), NULLIF(TRIM(js.jbs_job_name), '')), ''), vj.jbs_jobscope_code) AS JobscopeCode"),
                DB::raw('vj.jbc_category AS Category'),
                DB::raw("DATE_FORMAT(vj.createddate, '%d/%m/%Y') AS CreatedDate"),
            ])
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function vendorLicenceRows(string $table, string $idColumn, string $codeColumn, string $vendorCode): array
    {
        return $this->conn()->table($table)
            ->where('vcs_vendor_code', $vendorCode)
            ->orderByDesc('createddate')
            ->get([
                DB::raw($idColumn.' AS '.$idColumn),
                'vcs_vendor_code',
                $codeColumn,
            ])
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function vendorOtherLicenceRows(string $vendorCode): array
    {
        return $this->conn()->table('vend_licence_others')
            ->where('vcs_vendor_code', $vendorCode)
            ->orderByDesc('createddate')
            ->get(['vlo_id', 'vcs_vendor_code', 'vlo_licence_code', 'vlo_licence_desc'])
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    private function formatSqlDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return substr((string) $value, 0, 10);
    }

    private function purchasingVendorListByItem(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    private function purchasingListingOfVendor(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    private function purchasingQuotationList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('requisition_master as rm')
            ->select(['rm.rqm_requisition_id', 'rm.rqm_requisition_no', 'rm.rqm_request_date', 'rm.oun_code', 'rm.rqm_amount', 'rm.rqm_status'])
            ->orderByDesc('rm.rqm_requisition_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(rm.req_no,''), IFNULL(rm.oun_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_quotation_list']);
    }

    private function purchasingJobScope(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('vendor_jobscope as vj')
            ->select(['vj.vjb_id', 'vj.jbs_jobscope_code', 'vj.jbs_jobscope_code', 'vj.vjb_tag'])
            ->orderBy('vj.jbs_jobscope_code');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(vj.js_code,''), IFNULL(vj.jbs_jobscope_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_job_scope']);
    }

    private function purchasingCommitteeSetup(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('requisition_committee as rc')
            ->select(['rc.rqm_requisition_id', 'rc.stf_staff_id', 'rc.rqc_type', 'rc.rqc_status'])
            ->orderBy('rc.stf_staff_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(rc.stf_staff_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_committee_setup']);
    }

    private function purchasingTenderJobScope(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tender_jobscope as tj')
            ->select(['tj.tjs_id_ai', 'tj.tdm_tender_id', 'tj.tjs_jobscope_code', 'tj.tjs_jobscope_category'])
            ->orderByDesc('tj.tjs_id_ai');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(tj.tjs_name,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_tender_job_scope']);
    }

    private function purchasingSetupItem(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('store_item as si')
            ->select(['si.sit_store_item_id', 'si.itm_item_code', 'si.sit_desc_item', 'si.sit_uom_code', 'si.sit_available'])
            ->orderBy('si.sit_desc_item');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(si.item_code,''), IFNULL(si.sit_desc_item,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_setup_item']);
    }

    private function purchasingPrForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_pr_form');
    }

    private function purchasingAgreementList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('aggrement_po as ap')
            ->leftJoin('vend_customer_supplier as vc', 'vc.vcs_vendor_code', '=', 'ap.vcs_vendor_code')
            ->select([
                DB::raw('ap.agg_id AS AgreementID'),
                DB::raw('ap.agg_no AS AgreementNo'),
                DB::raw('ap.agg_ref_doc AS AgreementRef'),
                'ap.createddate',
                DB::raw('ap.vcs_vendor_code AS VendorCode'),
                DB::raw("COALESCE(NULLIF(TRIM(vc.vcs_vendor_name), ''), '') AS VendorName"),
                DB::raw("IFNULL(ap.agg_address, '') AS Address"),
                DB::raw("IFNULL(ap.agg_description, '') AS Description"),
                DB::raw('ap.agg_start_date AS StartDate'),
                DB::raw('ap.agg_end_date AS EndDate'),
                DB::raw('CAST(IFNULL(ap.agg_amt, 0) AS DECIMAL(15, 2)) AS Amount'),
                DB::raw('CAST(IFNULL(ap.agg_bal_amt, 0) AS DECIMAL(15, 2)) AS AmountBalance'),
                DB::raw('CAST(IFNULL(ap.agg_amt_monthly, 0) AS DECIMAL(15, 2)) AS AmountMonthly'),
                DB::raw('ap.agg_duration AS Duration'),
                DB::raw('ap.agg_tenure AS Type'),
                'ap.tdm_tender_no',
                DB::raw('ap.agg_status AS StatusAgreement'),
                DB::raw('ap.agg_status_wf AS StatusWfAgreement'),
            ])
            ->orderByDesc('ap.agg_id');

        $agreementNo = trim((string) $r->input('sf_0', ''));
        if ($agreementNo !== '') {
            $like = $this->likeEscape(mb_strtolower($agreementNo, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(ap.agg_no,'')) LIKE ?", [$like]);
        }

        $vendorCode = trim((string) $r->input('sf_1', ''));
        if ($vendorCode !== '') {
            $like = $this->likeEscape(mb_strtolower($vendorCode, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(ap.vcs_vendor_code,'')) LIKE ?", [$like]);
        }

        $typeDuration = trim((string) $r->input('sf_3', ''));
        if ($typeDuration !== '') {
            $base->where('ap.agg_tenure', $typeDuration);
        }

        $statusAgreement = trim((string) $r->input('sf_5', ''));
        if ($statusAgreement !== '') {
            $base->where('ap.agg_status', $statusAgreement);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ap.agg_no,''), IFNULL(ap.agg_ref_doc,''), IFNULL(ap.vcs_vendor_code,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(ap.agg_description,''), IFNULL(ap.agg_status,''), IFNULL(ap.agg_status_wf,''), IFNULL(ap.tdm_tender_no,''))) LIKE ?",
                [$like]
            );
        }

        $typeOptions = $this->conn()->table('lookup_details')
            ->where('lma_code_name', 'INVESTMENT_TENURE')
            ->whereRaw("TRIM(IFNULL(lde_description2,'')) <> ''")
            ->orderBy('lde_sorting')
            ->orderBy('lde_description2')
            ->pluck('lde_description2')
            ->unique()
            ->map(fn ($value) => ['value' => (string) $value, 'label' => (string) $value])
            ->values()
            ->all();

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_agreement_list',
            'smart_filter_options' => [
                'sf_3' => $typeOptions,
                'sf_5' => [
                    ['value' => '1', 'label' => 'ACTIVE'],
                    ['value' => '0', 'label' => 'INACTIVE'],
                    ['value' => '3', 'label' => 'PENDING'],
                ],
            ],
        ]);
    }

    private function purchasingAgreementListAll(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingAgreementList($r, $page, $limit, $q);
    }

    private function purchasingPoClosing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('purchase_order_closing as poc')
            ->select(['poc.poc_id', 'poc.poc_id', 'poc.createddate', 'poc.poc_year_closing'])
            ->orderByDesc('poc.poc_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(poc.poc_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_po_closing']);
    }

    private function purchasingPoUpdate(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingPurchaseOrderMenu1833($r, $page, $limit, $q);
    }

    private function purchasingVoList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('aggrement_vo as av')
            ->leftJoin('aggrement_po as ap', 'ap.agg_id', '=', 'av.agg_id')
            ->select([
                'av.agv_id',
                'av.agg_id',
                'av.agv_letter_date',
                'av.agv_no',
                'av.agv_reference_no',
                'ap.agg_no',
                'ap.agg_ref_doc',
                DB::raw('CAST(IFNULL(ap.agg_amt, 0) AS DECIMAL(15, 2)) AS agg_amt'),
                DB::raw('CAST(IFNULL(av.agv_amt, 0) AS DECIMAL(15, 2)) AS agv_amt'),
                DB::raw('CAST(IFNULL(ap.agg_amt, 0) + IFNULL(av.agv_amt, 0) AS DECIMAL(15, 2)) AS agg_amt_new'),
                DB::raw("IFNULL(av.agv_extended_field, '') AS reason"),
                'av.agv_status',
            ])
            ->orderByDesc('av.agv_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(av.agv_no,''), IFNULL(av.agv_reference_no,''), IFNULL(ap.agg_no,''), IFNULL(ap.agg_ref_doc,''), IFNULL(av.agv_status,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_vo_list']);
    }

    private function purchasingVoListAll(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVoList($r, $page, $limit, $q);
    }

    private function purchasingNewVo(Request $r, int $page, int $limit, string $q): array
    {
        unset($page, $limit, $q);

        $voId = (int) $r->input('agv_id', $r->input('agvId', $r->input('id', 0)));
        $formValues = [
            'agv_no' => 'Auto Assigned',
            'agv_letter_date' => now()->toDateString(),
            'agv_status' => 'DRAFT',
        ];

        if ($voId > 0) {
            $vo = $this->conn()->table('aggrement_vo AS av')
                ->leftJoin('aggrement_po AS ap', 'ap.agg_id', '=', 'av.agg_id')
                ->where('av.agv_id', $voId)
                ->select([
                    'av.*',
                    'ap.agg_no',
                    'ap.agg_ref_doc',
                    'ap.agg_amt',
                ])
                ->first();
            if ($vo) {
                $formValues = array_merge($formValues, (array) $vo);
            }
        }

        return [
            'rows' => [],
            'total' => 0,
            'connector' => 'purchasing_new_vo',
            'form_options' => $this->variationOrderOptions(),
            'form_values' => $formValues,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function variationOrderOptions(): array
    {
        $cx = $this->conn();

        $agreements = $cx->table('aggrement_po')
            ->orderByDesc('agg_id')
            ->limit(1000)
            ->get(['agg_id', 'agg_no', 'agg_ref_doc', 'agg_amt', 'agg_status'])
            ->map(fn ($r) => [
                'value' => (string) $r->agg_id,
                'label' => trim((string) ($r->agg_ref_doc ?? '')) !== ''
                    ? $r->agg_no.' - '.$r->agg_ref_doc
                    : (string) $r->agg_no,
                'agreementNo' => (string) ($r->agg_no ?? ''),
                'agreementRef' => (string) ($r->agg_ref_doc ?? ''),
                'agreementAmount' => (string) ($r->agg_amt ?? '0'),
                'status' => (string) ($r->agg_status ?? ''),
            ])
            ->values()
            ->all();

        $receivers = $cx->table('staff')
            ->orderBy('stf_staff_name')
            ->limit(1000)
            ->get(['stf_staff_id', 'stf_staff_name'])
            ->map(fn ($r) => [
                'value' => (string) $r->stf_staff_id,
                'label' => trim((string) ($r->stf_staff_name ?? '')) !== ''
                    ? $r->stf_staff_id.' - '.$r->stf_staff_name
                    : (string) $r->stf_staff_id,
            ])
            ->values()
            ->all();

        return [
            'agreements' => $agreements,
            'statusOptions' => [
                ['value' => 'ENTRY', 'label' => 'ENTRY'],
                ['value' => 'DRAFT', 'label' => 'DRAFT'],
                ['value' => 'REJECT', 'label' => 'REJECT'],
                ['value' => 'APPROVE', 'label' => 'APPROVE'],
            ],
            'nextReceivers' => $receivers,
        ];
    }

    /**
     * Purchasing / Report / Report Vendor Assessment (GRN) — menu 3100.
     * Registry {@see topFilterFields} dropdowns resolved via meta.top_filter_options (tf_*).
     */
    private function purchasingVendorAssessmentReport(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->vendorAssessmentReport3100Base($r, $q);

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_vendor_assessment_report_grn_3100',
            'top_filter_options' => $this->vendorAssessmentReportTopFilterOptions3100(),
        ]);
    }

    /**
     * Purchasing / Report / Report Vendor Assessment (WPN) — menu 3106.
     */
    private function purchasingVendorAssessmentWpn(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->vendorAssessmentReport3106Base($r, $q);

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_vendor_assessment_report_wpn_3106',
            'top_filter_options' => $this->vendorAssessmentReportTopFilterOptions3106(),
        ]);
    }

    /**
     * @return list<int, array{value: string, label: string}>
     */
    private function pluckDistinctToFilterOptions(?\Illuminate\Support\Collection $pluck): array
    {
        if ($pluck === null || $pluck->isEmpty()) {
            return [];
        }

        return $pluck
            ->map(fn ($v) => ['value' => (string) $v, 'label' => (string) $v])
            ->values()
            ->all();
    }

    /**
     * @return array<string, list<int, array{value: string, label: string}>>
     */
    private function vendorAssessmentReportTopFilterOptions3100(): array
    {
        $cx = $this->conn();

        $tfGrn = $cx->table('goods_receive_master AS grm')
            ->join('vendor_assessment_master AS vam', 'vam.vam_grn_no', '=', 'grm.grm_receive_no')
            ->whereNotNull('grm.grm_receive_no')
            ->whereRaw("TRIM(IFNULL(grm.grm_receive_no,'')) <> ''")
            ->selectRaw('DISTINCT TRIM(grm.grm_receive_no) AS col')
            ->orderByRaw('col')
            ->pluck('col');

        return $this->vendorAssessmentReportTopFilterOptionsShared($cx, $tfGrn);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, mixed>  $tf0DocNos  distinct GRN or WPN numbers for filter dropdown 1
     * @return array<string, list<int, array{value: string, label: string}>>
     */
    private function vendorAssessmentReportTopFilterOptionsShared(Connection $cx, \Illuminate\Support\Collection $tf0DocNos): array
    {
        $pre = $cx->table('purchase_order_details')
            ->whereRaw("TRIM(IFNULL(rqm_requisition_no,'')) <> ''")
            ->selectRaw('DISTINCT TRIM(rqm_requisition_no) AS col')
            ->orderByRaw('col')
            ->pluck('col');

        $por = $cx->table('purchase_order_master')
            ->whereRaw("TRIM(IFNULL(pom_order_no,'')) <> ''")
            ->selectRaw('DISTINCT TRIM(pom_order_no) AS col')
            ->orderByRaw('col')
            ->pluck('col');

        $vendorRows = $cx->table('vendor_assessment_master AS vam')
            ->join('vend_customer_supplier AS vcs', 'vcs.vcs_vendor_code', '=', 'vam.vcs_vendor_code')
            ->whereRaw("TRIM(IFNULL(vam.vcs_vendor_code,'')) <> ''")
            ->groupBy('vam.vcs_vendor_code')
            ->selectRaw('TRIM(vam.vcs_vendor_code) AS code')
            ->selectRaw('MAX(TRIM(IFNULL(vcs.vcs_vendor_name, \'\'))) AS vname')
            ->orderByRaw('code')
            ->get();

        $vendorOpts = $vendorRows->map(function ($row): array {
            $code = (string) $row->code;
            $name = trim((string) $row->vname);

            return [
                'value' => $code,
                'label' => $name !== '' ? $code.' — '.$name : $code,
            ];
        })->values()->all();

        try {
            $jenis = $cx->table('lookup_details AS ld')
                ->whereRaw('UPPER(ld.lma_code_name) LIKE ?', ['%TENDERTYPE%'])
                ->whereRaw("TRIM(IFNULL(ld.lde_description,'')) <> ''")
                ->whereRaw("IFNULL(ld.lde_status,'') NOT IN ('0','N','INACTIVE')")
                ->selectRaw('DISTINCT TRIM(ld.lde_description) AS col')
                ->orderByRaw('col')
                ->pluck('col');
        } catch (\Throwable) {
            $jenis = collect();
        }

        $ptj = $cx->table('purchase_order_details')
            ->whereRaw("TRIM(IFNULL(oun_code,'')) <> ''")
            ->selectRaw('DISTINCT TRIM(oun_code) AS col')
            ->orderByRaw('col')
            ->pluck('col');

        return [
            'tf_0' => $this->pluckDistinctToFilterOptions($tf0DocNos),
            'tf_1' => $this->pluckDistinctToFilterOptions($pre),
            'tf_2' => $this->pluckDistinctToFilterOptions($por),
            'tf_3' => $vendorOpts,
            'tf_5' => $this->pluckDistinctToFilterOptions($jenis),
            'tf_6' => $this->pluckDistinctToFilterOptions($ptj),
        ];
    }

    /**
     * @return array<string, list<int, array{value: string, label: string}>>
     */
    private function vendorAssessmentReportTopFilterOptions3106(): array
    {
        $cx = $this->conn();

        $tfWpn = $cx->table('work_progress_master AS wpm')
            ->join('vendor_assessment_master AS vam', 'vam.vam_grn_no', '=', 'wpm.wpm_progress_no')
            ->whereNotNull('wpm.wpm_progress_no')
            ->whereRaw("TRIM(IFNULL(wpm.wpm_progress_no,'')) <> ''")
            ->selectRaw('DISTINCT TRIM(wpm.wpm_progress_no) AS col')
            ->orderByRaw('col')
            ->pluck('col');

        return $this->vendorAssessmentReportTopFilterOptionsShared($cx, $tfWpn);
    }

    private function vendorAssessmentReport3100Base(Request $r, string $q): Builder
    {
        $cx = $this->conn();
        $subPoMeta = $cx->table('purchase_order_details')
            ->select([
                'pom_order_id',
                DB::raw('MIN(IFNULL(TRIM(oun_code), \'\')) AS meta_oun_code'),
                DB::raw('MIN(IFNULL(TRIM(rqm_requisition_no), \'\')) AS meta_rqm_no'),
            ])
            ->groupBy('pom_order_id');

        $base = $cx->table('vendor_assessment_master AS vam')
            ->join('goods_receive_master AS grm', 'grm.grm_receive_no', '=', 'vam.vam_grn_no')
            ->leftJoin('purchase_order_master AS pom', 'pom.pom_order_no', '=', 'grm.pom_order_no')
            ->leftJoinSub($subPoMeta, 'pometa', function (JoinClause $join) {
                $join->on('pometa.pom_order_id', '=', 'pom.pom_order_id');
            })
            ->leftJoin('requisition_master AS rm', 'rm.rqm_requisition_no', '=', 'pometa.meta_rqm_no')
            ->leftJoin('vend_customer_supplier AS vcs', 'vcs.vcs_vendor_code', '=', 'vam.vcs_vendor_code')
            ->select([
                'vam.vam_assessment_id',
                DB::raw('NULLIF(TRIM(pometa.meta_rqm_no), \'\') AS rqm_requisition_no'),
                'pom.pom_order_no',
                'grm.grm_receive_no',
                DB::raw('NULLIF(TRIM(pometa.meta_oun_code), \'\') AS oun_code'),
                'vam.createddate',
                'vcs.vcs_vendor_name',
                'vam.vcs_vendor_code',
                DB::raw('IFNULL(NULLIF(TRIM(vam.vam_status), \'\'), IFNULL(vcs.vcs_vendor_status, \'\')) AS vendStatus'),
                'rm.rqm_jenis_tender',
                DB::raw('IFNULL(vam.vam_evaluator_note, \'\') AS vam_evaluator_note'),
                DB::raw('\'\' AS pengesahan'),
                DB::raw('IFNULL(vam.vam_approve_note, \'\') AS vam_approve_note'),
            ]);

        return $this->vendorAssessmentReportApplyTopFiltersAndSearch($base, $r, $q, 'grm.grm_receive_no');
    }

    private function vendorAssessmentReport3106Base(Request $r, string $q): Builder
    {
        $cx = $this->conn();
        $subPoMeta = $cx->table('purchase_order_details')
            ->select([
                'pom_order_id',
                DB::raw('MIN(IFNULL(TRIM(oun_code), \'\')) AS meta_oun_code'),
                DB::raw('MIN(IFNULL(TRIM(rqm_requisition_no), \'\')) AS meta_rqm_no'),
            ])
            ->groupBy('pom_order_id');

        $base = $cx->table('vendor_assessment_master AS vam')
            ->join('work_progress_master AS wpm', 'wpm.wpm_progress_no', '=', 'vam.vam_grn_no')
            ->leftJoin('purchase_order_master AS pom', 'pom.pom_order_no', '=', 'wpm.pom_order_no')
            ->leftJoinSub($subPoMeta, 'pometa', function (JoinClause $join) {
                $join->on('pometa.pom_order_id', '=', 'pom.pom_order_id');
            })
            ->leftJoin('requisition_master AS rm', 'rm.rqm_requisition_no', '=', 'pometa.meta_rqm_no')
            ->leftJoin('vend_customer_supplier AS vcs', 'vcs.vcs_vendor_code', '=', 'vam.vcs_vendor_code')
            ->select([
                'vam.vam_assessment_id',
                DB::raw('NULLIF(TRIM(pometa.meta_rqm_no), \'\') AS rqm_requisition_no'),
                'pom.pom_order_no',
                'wpm.wpm_progress_no',
                DB::raw('NULLIF(TRIM(pometa.meta_oun_code), \'\') AS oun_code'),
                'vam.createddate',
                'vcs.vcs_vendor_name',
                'vam.vcs_vendor_code',
                DB::raw('IFNULL(NULLIF(TRIM(vam.vam_status), \'\'), IFNULL(vcs.vcs_vendor_status, \'\')) AS vendStatus'),
                'rm.rqm_jenis_tender',
                DB::raw('IFNULL(vam.vam_evaluator_note, \'\') AS vam_evaluator_note'),
                DB::raw('\'\' AS pengesahan'),
                DB::raw('IFNULL(vam.vam_approve_note, \'\') AS vam_approve_note'),
            ]);

        return $this->vendorAssessmentReportApplyTopFiltersAndSearch($base, $r, $q, 'wpm.wpm_progress_no');
    }

    /** @param  'grm.grm_receive_no'|'wpm.wpm_progress_no'  $qualifiedDocumentColumn */
    private function vendorAssessmentReportApplyTopFiltersAndSearch(
        Builder $base,
        Request $r,
        string $q,
        string $qualifiedDocumentColumn,
    ): Builder {
        $quotedDocCol = '`'.str_replace('.', '`.`', $qualifiedDocumentColumn).'`';
        $docTrimSql = 'IFNULL(TRIM(IFNULL('.$quotedDocCol.", '')), '')";

        $tf0 = trim((string) $r->input('tf_0', ''));
        if ($tf0 !== '') {
            $base->whereRaw($docTrimSql.' = ?', [$tf0]);
        }

        $tf1 = trim((string) $r->input('tf_1', ''));
        if ($tf1 !== '') {
            $base->whereRaw('TRIM(IFNULL(pometa.meta_rqm_no, \'\')) = ?', [$tf1]);
        }

        $tf2 = trim((string) $r->input('tf_2', ''));
        if ($tf2 !== '') {
            $base->whereRaw('TRIM(IFNULL(pom.pom_order_no, \'\')) = ?', [$tf2]);
        }

        $tf3 = trim((string) $r->input('tf_3', ''));
        if ($tf3 !== '') {
            $base->whereRaw('TRIM(IFNULL(vam.vcs_vendor_code, \'\')) = ?', [$tf3]);
        }

        $tf5 = trim((string) $r->input('tf_5', ''));
        if ($tf5 !== '') {
            $base->whereRaw('TRIM(IFNULL(rm.rqm_jenis_tender, \'\')) = ?', [$tf5]);
        }

        $tf6 = trim((string) $r->input('tf_6', ''));
        if ($tf6 !== '') {
            $base->whereRaw('TRIM(IFNULL(pometa.meta_oun_code, \'\')) = ?', [$tf6]);
        }

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '.
                $docTrimSql.", ".
                "IFNULL(TRIM(pometa.meta_rqm_no),''), ".
                "IFNULL(TRIM(pom.pom_order_no),''), ".
                "IFNULL(TRIM(pometa.meta_oun_code),''), ".
                "IFNULL(TRIM(vam.vcs_vendor_code),''), ".
                "IFNULL(TRIM(vcs.vcs_vendor_name),''), ".
                "IFNULL(TRIM(rm.rqm_jenis_tender),''), ".
                "IFNULL(TRIM(vam.vam_status),''), ".
                'IFNULL(TRIM(IFNULL(vam.vam_evaluator_note, \'\')),\'\'), '.
                'IFNULL(TRIM(IFNULL(vam.vam_approve_note, \'\')),\'\')'.
                ')) LIKE ?',
                [$like]
            );
        }

        return $base->orderByDesc('vam.vam_assessment_id');
    }

    private function purchasingTenderBrief(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tender_briefing as tb')
            ->select(['tb.tbr_briefing_id', 'tb.tdm_tender_id', 'tb.tbr_briefing_start_date', 'tb.tbr_address', 'tb.createddate'])
            ->orderByDesc('tb.tbr_briefing_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(tb.tdm_tender_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_tender_brief']);
    }

    private function purchasingTenderEvaluation(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tender_evaluation as te')
            ->select(['te.tde_evaluation_id', 'te.tdm_tender_id', 'te.createddate', 'te.createddate', 'te.tde_evaluate_committe_type'])
            ->orderByDesc('te.tde_evaluation_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(te.tdm_tender_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_tender_evaluation']);
    }

    private function purchasingEvaluationList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingTenderEvaluation($r, $page, $limit, $q);
    }

    private function purchasingTenderCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tender_master as tm')
            ->where('tm.tdm_status', 'CANCEL')
            ->select(['tm.tdm_tender_id', 'tm.tdm_tender_no', 'tm.createddate', 'tm.tdm_title', 'tm.tdm_status'])
            ->orderByDesc('tm.tdm_tender_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(tm.tender_no,''), IFNULL(tm.tender_title,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_tender_cancel']);
    }

    private function purchasingAdvertisementReqList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingTenderList($r, $page, $limit, $q);
    }

    private function purchasingAdvertisementComplete(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingTenderList($r, $page, $limit, $q);
    }

    private function purchasingAdvertisementTime(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingTenderList($r, $page, $limit, $q);
    }

    private function purchasingOfferLetter(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingTenderList($r, $page, $limit, $q);
    }

    private function purchasingQuotationSelection(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tender_selection_result as tsr')
            ->select(['tsr.tdsr_id', 'tsr.tdp_participant_id', 'tsr.tdsr_amount', 'tsr.tdsr_acceptance', 'tsr.tdsr_acceptance'])
            ->orderByDesc('tsr.tdsr_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(tsr.tdp_participant_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_quotation_selection']);
    }

    private function purchasingTenderParticipant(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('tender_participant as tp')
            ->select(['tp.tdp_participant_id', 'tp.tdm_tender_id', 'tp.vcs_vendor_code', 'tp.tdp_quotation_date', 'tp.tdp_status'])
            ->orderByDesc('tp.tdp_participant_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(tp.tdm_tender_id,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_tender_participant']);
    }

    /**
     * List of Cancel Partial PR — menu 3041 registry (`KerisiRemainingshell` datatable 0 keys):
     * nopr, rqm_request_by, titlepr, statuspr, amountpr, amountguna, baki, etc.
     *
     * Driven by `requisition_master` rows linked to a pre-PR (`ppr_requisition_id`),
     * enriched from `pre_purchase_requisition` when present — keys match camelCase outbound columns.
     */
    private function purchasingPartialCancelListFromPrePurchasing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('requisition_master as rm')
            ->leftJoin('pre_purchase_requisition as ppr', 'rm.ppr_requisition_id', '=', 'ppr.ppr_requisition_id')
            ->whereNotNull('rm.ppr_requisition_id')
            ->where('rm.ppr_requisition_id', '>', 0)
            ->select([
                'rm.rqm_requisition_id',
                DB::raw("COALESCE(NULLIF(TRIM(rm.rqm_requisition_no), ''), NULLIF(TRIM(ppr.ppr_pre_requisition_no), ''), '') AS nopr"),
                'rm.rqm_request_by',
                'rm.fty_fund_type',
                'rm.ccr_costcentre',
                'rm.at_activity_code',
                DB::raw("COALESCE(NULLIF(TRIM(rm.rqm_requisition_title), ''), NULLIF(TRIM(ppr.ppr_requisition_title), '')) AS titlepr"),
                DB::raw(
                    'COALESCE('
                    ."NULLIF(TRIM(ppr.ppr_partialcancel_status), ''), "
                    ."NULLIF(TRIM(ppr.ppr_progress_status), ''), "
                    ."NULLIF(TRIM(rm.rqm_status), '')"
                    .') AS statuspr'
                ),
                DB::raw('CAST(IFNULL(rm.rqm_amount, 0) AS DECIMAL(15, 2)) AS amountpr'),
                DB::raw('CAST(IFNULL(rm.rqm_bdg_expenses_amt, 0) AS DECIMAL(15, 2)) AS amountguna'),
                DB::raw('CAST(IFNULL(rm.rqm_balance_bdgt, 0) AS DECIMAL(15, 2)) AS baki'),
            ])
            ->orderByDesc('rm.rqm_requisition_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', '
                ."IFNULL(rm.rqm_requisition_no,''), IFNULL(rm.rqm_request_by,''), IFNULL(rm.rqm_requisition_title,''), "
                ."IFNULL(rm.rqm_status,''), IFNULL(ppr.ppr_partialcancel_status,''), IFNULL(ppr.ppr_status,''), IFNULL(rm.fty_fund_type,''), "
                .'IFNULL(rm.ccr_costcentre,\'\'), IFNULL(rm.at_activity_code,\'\'), '
                ."IFNULL(CAST(IFNULL(rm.rqm_amount,0) AS CHAR),''), IFNULL(CAST(IFNULL(rm.rqm_bdg_expenses_amt,0) AS CHAR),''), "
                ."IFNULL(CAST(IFNULL(rm.rqm_balance_bdgt,0) AS CHAR),''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_partial_pr_linked_list']);
    }

    private function purchasingPrCancelForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_pr_cancel_form');
    }

    private function purchasingPrToCancelPartial(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingPartialCancelListFromPrePurchasing($r, $page, $limit, $q);
    }

    private function purchasingPrCancelPartialForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_pr_cancel_partial_form');
    }

    private function purchasingCommitteeReport(Request $r, int $page, int $limit, string $q): array
    {
        $tenderId = (int) $r->input('tdm_tender_id', $r->input('tdmTenderId', $r->input('tender_id', 0)));
        $tenderOptions = $this->conn()->table('tender_master')
            ->orderByDesc('tdm_tender_id')
            ->get(['tdm_tender_id', 'tdm_tender_no', 'tdm_title'])
            ->map(fn ($row) => [
                'value' => (string) $row->tdm_tender_id,
                'label' => trim((string) ($row->tdm_title ?? '')) !== ''
                    ? $row->tdm_tender_no.' - '.$row->tdm_title
                    : (string) $row->tdm_tender_no,
            ])
            ->values()
            ->all();

        if ($tenderId < 1) {
            return [
                'rows' => [],
                'total' => 0,
                'connector' => 'purchasing_committee_report_3306',
                'form_options' => ['tenderNumbers' => $tenderOptions],
                'form_values' => ['tdm_tender_id' => ''],
            ];
        }

        $base = $this->conn()->table('tender_answer AS ta')
            ->leftJoin('tender_participant AS tp', function (JoinClause $join) {
                $join->on('tp.tdm_tender_id', '=', 'ta.tdm_tender_id')
                    ->on('tp.vcs_vendor_code', '=', 'ta.tas_cust_id');
            })
            ->leftJoin('vend_customer_supplier AS vc', 'vc.vcs_vendor_code', '=', 'ta.tas_cust_id')
            ->where('ta.tdm_tender_id', $tenderId)
            ->select([
                DB::raw("DATE_FORMAT(ta.tas_submit_date, '%Y%m%d%H%i%s') AS tas_submit_date_formatted"),
                'ta.tas_cust_id',
                DB::raw("COALESCE(NULLIF(TRIM(tp.vcs_vendor_name), ''), NULLIF(TRIM(vc.vcs_vendor_name), ''), '') AS tas_cust_name"),
            ])
            ->orderBy('ta.tas_submit_date');

        $qTrim = trim($q);
        if ($qTrim !== '') {
            $like = $this->likeEscape(mb_strtolower($qTrim, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ta.tas_cust_id,''), IFNULL(tp.vcs_vendor_name,''), IFNULL(vc.vcs_vendor_name,''), IFNULL(ta.tas_submit_date,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'purchasing_committee_report_3306',
            'form_options' => ['tenderNumbers' => $tenderOptions],
            'form_values' => ['tdm_tender_id' => (string) $tenderId],
        ]);
    }

    private function purchasingOtherPayment(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_other_payment');
    }

    private function purchasingPrintSab(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_print_sab');
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1701 — Cashbook / Bank Statement Reports
     * ══════════════════════════════════════════════════════════════════ */
    private function bankStatementList(Request $r, int $page, int $limit, string $q): array
    {
        // bank_statement_master: bsm_statement_master_id, bsm_trans_period, acm_acct_code (verified)
        $base = $this->conn()->table('bank_statement_master as bsm')
            ->select([
                'bsm.bsm_statement_master_id',
                'bsm.bsm_trans_period',
                'bsm.acm_acct_code',
                'bsm.bsm_open_balance',
                'bsm.bsm_debit_amount',
                'bsm.bsm_credit_amount',
                'bsm.bsm_close_balance',
                'bsm.bsm_type',
            ])
            ->orderByDesc('bsm.bsm_statement_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bsm.bsm_trans_period,''), IFNULL(bsm.acm_acct_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'bank_statement_list']);
    }

    private function bankStatementMonthlyList(Request $r, int $page, int $limit, string $q): array
    {
        // bank_statement_monthly uses bns_* prefix on bank_statement_recon table
        return $this->bankStatementList($r, $page, $limit, $q);
    }

    private function bankStatementAttachment(Request $r, int $page, int $limit, string $q, int $attachNum = 1): array
    {
        // bank_statement_his shares bns_* columns with bank_statement_recon (verified)
        $base = $this->conn()->table('bank_statement_his as bsh')
            ->select([
                'bsh.bns_id_seq',
                'bsh.bns_bank_seq_id',
                'bsh.bns_trans_date',
                'bsh.bns_trans_ref1',
                'bsh.bns_trans_ref2',
                'bsh.bns_recon_flag',
                'bsh.acm_acct_code_bank',
            ])
            ->orderByDesc('bsh.bns_id_seq');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bsh.bns_bank_seq_id,''), IFNULL(bsh.bns_trans_ref1,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => "bank_statement_attachment_{$attachNum}"]);
    }

    private function bankStatementMatchingReport(Request $r, int $page, int $limit, string $q): array
    {
        // bank_statement_recon uses bns_* prefix columns (verified against live DB)
        $base = $this->conn()->table('bank_statement_recon as bsr')
            ->select([
                'bsr.bns_id_seq',
                'bsr.bns_bank_seq_id',
                'bsr.bns_trans_date',
                'bsr.bns_trans_ref1',
                'bsr.bns_debit_amt',
                'bsr.bns_credit_amt',
                'bsr.bns_recon_status',
                'bsr.bns_recon_flag',
                'bsr.acm_acct_code_bank',
            ])
            ->orderByDesc('bsr.bns_id_seq');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bsr.bns_bank_seq_id,''), IFNULL(bsr.bns_trans_ref1,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'bank_statement_matching_report']);
    }

    private function bankReconStatement(Request $r, int $page, int $limit, string $q): array
    {
        return $this->bankStatementMatchingReport($r, $page, $limit, $q);
    }

    private function bankStatementUnidentifiedEntry(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('stud_unidentifed as su')
            ->select(['su.stu_id', 'su.stu_matric_no', 'su.stu_ic_passport', 'su.createddate', 'su.stu_category'])
            ->orderByDesc('su.stu_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(su.stu_matric_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'bank_statement_unidentified_entry']);
    }

    private function bankStatementUnmatched(Request $r, int $page, int $limit, string $q): array
    {
        // Filter on bns_recon_flag='N' (not reconciled) — actual column verified from live DB
        $base = $this->conn()->table('bank_statement_recon as bsr')
            ->where('bsr.bns_recon_flag', 'N')
            ->select([
                'bsr.bns_id_seq',
                'bsr.bns_bank_seq_id',
                'bsr.bns_trans_date',
                'bsr.bns_trans_ref1',
                'bsr.bns_debit_amt',
                'bsr.bns_credit_amt',
                'bsr.bns_recon_status',
            ])
            ->orderByDesc('bsr.bns_id_seq');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bsr.bns_bank_seq_id,''), IFNULL(bsr.bns_trans_ref1,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'bank_statement_unmatched']);
    }

    private function cashbookClosing(Request $r, int $page, int $limit, string $q): array
    {
        // cashbook_master: cbh_cashbook_master_id, cbh_trans_period, cbh_status (verified)
        $base = $this->conn()->table('cashbook_master as cm')
            ->select([
                'cm.cbh_cashbook_master_id',
                'cm.cbh_trans_period',
                'cm.cbh_open_balance',
                'cm.cbh_debit_amount',
                'cm.cbh_credit_amount',
                'cm.cbh_close_balance',
                'cm.cbh_status',
                'cm.bnm_bank_code',
            ])
            ->orderByDesc('cm.cbh_cashbook_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cm.cbh_trans_period,''), IFNULL(cm.bnm_bank_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'cashbook_closing']);
    }

    private function cashbookClosingAttachment(Request $r, int $page, int $limit, string $q, int $n = 1): array
    {
        return $this->cashbookClosing($r, $page, $limit, $q);
    }

    private function cashbookMasterFile(Request $r, int $page, int $limit, string $q): array
    {
        return $this->cashbookClosing($r, $page, $limit, $q);
    }

    private function cashbookMonthlyList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->cashbookClosing($r, $page, $limit, $q);
    }

    private function cashbookDailySummary(Request $r, int $page, int $limit, string $q): array
    {
        return $this->cashbookClosing($r, $page, $limit, $q);
    }

    private function cashbookMonthlySummary(Request $r, int $page, int $limit, string $q): array
    {
        return $this->cashbookDailySummary($r, $page, $limit, $q);
    }

    private function cashbookBalanceByFund(Request $r, int $page, int $limit, string $q): array
    {
        return $this->cashbookClosing($r, $page, $limit, $q);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1709 — Account Payable
     * ══════════════════════════════════════════════════════════════════ */
    private function apBillRegistrationList(Request $r, int $page, int $limit, string $q): array
    {
        $creditNotes = $this->conn()->table('credit_note_ap_master')
            ->selectRaw('bim_bills_no, GROUP_CONCAT(cna_crnote_no ORDER BY cna_credit_note_ap_master_id SEPARATOR ", ") AS cna_crnote_no, SUM(COALESCE(cna_cn_total_amount, 0)) AS cna_cn_total_amount')
            ->whereRaw("IFNULL(cna_status_cd, '') <> 'CANCEL'")
            ->groupBy('bim_bills_no');

        $base = $this->conn()->table('bills_master as bm')
            ->leftJoinSub($creditNotes, 'cn', function (JoinClause $join): void {
                $join->on('cn.bim_bills_no', '=', 'bm.bim_bills_no');
            })
            ->select([
                DB::raw('bm.bim_bills_id AS bim_bills_id'),
                DB::raw('bm.bim_system_id AS bim_system_id'),
                DB::raw('bm.bim_bills_no AS bim_bills_no'),
                DB::raw('bm.grm_receive_no AS grm_receive_no'),
                DB::raw('bm.rqm_requisition_no AS rqm_requisition_no'),
                DB::raw('bm.bim_payee_count AS bim_payee_count'),
                DB::raw('bm.bim_payto_id AS bim_payto_id'),
                DB::raw('bm.bim_payto_name AS bim_payto_name'),
                DB::raw('bm.bim_bills_desc AS bim_bills_desc'),
                DB::raw('bm.bim_cust_invoice_no AS bim_cust_invoice_no'),
                DB::raw('bm.bim_cust_invoice_date AS bim_cust_invoice_date'),
                DB::raw('bm.bim_ent_amt AS bim_ent_amt'),
                DB::raw('bm.bim_bill_amt AS bim_bill_amt'),
                DB::raw('cn.cna_crnote_no AS cna_crnote_no'),
                DB::raw('cn.cna_cn_total_amount AS cna_cn_total_amount'),
                DB::raw('(COALESCE(bm.bim_bill_amt, 0) - COALESCE(cn.cna_cn_total_amount, 0)) AS bim_balance'),
                DB::raw('bm.bim_status AS bim_status'),
                DB::raw("CASE WHEN IFNULL(bm.bim_system_id, '') = '' THEN 'NO' ELSE 'YES' END AS isMigration"),
                DB::raw('bm.createddate AS createddate'),
                DB::raw('bm.bim_approve_date AS bim_approve_date'),
                DB::raw('bm.bim_currency_code AS bim_currency_code'),
            ])
            ->orderByDesc('bm.bim_bills_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(bm.bim_bills_no,''), IFNULL(bm.grm_receive_no,''), IFNULL(bm.rqm_requisition_no,''), IFNULL(bm.bim_payto_id,''), IFNULL(bm.bim_payto_name,''), IFNULL(bm.bim_bills_desc,''), IFNULL(bm.bim_cust_invoice_no,''), IFNULL(bm.bim_status,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_bill_registration_list']);
    }

    private function apBillsList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apBillRegistrationList($r, $page, $limit, $q);
    }

    private function apBillCancelKnockoff(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('bills_master as bm')
            ->where('bm.bim_status', 'CANCEL')
            ->select([
                'bm.bim_bills_id',
                'bm.bim_bills_no',
                'bm.bim_bills_desc',
                'bm.vcs_vendor_code',
                'bm.bim_bill_amt',
                'bm.bim_status',
            ])
            ->orderByDesc('bm.bim_bills_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bm.bim_bills_no,''), IFNULL(bm.vcs_vendor_code,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_bill_cancel_knockoff']);
    }

    private function apBillCancelListing(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apBillCancelKnockoff($r, $page, $limit, $q);
    }

    private function apBillDaysReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_bill_days_report');
    }

    /**
     * Account Payable / Report / Bill Report (MENUID 2895).
     *
     * Legacy: AS_BL_AP_KPT_REPORT.dt_kpt_list=1.
     * UNION across bills_master + temp_advance_bills_master + temp_emergencyfund_bills_master
     * + temp_refund_bills_master, then LEFT JOINed to voucher_master/details, payment_record,
     * payment_batch, plus 11 status-scoped joins on wf_application_status to expose the
     * Bill / Voucher / BFT lifecycle dates (Check, Endorse, Receive, Verify, Approve, Entry, etc.).
     */
    private function apBillReport(Request $r, int $page, int $limit, string $q): array
    {
        $conn = $this->conn();

        $main = $this->apBillReportSubQuery($conn, 'bills_details', 'bills_master');
        $adv = $this->apBillReportSubQuery($conn, 'temp_advance_bills_details', 'temp_advance_bills_master');
        $ef = $this->apBillReportSubQuery($conn, 'temp_emergencyfund_bills_details', 'temp_emergencyfund_bills_master');
        $rf = $this->apBillReportSubQuery($conn, 'temp_refund_bills_details', 'temp_refund_bills_master');

        $inner = $main->union($adv)->union($ef)->union($rf);

        $statusJoins = [
            ['wch', 'tbl.bim_bills_no', 'CHECK'],
            ['wen', 'tbl.bim_bills_no', 'ENDORSE'],
            ['wre', 'tbl.bim_bills_no', 'RECEIVE'],
            ['wve', 'tbl.bim_bills_no', 'VERIFIED'],
            ['wap', 'tbl.bim_bills_no', 'APPROVE'],
            ['ven', 'tbl.bim_voucher_no', 'ENTRY'],
            ['vve', 'tbl.bim_voucher_no', 'VERIFIED'],
            ['vap', 'tbl.bim_voucher_no', 'APPROVE'],
            ['ben', 'pre.pre_payment_batch', 'ENTRY'],
            ['bve', 'pre.pre_payment_batch', 'VERIFIED'],
            ['bap', 'pre.pre_payment_batch', 'APPROVE'],
        ];

        $base = $conn->query()
            ->fromSub($inner, 'tbl')
            ->leftJoin('voucher_master as vma', function (JoinClause $j): void {
                $j->on('tbl.bim_voucher_no', '=', 'vma.vma_voucher_no')
                    ->whereNotIn('vma.vma_vch_status', ['ERROR', 'DRAFT']);
            })
            ->leftJoin('voucher_details as vde', function (JoinClause $j): void {
                $j->on('vma.vma_voucher_id', '=', 'vde.vma_voucher_id')
                    ->where('vde.vde_trans_type', 'CR');
            })
            ->leftJoin('payment_record as pre', function (JoinClause $j): void {
                $j->on('pre.pre_voucher_no', '=', 'vma.vma_voucher_no')
                    ->on('pre.pre_payment_no', '=', 'vde.vde_payment_no');
            })
            ->leftJoin('payment_batch as pb', 'pre.pre_payment_batch_id', '=', 'pb.pyb_pybatch_id');

        foreach ($statusJoins as [$alias, $idCol, $status]) {
            $base->leftJoin('wf_application_status as '.$alias, function (JoinClause $j) use ($alias, $idCol, $status): void {
                $j->on($idCol, '=', $alias.'.was_application_id')
                    ->where($alias.'.was_status', $status);
            });
        }

        $base->distinct()
            ->selectRaw(
                "tbl.bim_bills_no AS bim_bills_no,
                tbl.bim_bills_desc AS bim_bills_desc,
                tbl.bim_status AS bim_status,
                DATE_FORMAT(tbl.entry_date, '%d/%m/%Y') AS entry_date,
                tbl.bid_payto_id AS bid_payto_id,
                tbl.bid_payto_name AS bid_payto_name,
                tbl.bim_cust_invoice_no AS bim_cust_invoice_no,
                tbl.vsa_bank_accno AS vsa_bank_accno,
                tbl.third_party_id AS third_party_id,
                pre.pre_payto_id AS pre_payto_id,
                pre.pre_payto_name AS pre_payto_name,
                pre.pre_bank_name AS pre_bank_name,
                pre.acm_acct_code_bank AS acm_acct_code_bank,
                DATE_FORMAT(wch.createddate, '%d/%m/%Y') AS Bill_Check_Date,
                DATE_FORMAT(wen.createddate, '%d/%m/%Y') AS Bill_Endorsed_Date,
                DATE_FORMAT(wre.createddate, '%d/%m/%Y') AS Bill_Received_Date,
                DATE_FORMAT(wve.createddate, '%d/%m/%Y') AS Bill_Verify_Date,
                DATE_FORMAT(wap.createddate, '%d/%m/%Y') AS Bill_Approve_Date,
                tbl.bim_voucher_no AS bim_voucher_no,
                vma.vma_vch_status AS vma_vch_status,
                DATE_FORMAT(ven.createddate, '%d/%m/%Y') AS Voucher_Entry_Date,
                DATE_FORMAT(vve.createddate, '%d/%m/%Y') AS Voucher_Verify_date,
                DATE_FORMAT(vap.createddate, '%d/%m/%Y') AS Voucher_Approve_date,
                pre.pre_payment_no AS pre_payment_no,
                DATE_FORMAT(pre.pre_bankin_date, '%d/%m/%Y') AS pre_bankin_date,
                pre.pre_payment_batch AS pre_payment_batch,
                pb.pyb_status AS pyb_status,
                DATE_FORMAT(ben.createddate, '%d/%m/%Y') AS BFT_Entry_Date,
                DATE_FORMAT(bve.createddate, '%d/%m/%Y') AS BFT_Verify_Date,
                DATE_FORMAT(bap.createddate, '%d/%m/%Y') AS BFT_Approve_Date,
                DATE_FORMAT(pb.pyb_transfer_date, '%d/%m/%Y') AS pyb_transfer_date,
                FORMAT(pre.pre_total_amt, 2) AS pre_total_amt,
                CONCAT_WS('__',
                    IFNULL(tbl.bim_bills_no, ''),
                    IFNULL(tbl.bim_bills_desc, ''),
                    IFNULL(tbl.bim_status, ''),
                    IFNULL(DATE_FORMAT(tbl.entry_date, '%d/%m/%Y'), ''),
                    IFNULL(tbl.bid_payto_id, ''),
                    IFNULL(tbl.bid_payto_name, ''),
                    IFNULL(tbl.vsa_bank_accno, ''),
                    IFNULL(tbl.third_party_id, ''),
                    IFNULL(pre.pre_payto_id, ''),
                    IFNULL(pre.pre_payto_name, ''),
                    IFNULL(tbl.bim_cust_invoice_no, ''),
                    IFNULL(pre.pre_bank_name, ''),
                    IFNULL(pre.acm_acct_code_bank, ''),
                    IFNULL(tbl.bim_voucher_no, ''),
                    IFNULL(vma.vma_vch_status, ''),
                    IFNULL(pre.pre_payment_no, ''),
                    IFNULL(pre.pre_payment_batch, ''),
                    IFNULL(pb.pyb_status, '')
                ) AS _search_concat"
            );

        $wrapped = $conn->query()
            ->fromSub($base, 'rep')
            ->orderBy('bim_bills_no')
            ->orderBy('bid_payto_name');

        if ($q !== '') {
            $needle = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $wrapped->whereRaw('LOWER(IFNULL(_search_concat, \'\')) LIKE ?', [$needle]);
        }

        $pack = $this->paginate($wrapped, $page, $limit);
        $pack['rows'] = array_map(static function (array $row): array {
            unset($row['_search_concat']);

            return $row;
        }, $pack['rows']);

        return array_merge($pack, ['connector' => 'ap_bill_report']);
    }

    /**
     * One UNION branch for the AP Bill Report inner subquery.
     * Mirrors the legacy 4-way UNION across bills + temp_advance/emergency/refund.
     */
    private function apBillReportSubQuery(Connection $conn, string $detailsTable, string $masterTable): Builder
    {
        return $conn->table($detailsTable.' as bd')
            ->leftJoin($masterTable.' as bm', 'bd.bim_bills_id', '=', 'bm.bim_bills_id')
            ->whereNotNull('bm.bim_bills_no')
            ->distinct()
            ->selectRaw(
                'bm.bim_bills_no AS bim_bills_no,
                bm.bim_bills_desc AS bim_bills_desc,
                bm.bim_status AS bim_status,
                bm.createddate AS entry_date,
                bd.bid_payto_id AS bid_payto_id,
                bd.bid_payto_type AS bid_payto_type,
                bd.bid_payto_name AS bid_payto_name,
                bm.bim_cust_invoice_no AS bim_cust_invoice_no,
                bd.vsa_bank_accno AS vsa_bank_accno,
                bd.bid_factoring_name AS third_party_id,
                bm.bim_voucher_no AS bim_voucher_no'
            );
    }

    private function apVoucherRegistration(Request $r, int $page, int $limit, string $q): array
    {
        // voucher_master: vma_voucher_id, vma_voucher_no, vma_payto_name, vma_total_amt, vma_vch_status (verified)
        $base = $this->conn()->table('voucher_master as vm')
            ->select([
                'vm.vma_voucher_id',
                'vm.vma_voucher_no',
                'vm.vma_payto_name',
                'vm.vma_payto_id',
                'vm.vma_total_amt',
                'vm.vma_vch_status',
                'vm.oun_code',
                'vm.createddate',
            ])
            ->orderByDesc('vm.vma_voucher_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_registration']);
    }

    private function apVoucherListing(Request $r, int $page, int $limit, string $q): array
    {
        // Voucher Listing (menu 2297) — dtKey: vma_voucher_no, vma_payto_id, vma_payto_name,
        // bim_bills_no, vma_currency_code, vma_vch_description, vma_total_amt, createddate,
        // vma_vch_status, vde_cust_invoice_no
        $base = $this->conn()->table('voucher_master as vm')
            ->leftJoin('voucher_details as vde', function ($j) {
                $j->on('vde.vma_voucher_id', '=', 'vm.vma_voucher_id')
                  ->where('vde.vde_trans_type', 'DT');
            })
            ->select([
                'vm.vma_voucher_id',
                'vm.vma_voucher_no',
                'vm.vma_payto_id',
                'vm.vma_payto_name',
                DB::raw('MIN(vde.bim_bills_no) AS bim_bills_no'),
                'vm.vma_currency_code',
                'vm.vma_vch_description',
                'vm.vma_total_amt',
                'vm.createddate',
                'vm.vma_vch_status',
                DB::raw('MIN(vde.vde_cust_invoice_no) AS vde_cust_invoice_no'),
            ])
            ->groupBy([
                'vm.vma_voucher_id', 'vm.vma_voucher_no', 'vm.vma_payto_id',
                'vm.vma_payto_name', 'vm.vma_currency_code', 'vm.vma_vch_description',
                'vm.vma_total_amt', 'vm.createddate', 'vm.vma_vch_status',
            ])
            ->orderByDesc('vm.vma_voucher_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_id,''), IFNULL(vm.vma_payto_name,''), IFNULL(vm.vma_vch_description,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_listing']);
    }

    private function apVoucherCancel(Request $r, int $page, int $limit, string $q): array
    {
        // Voucher Cancel (menu 2298) — dtKey: vma_voucher_id, vma_voucher_no,
        // vma_vch_description, vma_total_amt, vma_payto_type, vma_payto_id, vma_payto_name
        // Shows vouchers eligible to be cancelled (APPROVE/ENTRY/VERIFIED status)
        $base = $this->conn()->table('voucher_master as vm')
            ->whereIn('vm.vma_vch_status', ['APPROVE', 'ENTRY', 'VERIFIED'])
            ->select([
                'vm.vma_voucher_id',
                'vm.vma_voucher_no',
                'vm.vma_vch_description',
                'vm.vma_total_amt',
                'vm.vma_payto_type',
                'vm.vma_payto_id',
                'vm.vma_payto_name',
            ])
            ->orderByDesc('vm.vma_voucher_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_id,''), IFNULL(vm.vma_payto_name,''), IFNULL(vm.vma_vch_description,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_cancel']);
    }

    private function apVoucherCancelJournal(Request $r, int $page, int $limit, string $q): array
    {
        // Journal Voucher Cancel (menu 2412) — dtKey: vma_voucher_id, mjm_journal_no,
        // mjm_journal_desc, mjm_typeofjournal, mjm_enterdate, mjm_total_amt, mjm_status,
        // mjm_system_id, createdby, mjm_journal_id
        $base = $this->conn()->table('manual_journal_master as mjm')
            ->leftJoin('voucher_master as vm', 'vm.mjm_journal_id', '=', 'mjm.mjm_journal_id')
            ->select([
                DB::raw('MIN(vm.vma_voucher_id) AS vma_voucher_id'),
                'mjm.mjm_journal_id',
                'mjm.mjm_journal_no',
                'mjm.mjm_journal_desc',
                'mjm.mjm_typeofjournal',
                'mjm.mjm_enterdate',
                'mjm.mjm_total_amt',
                'mjm.mjm_status',
                'mjm.mjm_system_id',
                'mjm.createdby',
            ])
            ->groupBy([
                'mjm.mjm_journal_id', 'mjm.mjm_journal_no', 'mjm.mjm_journal_desc',
                'mjm.mjm_typeofjournal', 'mjm.mjm_enterdate', 'mjm.mjm_total_amt',
                'mjm.mjm_status', 'mjm.mjm_system_id', 'mjm.createdby',
            ])
            ->orderByDesc('mjm.mjm_journal_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(mjm.mjm_journal_no,''), IFNULL(mjm.mjm_journal_desc,''), IFNULL(mjm.mjm_system_id,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_cancel_journal']);
    }

    private function apVoucherReplace(Request $r, int $page, int $limit, string $q): array
    {
        // Voucher Replace (menu 2337) — exact port of the legacy SQL
        // (BL: AS_BL_AP_VOUCHERREPLACE).
        //
        //   FROM manual_journal_master mjm,
        //        manual_journal_details mjd,
        //        voucher_master vm,
        //        voucher_details vd
        //   WHERE vm.vma_voucher_no = mjm.mjm_extended_field ->> '$.Voucher_No'
        //     AND mjm.mjm_journal_id = mjd.mjm_journal_no  -- legacy: column name is misleading, holds the FK ID
        //     AND vm.vma_voucher_id = vd.vma_voucher_id
        //     AND vd.vde_payto_id   = mjd.mjd_payto_id
        //     AND mjm.mjm_system_id = 'PAYMENT_REPLACE'
        //     AND mjm.mjm_status IN ('APPROVE','APPROVED')
        //     AND (vd.flag_vch_replace IS NULL OR vd.flag_vch_replace = 'N')
        //
        // The voucher number is NOT a FK column — it lives in the JSON field
        // mjm.mjm_extended_field at $.Voucher_No and is matched by string
        // equality to voucher_master.vma_voucher_no.
        //
        // The 4-table join multiplies each journal by every matching line item
        // (manual_journal_details × voucher_details), so SELECT DISTINCT is
        // required and the count must use COUNT(DISTINCT mjm_journal_id) — the
        // generic paginate() helper would otherwise report inflated totals.
        $applyJoinsAndFilters = function ($q1) use ($q) {
            $q1->join('manual_journal_details as mjd', 'mjm.mjm_journal_id', '=', 'mjd.mjm_journal_no')
                ->join('voucher_master as vm', function ($j) {
                    $j->whereRaw("vm.vma_voucher_no = mjm.mjm_extended_field ->> '$.Voucher_No'");
                })
                ->join('voucher_details as vd', function ($j) {
                    $j->on('vd.vma_voucher_id', '=', 'vm.vma_voucher_id')
                      ->on('vd.vde_payto_id', '=', 'mjd.mjd_payto_id');
                })
                ->where('mjm.mjm_system_id', 'PAYMENT_REPLACE')
                ->whereIn('mjm.mjm_status', ['APPROVE', 'APPROVED'])
                ->where(function ($w) {
                    $w->whereNull('vd.flag_vch_replace')
                      ->orWhere('vd.flag_vch_replace', 'N');
                });

            if ($q !== '') {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $q1->whereRaw(
                    "LOWER(CONCAT_WS('|', "
                    . "IFNULL(mjm.mjm_journal_no,''), "
                    . "IFNULL(mjm.mjm_journal_desc,''), "
                    . "IFNULL(vm.vma_subsystem_code,''), "
                    . "IFNULL(mjm.mjm_typeofjournal,''), "
                    . "IFNULL(mjm.mjm_total_amt,''), "
                    . "IFNULL(mjm.mjm_extended_field ->> '$.Voucher_No', ''), "
                    . "IFNULL(mjm.mjm_reverse_status,'')"
                    . ")) LIKE ?",
                    [$like]
                );
            }
        };

        $countQuery = $this->conn()->table('manual_journal_master as mjm');
        $applyJoinsAndFilters($countQuery);
        $total = (int) $countQuery
            ->selectRaw('COUNT(DISTINCT mjm.mjm_journal_id) as aggregate')
            ->value('aggregate');

        $rowsQuery = $this->conn()->table('manual_journal_master as mjm');
        $applyJoinsAndFilters($rowsQuery);
        $rows = $rowsQuery
            ->select([
                'mjm.mjm_journal_id',
                'mjm.mjm_journal_no',
                'mjm.mjm_journal_desc',
                'vm.vma_subsystem_code',
                'mjm.mjm_typeofjournal',
                'mjm.mjm_total_amt',
                'mjm.mjm_status',
                'mjm.mjm_system_id',
                'mjm.mjm_enterdate',
                DB::raw("mjm.mjm_extended_field ->> '$.Voucher_No' AS VoucherNo"),
            ])
            ->distinct()
            ->orderByDesc('mjm.mjm_journal_no')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toArray();

        return [
            'rows' => $rows,
            'total' => $total,
            'connector' => 'ap_voucher_replace',
        ];
    }

    private function apVoucherReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_voucher_report');
    }

    private function apVoucherProcess(Request $r, int $page, int $limit, string $q): array
    {
        // Voucher Process (menu 3535) — dtKey: vma_voucher_no, bim_bills_no, vma_payto,
        // vma_vch_description, vma_total_amt
        // task_id filter comes from topFilter / URL; here we list all DRAFT/ENTRY vouchers
        $base = $this->conn()->table('voucher_master as vm')
            ->leftJoin('voucher_details as vde', function ($j) {
                $j->on('vde.vma_voucher_id', '=', 'vm.vma_voucher_id')
                  ->where('vde.vde_trans_type', 'DT');
            })
            ->whereIn('vm.vma_vch_status', ['DRAFT', 'ENTRY'])
            ->select([
                'vm.vma_voucher_id',
                'vm.vma_voucher_no',
                DB::raw('MIN(vde.bim_bills_no) AS bim_bills_no'),
                DB::raw("COALESCE(vm.vma_payto_name, vm.vma_payto_id) AS vma_payto"),
                'vm.vma_vch_description',
                'vm.vma_total_amt',
            ])
            ->groupBy([
                'vm.vma_voucher_id', 'vm.vma_voucher_no',
                'vm.vma_payto_name', 'vm.vma_payto_id',
                'vm.vma_vch_description', 'vm.vma_total_amt',
            ])
            ->orderByDesc('vm.vma_voucher_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_name,''), IFNULL(vm.vma_vch_description,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_process']);
    }

    private function apVoucherInfoCreditor(Request $r, int $page, int $limit, string $q): array
    {
        // Voucher Information Creditor (menu 3546) — dtKey: vma_voucher_no, vde_voucher_detl_id,
        // vde_payment_no, vde_payto_type, vde_payto_id, vde_payto_name, bank_name (=vde_bank_name),
        // vde_bank_acctno, vde_factoring_type, vde_factoring_id, vde_factoring_name,
        // factoring_bank_name (=vde_fact_bank_name), vde_fact_bank_acctno, vde_status
        $voucherNo = $r->input('voucher_no', '');

        $base = $this->conn()->table('voucher_details as vde')
            ->join('voucher_master as vm', 'vm.vma_voucher_id', '=', 'vde.vma_voucher_id')
            ->select([
                'vm.vma_voucher_no',
                'vde.vde_voucher_detl_id',
                'vde.vde_payment_no',
                'vde.vde_payto_type',
                'vde.vde_payto_id',
                'vde.vde_payto_name',
                DB::raw('vde.vde_bank_name AS bank_name'),
                'vde.vde_bank_acctno',
                'vde.vde_factoring_type',
                'vde.vde_factoring_id',
                'vde.vde_factoring_name',
                DB::raw('vde.vde_fact_bank_name AS factoring_bank_name'),
                'vde.vde_fact_bank_acctno',
                'vde.vde_status',
                'vde.vde_trans_type',
            ])
            ->orderByDesc('vde.vde_voucher_detl_id');

        if ($voucherNo !== '') {
            $base->where('vm.vma_voucher_no', $voucherNo);
        }
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vde.vde_payto_id,''), IFNULL(vde.vde_payto_name,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_info_creditor']);
    }

    private function apVoucherMoneyTransferList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('voucher_master as vm')
            ->leftJoin('money_transfer_master as m', 'm.mtm_application_no', '=', 'vm.mtm_application_no')
            ->whereNotNull('vm.mtm_application_no')
            ->where('vm.mtm_application_no', '!=', '')
            ->select([
                'm.mtm_id',
                'vm.mtm_application_no',
                'vm.vma_voucher_no',
                'vm.vma_vch_status',
                'vm.createddate',
                'vm.vma_approve_date',
            ])
            ->orderByDesc('vm.vma_voucher_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vm.mtm_application_no,''), IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_vch_status,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_money_transfer_list']);
    }

    private function apOtherPayment(Request $r, int $page, int $limit, string $q, bool $includePaymentDate = false): array
    {
        $columns = [
            DB::raw('MIN(vd.vde_voucher_detl_id) AS groupId'),
            DB::raw('vm.vma_voucher_no AS vma_voucher_no'),
            DB::raw('vm.vma_approve_date AS approved_date'),
            DB::raw('vd.fty_fund_type AS fty_fund_type'),
            DB::raw('COALESCE(vd.vde_payto_type, vm.vma_payto_type) AS vde_payto_type'),
            DB::raw('COALESCE(vd.vde_payto_id, vm.vma_payto_id) AS vde_payto_id'),
            DB::raw('COALESCE(vd.vde_payto_name, vm.vma_payto_name) AS vde_payto_name'),
            DB::raw('vd.vde_bank_name AS vde_bank_name'),
            DB::raw('vd.vde_bank_acctno AS vde_bank_acctno'),
            DB::raw('vd.vde_factoring_type AS vde_factoring_type'),
            DB::raw('MAX(COALESCE(fact_type.lde_description2, fact_type.lde_description, vd.vde_factoring_type)) AS vde_factoring_type_desc'),
            DB::raw('vd.vde_factoring_id AS vde_factoring_id'),
            DB::raw('vd.vde_factoring_name AS vde_factoring_name'),
            DB::raw('MAX(COALESCE(fact_bank.lbm_bank_name, vd.vde_fact_bank_name)) AS vde_fact_bank_name_desc'),
            DB::raw('vd.vde_fact_bank_acctno AS vde_fact_bank_acctno'),
            DB::raw("CASE WHEN IFNULL(vd.vde_factoring_type, '') <> '' THEN CONCAT('Type: ', MAX(COALESCE(fact_type.lde_description2, fact_type.lde_description, vd.vde_factoring_type)), CHAR(10), 'ID: ', IFNULL(vd.vde_factoring_id,''), CHAR(10), 'Name: ', IFNULL(vd.vde_factoring_name,''), CHAR(10), 'Bank: ', MAX(COALESCE(fact_bank.lbm_bank_name, vd.vde_fact_bank_name)), CHAR(10), 'Acc No: ', IFNULL(vd.vde_fact_bank_acctno,'')) ELSE '' END AS factoring"),
            DB::raw('vd.vde_amount AS vde_amount'),
        ];

        if ($includePaymentDate) {
            $columns[] = DB::raw("(SELECT pr.pre_bankin_date FROM payment_record pr WHERE pr.pre_voucher_no = vm.vma_voucher_no ORDER BY pr.pre_payment_record_id DESC LIMIT 1) AS pre_bankin_date");
        }

        $base = $this->conn()->table('voucher_master as vm')
            ->join('voucher_details as vd', 'vd.vma_voucher_id', '=', 'vm.vma_voucher_id')
            ->leftJoin('lookup_details as fact_type', function (JoinClause $join): void {
                $join->on('fact_type.lde_value', '=', 'vd.vde_factoring_type')
                    ->where('fact_type.lma_code_name', '=', 'CUSTOMER_TYPE');
            })
            ->leftJoin('lookup_bank_main as fact_bank', 'fact_bank.lbm_bank_code', '=', 'vd.vde_fact_bank_name')
            ->where('vm.vma_vch_status', 'APPROVE')
            ->where('vd.vde_trans_type', 'DT')
            ->whereNull('vd.vde_paymode')
            ->select($columns)
            ->groupBy([
                'vm.vma_voucher_no',
                'vm.vma_approve_date',
                'vd.fty_fund_type',
                'vd.vde_payto_type',
                'vm.vma_payto_type',
                'vd.vde_payto_id',
                'vm.vma_payto_id',
                'vd.vde_payto_name',
                'vm.vma_payto_name',
                'vd.vde_bank_name',
                'vd.vde_bank_acctno',
                'vd.vde_factoring_type',
                'vd.vde_factoring_id',
                'vd.vde_factoring_name',
                'vd.vde_fact_bank_name',
                'vd.vde_fact_bank_acctno',
                'vd.vde_amount',
            ]);
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vd.vde_payto_id,''), IFNULL(vd.vde_payto_name,''), IFNULL(vd.vde_bank_name,''), IFNULL(vd.vde_factoring_name,''))) LIKE ?",
                [$like]
            );
        }

        $total = $this->conn()->query()->fromSub(clone $base, 'other_payment')->count();
        $rows = $base
            ->orderByDesc(DB::raw('MIN(vd.vde_voucher_detl_id)'))
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();

        return ['rows' => $rows, 'total' => $total, 'connector' => 'ap_other_payment'];
    }

    private function apOtherPaymentUpdate(Request $r, int $page, int $limit, string $q): array
    {
        return array_merge($this->apOtherPayment($r, $page, $limit, $q, true), ['connector' => 'ap_other_payment_update_date']);
    }

    private function apRefund(Request $r, int $page, int $limit, string $q): array
    {
        return array_merge(
            $this->refundApplication($r, $page, $limit, $q),
            ['top_filter_options' => $this->apRefundTopFilterOptions()],
        );
    }

    /**
     * Top-filter dropdown options for Account Payable / Integration / Refund (menu 1928).
     *
     * tf_0 — Type of Refund: pulled from lookup_details where lma_code_name = 'CUSTOMER_TYPE'
     *         (matches legacy NF_BL_AP_REFUND topFilter lookupQuery).
     * tf_1 — Bill Type: static INDIVIDU/BERKELOMPOK pair from the legacy lookupQuery.
     *
     * @return array<string, list<int, array{value: string, label: string}>>
     */
    private function apRefundTopFilterOptions(): array
    {
        try {
            $customerTypes = $this->conn()->table('lookup_details')
                ->where('lma_code_name', 'CUSTOMER_TYPE')
                ->whereRaw("TRIM(IFNULL(lde_value, '')) <> ''")
                ->orderByRaw('IFNULL(lde_description2, lde_description)')
                ->select(['lde_value', 'lde_description', 'lde_description2'])
                ->get();
        } catch (\Throwable) {
            $customerTypes = collect();
        }

        $tf0 = $customerTypes->map(function ($row): array {
            $value = (string) ($row->lde_value ?? '');
            $label = trim((string) ($row->lde_description2 ?? $row->lde_description ?? $row->lde_value ?? ''));

            return [
                'value' => $value,
                'label' => $label !== '' ? $label : $value,
            ];
        })->values()->all();

        $tf1 = [
            ['value' => 'I', 'label' => 'INDIVIDU'],
            ['value' => 'B', 'label' => 'BERKELOMPOK'],
        ];

        return [
            'tf_0' => $tf0,
            'tf_1' => $tf1,
        ];
    }

    private function apPayeeReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    /**
     * Account Payable / Report / Payee List Report by PTJ (menu 3133).
     *
     * Legacy: MM_API_AP_PAYEEREPORTBYPTJ.dt_listPayee=1
     * Joins voucher_details → voucher_master → payment_record → payment_batch → bills_master.
     * Groups by voucher/payment/payee to aggregate vde_amount per payment.
     *
     * Smart filter params:
     *   sf_0 — Payee Type     (exact match on vde_payto_type)
     *   sf_1 — Bill No.       (LIKE on bim_bills_no)
     *   sf_2 — Batch No.      (LIKE on pre_payment_batch)
     *   sf_3 — Date Entry     (LIKE on formatted createddate)
     *   sf_4 — Acct Code From (>= acm_acct_code)
     *   sf_5 — Acct Code To   (<= acm_acct_code)
     */
    private function apPayeeReportByPtj(Request $r, int $page, int $limit, string $q): array
    {
        $cx = $this->conn();

        $sfPayeeType = trim((string) $r->input('sf_0', ''));
        $sfBillNo    = trim((string) $r->input('sf_1', ''));
        $sfBatchNo   = trim((string) $r->input('sf_2', ''));
        $sfDate      = trim((string) $r->input('sf_3', ''));
        $sfAcctFrom  = trim((string) $r->input('sf_4', ''));
        $sfAcctTo    = trim((string) $r->input('sf_5', ''));

        $inner = $cx->table('voucher_details as vde')
            ->leftJoin('voucher_master as vma', 'vde.vma_voucher_id', '=', 'vma.vma_voucher_id')
            ->leftJoin('payment_record as pr', 'pr.pre_voucher_no', '=', 'vma.vma_voucher_no')
            ->leftJoin('payment_batch as pb', 'pb.pyb_batch_no', '=', 'pr.pre_payment_batch')
            ->leftJoin('bills_master as bm', 'vde.bim_bills_no', '=', 'bm.bim_bills_no')
            ->leftJoin('temp_refund_bills_master as trbm', 'trbm.bim_bills_no', '=', 'vde.bim_bills_no')
            ->where('vde.vde_trans_type', 'CR')
            ->whereRaw('vde.vde_payment_no = pr.pre_payment_no')
            ->selectRaw("
                vma.vma_voucher_id AS vma_voucher_id,
                vma.vma_voucher_no AS vma_voucher_no,
                IFNULL(vde.bim_bills_no, '') AS bim_bills_no,
                vde.acm_acct_code AS acm_acct_code,
                bm.bim_cust_invoice_no AS bim_cust_invoice_no,
                bm.grm_receive_no AS grm_receive_no,
                DATE_FORMAT(bm.createddate, '%d/%m/%Y') AS createddate,
                CASE WHEN vma.vma_subsystem_code = 'REFUND' THEN trbm.bim_bills_id ELSE bm.bim_bills_id END AS billid,
                vde.vde_payment_no AS vde_payment_no,
                vde.vde_payto_name AS vde_payto_name,
                vma.vma_subsystem_code AS mastersystemcode,
                CASE WHEN vma.vma_subsystem_code = 'REFUND' THEN trbm.bim_system_id ELSE vma.vma_subsystem_code END AS subsystemcode,
                vde.vde_payto_type AS vde_payto_type,
                vde.vde_payto_id AS vde_payto_id,
                pr.pre_payment_batch AS pre_payment_batch,
                pb.pyb_transfer_date AS pyb_transfer_date,
                vde.vde_pybatch_id AS vde_pybatch_id,
                pb.pyb_pybatch_id AS pyb_pybatch_id,
                bm.bim_status AS bim_status,
                SUM(vde.vde_amount) AS vde_amount,
                SUBSTRING(pr.pre_payment_batch, 1, 3) AS batchcode,
                DATE_FORMAT(bm.createddate, '%Y/%m/%d') AS sort
            ")
            ->groupByRaw("
                vma.vma_voucher_id, vma.vma_voucher_no, vde.bim_bills_no, vde.acm_acct_code,
                bm.bim_cust_invoice_no, bm.grm_receive_no, bm.createddate,
                CASE WHEN vma.vma_subsystem_code = 'REFUND' THEN trbm.bim_bills_id ELSE bm.bim_bills_id END,
                vde.vde_payment_no, vde.vde_payto_name, vma.vma_subsystem_code,
                CASE WHEN vma.vma_subsystem_code = 'REFUND' THEN trbm.bim_system_id ELSE vma.vma_subsystem_code END,
                vde.vde_payto_type, vde.vde_payto_id, pr.pre_payment_batch, pb.pyb_transfer_date,
                vde.vde_pybatch_id, pb.pyb_pybatch_id, bm.bim_status,
                SUBSTRING(pr.pre_payment_batch, 1, 3)
            ");

        if ($sfPayeeType !== '') {
            $inner->where('vde.vde_payto_type', $sfPayeeType);
        }
        if ($sfBillNo !== '') {
            $like = $this->likeEscape(mb_strtolower($sfBillNo, 'UTF-8'));
            $inner->whereRaw("LOWER(IFNULL(vde.bim_bills_no, '')) LIKE ?", [$like]);
        }
        if ($sfBatchNo !== '') {
            $like = $this->likeEscape(mb_strtolower($sfBatchNo, 'UTF-8'));
            $inner->whereRaw("LOWER(IFNULL(pr.pre_payment_batch, '')) LIKE ?", [$like]);
        }
        if ($sfDate !== '') {
            $like = $this->likeEscape(mb_strtolower($sfDate, 'UTF-8'));
            $inner->whereRaw("LOWER(DATE_FORMAT(bm.createddate, '%d/%m/%Y')) LIKE ?", [$like]);
        }
        if ($sfAcctFrom !== '') {
            $inner->whereRaw("IFNULL(vde.acm_acct_code, '') >= ?", [$sfAcctFrom]);
        }
        if ($sfAcctTo !== '') {
            $inner->whereRaw("IFNULL(vde.acm_acct_code, '') <= ?", [$sfAcctTo]);
        }

        $base = $cx->query()->fromSub($inner, 'XX');

        if ($q !== '') {
            $needle = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|',
                    IFNULL(vde_payto_type,''), IFNULL(vde_payto_id,''), IFNULL(vde_payto_name,''),
                    IFNULL(bim_bills_no,''), IFNULL(acm_acct_code,''), IFNULL(bim_cust_invoice_no,''),
                    IFNULL(bim_status,''), IFNULL(vma_voucher_no,''), IFNULL(vde_payment_no,''),
                    IFNULL(pre_payment_batch,'')
                )) LIKE ?",
                [$needle]
            );
        }

        $base->orderBy('sort')->orderBy('acm_acct_code');

        try {
            $pack = $this->paginate($base, $page, $limit);
        } catch (\Throwable $e) {
            return ['rows' => [], 'total' => 0, 'connector' => 'ap_payee_report_by_ptj', 'shellError' => $e->getMessage()];
        }

        $pack['rows'] = array_map(static function (array $row): array {
            if (isset($row['pyb_transfer_date']) && $row['pyb_transfer_date'] !== null && $row['pyb_transfer_date'] !== '') {
                try {
                    $row['pyb_transfer_date'] = \Carbon\Carbon::parse($row['pyb_transfer_date'])->format('d/m/Y');
                } catch (\Throwable) {
                    $row['pyb_transfer_date'] = (string) $row['pyb_transfer_date'];
                }
            }
            return $row;
        }, $pack['rows']);

        return array_merge($pack, ['connector' => 'ap_payee_report_by_ptj']);
    }

    /**
     * Account Payable / Report / Payee List Report by PTJ — secondary "List Payment" rows.
     *
     * Called by KerisiRemainingController::apPayeeReportByPtjPaymentDetails().
     * Returns payment record rows for a given vde_payment_no, joined to voucher + bank info.
     */
    public function apPayeeReportByPtjPaymentRows(string $paymentNo): array
    {
        if ($paymentNo === '') {
            return [];
        }

        try {
            return $this->conn()->table('payment_record as pr')
                ->leftJoin('voucher_master as vma', 'vma.vma_voucher_no', '=', 'pr.pre_voucher_no')
                ->leftJoin('bank_master as bm', 'bm.bnm_bank_code', '=', 'pr.pre_bank_name')
                ->leftJoin('lookup_bank_main as lbm', 'lbm.lbm_bank_code', '=', 'pr.pre_bank_name')
                ->where('pr.pre_payment_no', $paymentNo)
                ->select([
                    DB::raw("pr.pre_payment_no AS pre_payment_no"),
                    DB::raw("COALESCE(pr.pre_payto_name, '') AS vde_payto_name"),
                    DB::raw("COALESCE(pr.acm_acct_code_bank, '') AS noacc"),
                    DB::raw("COALESCE(bm.bnm_bank_desc, lbm.lbm_bank_name, pr.pre_bank_name, '') AS bnm_bank_desc"),
                    DB::raw("COALESCE(pr.pre_total_amt_rm, pr.pre_total_amt, 0) AS AMOUNT"),
                    DB::raw("COALESCE(vma.vma_voucher_no, pr.pre_voucher_no, '') AS vma_voucher_no"),
                    DB::raw("COALESCE(vma.vma_vch_description, '') AS vma_vch_description"),
                    DB::raw("'' AS pom_order_no"),
                    DB::raw("'' AS reportpdf"),
                ])
                ->get()
                ->map(fn($r) => (array) $r)
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Account Payable / Report / Listing of Payee old (MENUID 2766).
     *
     * Legacy: AM_ACCPAYABLE_REPORT_LISTINGOFPAYEE.dt_listPayee=1.
     * UNION across bills_master + temp_advance_bills_master + temp_emergencyfund_bills_master
     * + temp_refund_bills_master, joined to voucher_master/details, payment_record, payment_batch.
     * Bank description resolved from bank_master via vsa_vendor_bank lookup.
     */
    private function apPayeeListing(Request $r, int $page, int $limit, string $q): array
    {
        $conn = $this->conn();

        $main = $this->apPayeeListingSubQuery($conn, 'bills_details', 'bills_master');
        $adv = $this->apPayeeListingSubQuery($conn, 'temp_advance_bills_details', 'temp_advance_bills_master');
        $ef = $this->apPayeeListingSubQuery($conn, 'temp_emergencyfund_bills_details', 'temp_emergencyfund_bills_master');
        $rf = $this->apPayeeListingSubQuery($conn, 'temp_refund_bills_details', 'temp_refund_bills_master');

        $union = $main->union($adv)->union($ef)->union($rf);

        $base = $conn->query()
            ->fromSub($union, 'tbl')
            ->orderBy('bim_bills_no')
            ->orderBy('bid_payto_name');

        if ($q !== '') {
            $needle = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw('LOWER(IFNULL(_search_concat, \'\')) LIKE ?', [$needle]);
        }

        $pack = $this->paginate($base, $page, $limit);
        $pack['rows'] = array_map(static function (array $row): array {
            unset($row['_search_concat']);

            return $row;
        }, $pack['rows']);

        return array_merge($pack, ['connector' => 'ap_payee_listing']);
    }

    /**
     * Build one branch of the Listing of Payee UNION.
     * All branches share the same column set so the wrapper SELECT works.
     */
    private function apPayeeListingSubQuery(Connection $conn, string $detailsTable, string $masterTable): Builder
    {
        return $conn->table($detailsTable.' as bd')
            ->join($masterTable.' as bm', 'bd.bim_bills_id', '=', 'bm.bim_bills_id')
            ->join('voucher_master as vma', function (JoinClause $j): void {
                $j->on('bm.bim_voucher_no', '=', 'vma.vma_voucher_no')
                    ->whereNotIn('vma.vma_vch_status', ['ERROR', 'DRAFT']);
            })
            ->join('voucher_details as vde', function (JoinClause $j): void {
                $j->on('vma.vma_voucher_id', '=', 'vde.vma_voucher_id')
                    ->on('bd.bid_payto_id', '=', 'vde.vde_payto_id')
                    ->on('bd.bid_payto_type', '=', 'vde.vde_payto_type');
            })
            ->join('payment_record as pre', function (JoinClause $j): void {
                $j->on('pre.pre_voucher_no', '=', 'vma.vma_voucher_no')
                    ->on('pre.pre_payment_no', '=', 'vde.vde_payment_no')
                    ->on('pre.pre_payto_id', '=', 'vde.vde_payto_id')
                    ->on('pre.pre_payee_type', '=', 'vde.vde_payto_type');
            })
            ->join('payment_batch as pb', 'pre.pre_payment_batch', '=', 'pb.pyb_batch_no')
            ->whereNotNull('bm.bim_bills_no')
            ->distinct()
            ->selectRaw(
                "bm.bim_bills_no AS bim_bills_no,
                bm.bim_bills_desc AS bim_bills_desc,
                bm.bim_status AS bim_status,
                DATE_FORMAT(bm.createddate, '%d/%m/%Y') AS entry_date,
                bd.bid_payto_id AS bid_payto_id,
                bd.bid_payto_name AS bid_payto_name,
                (SELECT bnm_bank_desc FROM bank_master WHERE bnm_bank_code = bd.vsa_vendor_bank LIMIT 1) AS vsa_vendor_bank,
                bd.vsa_bank_accno AS vsa_bank_accno,
                bd.bid_factoring_name AS third_party_id,
                bm.bim_voucher_no AS bim_voucher_no,
                vma.vma_vch_status AS vma_vch_status,
                DATE_FORMAT(vma.createddate, '%d/%m/%Y') AS v_entry_date,
                DATE_FORMAT(vde.vde_transfer_date, '%d/%m/%Y') AS vde_transfer_date,
                pre.pre_payment_no AS pre_payment_no,
                DATE_FORMAT(pre.createddate, '%d/%m/%Y') AS eft_date,
                pre.pre_payment_batch AS pre_payment_batch,
                pb.pyb_status AS pyb_status,
                DATE_FORMAT(pb.createddate, '%d/%m/%Y') AS bft_date,
                pre.pre_total_amt AS pre_total_amt,
                pre.pre_status AS pre_status,
                CONCAT_WS('__',
                    IFNULL(vma.vma_voucher_no, ''),
                    IFNULL(bm.bim_bills_no, ''),
                    IFNULL(vde.vde_payment_no, ''),
                    IFNULL(vde.vde_payto_name, ''),
                    IFNULL(vde.vde_payto_type, ''),
                    IFNULL(pre.pre_payment_batch, '')
                ) AS _search_concat"
            );
    }

    private function apJournalBillCancel(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_journal_bill_cancel');
    }

    private function apIdentifiedReceiptsReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_identified_receipts_report');
    }

    private function apPaymentNotice(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payment_record as pr')
            ->select([
                DB::raw('pr.pre_payee_type AS vma_payto_type'),
                DB::raw('pr.pre_payto_id AS vma_payto_id'),
                DB::raw('pr.pre_payto_name AS vma_payto_name'),
                DB::raw('pr.pre_voucher_no AS pre_voucher_no'),
                DB::raw('pr.pre_payment_no AS pre_payment_no'),
                DB::raw('pr.pre_approve_date AS pre_approve_date'),
                DB::raw('(SELECT pb.pyb_transfer_date FROM payment_batch pb WHERE pb.pyb_pybatch_id = pr.pre_payment_batch_id OR pb.pyb_batch_no = pr.pre_payment_batch ORDER BY pb.pyb_pybatch_id DESC LIMIT 1) AS pyb_transfer_date'),
                DB::raw('COALESCE(pr.pre_total_amt_rm, pr.pre_total_amt) AS pre_total_amt_rm'),
                DB::raw('pr.pre_bank_name AS pre_bank_name'),
                DB::raw('pr.acm_acct_code_bank AS acm_acct_code_bank'),
            ])
            ->orderByDesc('pr.pre_payment_record_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pr.pre_payment_no,''), IFNULL(pr.pre_voucher_no,''), IFNULL(pr.pre_payto_id,''), IFNULL(pr.pre_payto_name,''), IFNULL(pr.pre_bank_name,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_payment_notice']);
    }

    private function apPrintSabBatch(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_print_sab_batch');
    }

    private function apPaymentListing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('payment_record as pr')
            ->select([
                DB::raw('pr.pre_payment_no AS pre_payment_no'),
                DB::raw('pr.pre_mod_type AS pre_mod_type'),
                DB::raw('pr.pre_payto_id AS pre_payto_id'),
                DB::raw('pr.pre_payto_name AS pre_payto_name'),
                DB::raw('pr.pre_total_amt AS pre_total_amt'),
                DB::raw('pr.pre_voucher_no AS pre_voucher_no'),
                DB::raw('pr.pre_status AS pre_status'),
                DB::raw('pr.pre_payment_batch AS pre_payment_batch'),
                DB::raw('pr.pre_print_date AS pre_print_date'),
                DB::raw('pr.pre_bankin_date AS pre_bankin_date'),
            ])
            ->orderByDesc('pr.pre_payment_record_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pr.pre_payment_no,''), IFNULL(pr.pre_voucher_no,''), IFNULL(pr.pre_payto_id,''), IFNULL(pr.pre_payto_name,''), IFNULL(pr.pre_status,''), IFNULL(pr.pre_payment_batch,''))) LIKE ?",
                [$like]
            );
        }

        $status = trim((string) $r->input('sf_1', ''));
        if ($status !== '') {
            $base->where('pr.pre_status', $status);
        }

        $pack = $this->paginate($base, $page, $limit);
        $statuses = $this->conn()->table('payment_record')
            ->whereNotNull('pre_status')
            ->distinct()
            ->orderBy('pre_status')
            ->pluck('pre_status')
            ->map(fn ($v) => ['value' => (string) $v, 'label' => (string) $v])
            ->values()->all();

        return array_merge($pack, [
            'connector' => 'ap_payment_listing',
            'smart_filter_options' => [
                'sf_1' => $statuses,
            ],
        ]);
    }

    private function apDownloadVoucherBatch(Request $r, int $page, int $limit, string $q): array
    {
        // Top-filter dropdown options:
        //   tf_0 = Filter By  ('batching' / 'voucher')
        //   tf_1 = Voucher No From   (visible when tf_0='voucher')
        //   tf_2 = Voucher No To     (visible when tf_0='voucher')
        //   tf_3 = Batch No          (visible when tf_0='batching')
        $conn = $this->conn();

        $filterByOpts = [
            ['value' => 'batching', 'label' => 'Batching No'],
            ['value' => 'voucher',  'label' => 'Voucher No'],
        ];

        // Voucher numbers — limit to APPROVE status (matching legacy use-case)
        $voucherOpts = $conn->table('voucher_master')
            ->where('vma_vch_status', 'APPROVE')
            ->orderByDesc('vma_voucher_id')
            ->limit(500)
            ->pluck('vma_voucher_no')
            ->filter()
            ->map(fn ($v) => ['value' => (string) $v, 'label' => (string) $v])
            ->values()
            ->all();

        // Batch numbers — payment_batch keyed by pyb_batch_no
        $batchOpts = $conn->table('payment_batch')
            ->orderByDesc('pyb_pybatch_id')
            ->limit(500)
            ->get(['pyb_batch_no', 'pyb_status'])
            ->filter(fn ($r) => ! empty($r->pyb_batch_no))
            ->map(fn ($r) => [
                'value' => (string) $r->pyb_batch_no,
                'label' => $r->pyb_status
                    ? sprintf('%s — %s', $r->pyb_batch_no, $r->pyb_status)
                    : (string) $r->pyb_batch_no,
            ])
            ->values()
            ->all();

        $tf0 = trim((string) $r->input('tf_0', ''));
        $tf1 = trim((string) $r->input('tf_1', ''));
        $tf2 = trim((string) $r->input('tf_2', ''));
        $tf3 = trim((string) $r->input('tf_3', ''));

        // Build matching voucher list when filter is applied
        $rows  = [];
        $total = 0;

        if ($tf0 === 'voucher' && ($tf1 !== '' || $tf2 !== '')) {
            $base = $conn->table('voucher_master as vm')
                ->select([
                    DB::raw('vm.vma_voucher_no AS `Voucher No`'),
                    DB::raw('vm.vma_payto_id   AS `Payee Code`'),
                    DB::raw('vm.vma_payto_name AS `Payee Name`'),
                    DB::raw('vm.vma_vch_description AS `Description`'),
                    DB::raw('vm.vma_total_amt  AS `Amount`'),
                    DB::raw('vm.vma_vch_status AS `Status`'),
                ])
                ->where('vm.vma_vch_status', 'APPROVE')
                ->orderBy('vm.vma_voucher_no');

            if ($tf1 !== '') {
                $base->where('vm.vma_voucher_no', '>=', $tf1);
            }
            if ($tf2 !== '') {
                $base->where('vm.vma_voucher_no', '<=', $tf2);
            }
            if ($q !== '') {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $base->whereRaw(
                    "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_name,''), IFNULL(vm.vma_vch_description,''))) LIKE ?",
                    [$like]
                );
            }

            $pack  = $this->paginate($base, $page, $limit);
            $rows  = $pack['rows'];
            $total = $pack['total'];
        } elseif ($tf0 === 'batching' && $tf3 !== '') {
            // Link batch → vouchers via voucher_details.vde_pybatch_id (legacy).
            $base = $conn->table('voucher_master as vm')
                ->join('voucher_details as vd', 'vd.vma_voucher_id', '=', 'vm.vma_voucher_id')
                ->join('payment_batch as pb',   'pb.pyb_pybatch_id', '=', 'vd.vde_pybatch_id')
                ->where('pb.pyb_batch_no', $tf3)
                ->groupBy(
                    'vm.vma_voucher_no', 'vm.vma_payto_id', 'vm.vma_payto_name',
                    'vm.vma_vch_description', 'vm.vma_total_amt', 'vm.vma_vch_status'
                )
                ->select([
                    DB::raw('vm.vma_voucher_no AS `Voucher No`'),
                    DB::raw('vm.vma_payto_id   AS `Payee Code`'),
                    DB::raw('vm.vma_payto_name AS `Payee Name`'),
                    DB::raw('vm.vma_vch_description AS `Description`'),
                    DB::raw('vm.vma_total_amt  AS `Amount`'),
                    DB::raw('vm.vma_vch_status AS `Status`'),
                ])
                ->orderBy('vm.vma_voucher_no');

            if ($q !== '') {
                $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
                $base->whereRaw(
                    "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_name,''), IFNULL(vm.vma_vch_description,''))) LIKE ?",
                    [$like]
                );
            }

            $pack  = $this->paginate($base, $page, $limit);
            $rows  = $pack['rows'];
            $total = $pack['total'];
        }

        return [
            'rows'      => $rows,
            'total'     => $total,
            'connector' => 'ap_download_voucher_batch',
            'top_filter_options' => [
                'tf_0' => $filterByOpts,
                'tf_1' => $voucherOpts,
                'tf_2' => $voucherOpts,
                'tf_3' => $batchOpts,
            ],
        ];
    }

    private function apDownloadVoucherByRef(Request $r, int $page, int $limit, string $q): array
    {
        // Download Voucher By Reference (menu 3534) — top filter:
        //   tf_0 = Created By     (dropdown — distinct createdby)
        //   tf_1 = Created Date   (custom — date input)
        //   tf_2 = Payment Method (dropdown — distinct vde_paymode)
        //   tf_3 = Reference No   (custom — text input)
        //   tf_4 = Status         (dropdown — legacy SQL on vde_status)
        $conn = $this->conn();

        // ── Dropdown options ──────────────────────────────────────────────────
        $createdByOpts = $conn->table('voucher_master')
            ->whereNotNull('createdby')
            ->where('createdby', '!=', '')
            ->groupBy('createdby')
            ->orderBy('createdby')
            ->pluck('createdby')
            ->map(fn ($v) => ['value' => (string) $v, 'label' => (string) $v])
            ->values()
            ->all();

        $paymodeOpts = $conn->table('voucher_details')
            ->whereNotNull('vde_paymode')
            ->where('vde_paymode', '!=', '')
            ->groupBy('vde_paymode')
            ->orderBy('vde_paymode')
            ->pluck('vde_paymode')
            ->map(fn ($v) => ['value' => (string) $v, 'label' => (string) $v])
            ->values()
            ->all();

        // Status — legacy SQL: distinct vde_status where vde_payment_no IS NULL,
        // vde_trans_type='CR', and account is BANK_MAIN level 5
        $statusOpts = $conn->table('voucher_details as vde')
            ->join('voucher_master as vma', 'vma.vma_voucher_id', '=', 'vde.vma_voucher_id')
            ->whereNull('vde.vde_payment_no')
            ->whereNotNull('vde.vde_status')
            ->where('vde.vde_trans_type', 'CR')
            ->whereIn('vde.acm_acct_code', function ($sub) {
                $sub->select('acm_acct_code')
                    ->from('account_main')
                    ->where('acm_acct_group', 'BANK_MAIN')
                    ->where('acm_acct_level', 5);
            })
            ->groupBy('vde.vde_status')
            ->orderBy('vde.vde_status')
            ->pluck('vde.vde_status')
            ->map(fn ($v) => ['value' => (string) $v, 'label' => (string) $v])
            ->values()
            ->all();

        // ── Filter inputs ─────────────────────────────────────────────────────
        $tf0 = trim((string) $r->input('tf_0', ''));                       // Created By
        $tf1 = trim((string) $r->input('tf_1', ''));                       // Created Date
        $tf2 = trim((string) $r->input('tf_2', ''));                       // Payment Method
        $tf3 = trim((string) $r->input('tf_3', $r->input('reference_no', ''))); // Reference No
        $tf4 = trim((string) $r->input('tf_4', $r->input('status', '')));  // Status

        // ── Base query (dtKey columns aliased to label strings) ──────────────
        $base = $conn->table('voucher_master as vm')
            ->join('voucher_details as vde', function ($j) {
                $j->on('vde.vma_voucher_id', '=', 'vm.vma_voucher_id')
                  ->where('vde.vde_trans_type', 'DT');
            })
            ->where('vm.vma_vch_status', 'APPROVE')
            ->select([
                DB::raw('vm.vma_voucher_no AS `Voucher No`'),
                DB::raw('vde.vde_cust_invoice_no AS `Reference No`'),
                DB::raw('vm.vma_vch_description AS `Description`'),
                DB::raw('vde.vde_amount AS `Amount`'),
                DB::raw("COALESCE(vde.vde_payto_name, vm.vma_payto_name) AS `Payee`"),
                DB::raw('vde.vde_bank_name AS `Payee Bank Name`'),
                DB::raw('vde.vde_bank_acctno AS `Payee Account No`'),
                DB::raw('vde.vde_factoring_name AS `Factoring Name`'),
                DB::raw('vde.vde_fact_bank_name AS `Factoring Bank Name`'),
                DB::raw('vde.vde_fact_bank_acctno AS `Factoring Account No`'),
            ])
            ->orderByDesc('vm.vma_voucher_id');

        if ($tf0 !== '') {
            $base->where('vm.createdby', $tf0);
        }
        if ($tf1 !== '') {
            $base->whereRaw('DATE(vm.createddate) = ?', [$tf1]);
        }
        if ($tf2 !== '') {
            $base->where('vde.vde_paymode', $tf2);
        }
        if ($tf3 !== '') {
            $like = $this->likeEscape(mb_strtolower($tf3, 'UTF-8'));
            $base->whereRaw('LOWER(IFNULL(vde.vde_cust_invoice_no,\'\')) LIKE ?', [$like]);
        }
        if ($tf4 !== '') {
            $base->where('vde.vde_status', $tf4);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vde.vde_cust_invoice_no,''), IFNULL(vm.vma_vch_description,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'ap_download_voucher_by_ref',
            'top_filter_options' => [
                'tf_0' => $createdByOpts,
                'tf_2' => $paymodeOpts,
                'tf_4' => $statusOpts,
            ],
        ]);
    }

    private function apSendEmail(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_send_email');
    }

    private function apHistoricalSender(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_historical_sender');
    }

    /**
     * Account Payable / Report / Transaction History (menu 2974).
     *
     * Top-filter params:
     *   tf_0 — Date From  (DATE format YYYY-MM-DD)
     *   tf_1 — Date To    (DATE format YYYY-MM-DD)
     *   tf_2 — Operator   (=, >, <, >=, <=, <>)
     *   tf_3 — Amount     (decimal string, commas stripped)
     *   tf_4 — Vendor Status (vcs_bumi_status from TARAF_VENDOR lookup)
     *   tf_5 — OU Name   (oun_code from organization_unit)
     *   tf_6 — Vendor Name (vcs_vendor_code from vend_customer_supplier)
     */
    private function apTransactionHistory(Request $r, int $page, int $limit, string $q): array
    {
        $cx = $this->conn();

        $dateFrom     = trim((string) $r->input('tf_0', ''));
        $dateTo       = trim((string) $r->input('tf_1', ''));
        $operator     = trim((string) $r->input('tf_2', ''));
        $amount       = trim((string) $r->input('tf_3', ''));
        $vendorStatus = trim((string) $r->input('tf_4', ''));
        $ouName       = trim((string) $r->input('tf_5', ''));
        $vendorCode   = trim((string) $r->input('tf_6', ''));

        // Sub-query: one row per requisition — includes oun_code from requisition_details
        $rdSub = $cx->table('requisition_details')
            ->selectRaw(
                'rqm_requisition_id,
                 MIN(oun_code)         AS oun_code,
                 MIN(fty_fund_type)    AS fty_fund_type,
                 MIN(at_activity_code) AS at_activity_code,
                 MIN(ccr_costcentre)   AS ccr_costcentre,
                 MIN(acm_acct_code)    AS acm_acct_code'
            )
            ->groupBy('rqm_requisition_id');

        $base = $cx->table('bills_details as bd')
            ->leftJoin('bills_master as bm', 'bd.bim_bills_id', '=', 'bm.bim_bills_id')
            ->leftJoin('purchase_order_master as pm', 'bm.pom_order_no', '=', 'pm.pom_order_no')
            ->leftJoin('requisition_master as rm', 'pm.pom_requisition_no', '=', 'rm.rqm_requisition_no')
            ->leftJoinSub($rdSub, 'rd', fn (JoinClause $j) => $j->on('rm.rqm_requisition_id', '=', 'rd.rqm_requisition_id'))
            // oun_code comes from requisition_details; join organization_unit for the description
            ->leftJoin('organization_unit as ou', 'rd.oun_code', '=', 'ou.oun_code')
            ->leftJoin('vend_customer_supplier as vcs', 'bd.bid_payto_id', '=', 'vcs.vcs_vendor_code')
            ->leftJoin('voucher_master as vm', 'bm.bim_voucher_no', '=', 'vm.vma_voucher_no')
            ->leftJoin('account_main as am', 'rd.acm_acct_code', '=', 'am.acm_acct_code')
            ->whereNotNull('bm.bim_bills_no');

        if ($dateFrom !== '') {
            $base->whereRaw('DATE(bm.createddate) >= ?', [$dateFrom]);
        }
        if ($dateTo !== '') {
            $base->whereRaw('DATE(bm.createddate) <= ?', [$dateTo]);
        }

        $allowedOps = ['=', '>', '<', '>=', '<=', '<>'];
        if ($operator !== '' && $amount !== '' && in_array($operator, $allowedOps, true)) {
            $base->whereRaw("bd.bid_amt {$operator} ?", [(float) str_replace(',', '', $amount)]);
        }

        if ($vendorStatus !== '') {
            $base->where('vcs.vcs_bumi_status', $vendorStatus);
        }
        if ($ouName !== '') {
            $base->where('rd.oun_code', $ouName);
        }
        if ($vendorCode !== '') {
            $base->where('vcs.vcs_vendor_code', $vendorCode);
        }

        $base
            ->distinct()
            ->selectRaw(
                "ou.oun_desc AS nama_ptj,
                 vcs.vcs_vendor_name AS vcs_vendor_name,
                 bm.bim_bills_desc AS bim_bills_desc,
                 rm.rqm_requisition_no AS rqm_requisition_no,
                 pm.pom_order_no AS pom_order_no,
                 bm.bim_bills_no AS bim_bills_no,
                 bd.bid_status AS bid_status,
                 vm.vma_voucher_no AS vma_voucher_no,
                 (SELECT vd.vde_payment_no FROM voucher_details vd
                  WHERE vd.vma_voucher_id = vm.vma_voucher_id LIMIT 1) AS vde_payment_no,
                 FORMAT(bd.bid_amt, 2) AS bid_amt,
                 rm.rqm_amount AS rqm_amount,
                 rm.rqm_requisition_title AS rqm_requisition_title,
                 rd.fty_fund_type AS fty_fund_type,
                 rd.at_activity_code AS at_activity_code,
                 rd.oun_code AS oun_code,
                 rd.ccr_costcentre AS ccr_costcentre,
                 rd.acm_acct_code AS acm_acct_code,
                 vcs.vcs_bumi_status AS status,
                 vcs.vcs_vendor_code AS vcs_vendor_code,
                 am.acm_acct_desc AS acm_acct_desc,
                 rm.rqm_agg_no AS rqm_agg_no,
                 pm.pom_aggrement_no AS tender_qua_no,
                 CONCAT_WS('__',
                     IFNULL(bm.bim_bills_no, ''),
                     IFNULL(bm.bim_bills_desc, ''),
                     IFNULL(vcs.vcs_vendor_name, ''),
                     IFNULL(vcs.vcs_vendor_code, ''),
                     IFNULL(rm.rqm_requisition_no, ''),
                     IFNULL(pm.pom_order_no, ''),
                     IFNULL(bd.bid_status, ''),
                     IFNULL(vm.vma_voucher_no, '')
                 ) AS _search_concat"
            )
            ->orderBy('bm.bim_bills_no');

        // Wrap in sub-query so COUNT(*) for pagination is correct with DISTINCT
        $wrapped = $cx->query()->fromSub($base, 'th');

        if ($q !== '') {
            $needle = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $wrapped->whereRaw('LOWER(IFNULL(_search_concat, \'\')) LIKE ?', [$needle]);
        }

        $pack = $this->paginate($wrapped, $page, $limit);
        $pack['rows'] = array_map(static function (array $row): array {
            unset($row['_search_concat']);

            return $row;
        }, $pack['rows']);

        return array_merge($pack, [
            'connector'          => 'ap_transaction_history',
            'top_filter_options' => $this->apTransactionHistoryTopFilterOptions(),
        ]);
    }

    /**
     * @return array<string, list<array{value: string, label: string}>>
     */
    private function apTransactionHistoryTopFilterOptions(): array
    {
        $cx = $this->conn();

        $operatorOpts = [
            ['value' => '=',  'label' => '= (Equal)'],
            ['value' => '>',  'label' => '> (More Than)'],
            ['value' => '<',  'label' => '< (Less Than)'],
            ['value' => '>=', 'label' => '>= (More Than Equal)'],
            ['value' => '<=', 'label' => '<= (Less Than Equal)'],
            ['value' => '<>', 'label' => '<> (Not Equal)'],
        ];

        $vendorStatusOpts = $cx->table('lookup_details')
            ->where('lma_code_name', 'TARAF_VENDOR')
            ->whereNotNull('lde_value')
            ->where('lde_value', '!=', '')
            ->selectRaw('TRIM(lde_value) AS val, UPPER(TRIM(IFNULL(lde_description, \'\'))) AS lbl')
            ->orderBy('lde_value')
            ->get()
            ->map(fn ($row) => ['value' => (string) $row->val, 'label' => (string) $row->lbl])
            ->values()
            ->all();

        // OU Name: distinct oun_code from requisition_details joined to organization_unit
        $ouOpts = $cx->table('requisition_details as rd')
            ->join('organization_unit as ou', 'rd.oun_code', '=', 'ou.oun_code')
            ->whereNotNull('rd.oun_code')
            ->where('rd.oun_code', '!=', '')
            ->selectRaw("DISTINCT TRIM(rd.oun_code) AS val, CONCAT_WS(' - ', TRIM(rd.oun_code), TRIM(IFNULL(ou.oun_desc, ''))) AS lbl")
            ->orderByRaw('val ASC')
            ->get()
            ->map(fn ($row) => ['value' => (string) $row->val, 'label' => (string) $row->lbl])
            ->values()
            ->all();

        // Vendor Name: active vendors (vcs_vendor_status = 1)
        $vendorOpts = $cx->table('vend_customer_supplier')
            ->where('vcs_vendor_status', '1')
            ->whereNotNull('vcs_vendor_code')
            ->where('vcs_vendor_code', '!=', '')
            ->selectRaw("TRIM(vcs_vendor_code) AS val, CONCAT_WS(' - ', TRIM(vcs_vendor_code), TRIM(IFNULL(vcs_vendor_name, ''))) AS lbl")
            ->orderBy('vcs_vendor_code')
            ->get()
            ->map(fn ($row) => ['value' => (string) $row->val, 'label' => (string) $row->lbl])
            ->values()
            ->all();

        return [
            'tf_2' => $operatorOpts,
            'tf_4' => $vendorStatusOpts,
            'tf_5' => $ouOpts,
            'tf_6' => $vendorOpts,
        ];
    }

    private function apCreditNoteForm(Request $r, int $page, int $limit, string $q): array
    {
        // credit_note_ap_master: cna_credit_note_ap_master_id, cna_crnote_no, cna_crnote_date, cna_cn_total_amount, cna_status_cd (verified)
        $base = $this->conn()->table('credit_note_ap_master as cam')
            ->select([
                'cam.cna_credit_note_ap_master_id',
                'cam.cna_crnote_no',
                'cam.bim_bills_no',
                'cam.cna_crnote_date',
                'cam.cna_cn_total_amount',
                'cam.cna_status_cd',
            ])
            ->orderByDesc('cam.cna_credit_note_ap_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(cam.cna_crnote_no,''), IFNULL(cam.bim_bills_no,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_credit_note_form']);
    }

    private function apCreditNoteListing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('credit_note_ap_master as cam')
            ->leftJoin('bills_master as bm', 'bm.bim_bills_no', '=', 'cam.bim_bills_no')
            ->select([
                DB::raw('cam.cna_credit_note_ap_master_id AS cna_id'),
                DB::raw('cam.cna_crnote_no AS cna_crnote_no'),
                DB::raw('cam.cna_crnote_desc AS cna_crnote_desc'),
                DB::raw('cam.bim_bills_no AS bim_bills_no'),
                DB::raw('bm.bim_bills_desc AS bim_bills_desc'),
                DB::raw('bm.bim_cust_invoice_no AS bim_cust_invoice_no'),
                DB::raw('bm.bim_cust_invoice_date AS bim_cust_invoice_date'),
                DB::raw('COALESCE(cam.cna_currency_code, bm.bim_currency_code) AS bim_currency_code'),
                DB::raw('bm.bim_ent_amt AS bim_ent_amt'),
                DB::raw('bm.bim_bill_amt AS bim_bill_amt'),
                DB::raw('bm.bim_status AS bim_status'),
                DB::raw('cam.cna_ent_total_amount AS cna_ent_total_amount'),
                DB::raw('cam.cna_cn_total_amount AS cna_cn_total_amount'),
                DB::raw('(COALESCE(bm.bim_ent_amt, 0) - COALESCE(cam.cna_ent_total_amount, 0)) AS cna_ent_bal'),
                DB::raw('(COALESCE(bm.bim_bill_amt, 0) - COALESCE(cam.cna_cn_total_amount, 0)) AS cna_cn_bal'),
                DB::raw('cam.cna_crnote_date AS cna_crnote_date'),
                DB::raw('cam.cna_approve_date AS cna_approve_date'),
                DB::raw('cam.cna_status_cd AS cna_status_cd'),
                DB::raw("CONCAT('/admin/kerisi/m/3242?cna_id=', cam.cna_credit_note_ap_master_id) AS urlE"),
                DB::raw("CONCAT('/admin/kerisi/m/3242?cna_id=', cam.cna_credit_note_ap_master_id, '&mode=view') AS urlV"),
            ])
            ->whereRaw("IFNULL(cam.cna_status_cd, '') <> 'CANCEL'")
            ->orderByDesc('cam.cna_credit_note_ap_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cam.cna_crnote_no,''), IFNULL(cam.cna_crnote_desc,''), IFNULL(cam.bim_bills_no,''), IFNULL(bm.bim_bills_desc,''), IFNULL(bm.bim_cust_invoice_no,''), IFNULL(cam.cna_status_cd,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_credit_note_listing']);
    }

    private function apCreditNoteCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('credit_note_ap_master as cam')
            ->leftJoin('bills_master as bm', 'bm.bim_bills_no', '=', 'cam.bim_bills_no')
            ->where('cam.cna_status_cd', 'CANCEL')
            ->select([
                DB::raw('cam.cna_credit_note_ap_master_id AS cna_id'),
                DB::raw('cam.cna_crnote_no AS cna_crnote_no'),
                DB::raw('cam.cna_crnote_desc AS cna_crnote_desc'),
                DB::raw('cam.bim_bills_no AS bim_bills_no'),
                DB::raw('bm.bim_bills_desc AS bim_bills_desc'),
                DB::raw('bm.bim_cust_invoice_no AS bim_cust_invoice_no'),
                DB::raw('bm.bim_cust_invoice_date AS bim_cust_invoice_date'),
                DB::raw('COALESCE(cam.cna_currency_code, bm.bim_currency_code) AS bim_currency_code'),
                DB::raw('bm.bim_ent_amt AS bim_ent_amt'),
                DB::raw('bm.bim_bill_amt AS bim_bill_amt'),
                DB::raw('bm.bim_status AS bim_status'),
                DB::raw('cam.cna_ent_total_amount AS cna_ent_total_amount'),
                DB::raw('cam.cna_cn_total_amount AS cna_cn_total_amount'),
                DB::raw('(COALESCE(bm.bim_ent_amt, 0) - COALESCE(cam.cna_ent_total_amount, 0)) AS cna_ent_bal'),
                DB::raw('(COALESCE(bm.bim_bill_amt, 0) - COALESCE(cam.cna_cn_total_amount, 0)) AS cna_cn_bal'),
                DB::raw('cam.cna_crnote_date AS cna_crnote_date'),
                DB::raw('cam.cna_approve_date AS cna_approve_date'),
                DB::raw('cam.cna_status_cd AS cna_status_cd'),
                DB::raw('cam.cna_cancel_date AS cna_cancel_date'),
                DB::raw('cam.cna_cancel_reason AS cna_cancel_reason'),
                DB::raw("CONCAT('/admin/kerisi/m/3242?cna_id=', cam.cna_credit_note_ap_master_id, '&mode=view') AS urlV"),
            ])
            ->orderByDesc('cam.cna_credit_note_ap_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cam.cna_crnote_no,''), IFNULL(cam.cna_crnote_desc,''), IFNULL(cam.bim_bills_no,''), IFNULL(bm.bim_bills_desc,''), IFNULL(cam.cna_cancel_reason,''), IFNULL(cam.cna_status_cd,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_credit_note_cancel']);
    }

    private function apDebitNoteForm(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('debit_note_ap_master as dam')
            ->leftJoin('bills_master as bm', 'bm.bim_bills_no', '=', 'dam.bim_bills_no')
            ->select([
                DB::raw('dam.dna_debit_note_ap_master_id AS dna_id'),
                DB::raw('dam.dna_dnnote_no AS dna_dnnote_no'),
                DB::raw('dam.dna_dnnote_desc AS dna_dnnote_desc'),
                DB::raw('dam.bim_bills_no AS bim_bills_no'),
                DB::raw('bm.bim_bills_desc AS bim_bills_desc'),
                DB::raw('bm.bim_cust_invoice_no AS bim_cust_invoice_no'),
                DB::raw('bm.bim_cust_invoice_date AS bim_cust_invoice_date'),
                DB::raw('COALESCE(dam.dna_currency_code, bm.bim_currency_code) AS bim_currency_code'),
                DB::raw('bm.bim_ent_amt AS bim_ent_amt'),
                DB::raw('bm.bim_bill_amt AS bim_bill_amt'),
                DB::raw('bm.bim_status AS bim_status'),
                DB::raw('dam.dna_ent_total_amount AS dna_ent_total_amount'),
                DB::raw('dam.dna_dn_total_amount AS dna_dn_total_amount'),
                DB::raw('(COALESCE(bm.bim_ent_amt, 0) - COALESCE(dam.dna_ent_total_amount, 0)) AS dna_ent_bal'),
                DB::raw('(COALESCE(bm.bim_bill_amt, 0) - COALESCE(dam.dna_dn_total_amount, 0)) AS dna_dn_bal'),
                DB::raw('dam.dna_dnnote_date AS dna_dnnote_date'),
                DB::raw('dam.dna_approve_date AS dna_approve_date'),
                DB::raw('dam.dna_status_dn AS dna_status_dn'),
                DB::raw("CONCAT('/admin/kerisi/m/3548?dna_id=', dam.dna_debit_note_ap_master_id) AS urlE"),
                DB::raw("CONCAT('/admin/kerisi/m/3548?dna_id=', dam.dna_debit_note_ap_master_id, '&mode=view') AS urlV"),
            ])
            ->whereRaw("IFNULL(dam.dna_status_dn, '') <> 'CANCEL'")
            ->orderByDesc('dam.dna_debit_note_ap_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(dam.dna_dnnote_no,''), IFNULL(dam.dna_dnnote_desc,''), IFNULL(dam.bim_bills_no,''), IFNULL(bm.bim_bills_desc,''), IFNULL(bm.bim_cust_invoice_no,''), IFNULL(dam.dna_status_dn,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_debit_note_form']);
    }

    private function apDebitNoteCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('debit_note_ap_master as dam')
            ->leftJoin('bills_master as bm', 'bm.bim_bills_no', '=', 'dam.bim_bills_no')
            ->where('dam.dna_status_dn', 'CANCEL')
            ->select([
                DB::raw('dam.dna_debit_note_ap_master_id AS dna_id'),
                DB::raw('dam.dna_dnnote_no AS dna_dnnote_no'),
                DB::raw('dam.dna_dnnote_desc AS dna_dnnote_desc'),
                DB::raw('dam.bim_bills_no AS bim_bills_no'),
                DB::raw('bm.bim_bills_desc AS bim_bills_desc'),
                DB::raw('bm.bim_cust_invoice_no AS bim_cust_invoice_no'),
                DB::raw('bm.bim_cust_invoice_date AS bim_cust_invoice_date'),
                DB::raw('COALESCE(dam.dna_currency_code, bm.bim_currency_code) AS bim_currency_code'),
                DB::raw('bm.bim_ent_amt AS bim_ent_amt'),
                DB::raw('bm.bim_bill_amt AS bim_bill_amt'),
                DB::raw('bm.bim_status AS bim_status'),
                DB::raw('dam.dna_ent_total_amount AS dna_ent_total_amount'),
                DB::raw('dam.dna_dn_total_amount AS dna_dn_total_amount'),
                DB::raw('(COALESCE(bm.bim_ent_amt, 0) - COALESCE(dam.dna_ent_total_amount, 0)) AS dna_ent_bal'),
                DB::raw('(COALESCE(bm.bim_bill_amt, 0) - COALESCE(dam.dna_dn_total_amount, 0)) AS dna_cn_bal'),
                DB::raw('dam.dna_dnnote_date AS dna_dnnote_date'),
                DB::raw('dam.dna_approve_date AS dna_approve_date'),
                DB::raw('dam.dna_status_dn AS dna_status_dn'),
                DB::raw('dam.dna_cancel_date AS dna_cancel_date'),
                DB::raw('dam.dna_cancel_reason AS dna_cancel_reason'),
                DB::raw("CONCAT('/admin/kerisi/m/3550?dna_id=', dam.dna_debit_note_ap_master_id, '&mode=view') AS urlV"),
            ])
            ->orderByDesc('dam.dna_debit_note_ap_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(dam.dna_dnnote_no,''), IFNULL(dam.dna_dnnote_desc,''), IFNULL(dam.bim_bills_no,''), IFNULL(bm.bim_bills_desc,''), IFNULL(dam.dna_cancel_reason,''), IFNULL(dam.dna_status_dn,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_debit_note_cancel']);
    }

    private function apDebitNoteCancelForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_debit_note_cancel_form');
    }

    private function apJournalRevaluation(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_journal_revaluation');
    }

    private function apMoneyTransferList(Request $r, int $page, int $limit, string $q): array
    {
        $conn = $this->conn();

        // Destination PTJ (MIN oun_code from details; paired with organisation_unit.oun_desc for display).
        $ptjAgg = $conn->table('money_transfer_details')
            ->select(['mtm_id', DB::raw('MIN(oun_code) AS oun_code')])
            ->whereNotNull('oun_code')
            ->where('oun_code', '!=', '')
            ->groupBy('mtm_id');

        // wujud: Y when a voucher row is linked via mtm_application_no (legacy uses for Cancel rules).
        $base = $conn->table('money_transfer_master as m')
            ->leftJoinSub($ptjAgg, 'pj', 'pj.mtm_id', '=', 'm.mtm_id')
            ->leftJoin('organization_unit as ou', 'ou.oun_code', '=', 'pj.oun_code')
            ->select([
                'm.mtm_id',
                'm.mtm_application_no',
                'm.mtm_reference_no',
                'm.mtm_status',
                'm.mtm_cancel_reason',
                'm.mtm_cancel_date',
                'm.mtm_cancel_by',
                'pj.oun_code',
                'ou.oun_desc AS oun_code_desc',
                DB::raw("(CASE WHEN EXISTS (SELECT 1 FROM voucher_master vm WHERE vm.mtm_application_no = m.mtm_application_no AND vm.mtm_application_no IS NOT NULL AND TRIM(vm.mtm_application_no) <> '') THEN 'Y' ELSE 'N' END) AS wujud"),
            ])
            ->orderByDesc('m.mtm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'|\', IFNULL(m.mtm_application_no,\'\'), IFNULL(m.mtm_reference_no,\'\'), '
                .'IFNULL(m.mtm_status,\'\'), IFNULL(m.mtm_cancel_reason,\'\'), IFNULL(m.mtm_cancel_by,\'\'), '
                .'IFNULL(pj.oun_code,\'\'), IFNULL(ou.oun_desc,\'\'))) LIKE ?',
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_money_transfer_list']);
    }

    private function apGenerateVoucherDraft(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('money_transfer_master as m')
            ->where('m.mtm_status', 'APPROVE')
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('voucher_master as vm')
                    ->whereColumn('vm.mtm_application_no', 'm.mtm_application_no')
                    ->whereNotNull('vm.mtm_application_no')
                    ->where('vm.mtm_application_no', '!=', '');
            })
            ->select([
                'm.mtm_id',
                'm.mtm_application_no',
                'm.mtm_reference_no',
                'm.mtm_status',
            ])
            ->orderByDesc('m.mtm_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(m.mtm_application_no,''), IFNULL(m.mtm_reference_no,''), IFNULL(m.mtm_status,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_generate_voucher_draft']);
    }

    private function apBukuDaftarTerimaan(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_buku_daftar_terimaan');
    }

    /* ══════════════════════════════════════════════════════════════════
     * AP — Direct Voucher creation form (MENUID 3461)
     * This is a form page (Voucher Details + Debit/Credit line tables).
     * The page starts empty; rows are populated after a voucher is selected.
     * ══════════════════════════════════════════════════════════════════ */
    private function apDirectVoucher(Request $r, int $page, int $limit, string $q): array
    {
        return [
            'rows'       => [],
            'total'      => 0,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => 0,
            'connector'  => 'ap_direct_voucher',
            'form_values' => [
                'description'    => 'PEMBAYARAN KE ATAS',
                'status'         => 'DRAFT',
                'amount'         => '0.00',
            ],
        ];
    }

    private function apUpdateBankAccount(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    private function apPaymentRejectBatch(Request $r, int $page, int $limit, string $q): array
    {
        $batchId = trim((string) $r->input('tf_0', $r->input('pyb_pybatch_id', $r->input('pybPybatchId', ''))));

        $base = $this->conn()->table('payment_record as pr')
            ->join('payment_batch as pb', 'pb.pyb_pybatch_id', '=', 'pr.pre_payment_batch_id')
            ->leftJoin('bank_master as bm', 'bm.bnm_bank_code', '=', 'pr.pre_bank_name')
            ->leftJoin('lookup_bank_main as lbm', 'lbm.lbm_bank_code', '=', 'pr.pre_bank_name')
            ->where('pb.pyb_status', 'REJECT')
            ->select([
                DB::raw('pr.pre_payment_record_id AS pre_payment_record_id'),
                DB::raw('pr.pre_payment_no AS pre_payment_no'),
                DB::raw('pr.pre_voucher_no AS pre_voucher_no'),
                DB::raw('pr.pre_payee_type AS pre_payee_type'),
                DB::raw('pr.pre_payto_id AS pre_payto_id'),
                DB::raw('pr.pre_payto_name AS pre_payto_name'),
                DB::raw('COALESCE(bm.bnm_bank_desc, lbm.lbm_bank_name, pr.pre_bank_name) AS bnm_bank_desc'),
                DB::raw('pr.acm_acct_code_bank AS acm_acct_code_bank'),
                DB::raw('COALESCE(pr.pre_total_amt_rm, pr.pre_total_amt) AS pre_total_amt'),
                DB::raw('pr.pre_status AS pre_status'),
                DB::raw("CASE WHEN IFNULL(pr.pre_cashbook_batch, '') <> '' OR IFNULL(pr.pre_approve_glbatch, '') <> '' THEN 'Yes' ELSE 'No' END AS has_posting"),
                DB::raw('pr.pre_payment_batch_id AS pre_payment_batch_id'),
            ])
            ->orderByDesc('pr.pre_payment_record_id');

        if ($batchId !== '') {
            $base->where('pr.pre_payment_batch_id', $batchId);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pr.pre_payment_no,''), IFNULL(pr.pre_voucher_no,''), IFNULL(pr.pre_payee_type,''), IFNULL(pr.pre_payto_id,''), IFNULL(pr.pre_payto_name,''), IFNULL(pr.pre_bank_name,''), IFNULL(bm.bnm_bank_desc,''), IFNULL(lbm.lbm_bank_name,''), IFNULL(pr.acm_acct_code_bank,''), IFNULL(pr.pre_status,''), IFNULL(pb.pyb_batch_no,''))) LIKE ?",
                [$like]
            );
        }

        return array_merge($this->paginate($base, $page, $limit), [
            'connector' => 'ap_payment_reject_batch',
            'top_filter_options' => $this->apPaymentRejectBatchTopFilterOptions(),
        ]);
    }

    /**
     * @return array<string, list<int, array{value: string, label: string}>>
     */
    private function apPaymentRejectBatchTopFilterOptions(): array
    {
        $rows = $this->conn()->table('payment_batch as pb')
            ->join('payment_record as pr', 'pr.pre_payment_batch_id', '=', 'pb.pyb_pybatch_id')
            ->where('pb.pyb_status', 'REJECT')
            ->select([
                DB::raw('pb.pyb_pybatch_id AS id'),
                DB::raw('pb.pyb_batch_no AS batch_no'),
                DB::raw('COUNT(pr.pre_payment_record_id) AS record_count'),
            ])
            ->groupBy('pb.pyb_pybatch_id', 'pb.pyb_batch_no')
            ->orderByDesc('pb.pyb_pybatch_id')
            ->get();

        return [
            'tf_0' => $rows
                ->map(fn ($row): array => [
                    'value' => (string) $row->id,
                    'label' => sprintf('%s (%d)', (string) $row->batch_no, (int) $row->record_count),
                ])
                ->values()
                ->all(),
        ];
    }

    private function apPaymentRecord(Request $r, int $page, int $limit, string $q): array
    {
        // payment_record: pre_payment_record_id, pre_payment_no, pre_payto_name, pre_total_amt (verified)
        $base = $this->conn()->table('payment_record as pr')
            ->select([
                'pr.pre_payment_record_id',
                'pr.pre_payment_no',
                'pr.pre_payto_name',
                'pr.pre_payto_id',
                'pr.pre_total_amt',
                'pr.pre_approve_date',
            ])
            ->orderByDesc('pr.pre_payment_record_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(pr.pre_payment_no,''), IFNULL(pr.pre_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_payment_record']);
    }

    private function apLoanDisburse(Request $r, int $page, int $limit, string $q): array
    {
        return $this->loanDisburse($r, $page, $limit, $q);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 3401 — Parliament / Budget Setup
     * ══════════════════════════════════════════════════════════════════ */
    private function parliamentGroupSetup(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('setup_budget_structure_search as sbss')
            ->select(['sbss.sbss_id', 'sbss.sbss_type', 'sbss.sbss_type', 'sbss.sbss_level_selection', 'sbss.sbss_status'])
            ->orderByDesc('sbss.sbss_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(sbss.sbss_type,''), IFNULL(sbss.sbss_type,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'parliament_group_setup']);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 3454 — Treatment (Medical/Patient)
     * ══════════════════════════════════════════════════════════════════ */
    private function treatmentList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('int_patient_bill_master as pbm')
            ->select(['pbm.pbm_id', 'pbm.pbm_mrn_no', 'pbm.createddate', 'pbm.pbm_total_amt', 'pbm.flag'])
            ->orderByDesc('pbm.pbm_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(pbm.pbm_mrn_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'treatment_list']);
    }

    private function treatmentLedgerByYear(Request $r, int $page, int $limit, string $q): array
    {
        return $this->treatmentList($r, $page, $limit, $q);
    }

    private function treatmentNotificationLetter(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('treatment_notification_letter');
    }

    private function treatmentOverallLedger(Request $r, int $page, int $limit, string $q): array
    {
        return $this->treatmentList($r, $page, $limit, $q);
    }

    private function treatmentStatementByStaff(Request $r, int $page, int $limit, string $q): array
    {
        return $this->treatmentList($r, $page, $limit, $q);
    }

    /* ══════════════════════════════════════════════════════════════════
     * FILE 1052 — GL Reports
     * ══════════════════════════════════════════════════════════════════ */
    private function glReversal(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('posting_master as pm')
            ->select(['pm.pmt_posting_id', 'pm.pmt_posting_no', 'pm.createddate', 'pm.pmt_total_amt', 'pm.pmt_status'])
            ->orderByDesc('pm.pmt_posting_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(pm.pmt_posting_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'gl_reversal']);
    }

    private function glOpening(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('glopening_yearly as gy')
            ->select(['gy.gly_glopening_yearly_id', 'gy.gly_year', 'gy.acm_acct_code', 'gy.gly_debit_bal', 'gy.createddate'])
            ->orderByDesc('gy.gly_glopening_yearly_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(gy.acm_acct_code,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'gl_opening']);
    }

    private function glReverseJournalListing(Request $r, int $page, int $limit, string $q): array
    {
        return $this->glReversal($r, $page, $limit, $q);
    }

    private function glUnidentifiedReceiptList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('stud_unidentifed as su')
            ->select(['su.stu_id', 'su.stu_matric_no', 'su.stu_ic_passport', 'su.createddate', 'su.stu_category'])
            ->orderByDesc('su.stu_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(su.stu_matric_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'gl_unidentified_receipt_list']);
    }

    private function glUnidentifiedReceiptEntry(Request $r, int $page, int $limit, string $q): array
    {
        return $this->glUnidentifiedReceiptList($r, $page, $limit, $q);
    }

    private function glManualJournalEntry(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('gl_manual_journal_entry');
    }

    private function glIdentifiedReceiptsReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('gl_identified_receipts_report');
    }

    private function glYearlyClosing(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('gl_year_month as gym')
            ->select(['gym.gym_id', 'gym.gym_year', 'gym.gym_month', 'gym.gym_status'])
            ->orderByDesc('gym.gym_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(gym.gym_year,''), IFNULL(gym.gym_month,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'gl_yearly_closing']);
    }

    private function glJournalAdjustmentListing(Request $r, int $page, int $limit, string $q): array
    {
        return $this->glReversal($r, $page, $limit, $q);
    }

    private function glJournalAdjustmentForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('gl_journal_adjustment_form');
    }

    private function glTrialBalanceSummary(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('gl_trial_balance_summary');
    }

    private function glDetailsTransaction(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('posting_details as pd')
            ->select(['pd.pde_posting_detl_id', 'pd.pde_posting_detl_id', 'pd.acm_acct_code', 'pd.pde_trans_amt', 'pd.pde_trans_amt', 'pd.pde_trans_date'])
            ->orderByDesc('pd.pde_posting_detl_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(pd.acm_acct_code,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'gl_details_transaction']);
    }

    private function glCombineReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('gl_combine_report');
    }

    private function glTrialBalance(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('gl_trial_balance');
    }

    private function glBsPl(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('gl_bs_pl');
    }

    private function glReportItemSetup(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('report_info as ri')
            ->select(['ri.ri_report_id', 'ri.ri_folder_name', 'ri.ri_template_name', 'ri.ri_flag_pdf', 'ri.ri_flag_word'])
            ->orderBy('ri.ri_template_name');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(ri.ri_folder_name,''), IFNULL(ri.rep_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'gl_report_item_setup']);
    }

    /* ══════════════════════════════════════════════════════════════════
     * Fallback helpers
     * ══════════════════════════════════════════════════════════════════ */
    private function shellPreview(string $connector): array
    {
        return ['rows' => [], 'total' => 0, 'connector' => $connector];
    }
}
