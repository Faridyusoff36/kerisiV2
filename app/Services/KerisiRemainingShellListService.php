<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
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
            2618 => $this->assetVerification($request, $page, $limit, $q),
            2624 => $this->assetList($request, $page, $limit, $q),
            2626 => $this->assetLostListing($request, $page, $limit, $q),
            2663 => $this->assetMaintenanceList($request, $page, $limit, $q),
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
            2633 => $this->ccDebtMovementReport($request, $page, $limit, $q),
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
            1828 => $this->purchasingTenderList($request, $page, $limit, $q),
            1829 => $this->purchasingItemMainListing($request, $page, $limit, $q),
            1833 => $this->purchasingQuotationList($request, $page, $limit, $q),
            1838 => $this->purchasingJobScope($request, $page, $limit, $q),
            1839 => $this->purchasingCommitteeSetup($request, $page, $limit, $q),
            1840 => $this->purchasingTenderJobScope($request, $page, $limit, $q),
            1856 => $this->purchasingPrForm($request, $page, $limit, $q),
            1858 => $this->purchasingGrnForm($request, $page, $limit, $q),
            2039 => $this->purchasingAgreementList($request, $page, $limit, $q),
            2041 => $this->purchasingPoClosing($request, $page, $limit, $q),
            2042 => $this->purchasingPoUpdate($request, $page, $limit, $q),
            2082 => $this->purchasingVoList($request, $page, $limit, $q),
            2085 => $this->purchasingVendorAssessment($request, $page, $limit, $q),
            2320 => $this->purchasingTenderBrief($request, $page, $limit, $q),
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
            3038 => $this->purchasingPrToCancel($request, $page, $limit, $q),
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
            2030 => $this->apBillsList($request, $page, $limit, $q),
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

    private function purchasingPoList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('purchase_order_master as pom')
            ->select(['pom.pom_order_id', 'pom.pom_order_no', 'pom.pom_request_date', 'pom.vcs_vendor_code', 'pom.pom_order_amt', 'pom.pom_order_status'])
            ->orderByDesc('pom.pom_order_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(pom.po_no,''), IFNULL(pom.po_vendor,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_po_list']);
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
        $base = $this->conn()->table('vend_customer_supplier as vcs')
            ->select(['vcs.vcs_id', 'vcs.vcs_vendor_code', 'vcs.vcs_vendor_name', 'vcs.vcs_type_gov', 'vcs.vcs_vendor_status'])
            ->orderBy('vcs.vcs_vendor_name');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(vcs.vendor_code,''), IFNULL(vcs.vendor_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_vendor_list']);
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
            ->select(['ap.agg_id', 'ap.agg_no', 'ap.agg_start_date', 'ap.vcs_vendor_code', 'ap.agg_status'])
            ->orderByDesc('ap.agg_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(ap.agr_no,''), IFNULL(ap.agr_vendor,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_agreement_list']);
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
        return $this->purchasingPoList($r, $page, $limit, $q);
    }

    private function purchasingVoList(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('aggrement_vo as av')
            ->select(['av.agv_id', 'av.agv_no', 'av.agg_id', 'av.agv_letter_date', 'av.agv_amt', 'av.agv_status'])
            ->orderByDesc('av.agv_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(av.agv_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_vo_list']);
    }

    private function purchasingVoListAll(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVoList($r, $page, $limit, $q);
    }

    private function purchasingNewVo(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_new_vo');
    }

    private function purchasingVendorAssessment(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('vendor_assessment_master as vam')
            ->select(['vam.vam_assessment_id', 'vam.vcs_vendor_code', 'vam.createddate', 'vam.vam_mark', 'vam.vam_grade', 'vam.vam_status'])
            ->orderByDesc('vam.vam_assessment_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(vam.vcs_vendor_code,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_vendor_assessment']);
    }

    private function purchasingVendorAssessmentReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorAssessment($r, $page, $limit, $q);
    }

    private function purchasingVendorAssessmentWpn(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorAssessment($r, $page, $limit, $q);
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

    private function purchasingPrToCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('pre_purchase_requisition as ppr')
            ->select(['ppr.ppr_requisition_id', 'ppr.ppr_pre_requisition_no', 'ppr.ppr_request_date', 'ppr.ppr_status'])
            ->orderByDesc('ppr.ppr_requisition_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(ppr.ppr_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'purchasing_pr_to_cancel']);
    }

    private function purchasingPrCancelForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_pr_cancel_form');
    }

    private function purchasingPrToCancelPartial(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingPrToCancel($r, $page, $limit, $q);
    }

    private function purchasingPrCancelPartialForm(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_pr_cancel_partial_form');
    }

    private function purchasingCommitteeReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('purchasing_committee_report');
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
        // bills_master: bim_bills_id, bim_bills_no, vcs_vendor_code, bim_bill_amt, bim_status (verified)
        $base = $this->conn()->table('bills_master as bm')
            ->select([
                'bm.bim_bills_id',
                'bm.bim_bills_no',
                'bm.bim_bills_desc',
                'bm.vcs_vendor_code',
                'bm.bim_bill_amt',
                'bm.bim_status',
                'bm.oun_code',
            ])
            ->orderByDesc('bm.bim_bills_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(bm.bim_bills_no,''), IFNULL(bm.vcs_vendor_code,''))) LIKE ?", [$like]);
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

    private function apBillReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_bill_report');
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
        return $this->apVoucherRegistration($r, $page, $limit, $q);
    }

    private function apVoucherCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('voucher_master as vm')
            ->where('vm.vma_vch_status', 'CANCEL')
            ->select([
                'vm.vma_voucher_id',
                'vm.vma_voucher_no',
                'vm.vma_payto_name',
                'vm.vma_total_amt',
                'vm.vma_vch_status',
                'vm.createddate',
            ])
            ->orderByDesc('vm.vma_voucher_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_voucher_cancel']);
    }

    private function apVoucherCancelJournal(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apVoucherCancel($r, $page, $limit, $q);
    }

    private function apVoucherReplace(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apVoucherRegistration($r, $page, $limit, $q);
    }

    private function apVoucherReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_voucher_report');
    }

    private function apVoucherProcess(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apVoucherRegistration($r, $page, $limit, $q);
    }

    private function apVoucherInfoCreditor(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apVoucherRegistration($r, $page, $limit, $q);
    }

    private function apVoucherMoneyTransferList(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apVoucherRegistration($r, $page, $limit, $q);
    }

    private function apOtherPayment(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('voucher_master as vm')
            ->select([
                'vm.vma_voucher_id',
                'vm.vma_voucher_no',
                'vm.vma_payto_name',
                'vm.vma_total_amt',
                'vm.vma_vch_status',
                'vm.createddate',
            ])
            ->orderByDesc('vm.vma_voucher_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(vm.vma_voucher_no,''), IFNULL(vm.vma_payto_name,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_other_payment']);
    }

    private function apOtherPaymentUpdate(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apOtherPayment($r, $page, $limit, $q);
    }

    private function apRefund(Request $r, int $page, int $limit, string $q): array
    {
        return $this->refundApplication($r, $page, $limit, $q);
    }

    private function apPayeeReport(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    private function apPayeeReportByPtj(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    private function apPayeeListing(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
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
        // payment_batch: pyb_pybatch_id, pyb_batch_no, pyb_total_amt, pyb_transfer_date, pyb_status (verified)
        $base = $this->conn()->table('payment_batch as pb')
            ->select([
                'pb.pyb_pybatch_id',
                'pb.pyb_batch_no',
                'pb.pyb_total_amt',
                'pb.pyb_qty',
                'pb.pyb_transfer_date',
                'pb.pyb_status',
            ])
            ->orderByDesc('pb.pyb_pybatch_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(pb.pyb_batch_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_payment_notice']);
    }

    private function apPrintSabBatch(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_print_sab_batch');
    }

    private function apPaymentListing(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apPaymentNotice($r, $page, $limit, $q);
    }

    private function apDownloadVoucherBatch(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_download_voucher_batch');
    }

    private function apDownloadVoucherByRef(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_download_voucher_by_ref');
    }

    private function apSendEmail(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_send_email');
    }

    private function apHistoricalSender(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_historical_sender');
    }

    private function apTransactionHistory(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apBillRegistrationList($r, $page, $limit, $q);
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
        return $this->apCreditNoteForm($r, $page, $limit, $q);
    }

    private function apCreditNoteCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('credit_note_ap_master as cam')
            ->where('cam.cna_status_cd', 'CANCEL')
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
            $base->whereRaw("LOWER(IFNULL(cam.cna_crnote_no,'')) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_credit_note_cancel']);
    }

    private function apDebitNoteForm(Request $r, int $page, int $limit, string $q): array
    {
        // debit_note_ap_master: dna_debit_note_ap_master_id, dna_dnnote_no, dna_dnnote_date, dna_dn_total_amount, dna_status_dn (verified)
        $base = $this->conn()->table('debit_note_ap_master as dam')
            ->select([
                'dam.dna_debit_note_ap_master_id',
                'dam.dna_dnnote_no',
                'dam.bim_bills_no',
                'dam.dna_dnnote_date',
                'dam.dna_dn_total_amount',
                'dam.dna_status_dn',
            ])
            ->orderByDesc('dam.dna_debit_note_ap_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(dam.dna_dnnote_no,''), IFNULL(dam.bim_bills_no,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_debit_note_form']);
    }

    private function apDebitNoteCancel(Request $r, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('debit_note_ap_master as dam')
            ->where('dam.dna_status_dn', 'CANCEL')
            ->select([
                'dam.dna_debit_note_ap_master_id',
                'dam.dna_dnnote_no',
                'dam.bim_bills_no',
                'dam.dna_dnnote_date',
                'dam.dna_dn_total_amount',
                'dam.dna_status_dn',
            ])
            ->orderByDesc('dam.dna_debit_note_ap_master_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(IFNULL(dam.dna_dnnote_no,'')) LIKE ?", [$like]);
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
        // vot_transfer_master: vtm_posting_id, vtm_posting_no, vtm_enter_date, vtm_total_amt, vtm_status (verified)
        $base = $this->conn()->table('vot_transfer_master as vtm')
            ->select([
                'vtm.vtm_posting_id',
                'vtm.vtm_posting_no',
                'vtm.vtm_enter_date',
                'vtm.vtm_total_amt',
                'vtm.vtm_status',
                'vtm.vtm_description',
            ])
            ->orderByDesc('vtm.vtm_posting_id');
        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw("LOWER(CONCAT_WS('|', IFNULL(vtm.vtm_posting_no,''), IFNULL(vtm.vtm_description,''))) LIKE ?", [$like]);
        }

        return array_merge($this->paginate($base, $page, $limit), ['connector' => 'ap_money_transfer_list']);
    }

    private function apGenerateVoucherDraft(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apVoucherRegistration($r, $page, $limit, $q);
    }

    private function apBukuDaftarTerimaan(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_buku_daftar_terimaan');
    }

    private function apDirectVoucher(Request $r, int $page, int $limit, string $q): array
    {
        return $this->apVoucherRegistration($r, $page, $limit, $q);
    }

    private function apUpdateBankAccount(Request $r, int $page, int $limit, string $q): array
    {
        return $this->purchasingVendorList($r, $page, $limit, $q);
    }

    private function apPaymentRejectBatch(Request $r, int $page, int $limit, string $q): array
    {
        return $this->shellPreview('ap_payment_reject_batch');
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
