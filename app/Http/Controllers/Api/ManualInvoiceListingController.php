<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManualInvoiceLineRequest;
use App\Http\Traits\ApiResponse;
use App\Models\CustInvoiceDetails;
use App\Models\CustInvoiceMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Student Finance > Manual Invoice Listing (PAGEID 2343 / MENUID 2897).
 *
 * Source: FIMS BL `DT_SF_MANUAL_INV_LISTING`. Reads from
 * `cust_invoice_master` scoped to `cim_system_id='STUD_INV'` AND
 * `cim_invoice_type='12'`.
 *
 * Manual Invoice Form (MENUID 2898): `show()` hydrates Invoice Head /
 * Debit / Credit grids. Lines are inserted/deleted (`POST`, `DELETE` …/lines)
 * only while status is DRAFT, using `cust_invoice_details`: next id =
 * legacy-style `MAX(cid_cust_invoice_detl_id)+1` on mysql_secondary,
 * `cid_transaction_type` DT or CR. Header totals are rolled up from DT
 * amounts (fallback: SUM of all lines). Save/Submit head workflow deferred.
 */
class ManualInvoiceListingController extends Controller
{
    use ApiResponse;

    private const STATUSES = [
        'DRAFT' => 'Draft',
        'APPROVE' => 'Approved',
        'ENTRY' => 'Entry',
        'VERIFIED' => 'Verified',
        'ENDORSE' => 'Endorsed',
        'REJECT' => 'Rejected',
        'CANCEL' => 'Cancelled',
    ];

    private const CUST_TYPES = [
        'A' => 'PELAJAR',
        'E' => 'PENAJA',
    ];

    private const SORTABLE = [
        'cim_invoice_no',
        'cim_invoice_date',
        'cim_cust_id',
        'cim_cust_name',
        'cim_status',
        'cim_total_amt',
    ];

    public function options(): JsonResponse
    {
        return $this->sendOk([
            'debtorType' => array_map(
                fn ($id, $label) => ['id' => $id, 'label' => $label],
                array_keys(self::CUST_TYPES),
                array_values(self::CUST_TYPES),
            ),
            'status' => array_map(
                fn ($id, $label) => ['id' => $id, 'label' => $label],
                array_keys(self::STATUSES),
                array_values(self::STATUSES),
            ),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'cim_invoice_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'cim_invoice_date';
        }

        $invoiceDate = trim((string) $request->input('cim_invoice_date', ''));
        $custType = trim((string) $request->input('cim_cust_type', ''));
        $status = trim((string) $request->input('cim_status', ''));
        if ($custType !== '' && ! isset(self::CUST_TYPES[$custType])) {
            $custType = '';
        }
        if ($status !== '' && ! isset(self::STATUSES[$status])) {
            $status = '';
        }

        $base = CustInvoiceMaster::query()
            ->where('cim_system_id', 'STUD_INV')
            ->where('cim_invoice_type', '12');

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(cim_invoice_no, ''),
                    IFNULL(cim_status, ''),
                    IFNULL(cim_cust_id, ''),
                    IFNULL(cim_cust_name, ''),
                    IFNULL(cim_extended_field->>'$.cim_cust_type_desc',
                        IF(cim_cust_type='E','PENAJA','PELAJAR')),
                    IFNULL(cim_semester_id, ''),
                    IFNULL(cim_total_amt, ''),
                    IFNULL(DATE_FORMAT(cim_invoice_date, '%d/%m/%Y %H:%i'), '')
                )) LIKE ?",
                [$like]
            );
        }

        if ($invoiceDate !== '') {
            $like = $this->likeEscape(mb_strtolower($invoiceDate, 'UTF-8'));
            $base->whereRaw(
                "LOWER(IFNULL(DATE_FORMAT(cim_invoice_date, '%d/%m/%Y'), '')) LIKE ?",
                [$like]
            );
        }

        if ($custType !== '') {
            $base->where('cim_cust_type', $custType);
        }

        if ($status !== '') {
            $base->where('cim_status', $status);
        }

        $total = (clone $base)->count();
        $grand = (clone $base)->sum('cim_total_amt');

        $rows = (clone $base)
            ->select([
                'cim_cust_invoice_id',
                'cim_invoice_no',
                'cim_invoice_date',
                'cim_status',
                'cim_cust_id',
                'cim_cust_name',
                'cim_cust_type',
                'cim_total_amt',
                'cim_crnote_amt',
                'cim_dnnote_amt',
                'cim_dcnote_amt',
                'cim_paid_amt',
                'cim_bal_amt',
                DB::raw("cim_extended_field->>'\$.cim_cust_type_desc' as cust_type_desc"),
            ])
            ->orderBy($sortBy, $sortDir)
            ->orderBy('cim_cust_invoice_id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(function ($r, int $i) use ($page, $limit) {
            $debtorTypeLabel = $r->cust_type_desc
                ?: ($r->cim_cust_type === 'E' ? 'PENAJA' : 'PELAJAR');
            $dt = $r->cim_invoice_date ? Carbon::parse($r->cim_invoice_date) : null;

            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'id' => (int) $r->cim_cust_invoice_id,
                'invoiceNo' => $r->cim_invoice_no,
                'invoiceDate' => $dt ? $dt->format('d/m/Y') : null,
                'invoiceDateTime' => $dt ? $dt->format('d/m/Y H:i') : null,
                'invoiceDateIso' => $dt ? $dt->toIso8601String() : null,
                'status' => $r->cim_status,
                'debtorId' => $r->cim_cust_id,
                'debtorName' => $r->cim_cust_name,
                'debtorType' => $r->cim_cust_type,
                'debtorTypeLabel' => $debtorTypeLabel,
                'totalAmt' => $r->cim_total_amt !== null ? (float) $r->cim_total_amt : 0.0,
                'crNoteAmt' => $r->cim_crnote_amt !== null ? (float) $r->cim_crnote_amt : 0.0,
                'dnNoteAmt' => $r->cim_dnnote_amt !== null ? (float) $r->cim_dnnote_amt : 0.0,
                'dcNoteAmt' => $r->cim_dcnote_amt !== null ? (float) $r->cim_dcnote_amt : 0.0,
                'paidAmt' => $r->cim_paid_amt !== null ? (float) $r->cim_paid_amt : 0.0,
                'balAmt' => $r->cim_bal_amt !== null ? (float) $r->cim_bal_amt : 0.0,
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'footer' => [
                'totalAmt' => (float) ($grand ?? 0),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->findScopedInvoice($id);

        if ($invoice === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Manual invoice not found');
        }

        return $this->sendOk($this->detailPayload($invoice));
    }

    public function storeLine(StoreManualInvoiceLineRequest $request, int $id): JsonResponse
    {
        $invoice = $this->findScopedInvoice($id);
        if ($invoice === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Manual invoice not found');
        }

        if ($invoice->cim_status !== 'DRAFT') {
            return $this->sendError(
                409,
                'INVOICE_NOT_DRAFT',
                'Lines can only be added while the invoice is DRAFT.',
            );
        }

        $validated = $request->validated();
        $txType = strtoupper((string) $validated['transaction_type']);

        $totalAmt = round((float) $validated['total_amt'], 2);
        $taxAmt = round((float) ($validated['tax_amt'] ?? 0), 2);
        $nettAmt = round(max(0.0, $totalAmt - $taxAmt), 2);

        $nowStr = now()->format('Y-m-d H:i:s');
        $username = $this->currentUsername();

        DB::connection('mysql_secondary')->transaction(function () use (
            $validated,
            $invoice,
            $txType,
            $totalAmt,
            $taxAmt,
            $nettAmt,
            $username,
            $nowStr,
        ) {
            $newId = $this->nextDetailSeq();

            CustInvoiceDetails::query()->create([
                'cid_cust_invoice_detl_id' => $newId,
                'cim_cust_invoice_id' => $invoice->cim_cust_invoice_id,
                'cii_item_category' => $validated['item_category'] ?? null,
                'cii_item_code' => $validated['item_code'] ?? null,
                'fty_fund_type' => $validated['fund_type'] ?? null,
                'at_activity_code' => $validated['activity_code'] ?? null,
                'oun_code' => $validated['oun_code'] ?? null,
                'ccr_costcentre' => $validated['cost_centre'] ?? null,
                'cpa_project_no' => $validated['project_no'] ?? null,
                'acm_acct_code' => $validated['acct_code'] ?? null,
                'cid_taxcode' => $validated['tax_code'] ?? null,
                'cid_taxamt' => $taxAmt,
                'cid_total_amt' => $totalAmt,
                'cid_crnote_amt' => 0,
                'cid_dnnote_amt' => 0,
                'cid_dcnote_amt' => 0,
                'cid_nett_amt' => $nettAmt,
                'cid_bal_amt' => $nettAmt,
                'cid_transaction_type' => $txType,
                'cid_extended_field' => [],
                'createdby' => $username,
                'createddate' => $nowStr,
            ]);

            $this->refreshTotalsFromLines((string) $invoice->cim_cust_invoice_id, $username, $nowStr);
        });

        $fresh = CustInvoiceMaster::query()
            ->where('cim_cust_invoice_id', $invoice->cim_cust_invoice_id)
            ->first();

        return $fresh
            ? $this->sendOk($this->detailPayload($fresh))
            : $this->sendError(500, 'INTERNAL_ERROR', 'Unable to reload invoice.');
    }

    public function destroyLine(int $id, int $lineId): JsonResponse
    {
        $invoice = $this->findScopedInvoice($id);
        if ($invoice === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Manual invoice not found');
        }

        if ($invoice->cim_status !== 'DRAFT') {
            return $this->sendError(
                409,
                'INVOICE_NOT_DRAFT',
                'Lines can only be removed while the invoice is DRAFT.',
            );
        }

        $line = CustInvoiceDetails::query()
            ->where('cid_cust_invoice_detl_id', $lineId)
            ->where('cim_cust_invoice_id', $invoice->cim_cust_invoice_id)
            ->first();

        if ($line === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Invoice line not found.');
        }

        $nowStr = now()->format('Y-m-d H:i:s');
        $username = $this->currentUsername();

        DB::connection('mysql_secondary')->transaction(function () use ($line, $invoice, $username, $nowStr) {
            $line->delete();
            $this->refreshTotalsFromLines((string) $invoice->cim_cust_invoice_id, $username, $nowStr);
        });

        $fresh = CustInvoiceMaster::query()
            ->where('cim_cust_invoice_id', $invoice->cim_cust_invoice_id)
            ->first();

        return $fresh
            ? $this->sendOk($this->detailPayload($fresh))
            : $this->sendError(500, 'INTERNAL_ERROR', 'Unable to reload invoice.');
    }

    public function destroy(int $id): JsonResponse
    {
        $invoice = CustInvoiceMaster::query()
            ->where('cim_cust_invoice_id', $id)
            ->where('cim_system_id', 'STUD_INV')
            ->where('cim_invoice_type', '12')
            ->first(['cim_cust_invoice_id', 'cim_status']);

        if (! $invoice) {
            return $this->sendError(404, 'NOT_FOUND', 'Manual invoice not found');
        }

        if ($invoice->cim_status !== 'DRAFT') {
            return $this->sendError(
                409,
                'INVOICE_NOT_DRAFT',
                'Only DRAFT invoices can be deleted.',
            );
        }

        DB::connection('mysql_secondary')->transaction(function () use ($invoice) {
            CustInvoiceDetails::query()
                ->where('cim_cust_invoice_id', $invoice->cim_cust_invoice_id)
                ->delete();
            CustInvoiceMaster::query()
                ->where('cim_cust_invoice_id', $invoice->cim_cust_invoice_id)
                ->delete();
        });

        return $this->sendOk(['success' => true]);
    }

    private function findScopedInvoice(int $id): ?CustInvoiceMaster
    {
        return CustInvoiceMaster::query()
            ->where('cim_cust_invoice_id', $id)
            ->where('cim_system_id', 'STUD_INV')
            ->where('cim_invoice_type', '12')
            ->first();
    }

    /** Build the SPA detail payload matching `GET /manual-invoice/{id}`. */
    private function detailPayload(CustInvoiceMaster $invoice): array
    {
        $extended = is_array($invoice->cim_extended_field) ? $invoice->cim_extended_field : [];
        $debtorTypeLabel = ($extended['cim_cust_type_desc'] ?? null)
            ?: ($invoice->cim_cust_type === 'E' ? 'PENAJA' : 'PELAJAR');

        $lines = CustInvoiceDetails::query()
            ->where('cim_cust_invoice_id', $invoice->cim_cust_invoice_id)
            ->orderBy('cid_cust_invoice_detl_id')
            ->get();

        $txType = strtolower((string) ($extended['manual_inv_tx_split'] ?? 'split'));
        $splitDebitCredit = $txType !== 'single';

        $mapLine = function ($r) {
            return [
                'id' => (int) $r->cid_cust_invoice_detl_id,
                'transactionType' => $r->cid_transaction_type,
                'itemCategory' => $r->cii_item_category,
                'itemCode' => $r->cii_item_code,
                'fundType' => $r->fty_fund_type,
                'activityCode' => $r->at_activity_code,
                'ounCode' => $r->oun_code,
                'costCentre' => $r->ccr_costcentre,
                'projectNo' => $r->cpa_project_no,
                'acctCode' => $r->acm_acct_code,
                'taxCode' => $r->cid_taxcode,
                'taxAmt' => $this->money($r->cid_taxamt),
                'totalAmt' => $this->money($r->cid_total_amt),
                'crNoteAmt' => $this->money($r->cid_crnote_amt),
                'dnNoteAmt' => $this->money($r->cid_dnnote_amt),
                'dcNoteAmt' => $this->money($r->cid_dcnote_amt),
                'nettAmt' => $this->money($r->cid_nett_amt),
                'balAmt' => $this->money($r->cid_bal_amt),
            ];
        };

        $debitLines = $lines->filter(fn ($r) => strtoupper((string) $r->cid_transaction_type) === 'DT');
        $creditLines = $lines->filter(fn ($r) => strtoupper((string) $r->cid_transaction_type) === 'CR');

        $mappedDebit = $debitLines->map($mapLine)->values()->all();
        $mappedCredit = $creditLines->map($mapLine)->values()->all();
        if (! $splitDebitCredit && $mappedDebit === [] && $mappedCredit === []) {
            $mappedDebit = $lines->map($mapLine)->values()->all();
        }

        $dt = $invoice->cim_invoice_date ? Carbon::parse($invoice->cim_invoice_date) : null;

        return [
            'id' => (int) $invoice->cim_cust_invoice_id,
            'invoiceNo' => $invoice->cim_invoice_no,
            'invoiceDate' => $dt ? $dt->format('d/m/Y') : null,
            'invoiceDateTime' => $dt ? $dt->format('d/m/Y H:i') : null,
            'invoiceDateIso' => $dt ? $dt->toIso8601String() : null,
            'status' => $invoice->cim_status,
            'debtorId' => $invoice->cim_cust_id,
            'debtorName' => $invoice->cim_cust_name,
            'debtorType' => $invoice->cim_cust_type,
            'debtorTypeLabel' => $debtorTypeLabel,
            'semesterId' => $invoice->cim_semester_id,
            'ourRef' => $extended['our_ref'] ?? $extended['cim_our_ref'] ?? null,
            'yourRef' => $extended['your_ref'] ?? $extended['cim_your_ref'] ?? null,
            'useRounding' => $extended['use_rounding'] ?? $extended['cim_use_rounding'] ?? null,
            'description' => $extended['description'] ?? $extended['cim_description'] ?? null,
            'addressType' => $extended['address_type'] ?? null,
            'address1' => $extended['address1'] ?? $extended['cim_address1'] ?? null,
            'address2' => $extended['address2'] ?? $extended['cim_address2'] ?? null,
            'postcode' => $extended['postcode'] ?? null,
            'country' => $extended['country'] ?? null,
            'city' => $extended['city'] ?? null,
            'state' => $extended['state'] ?? null,
            'contactPerson' => $extended['contact_person'] ?? null,
            'telNo' => $extended['tel_no'] ?? $extended['cim_tel'] ?? null,
            'email' => $extended['email'] ?? null,
            'totalAmt' => $this->money($invoice->cim_total_amt),
            'crNoteAmt' => $this->money($invoice->cim_crnote_amt),
            'dnNoteAmt' => $this->money($invoice->cim_dnnote_amt),
            'dcNoteAmt' => $this->money($invoice->cim_dcnote_amt),
            'paidAmt' => $this->money($invoice->cim_paid_amt),
            'balAmt' => $this->money($invoice->cim_bal_amt),
            'splitDebitCredit' => $splitDebitCredit,
            'debitLines' => $mappedDebit,
            'creditLines' => $mappedCredit,
            'processFlow' => [],
        ];
    }

    /**
     * Emulate legacy sequence for `cust_invoice_details`.
     */
    private function nextDetailSeq(): string|int
    {
        $max = DB::connection('mysql_secondary')->table('cust_invoice_details')->max('cid_cust_invoice_detl_id');

        return ($max !== null ? (int) $max : 0) + 1;
    }

    /**
     * Roll `cim_total_amt` from debit (DT) line amounts; fallback to SUM(all).
     * `cim_bal_amt` = total minus paid bucket (preserve note adjustments on master omitted here).
     */
    private function refreshTotalsFromLines(string $cimCustInvoiceId, string $username, string $nowStr): void
    {
        $master = CustInvoiceMaster::query()
            ->where('cim_cust_invoice_id', $cimCustInvoiceId)
            ->first();

        if (! $master) {
            return;
        }

        $dtSum = (float) CustInvoiceDetails::query()
            ->where('cim_cust_invoice_id', $cimCustInvoiceId)
            ->whereRaw("UPPER(IFNULL(cid_transaction_type,'')) = 'DT'")
            ->sum('cid_total_amt');

        if ($dtSum <= 0.0) {
            $dtSum = (float) CustInvoiceDetails::query()
                ->where('cim_cust_invoice_id', $cimCustInvoiceId)
                ->sum('cid_total_amt');
        }

        $paid = $this->money($master->cim_paid_amt);
        $bal = max(0.0, $dtSum - $paid);

        CustInvoiceMaster::query()
            ->where('cim_cust_invoice_id', $cimCustInvoiceId)
            ->update([
                'cim_total_amt' => $dtSum,
                'cim_bal_amt' => $bal,
                'updatedby' => $username,
                'updateddate' => $nowStr,
            ]);
    }

    private function currentUsername(): string
    {
        return (string) (Auth::user()?->email ?? Auth::user()?->name ?? 'system');
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }

    private function money(mixed $v): float
    {
        return $v !== null && $v !== '' ? (float) $v : 0.0;
    }
}
