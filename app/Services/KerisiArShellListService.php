<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shell list service for Account Receivable pages (MENUID 1024 parent).
 * Source: PAGE_MENUID1024_LEVEL3.json. Each handler mirrors the legacy PHP BL
 * (Business Logic) SQL but uses the Laravel Eloquent/query-builder ORM on the
 * `mysql_secondary` connection. No raw SQL strings; only the query builder.
 *
 * Return contract: ['rows' => array, 'total' => int, 'connector' => string]
 */
class KerisiArShellListService
{
    /** @return array{rows: list<array<string,mixed>>, total: int, connector: string} */
    public function fetch(int $menuId, Request $request): array
    {
        $page  = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q     = trim((string) $request->input('q', ''));

        return match ($menuId) {
            // Invoice
            1581 => $this->myInvoiceRequest($request, $page, $limit, $q),
            1757 => $this->recurringList($request, $page, $limit, $q),
            3280 => $this->salaryDeductionScheduleListing($request, $page, $limit, $q),
            // Receipt
            1590 => $this->listOfReceipts($request, $page, $limit, $q),
            1742 => $this->bankInSlip($request, $page, $limit, $q, 'generation'),
            1761 => $this->bankInSlip($request, $page, $limit, $q, 'download'),
            2347 => $this->receiptOnBehalf($request, $page, $limit, $q),
            1740 => $this->updateForeignCurrency($request, $page, $limit, $q),
            3249 => $this->cashReceiptRelease($request, $page, $limit, $q),
            // Cheque
            1652 => $this->chequeRegistry($request, $page, $limit, $q),
            1656 => $this->chequeRelease($request, $page, $limit, $q),
            2528 => $this->chequeReleaseView($request, $page, $limit, $q),
            1720 => $this->chequeList($request, $page, $limit, $q),
            1045 => $this->returnChequeList($request, $page, $limit, $q),
            // Offline Receipt
            2183 => $this->offlineReceiptApplication($request, $page, $limit, $q),
            2108 => $this->offlineReceiptCollectionEntry($request, $page, $limit, $q),
            2500 => $this->offlineReceiptCounter($request, $page, $limit, $q),
            // Setup
            2572 => $this->signatureSetup($request, $page, $limit, $q),
            3507 => $this->premiseDetails($request, $page, $limit, $q),
            1936 => $this->nonInvoiceStructure($request, $page, $limit, $q),
            2117 => $this->invoiceStructure($request, $page, $limit, $q),
            default => ['rows' => [], 'total' => 0, 'connector' => 'ar_shell_preview'],
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function conn(): \Illuminate\Database\Connection
    {
        return DB::connection('mysql_secondary');
    }

    private function likeEscape(string $q): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1581 — Invoice > My Request  (BL: DT_AR_MYREQUEST)
    // Table: cust_invoice_master (cim_system_id='AR_INV')
    // ─────────────────────────────────────────────────────────────────────────
    private function myInvoiceRequest(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'invoiceDate' => trim((string) $request->input('sf_0', '')),
            'debtorType'  => trim((string) $request->input('sf_1', '')),
            'status'      => trim((string) $request->input('sf_2', '')),
        ];

        $base = $this->conn()->table('cust_invoice_master as cim')
            ->where('cim.cim_system_id', 'AR_INV')
            ->whereNotNull('cim.cim_invoice_no');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cim_invoice_no,''), IFNULL(cim_status,''), IFNULL(cim_cust_id,''), IFNULL(cim_cust_name,''), IFNULL(cim_total_amt,''))) LIKE ?",
                [$like]
            );
        }
        if ($sf['invoiceDate'] !== '') {
            $base->whereRaw("DATE_FORMAT(cim_invoice_date,'%d/%m/%Y') LIKE ?", ['%'.$sf['invoiceDate'].'%']);
        }
        if ($sf['debtorType'] !== '') {
            $base->where('cim_cust_type', $sf['debtorType']);
        }
        if ($sf['status'] !== '') {
            $base->where('cim_status', $sf['status']);
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'cim_cust_invoice_id',
                'cim_invoice_no',
                'cim_status',
                'cim_cust_id',
                'cim_cust_name',
                'cim_cust_type',
                'cim_total_amt',
                'cim_invoice_date',
                'cim_extended_field',
            ])
            ->orderByDesc('cim_invoice_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, $i) {
            $ext = is_string($r->cim_extended_field) ? json_decode($r->cim_extended_field, true) : (array) $r->cim_extended_field;
            return [
                'no'          => ($i + 1),
                'invoiceNo'   => $r->cim_invoice_no,
                'date'        => $r->cim_invoice_date ? date('d/m/Y', strtotime((string) $r->cim_invoice_date)) : '',
                'debtorId'    => $r->cim_cust_id,
                'debtorName'  => $r->cim_cust_name,
                'debtorType'  => $ext['cim_cust_type_desc'] ?? ($r->cim_cust_type === 'E' ? 'PENAJA' : 'PELAJAR'),
                'invoiceDate' => $r->cim_invoice_date ? date('d/m/Y', strtotime((string) $r->cim_invoice_date)) : '',
                'cimStatus'   => $r->cim_status,
                'amt'         => $r->cim_total_amt,
            ];
        })->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cust_invoice_master'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1757 — Invoice > Recurring List  (BL: DT_AR_RECURRING)
    // Table: cust_recurring_invoice_master
    // ─────────────────────────────────────────────────────────────────────────
    private function recurringList(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'debtorType'  => trim((string) $request->input('sf_0', '')),
            'startDate'   => trim((string) $request->input('sf_1', '')),
            'endDate'     => trim((string) $request->input('sf_2', '')),
            'status'      => trim((string) $request->input('sf_3', '')),
        ];

        $base = $this->conn()->table('cust_recurring_invoice_master');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(csm_recurring_no,''), IFNULL(vcs_vendor_code,''), IFNULL(csm_extended_field->>'$.csm_cust_type_desc',''), IFNULL(csm_extended_field->>'$.statusDesc',''))) LIKE ?",
                [$like]
            );
        }
        if ($sf['debtorType'] !== '') {
            $base->whereRaw("csm_extended_field->>'$.csm_cust_type_desc' = ?", [$sf['debtorType']]);
        }
        if ($sf['startDate'] !== '') {
            $base->whereRaw("DATE_FORMAT(csm_start_date,'%d/%m/%Y') LIKE ?", ['%'.$sf['startDate'].'%']);
        }
        if ($sf['endDate'] !== '') {
            $base->whereRaw("DATE_FORMAT(csm_end_date,'%d/%m/%Y') LIKE ?", ['%'.$sf['endDate'].'%']);
        }
        if ($sf['status'] !== '') {
            $base->whereRaw("csm_extended_field->>'$.statusDesc' = ?", [$sf['status']]);
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'csm_recur_invoice_master_id',
                'csm_recurring_no',
                'vcs_vendor_code',
                'csm_extended_field',
                'csm_start_date',
                'csm_end_date',
                'csm_enter_date',
                'csm_amount_permth',
            ])
            ->orderByDesc('csm_enter_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, $i) {
            $ext = is_string($r->csm_extended_field) ? json_decode($r->csm_extended_field, true) : (array) $r->csm_extended_field;
            return [
                'no'              => $i + 1,
                'recurringNo'     => $r->csm_recurring_no,
                'vendorCode'      => $r->vcs_vendor_code,
                'debtorType'      => $ext['csm_cust_type_desc'] ?? '',
                'premiseDesc'     => $ext['pe_id_desc'] ?? '',
                'startDate'       => $r->csm_start_date ? date('d/m/Y', strtotime((string) $r->csm_start_date)) : '',
                'endDate'         => $r->csm_end_date ? date('d/m/Y', strtotime((string) $r->csm_end_date)) : '',
                'generationDate'  => $r->csm_enter_date ? date('d/m/Y', strtotime((string) $r->csm_enter_date)) : '',
                'status'          => $ext['statusDesc'] ?? '',
                'amountPerMonth'  => $r->csm_amount_permth,
            ];
        })->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cust_recurring_invoice_master'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3280 — Invoice > Salary Deduction Schedule Listing
    // Table: cust_deduction_calculator JOIN cust_invoice_master
    // ─────────────────────────────────────────────────────────────────────────
    private function salaryDeductionScheduleListing(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('cust_deduction_calculator as cdc')
            ->join('cust_invoice_master as cim', 'cim.cim_invoice_no', '=', 'cdc.cim_invoice_no');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cim.cim_cust_id,''), IFNULL(cim.cim_cust_name,''), IFNULL(cdc.cim_invoice_no,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'cdc.cdc_id',
                'cim.cim_cust_id',
                'cim.cim_cust_name',
                'cdc.cim_invoice_no',
                'cim.cim_total_amt',
                'cdc.cdc_repayment_period',
                'cdc.cdc_amt_per_month',
            ])
            ->orderByDesc('cdc.cdc_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, $i) {
            return [
                'no'              => $i + 1,
                'staffId'         => $r->cim_cust_id.' - '.$r->cim_cust_name,
                'invoiceNo'       => $r->cim_invoice_no,
                'totalInvAmt'     => $r->cim_total_amt,
                'repaymentPeriod' => $r->cdc_repayment_period,
                'amtPerMonth'     => $r->cdc_amt_per_month,
            ];
        })->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cust_deduction_calculator'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1590 — Receipt > List of Receipts  (BL: V2_AR_RECEIPT_ENTRY_API)
    // Table: receipt_master
    // ─────────────────────────────────────────────────────────────────────────
    private function listOfReceipts(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'debtorType'   => trim((string) $request->input('sf_0', '')),
            'ou'           => trim((string) $request->input('sf_1', '')),
            'status'       => trim((string) $request->input('sf_2', '')),
        ];

        $base = $this->conn()->table('receipt_master as rma');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(rma.rma_receipt_no,''), IFNULL(rma.rma_cust_id,''), IFNULL(rma.rma_cust_name,''), IFNULL(rma.rma_status,''), IFNULL(rma.rma_cust_type,''))) LIKE ?",
                [$like]
            );
        }
        if ($sf['debtorType'] !== '') {
            $base->where('rma.rma_cust_type', $sf['debtorType']);
        }
        if ($sf['status'] !== '') {
            $base->where('rma.rma_status', $sf['status']);
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'rma.rma_receipt_master_id',
                'rma.rma_receipt_no',
                'rma.rma_cust_id',
                'rma.rma_cust_name',
                'rma.rma_receipt_desc',
                'rma.rma_cust_type',
                'rma.rma_approve_date',
                'rma.rma_status',
                'rma.rma_total_amt',
                'rma.rma_receipt_ref',
                'rma.createddate',
            ])
            ->orderByDesc('rma.createddate')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, $i) {
            return [
                'no'           => $i + 1,
                'receiptDate'  => $r->createddate ? date('d/m/Y', strtotime((string) $r->createddate)) : '',
                'receiptNo'    => $r->rma_receipt_no,
                'debtorId'     => $r->rma_cust_id,
                'debtorName'   => $r->rma_cust_name,
                'description'  => $r->rma_receipt_desc,
                'debtorType'   => $r->rma_cust_type,
                'approvedDate' => $r->rma_approve_date ? date('d/m/Y', strtotime((string) $r->rma_approve_date)) : '',
                'rmaStatus'    => $r->rma_status,
                'rmaTotalAmt'  => $r->rma_total_amt,
                'rmaReceiptRef'=> $r->rma_receipt_ref,
            ];
        })->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:receipt_master'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1742 / 1761 — Receipt > Bank-In Slip Generation / Download
    // Table: receipt_master JOIN receipt_pay_mode
    // ─────────────────────────────────────────────────────────────────────────
    private function bankInSlip(Request $request, int $page, int $limit, string $q, string $mode): array
    {
        $base = $this->conn()->table('receipt_master as rma')
            ->join('receipt_pay_mode as rpm', 'rpm.rma_receipt_master_id', '=', 'rma.rma_receipt_master_id');

        if ($mode === 'generation') {
            // Only DRAFT / cash or non-cash pending bank slip
            $base->whereIn('rma.rma_status', ['DRAFT', 'ENTRY', 'VERIFIED']);
        } else {
            $base->where('rma.rma_status', 'APPROVE');
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(rma.rma_receipt_no,''), IFNULL(rma.rma_cust_id,''), IFNULL(rma.rma_cust_name,''), IFNULL(rpm.rpm_bank_slip,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'rpm.rpm_receipt_paymode_id',
                'rma.rma_receipt_no',
                'rma.rma_cust_id',
                'rma.rma_cust_name',
                'rma.rma_status',
                'rma.rma_total_amt',
                'rpm.rpm_bank_slip',
                'rpm.rpm_total_amt',
                'rpm.rpm_extended_field',
                'rma.createddate',
                'rma.rma_approve_date',
            ])
            ->orderByDesc('rma.createddate')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, $i) {
            $ext = is_string($r->rpm_extended_field) ? json_decode($r->rpm_extended_field, true) : (array) $r->rpm_extended_field;
            return [
                'no'              => $i + 1,
                'receiptNo'       => $r->rma_receipt_no,
                'receiptDate'     => $r->createddate ? date('d/m/Y', strtotime((string) $r->createddate)) : '',
                'debtorId'        => $r->rma_cust_id,
                'debtorName'      => $r->rma_cust_name,
                'status'          => $r->rma_status,
                'totalAmt'        => $r->rma_total_amt,
                'bankSlip'        => $r->rpm_bank_slip,
                'paymentMode'     => $ext['rpm_payment_mode_desc'] ?? '',
            ];
        })->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:receipt_bankin_slip_'.$mode];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2347 — Receipt > List of Receipt on Behalf
    // Table: receipt_master
    // ─────────────────────────────────────────────────────────────────────────
    private function receiptOnBehalf(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('receipt_master as rma')
            ->whereNotNull('rma.rma_extended_field')
            ->whereRaw("rma.rma_extended_field->>'$.rma_on_behalf' = 'Y'");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(rma.rma_receipt_no,''), IFNULL(rma.rma_cust_id,''), IFNULL(rma.rma_cust_name,''), IFNULL(rma.rma_status,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select(['rma.rma_receipt_master_id', 'rma.rma_receipt_no', 'rma.rma_cust_id',
                      'rma.rma_cust_name', 'rma.rma_status', 'rma.rma_total_amt', 'rma.createddate'])
            ->orderByDesc('rma.createddate')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(fn ($r, $i) => [
            'no'         => $i + 1,
            'receiptNo'  => $r->rma_receipt_no,
            'debtorId'   => $r->rma_cust_id,
            'debtorName' => $r->rma_cust_name,
            'status'     => $r->rma_status,
            'totalAmt'   => $r->rma_total_amt,
            'date'       => $r->createddate ? date('d/m/Y', strtotime((string) $r->createddate)) : '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:receipt_on_behalf'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1740 — Receipt > Update Foreign Currency
    // Table: receipt_master JOIN receipt_pay_mode (non-MYR)
    // ─────────────────────────────────────────────────────────────────────────
    private function updateForeignCurrency(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('receipt_master as rm')
            ->join('receipt_pay_mode as rpm', 'rpm.rma_receipt_master_id', '=', 'rm.rma_receipt_master_id')
            ->where('rpm.rpm_payment_mode', '!=', 1)  // non-cash (foreign currency)
            ->whereRaw("IFNULL(rm.rma_currency_code,'MYR') != 'MYR'");

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(rm.rma_receipt_no,''), IFNULL(rm.rma_cust_id,''), IFNULL(rm.rma_cust_name,''), IFNULL(rm.rma_currency_code,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'rpm.rpm_receipt_paymode_id',
                'rm.rma_receipt_no',
                'rm.createddate',
                'rm.rma_cust_id',
                'rm.rma_cust_name',
                'rm.rma_status',
                'rpm.rpm_extended_field',
                'rpm.rpm_reference_no',
                'rm.rma_currency_code',
                'rpm.rpm_total_amt_fc',
                'rm.rma_conversion_rate',
                'rpm.rpm_total_amt',
                'rpm.rpm_adjust_amt',
            ])
            ->orderByDesc('rm.createddate')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, $i) {
            $ext = is_string($r->rpm_extended_field) ? json_decode($r->rpm_extended_field, true) : (array) $r->rpm_extended_field;
            return [
                'no'             => $i + 1,
                'receiptNo'      => $r->rma_receipt_no,
                'receiptDate'    => $r->createddate ? date('d/m/Y', strtotime((string) $r->createddate)) : '',
                'debtorId'       => $r->rma_cust_id,
                'debtorName'     => $r->rma_cust_name,
                'receiptStatus'  => $r->rma_status,
                'paymentMode'    => $ext['rpm_payment_mode_desc'] ?? '',
                'referenceNo'    => $r->rpm_reference_no,
                'currencyCode'   => $r->rma_currency_code,
                'totalAmtFc'     => $r->rpm_total_amt_fc,
                'conversionRate' => $r->rma_conversion_rate,
                'totalAmt'       => $r->rpm_total_amt,
                'adjustAmt'      => $r->rpm_adjust_amt,
            ];
        })->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:receipt_foreign_currency'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3249 — Receipt > Cash Receipt Release
    // Table: receipt_master JOIN receipt_pay_mode
    // ─────────────────────────────────────────────────────────────────────────
    private function cashReceiptRelease(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'debtorType' => trim((string) $request->input('sf_0', '')),
            'status'     => trim((string) $request->input('sf_1', '')),
            'payMode'    => trim((string) $request->input('sf_2', '')),
        ];

        $base = $this->conn()->table('receipt_master as rma')
            ->join('receipt_pay_mode as rpm', 'rpm.rma_receipt_master_id', '=', 'rma.rma_receipt_master_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(rma.rma_receipt_no,''), IFNULL(rma.rma_cust_id,''), IFNULL(rma.rma_cust_name,''), IFNULL(rma.rma_status,''))) LIKE ?",
                [$like]
            );
        }
        if ($sf['debtorType'] !== '') {
            $base->where('rma.rma_cust_type', $sf['debtorType']);
        }
        if ($sf['status'] !== '') {
            $base->where('rma.rma_status', $sf['status']);
        }
        if ($sf['payMode'] !== '') {
            $base->where('rpm.rpm_payment_mode', $sf['payMode']);
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'rma.rma_receipt_no', 'rma.createddate', 'rma.rma_cust_id',
                'rma.rma_cust_name', 'rma.rma_status', 'rma.rma_total_amt',
                'rpm.rpm_bank_slip', 'rpm.rpm_bank_slip_date',
            ])
            ->orderByDesc('rma.createddate')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(fn ($r, $i) => [
            'no'           => $i + 1,
            'receiptNo'    => $r->rma_receipt_no,
            'date'         => $r->createddate ? date('d/m/Y', strtotime((string) $r->createddate)) : '',
            'debtorId'     => $r->rma_cust_id,
            'debtorName'   => $r->rma_cust_name,
            'rmaStatus'    => $r->rma_status,
            'rmaTotalAmt'  => $r->rma_total_amt,
            'rpmBankSlip'  => $r->rpm_bank_slip,
            'rpmBankSlipDate' => $r->rpm_bank_slip_date ? date('d/m/Y', strtotime((string) $r->rpm_bank_slip_date)) : '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cash_receipt_release'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1652 — Cheque > Cheque Registry  (no complete BL available)
    // Table: cheque_registry
    // ─────────────────────────────────────────────────────────────────────────
    private function chequeRegistry(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'receiptNo'  => trim((string) $request->input('sf_0', '')),
            'debtorId'   => trim((string) $request->input('sf_2', '')),
            'debtorName' => trim((string) $request->input('sf_3', '')),
            'chequeNo'   => trim((string) $request->input('sf_4', '')),
        ];

        $base = $this->conn()->table('cheque_registry as cr');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cr.cr_cheque_no,''), IFNULL(cr.cr_cust_id,''), IFNULL(cr.cr_drawer_name,''), IFNULL(cr.cr_invoice_no,''))) LIKE ?",
                [$like]
            );
        }
        foreach (['chequeNo' => 'cr.cr_cheque_no', 'debtorId' => 'cr.cr_cust_id', 'debtorName' => 'cr.cr_drawer_name'] as $sfKey => $col) {
            if ($sf[$sfKey] !== '') {
                $base->whereRaw("LOWER($col) LIKE ?", [$this->likeEscape(mb_strtolower($sf[$sfKey], 'UTF-8'))]);
            }
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->orderByDesc('cr.cr_received_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'           => $i + 1,
            'chequeNo'     => $r->cr_cheque_no ?? '',
            'debtorId'     => $r->cr_cust_id ?? '',
            'drawerName'   => $r->cr_drawer_name ?? '',
            'issuerBank'   => $r->cr_issuer_bank ?? '',
            'branch'       => $r->cr_branch_name ?? '',
            'chequeDate'   => isset($r->cr_cheque_date) ? date('d/m/Y', strtotime((string) $r->cr_cheque_date)) : '',
            'chequeAmt'    => $r->cr_cheque_amt ?? '',
            'receivedDate' => isset($r->cr_received_date) ? date('d/m/Y', strtotime((string) $r->cr_received_date)) : '',
            'invoiceNo'    => $r->cr_invoice_no ?? '',
            'validity'     => $r->cr_validity ?? '',
            'remark'       => $r->cr_remark ?? '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cheque_registry'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1656 — Cheque > Cheque Release  (cheques pending release)
    // ─────────────────────────────────────────────────────────────────────────
    private function chequeRelease(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('cheque_registry as cr')
            ->where(function ($b) {
                $b->whereNull('cr.cr_flag')->orWhere('cr.cr_flag', '!=', 'Y');
            });

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cr.cr_cheque_no,''), IFNULL(cr.cr_cust_id,''), IFNULL(cr.cr_drawer_name,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->orderByDesc('cr.cr_received_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'         => $i + 1,
            'chequeNo'   => $r->cr_cheque_no ?? '',
            'debtorId'   => $r->cr_cust_id ?? '',
            'drawerName' => $r->cr_drawer_name ?? '',
            'issuerBank' => $r->cr_issuer_bank ?? '',
            'chequeDate' => isset($r->cr_cheque_date) ? date('d/m/Y', strtotime((string) $r->cr_cheque_date)) : '',
            'chequeAmt'  => $r->cr_cheque_amt ?? '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cheque_release'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2528 — Cheque > List of Cheque Release (view by cheque no)
    // ─────────────────────────────────────────────────────────────────────────
    private function chequeReleaseView(Request $request, int $page, int $limit, string $q): array
    {
        $chequeNo = trim((string) $request->input('cr_cheque_no', ''));

        $base = $this->conn()->table('cheque_registry as cr')
            ->leftJoin('receipt_pay_mode as rpm', 'rpm.rpm_cheque_no', '=', 'cr.cr_cheque_no')
            ->leftJoin('receipt_master as rma', 'rma.rma_receipt_master_id', '=', 'rpm.rma_receipt_master_id');

        if ($chequeNo !== '') {
            $base->where('cr.cr_cheque_no', $chequeNo);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cr.cr_cheque_no,''), IFNULL(cr.cr_cust_id,''), IFNULL(cr.cr_drawer_name,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'cr.cr_cust_id', 'cr.cr_drawer_name', 'cr.cr_issuer_bank',
                'cr.cr_branch_name', 'cr.cr_cheque_no', 'cr.cr_cheque_date',
                'cr.cr_cheque_amt', 'cr.cr_received_date', 'cr.cr_invoice_no',
                'cr.cr_validity', 'cr.cr_remark', 'cr.cr_release_date',
                'rma.rma_receipt_no', 'rma.createddate as receipt_date', 'rma.rma_status',
            ])
            ->orderByDesc('cr.cr_received_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'           => $i + 1,
            'debtorId'     => $r->cr_cust_id ?? '',
            'drawerName'   => $r->cr_drawer_name ?? '',
            'issuerBank'   => $r->cr_issuer_bank ?? '',
            'branch'       => $r->cr_branch_name ?? '',
            'chequeNo'     => $r->cr_cheque_no ?? '',
            'chequeDate'   => isset($r->cr_cheque_date) ? date('d/m/Y', strtotime((string) $r->cr_cheque_date)) : '',
            'chequeAmt'    => $r->cr_cheque_amt ?? '',
            'receivedDate' => isset($r->cr_received_date) ? date('d/m/Y', strtotime((string) $r->cr_received_date)) : '',
            'referenceNo'  => $r->cr_invoice_no ?? '',
            'validity'     => $r->cr_validity ?? '',
            'remark'       => $r->cr_remark ?? '',
            'releaseDate'  => isset($r->cr_release_date) ? date('d/m/Y', strtotime((string) $r->cr_release_date)) : '',
            'noReceipt'    => $r->rma_receipt_no ?? '',
            'receiptDate'  => isset($r->receipt_date) ? date('d/m/Y', strtotime((string) $r->receipt_date)) : '',
            'receiptStatus'=> $r->rma_status ?? '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cheque_release_view'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1720 — Cheque > Cheque List
    // ─────────────────────────────────────────────────────────────────────────
    private function chequeList(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'chequeNo'   => trim((string) $request->input('sf_0', '')),
            'debtorId'   => trim((string) $request->input('sf_1', '')),
            'debtorName' => trim((string) $request->input('sf_2', '')),
            'issuerBank' => trim((string) $request->input('sf_3', '')),
            'amount'     => trim((string) $request->input('sf_4', '')),
        ];

        $base = $this->conn()->table('cheque_registry as cr');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(cr.cr_cheque_no,''), IFNULL(cr.cr_cust_id,''), IFNULL(cr.cr_drawer_name,''), IFNULL(cr.cr_issuer_bank,''))) LIKE ?",
                [$like]
            );
        }
        foreach (['chequeNo' => 'cr.cr_cheque_no', 'debtorId' => 'cr.cr_cust_id', 'debtorName' => 'cr.cr_drawer_name', 'issuerBank' => 'cr.cr_issuer_bank'] as $sfKey => $col) {
            if ($sf[$sfKey] !== '') {
                $base->whereRaw("LOWER($col) LIKE ?", [$this->likeEscape(mb_strtolower($sf[$sfKey], 'UTF-8'))]);
            }
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->orderByDesc('cr.cr_received_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'           => $i + 1,
            'chequeNo'     => $r->cr_cheque_no ?? '',
            'debtorId'     => $r->cr_cust_id ?? '',
            'drawerName'   => $r->cr_drawer_name ?? '',
            'issuerBank'   => $r->cr_issuer_bank ?? '',
            'branch'       => $r->cr_branch_name ?? '',
            'chequeDate'   => isset($r->cr_cheque_date) ? date('d/m/Y', strtotime((string) $r->cr_cheque_date)) : '',
            'chequeAmt'    => $r->cr_cheque_amt ?? '',
            'receivedDate' => isset($r->cr_received_date) ? date('d/m/Y', strtotime((string) $r->cr_received_date)) : '',
            'invoiceNo'    => $r->cr_invoice_no ?? '',
            'status'       => $r->cr_flag === 'Y' ? 'Released' : 'Pending',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:cheque_list'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1045 — Cheque Return > List Of Return Cheque
    // Table: return_cheque/cheque_return + receipt_master + receipt_pay_mode
    // ─────────────────────────────────────────────────────────────────────────
    private function returnChequeList(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'receiptNo'  => trim((string) $request->input('sf_0', '')),
            'debtorId'   => trim((string) $request->input('sf_2', '')),
            'debtorName' => trim((string) $request->input('sf_3', '')),
            'chequeNo'   => trim((string) $request->input('sf_4', '')),
        ];

        // Legacy BL uses cheque_registry + receipt_master + receipt_pay_mode
        $base = $this->conn()->table('cheque_registry as rck')
            ->leftJoin('receipt_pay_mode as rpm', 'rpm.rpm_cheque_no', '=', 'rck.cr_cheque_no')
            ->leftJoin('receipt_master as rma', 'rma.rma_receipt_master_id', '=', 'rpm.rma_receipt_master_id')
            ->whereNotNull('rck.cr_return_date');  // returned cheques only

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(rma.rma_receipt_no,''), IFNULL(rma.rma_cust_id,''), IFNULL(rma.rma_cust_name,''), IFNULL(rck.cr_cheque_no,''))) LIKE ?",
                [$like]
            );
        }
        if ($sf['receiptNo'] !== '') {
            $base->whereRaw("LOWER(rma.rma_receipt_no) LIKE ?", [$this->likeEscape(mb_strtolower($sf['receiptNo'], 'UTF-8'))]);
        }
        if ($sf['debtorId'] !== '') {
            $base->whereRaw("LOWER(rma.rma_cust_id) LIKE ?", [$this->likeEscape(mb_strtolower($sf['debtorId'], 'UTF-8'))]);
        }
        if ($sf['chequeNo'] !== '') {
            $base->whereRaw("LOWER(rck.cr_cheque_no) LIKE ?", [$this->likeEscape(mb_strtolower($sf['chequeNo'], 'UTF-8'))]);
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'rma.rma_receipt_no', 'rma.createddate as receipt_date',
                'rma.rma_cust_id', 'rma.rma_cust_name',
                'rck.cr_cheque_no', 'rck.cr_issuer_bank', 'rpm.rpm_total_amt',
                'rck.cr_remark', 'rck.cr_return_date', 'rma.rma_status',
                'rck.cr_return_reason',
            ])
            ->orderByDesc('rck.cr_return_date')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'          => $i + 1,
            'receiptNo'   => $r->rma_receipt_no ?? '',
            'receiptDate' => isset($r->receipt_date) ? date('d/m/Y', strtotime((string) $r->receipt_date)) : '',
            'debtorId'    => $r->rma_cust_id ?? '',
            'debtorName'  => $r->rma_cust_name ?? '',
            'chequeNo'    => $r->cr_cheque_no ?? '',
            'slipBank'    => $r->cr_issuer_bank ?? '',
            'amount'      => $r->rpm_total_amt ?? '',
            'statusDesc'  => $r->rma_status ?? '',
            'rcqReason'   => $r->cr_return_reason ?? $r->cr_remark ?? '',
            'approveDate' => isset($r->cr_return_date) ? date('d/m/Y', strtotime((string) $r->cr_return_date)) : '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:return_cheque'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2183 — Offline Receipt > Application  (My Applications)
    // Table: offline_receipt_authorize
    // ─────────────────────────────────────────────────────────────────────────
    private function offlineReceiptApplication(Request $request, int $page, int $limit, string $q): array
    {
        $sf = [
            'status' => trim((string) $request->input('sf_2', '')),
            'ptj'    => trim((string) $request->input('sf_1', '')),
        ];

        $base = $this->conn()->table('offline_receipt_authorize as ora')
            ->where('ora.ore_application_type', '!=', 'PREPRINTED');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ore_counter_no,''), IFNULL(oun_code_ptj,''), IFNULL(ore_status,''))) LIKE ?",
                [$like]
            );
        }
        if ($sf['status'] !== '') {
            $base->where('ora.ore_status', $sf['status']);
        }
        if ($sf['ptj'] !== '') {
            $base->where('ora.oun_code_ptj', $sf['ptj']);
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'ora.ore_offline_receipt_id', 'ora.ore_counter_no', 'ora.oun_code_ptj',
                'ora.ore_purposed_code', 'ora.ore_status', 'ora.ore_application_type',
                'ora.createddate',
            ])
            ->orderByDesc('ora.createddate')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'         => $i + 1,
            'counterNo'  => $r->ore_counter_no ?? '',
            'ptj'        => $r->oun_code_ptj ?? '',
            'purpose'    => $r->ore_purposed_code ?? '',
            'status'     => $r->ore_status ?? '',
            'type'       => $r->ore_application_type ?? '',
            'date'       => $r->createddate ? date('d/m/Y', strtotime((string) $r->createddate)) : '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:offline_receipt_application'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2108 — Offline Receipt > Receipt Collection Entry
    // Table: offline_receipt_master + offline_receipt_details
    // ─────────────────────────────────────────────────────────────────────────
    private function offlineReceiptCollectionEntry(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('offline_receipt_master as orm')
            ->join('offline_receipt_details as ofd', 'ofd.orm_receipt_master_id', '=', 'orm.orm_receipt_master_id');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(orm.orm_counter_no,''), IFNULL(orm.orm_batch_no,''), IFNULL(orm.orm_status,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->distinct()->count('orm.orm_receipt_master_id');
        $rows  = (clone $base)
            ->select([
                'orm.orm_receipt_master_id', 'orm.orm_counter_no', 'orm.orm_batch_no',
                'orm.orm_status', 'orm.orm_total_amt', 'orm.createddate',
            ])
            ->groupBy('orm.orm_receipt_master_id', 'orm.orm_counter_no', 'orm.orm_batch_no', 'orm.orm_status', 'orm.orm_total_amt', 'orm.createddate')
            ->orderByDesc('orm.createddate')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'        => $i + 1,
            'counterNo' => $r->orm_counter_no ?? '',
            'batchNo'   => $r->orm_batch_no ?? '',
            'status'    => $r->orm_status ?? '',
            'totalAmt'  => $r->orm_total_amt ?? '',
            'date'      => $r->createddate ? date('d/m/Y', strtotime((string) $r->createddate)) : '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:offline_receipt_collection'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2500 — Offline Receipt > Counter
    // Table: offline_receipt_authorize
    // ─────────────────────────────────────────────────────────────────────────
    private function offlineReceiptCounter(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('offline_receipt_authorize as ora');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ore_counter_no,''), IFNULL(oun_code_ptj,''), IFNULL(ore_purposed_code,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select(['ora.ore_offline_receipt_id', 'ora.ore_counter_no', 'ora.oun_code_ptj', 'ora.ore_purposed_code', 'ora.ore_status'])
            ->orderBy('ora.ore_counter_no')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'        => $i + 1,
            'counterNo' => $r->ore_counter_no ?? '',
            'ptj'       => $r->oun_code_ptj ?? '',
            'purpose'   => $r->ore_purposed_code ?? '',
            'status'    => $r->ore_status ?? '',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:offline_receipt_counter'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 2572 — Setup > Signature Setup
    // Table: lookup_signature
    // ─────────────────────────────────────────────────────────────────────────
    private function signatureSetup(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('lookup_signature');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(lks_staff_id,''), IFNULL(lks_staff_name,''), IFNULL(lks_staff_position,''), IFNULL(lks_status,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'lks_id', 'lks_staff_id', 'lks_staff_name', 'lks_staff_position',
                'lks_staff_jobcode', 'lks_signature', 'lks_status', 'lks_extended_field',
            ])
            ->orderBy('lks_id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(function ($r, $i) {
            $ext = is_string($r->lks_extended_field) ? json_decode($r->lks_extended_field, true) : (array) ($r->lks_extended_field ?? []);
            return [
                'no'           => $i + 1,
                'lksId'        => $r->lks_id,
                'lksStaffId'   => $r->lks_staff_id,
                'lksStaffName' => $r->lks_staff_name,
                'lksStaffPosition' => trim($r->lks_staff_position.' - '.($ext['lks_staff_position_desc'] ?? ''), ' - '),
                'lksStaffJobcode'  => trim($r->lks_staff_jobcode.' - '.($ext['lks_staff_jobcode_desc'] ?? ''), ' - '),
                'lksSignature' => $r->lks_signature,
                'lksStatus'    => $r->lks_status === 'Y' ? 'YES' : 'NO',
            ];
        })->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:lookup_signature'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 3507 — Setup > Premise Details
    // Table: premise
    // ─────────────────────────────────────────────────────────────────────────
    private function premiseDetails(Request $request, int $page, int $limit, string $q): array
    {
        $base = $this->conn()->table('premise');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(pe_premise_code,''), IFNULL(pe_premise_desc,''), IFNULL(pe_address1,''), IFNULL(pe_city,''), IFNULL(pe_category,''), IFNULL(fty_fund_type,''), IFNULL(aim_asset_code,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();
        $rows  = (clone $base)
            ->select([
                'pe_id', 'pe_premise_code', 'pe_premise_desc', 'pe_address1',
                'pe_address2', 'pe_city', 'pe_postcode', 'pe_state',
                'pe_category', 'fty_fund_type', 'aim_asset_code', 'pe_status',
            ])
            ->orderBy('pe_premise_code')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'peId'          => $r->pe_id,
            'no'            => $i + 1,
            'premiseCode'   => $r->pe_premise_code,
            'premiseDesc'   => $r->pe_premise_desc,
            'address1'      => $r->pe_address1,
            'address2'      => $r->pe_address2,
            'city'          => $r->pe_city,
            'postcode'      => $r->pe_postcode,
            'state'         => $r->pe_state,
            'category'      => $r->pe_category,
            'ftyFundType'   => $r->fty_fund_type,
            'aimAssetCode'  => $r->aim_asset_code,
            'ldeStatusDesc' => $r->pe_status == '1' ? 'ACTIVE' : 'INACTIVE',
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => 'ar:premise'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MENUID 1936 — Setup > Non-Invoice Structure
    // MENUID 2117 — Setup > Invoice Structure
    // Table: noninv_struct_master + noninv_struct_details + cust_invoice_item
    // ─────────────────────────────────────────────────────────────────────────
    private function nonInvoiceStructure(Request $request, int $page, int $limit, string $q): array
    {
        return $this->structureListing($request, $page, $limit, $q, 'non_inv', 'ar:noninv_struct_master');
    }

    private function invoiceStructure(Request $request, int $page, int $limit, string $q): array
    {
        return $this->structureListing($request, $page, $limit, $q, 'inv', 'ar:noninv_struct_master_invoice');
    }

    private function structureListing(Request $request, int $page, int $limit, string $q, string $type, string $connector): array
    {
        $base = $this->conn()->table('noninv_struct_master as nsm')
            ->join('noninv_struct_details as nsd', 'nsd.nsm_id', '=', 'nsm.nsm_id')
            ->join('cust_invoice_item as cii', function ($join) {
                $join->on('nsd.cii_item_code', '=', 'cii.cii_item_code')
                     ->where('cii.cii_module_id', 'AR')
                     ->where('cii.cii_isopenpayment', 'N');
            })
            ->where('nsd.nsd_trans_type', $type === 'inv' ? 'DT' : 'CT');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(nsm.nsm_fee_str_code,''), IFNULL(nsm.nsm_fee_str_desc,''), IFNULL(nsm.nsm_program_level,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->distinct()->count('nsm.nsm_id');
        $rows  = (clone $base)
            ->select([
                'nsm.nsm_id', 'nsm.nsm_fee_str_code', 'nsm.nsm_fee_str_desc',
                'nsm.nsm_program_level', 'nsd.nsd_tot_amt', 'nsd.cii_item_code',
            ])
            ->groupBy('nsm.nsm_id', 'nsm.nsm_fee_str_code', 'nsm.nsm_fee_str_desc', 'nsm.nsm_program_level', 'nsd.nsd_tot_amt', 'nsd.cii_item_code')
            ->orderBy('nsm.nsm_fee_str_code')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = collect($rows)->map(fn ($r, $i) => [
            'no'            => $i + 1,
            'nsmId'         => $r->nsm_id,
            'nsmFeeStrCode' => $r->nsm_fee_str_code,
            'nsmFeeStrDesc' => $r->nsm_fee_str_desc,
            'nsmProgramLevel' => $r->nsm_program_level,
            'ciiItemCode'   => $r->cii_item_code,
            'nsdTotAmt'     => $r->nsd_tot_amt,
        ])->all();

        return ['rows' => $data, 'total' => $total, 'connector' => $connector];
    }
}
