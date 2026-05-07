<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKerisiPaymentRejectBatchActionRequest;
use App\Http\Requests\StoreKerisiWpnCancelRequest;
use App\Http\Traits\ApiResponse;
use App\Services\KerisiRemainingShellListService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KerisiRemainingController extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $menuId): JsonResponse
    {
        $allowedMenus = config('kerisi_remaining.menu_ids', []);
        if (! in_array($menuId, $allowedMenus, true)) {
            return $this->sendError(404, 'NOT_FOUND', 'Not a registered Kerisi menu');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));

        $pack = app(KerisiRemainingShellListService::class)->fetch($menuId, $request);
        $rows = $pack['rows'] ?? [];
        $total = $pack['total'] ?? 0;
        $connector = $pack['connector'] ?? 'unknown';

        $meta = [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $total > 0 ? (int) ceil($total / $limit) : 1,
            'connector' => $connector,
            'menuId' => $menuId,
        ];

        if (! empty($pack['shellError'])) {
            $meta['shellError'] = $pack['shellError'];
        }

        if (! empty($pack['top_filter_options']) && is_array($pack['top_filter_options'])) {
            $meta['top_filter_options'] = $pack['top_filter_options'];
        }

        if (! empty($pack['smart_filter_options']) && is_array($pack['smart_filter_options'])) {
            $meta['smart_filter_options'] = $pack['smart_filter_options'];
        }

        if (! empty($pack['form_options']) && is_array($pack['form_options'])) {
            $meta['form_options'] = $pack['form_options'];
        }

        if (! empty($pack['form_values']) && is_array($pack['form_values'])) {
            $meta['form_values'] = $pack['form_values'];
        }

        if (! empty($pack['extra_datatable_rows']) && is_array($pack['extra_datatable_rows'])) {
            $meta['extra_datatable_rows'] = $pack['extra_datatable_rows'];
        }

        if (array_key_exists('secondary_total', $pack)) {
            $meta['secondary_total'] = $pack['secondary_total'];
        }

        if (array_key_exists('grand_total_pom_order_amt_rm', $pack)) {
            $meta['grand_total_pom_order_amt_rm'] = $pack['grand_total_pom_order_amt_rm'];
        }

        return $this->sendOk($rows, $meta);
    }

    /**
     * Account Payable / Money Transfer — “New (From Virement)” modal feeder.
     * Approved virement applications not flagged as consumed for money transfer.
     */
    public function moneyTransferVirementNumbers(Request $request): JsonResponse
    {
        $needle = mb_strtolower(trim((string) $request->input('q', '')), 'UTF-8');
        $limit = max(1, min(500, (int) $request->input('limit', 200)));

        $base = DB::connection('mysql_secondary')
            ->table('budget_movement_master')
            ->where('bmm_trans_type', 'VIREMENT')
            ->where('bmm_status', 'APPROVE')
            ->where(function (Builder $b): void {
                $b->whereNull('bmm_money_transfer')->orWhere('bmm_money_transfer', 'N');
            })
            ->orderByDesc('bmm_budget_movement_no')
            ->select(['bmm_budget_movement_id', 'bmm_budget_movement_no']);

        if ($needle !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $base->whereRaw('LOWER(IFNULL(bmm_budget_movement_no, \'\')) LIKE ?', [$like]);
        }

        $rows = $base->limit($limit)->get();

        $data = $rows->map(fn ($r) => [
            'bmm_budget_movement_id' => (int) $r->bmm_budget_movement_id,
            'bmm_budget_movement_no' => (string) $r->bmm_budget_movement_no,
        ])->values()->all();

        return $this->sendOk($data);
    }

    /**
     * Purchasing / List of PR To Be Cancel — Details PR grid (linked GRN/WPN/PO/Bill rows).
     *
     * Query: ?rqm_requisition_no=… and/or ?rqm_requisition_id=…
     */
    public function prToCancelDetails(Request $request): JsonResponse
    {
        $rqmNo = trim((string) $request->input('rqm_requisition_no', ''));
        if ($rqmNo === '') {
            $rid = $request->input('rqm_requisition_id');
            if ($rid !== null && $rid !== '') {
                $rqmNo = (string) (DB::connection('mysql_secondary')
                    ->table('requisition_master')
                    ->where('rqm_requisition_id', (int) $rid)
                    ->value('rqm_requisition_no') ?? '');
                $rqmNo = trim($rqmNo);
            }
        }

        if ($rqmNo === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'rqm_requisition_no or rqm_requisition_id is required');
        }

        $rows = app(KerisiRemainingShellListService::class)->purchasingPrToCancelDetailRows($rqmNo);

        return $this->sendOk($rows);
    }

    /**
     * Purchasing / Work Progress Note Cancel — submit cancel (legacy `processcancelwpn_entry`).
     * workflowSubmit was commented in legacy; this updates master + detail rows only.
     */
    public function wpnCancel(StoreKerisiWpnCancelRequest $request): JsonResponse
    {
        $username = $request->user()?->name ?? $request->user()?->email ?? 'system';
        $selectedId = (string) $request->validated()['selected_id'];

        $result = app(KerisiRemainingShellListService::class)->processWpnCancelEntry($selectedId, $username);

        if (! ($result['success'] ?? false)) {
            return $this->sendError(400, 'BAD_REQUEST', (string) ($result['errorMsg'] ?? $result['message'] ?? 'WPN cancel failed'));
        }

        return $this->sendOk([
            'status' => 'ok',
            'successMessage' => (string) ($result['successMessage'] ?? ''),
            'wpnNo' => $result['wpnNo'] ?? null,
        ]);
    }

    public function paymentRejectBatchPaymentCancel(StoreKerisiPaymentRejectBatchActionRequest $request): JsonResponse
    {
        $result = $this->processPaymentRejectBatchAction(
            array_map('intval', $request->validated()['selected_ids']),
            'payment',
            $request->user()?->name ?? $request->user()?->email ?? 'system'
        );

        return $this->sendOk($result);
    }

    public function paymentRejectBatchVoucherCancel(StoreKerisiPaymentRejectBatchActionRequest $request): JsonResponse
    {
        $result = $this->processPaymentRejectBatchAction(
            array_map('intval', $request->validated()['selected_ids']),
            'voucher',
            $request->user()?->name ?? $request->user()?->email ?? 'system'
        );

        return $this->sendOk($result);
    }

    /**
     * Port of legacy YUS_PAYMENT_REJECT_BATCH_API paymentReject / voucherReject.
     *
     * @param  list<int>  $selectedIds
     * @return array<string, mixed>
     */
    private function processPaymentRejectBatchAction(array $selectedIds, string $action, string $username): array
    {
        $conn = $this->secondaryConn();
        $selectedIds = array_values(array_unique(array_filter($selectedIds, fn (int $id): bool => $id > 0)));

        if ($selectedIds === []) {
            return ['status' => 'ok', 'processed' => 0, 'itemRefs' => []];
        }

        return $conn->transaction(function () use ($conn, $selectedIds, $action, $username): array {
            $records = $conn->table('payment_record')
                ->whereIn('pre_payment_record_id', $selectedIds)
                ->whereNotIn('pre_status', ['REPLACE', 'ERROR'])
                ->orderBy('pre_payment_record_id')
                ->get();

            $itemRefs = [];
            $processed = 0;

            foreach ($records as $paymentRecord) {
                if ($action === 'payment') {
                    $conn->table('payment_record')
                        ->where('pre_payment_no', $paymentRecord->pre_payment_no)
                        ->where('pre_payment_batch', $paymentRecord->pre_payment_batch)
                        ->update([
                            'pre_status' => 'REPLACE',
                            'updateddate' => DB::raw('NOW()'),
                            'updatedby' => $username,
                        ]);

                    $itemRef = $this->createPaymentRejectReverseJournalIfPosted($paymentRecord, 'PAYMENT_REPLACE', $username);
                    if ($itemRef !== null) {
                        $itemRefs[] = $itemRef;
                    }

                    $this->clearVoucherPaymentFields($paymentRecord, $username);
                    $this->refreshPaymentBatchTotals((string) $paymentRecord->pre_payment_batch, $username);
                } else {
                    $itemRef = $this->createPaymentRejectReverseJournalIfPosted($paymentRecord, 'PAYMENT_REJECT', $username);
                    if ($itemRef !== null) {
                        $itemRefs[] = $itemRef;
                    }

                    $this->cancelVoucherPaymentDetails($paymentRecord, $username);
                }

                $processed++;
            }

            return [
                'status' => 'ok',
                'processed' => $processed,
                'itemRefs' => $itemRefs,
                'selectedIds' => $selectedIds,
            ];
        });
    }

    private function createPaymentRejectReverseJournalIfPosted(object $paymentRecord, string $systemId, string $username): ?string
    {
        $conn = $this->secondaryConn();
        $postingCount = (int) $conn->table('posting_details')
            ->where('pde_document_no', $paymentRecord->pre_voucher_no)
            ->where('pde_reference', $paymentRecord->pre_payment_no)
            ->where('pde_status', 'APPROVE')
            ->count();

        if ($postingCount < 1) {
            return null;
        }

        $creditRows = $this->paymentRejectVoucherRows($paymentRecord, 'CR');
        $debitRows = $this->paymentRejectVoucherRows($paymentRecord, 'DT');
        if ($creditRows->isEmpty() && $debitRows->isEmpty()) {
            return null;
        }

        $first = $creditRows->first() ?? $debitRows->first();
        $itemRef = $this->nextPaymentRejectJournalRef((string) ($first->fty_fund_type ?? ''));
        $journalId = $this->nextTableSequence('manual_journal_master');
        $amount = (float) ($paymentRecord->pre_total_amt_rm ?? $paymentRecord->pre_total_amt ?? 0);

        $this->secondaryConn()->table('manual_journal_master')->insert([
            'mjm_journal_id' => $journalId,
            'mjm_journal_no' => $itemRef,
            'mjm_status' => 'APPROVE',
            'mjm_system_id' => $systemId,
            'mjm_journal_desc' => sprintf(
                '%s for Payment %s Amount (RM%s)',
                $systemId === 'PAYMENT_REJECT' ? 'Payment Reject' : 'Payment Replace',
                (string) $paymentRecord->pre_voucher_no,
                number_format($amount, 2, '.', '')
            ),
            'createddate' => DB::raw('NOW()'),
            'mjm_typeofjournal' => 'General',
            'mjm_total_amt' => $amount,
            'mjm_conversion_rate' => $first->vma_conversion_rate ?? null,
            'mjm_currency_unit' => $first->vma_currency_unit ?? null,
            'mjm_currency_code' => $first->vma_currency_code ?? null,
            'mjm_rate_type' => $first->vma_rate_type ?? null,
            'mjm_ent_amt' => $first->vma_ent_amt ?? null,
            'mjm_approvedate' => DB::raw('NOW()'),
            'mjm_approveby' => $username,
            'mjm_enterdate' => DB::raw('NOW()'),
            'mjm_enterby' => $username,
            'mjm_extended_field' => json_encode([
                'Voucher_No' => (string) $paymentRecord->pre_voucher_no,
                'mjm_status_desc' => 'Approved',
                'EFT_No' => (string) $paymentRecord->pre_payment_no,
            ]),
            'createdby' => $username,
            'org_code' => $first->org_code ?? null,
        ]);

        foreach ($creditRows as $row) {
            $this->insertPaymentRejectJournalDetail($journalId, $paymentRecord, $row, 'DT');
        }
        foreach ($debitRows as $row) {
            $this->insertPaymentRejectJournalDetail($journalId, $paymentRecord, $row, 'CR');
        }

        $conn = $this->secondaryConn();
        $conn->statement('CALL approveJNL(?, ?, ?, ?, @OUT, @FLAG)', [
            $itemRef,
            '2938',
            $systemId,
            $username,
        ]);
        $conn->select('SELECT @OUT AS out_msg, @FLAG AS out_flag');

        return $itemRef;
    }

    private function insertPaymentRejectJournalDetail(int $journalId, object $paymentRecord, object $row, string $transType): void
    {
        $this->secondaryConn()->table('manual_journal_details')->insert([
            'mjd_journal_detl_id' => $this->nextTableSequence('manual_journal_details'),
            'mjm_journal_no' => $journalId,
            'fty_fund_type' => $row->fty_fund_type ?? null,
            'at_activity_code' => $row->at_activity_code ?? null,
            'oun_code' => $row->oun_code ?? null,
            'ccr_costcentre' => $row->ccr_costcentre ?? null,
            'code_so' => $row->so_code ?? null,
            'acm_acct_code' => $row->acm_acct_code ?? null,
            'mjd_trans_type' => $transType,
            'sbg_budget_id' => $row->sbg_budget_id ?? null,
            'mjd_trans_amt' => $row->vde_amount ?? null,
            'mjd_ent_amt' => $row->vde_amount ?? null,
            'mjd_document_no' => $paymentRecord->pre_voucher_no,
            'mjd_reference' => $paymentRecord->pre_payment_no,
            'mjd_reference_2' => $row->bim_bills_no ?? null,
            'mjd_payto_type' => $row->vde_payto_type ?? null,
            'mjd_payto_id' => $row->vde_payto_id ?? null,
            'mjd_payto_name' => $row->vde_payto_name ?? null,
            'mjd_status' => 'APPROVE',
            'mjd_extended_field' => json_encode([
                'bank_name' => $row->vde_bank_name ?? null,
                'bank_acctno' => $row->vde_bank_acctno ?? null,
                'address_payto' => $row->vde_payto_address ?? null,
            ]),
            'createddate' => DB::raw('NOW()'),
            'cpa_project_no' => $row->cpa_project_no ?? null,
            'mjd_item_lineno' => $row->bid_line_no ?? null,
            'mjd_trans_date' => DB::raw('NOW()'),
            'mjd_taxcode' => 'NR',
        ]);
    }

    private function paymentRejectVoucherRows(object $paymentRecord, string $transType): \Illuminate\Support\Collection
    {
        return $this->secondaryConn()->table('voucher_master as vm')
            ->join('voucher_details as vd', 'vd.vma_voucher_id', '=', 'vm.vma_voucher_id')
            ->where('vm.vma_vch_status', 'APPROVE')
            ->where('vd.vde_payment_no', $paymentRecord->pre_payment_no)
            ->where('vm.vma_voucher_no', $paymentRecord->pre_voucher_no)
            ->where('vd.vde_status', 'APPROVE')
            ->where('vd.vde_trans_type', $transType)
            ->select(['vm.*', 'vd.*'])
            ->get();
    }

    private function clearVoucherPaymentFields(object $paymentRecord, string $username): void
    {
        $voucher = $this->secondaryConn()->table('voucher_master')
            ->where('vma_vch_status', 'APPROVE')
            ->where('vma_voucher_no', $paymentRecord->pre_voucher_no)
            ->first();

        if (! $voucher) {
            return;
        }

        $voucherData = $this->secondaryConn()->table('voucher_details')
            ->where('vma_voucher_id', $voucher->vma_voucher_id)
            ->where('vde_payment_no', $paymentRecord->pre_payment_no)
            ->first(['vde_extended_field', 'vde_paymode', 'vde_transfer_date', 'vde_pybatch_id', 'flag_vch_replace']);

        $extended = json_decode((string) ($voucherData->vde_extended_field ?? '{}'), true);
        if (! is_array($extended)) {
            $extended = [];
        }
        unset($extended['isApproveEFT']);
        $extended['vde_payment_no'] = $paymentRecord->pre_payment_no;
        $extended['vde_paymode'] = $voucherData->vde_paymode ?? '';
        $extended['vde_transfer_date'] = $voucherData->vde_transfer_date ?? '';
        $extended['vde_pybatch_id'] = $voucherData->vde_pybatch_id ?? '';
        $extended['flag_vch_replace'] = $voucherData->flag_vch_replace ?? '';

        $this->secondaryConn()->table('voucher_details')
            ->where('vma_voucher_id', $voucher->vma_voucher_id)
            ->where('vde_payment_no', $paymentRecord->pre_payment_no)
            ->update([
                'vde_payment_no' => null,
                'vde_paymode' => null,
                'vde_posting_no' => null,
                'vde_transfer_date' => null,
                'vde_pybatch_id' => null,
                'flag_vch_replace' => 'Y',
                'vde_extended_field' => json_encode($extended),
                'updateddate' => DB::raw('NOW()'),
                'updatedby' => $username,
            ]);
    }

    private function cancelVoucherPaymentDetails(object $paymentRecord, string $username): void
    {
        $voucher = $this->secondaryConn()->table('voucher_master')
            ->where('vma_vch_status', 'APPROVE')
            ->where('vma_voucher_no', $paymentRecord->pre_voucher_no)
            ->first();

        if (! $voucher) {
            return;
        }

        $this->secondaryConn()->table('voucher_details')
            ->where('vma_voucher_id', $voucher->vma_voucher_id)
            ->where('vde_payment_no', $paymentRecord->pre_payment_no)
            ->update([
                'vde_status' => 'CANCEL',
                'updateddate' => DB::raw('NOW()'),
                'updatedby' => $username,
            ]);

        $activeDetails = (int) $this->secondaryConn()->table('voucher_details')
            ->where('vma_voucher_id', $voucher->vma_voucher_id)
            ->where('vde_status', '!=', 'CANCEL')
            ->count();

        if ($activeDetails === 0) {
            $this->secondaryConn()->table('voucher_master')
                ->where('vma_voucher_id', $voucher->vma_voucher_id)
                ->update([
                    'vma_vch_status' => 'CANCEL',
                    'updateddate' => DB::raw('NOW()'),
                    'updatedby' => $username,
                ]);
        }
    }

    private function refreshPaymentBatchTotals(string $batchNo, string $username): void
    {
        if ($batchNo === '') {
            return;
        }

        $summary = $this->secondaryConn()->table('payment_record')
            ->where('pre_payment_batch', $batchNo)
            ->whereNotIn('pre_status', ['ERROR', 'REJECT', 'REPLACE'])
            ->selectRaw('COUNT(1) AS row_count, COALESCE(SUM(pre_total_amt), 0) AS amount')
            ->first();

        $this->secondaryConn()->table('payment_batch')
            ->where('pyb_batch_no', $batchNo)
            ->update([
                'pyb_qty' => (int) ($summary->row_count ?? 0),
                'pyb_total_amt' => (float) ($summary->amount ?? 0),
                'updateddate' => DB::raw('NOW()'),
                'updatedby' => $username,
            ]);
    }

    private function nextTableSequence(string $table): int
    {
        $conn = $this->secondaryConn();
        $conn->statement('CALL getTableSequenceNum(?, @SEQ)', [$table]);
        $row = $conn->selectOne('SELECT @SEQ AS seq');

        return (int) ($row->seq ?? 0);
    }

    private function nextPaymentRejectJournalRef(string $fundType): string
    {
        $fund = (string) ($this->secondaryConn()->table('fund_type')
            ->where('fty_fund_type', $fundType)
            ->value('fty_prefix') ?? '');

        $payload = json_encode([
            'code' => 'MANUAL_JOURNAL_REVERSE_AUTO',
            'date' => now()->format('Ymd'),
            'fund' => $fund,
            'groupby' => now()->format('Ym'),
        ]);

        $conn = $this->secondaryConn();
        $conn->statement('CALL getRefNo(?, @SEQ)', [$payload]);
        $row = $conn->selectOne('SELECT @SEQ AS seq');

        return (string) ($row->seq ?? '');
    }

    // ── AP Credit Note Form (MENUID 3242) ────────────────────────────────────

    private function secondaryConn(): \Illuminate\Database\Connection
    {
        return DB::connection('mysql_secondary');
    }

    private function likeEsc(string $q): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($q, 'UTF-8')).'%';
    }

    /** Search bills_master by bill no for the Bill No combobox on the AP CN form. */
    public function apCreditNoteFormBillSearch(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $limit = min(50, max(1, (int) $request->input('limit', 20)));

        $query = $this->secondaryConn()
            ->table('bills_master')
            ->select(['bim_bills_id', 'bim_bills_no', 'bim_payto_name', 'bim_status'])
            ->whereNotIn('bim_status', ['CANCEL'])
            ->orderByDesc('bim_bills_id')
            ->limit($limit);

        if ($q !== '') {
            $query->whereRaw('LOWER(IFNULL(bim_bills_no, \'\')) LIKE ?', [$this->likeEsc($q)]);
        }

        $rows = $query->get()->map(fn ($r) => [
            'id'        => (string) ($r->bim_bills_id ?? ''),
            'billNo'    => (string) ($r->bim_bills_no ?? ''),
            'paytoName' => (string) ($r->bim_payto_name ?? ''),
            'status'    => (string) ($r->bim_status ?? ''),
        ])->values()->all();

        return $this->sendOk($rows);
    }

    /**
     * Fetch bill head + debit/credit lines for the AP CN form.
     * Lines come from bills_details filtered by bid_trans_type.
     */
    public function apCreditNoteFormBillDetails(Request $request): JsonResponse
    {
        $billNo = trim((string) $request->input('bill_no', ''));
        if ($billNo === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'bill_no is required');
        }

        $bill = $this->secondaryConn()
            ->table('bills_master as bm')
            ->where('bm.bim_bills_no', $billNo)
            ->select([
                'bm.bim_bills_id', 'bm.bim_bills_no',
                'bm.bim_payto_id', 'bm.bim_payto_name', 'bm.bim_payto_type',
                'bm.bim_ent_amt', 'bm.bim_bill_amt',
                'bm.bim_currency_code', 'bm.bim_approve_date',
            ])
            ->first();

        if (! $bill) {
            return $this->sendError(404, 'NOT_FOUND', 'Bill not found');
        }

        $billId = (string) ($bill->bim_bills_id ?? '');
        $makeLines = function (string $transType) use ($billId): array {
            return $this->secondaryConn()
                ->table('bills_details as bd')
                ->where('bd.bim_bills_id', $billId)
                ->where('bd.bid_trans_type', $transType)
                ->select([
                    'bd.bid_bills_details_id',
                    'bd.fty_fund_type',
                    'bd.at_activity_code',
                    'bd.oun_code',
                    'bd.ccr_costcentre',
                    DB::raw('NULL AS so_code'),
                    'bd.itm_item_code',
                    'bd.acm_acct_code',
                    'bd.bdg_budget_code',
                    'bd.bid_ent_amt',
                    'bd.bid_amt',
                ])
                ->get()
                ->map(fn ($r) => [
                    'crdId'              => null,
                    'bidBillsDetailsId'  => (string) ($r->bid_bills_details_id ?? ''),
                    'crdLineNo'          => null,
                    'ftyFundType'        => $r->fty_fund_type ?? null,
                    'atActivityCode'     => $r->at_activity_code ?? null,
                    'ounCode'            => $r->oun_code ?? null,
                    'ccrCostcentre'      => $r->ccr_costcentre ?? null,
                    'soCode'             => $r->so_code ?? null,
                    'itItemCode'         => $r->itm_item_code ?? null,
                    'acmAcctCode'        => $r->acm_acct_code ?? null,
                    'bdgBudgetCode'      => $r->bdg_budget_code ?? null,
                    'bidEntAmt'          => (float) ($r->bid_ent_amt ?? 0),
                    'bidAmt'             => (float) ($r->bid_amt ?? 0),
                    'crdCnEntAmt'        => 0,
                    'crdCnAmt'           => 0,
                    'crdBalEntAmt'       => (float) ($r->bid_ent_amt ?? 0),
                    'crdBalAmt'          => (float) ($r->bid_amt ?? 0),
                ])
                ->values()->all();
        };

        return $this->sendOk([
            'bimBillsId'      => (string) ($bill->bim_bills_id ?? ''),
            'bimBillsNo'      => (string) ($bill->bim_bills_no ?? ''),
            'bimPaytoId'      => $bill->bim_payto_id ?? null,
            'bimPaytoName'    => $bill->bim_payto_name ?? null,
            'bimPaytoType'    => $bill->bim_payto_type ?? null,
            'bimFactoringCode'=> null,
            'bimFactoringType'=> null,
            'bimRateType'     => null,
            'bimCurrencyUnit' => null,
            'bimCurrencyCode' => $bill->bim_currency_code ?? null,
            'bimCurrencyRate' => null,
            'bimEntAmt'       => (float) ($bill->bim_ent_amt ?? 0),
            'bimBillAmt'      => (float) ($bill->bim_bill_amt ?? 0),
            'bimCurrentRate'  => null,
            'debitLines'      => $makeLines('DT'),
            'creditLines'     => $makeLines('CR'),
        ]);
    }

    /** Currency options for the Currency dropdown on the AP CN form. */
    public function apCreditNoteFormCurrencies(Request $request): JsonResponse
    {
        $rows = $this->secondaryConn()
            ->table('currency_master')
            ->select(['cym_currency_code', 'cym_currency_desc'])
            ->orderBy('cym_currency_code')
            ->get()
            ->map(fn ($r) => [
                'value' => (string) ($r->cym_currency_code ?? ''),
                'label' => trim(($r->cym_currency_code ?? '').' - '.($r->cym_currency_desc ?? ''), ' -'),
            ])
            ->values()->all();

        return $this->sendOk($rows);
    }

    /** Fetch an existing AP Credit Note (credit_note_ap_master + lines). */
    public function apCreditNoteFormGet(Request $request, string $id): JsonResponse
    {
        $cna = $this->secondaryConn()
            ->table('credit_note_ap_master as cna')
            ->leftJoin('bills_master as bm', 'bm.bim_bills_no', '=', 'cna.bim_bills_no')
            ->where('cna.cna_credit_note_ap_master_id', $id)
            ->select([
                'cna.cna_credit_note_ap_master_id',
                'cna.cna_crnote_no',
                'cna.cna_crnote_date',
                'cna.cna_cn_total_amount',
                'cna.cna_ent_total_amount',
                'cna.cna_status_cd',
                'cna.bim_bills_no',
                'cna.cna_crnote_desc as cna_description',
                'cna.cna_approve_date',
                'bm.bim_bills_id',
                'bm.bim_payto_id',
                'bm.bim_payto_name',
                'bm.bim_payto_type',
                'bm.bim_ent_amt',
                'bm.bim_bill_amt',
                'bm.bim_currency_code',
                'bm.bim_approve_date as bim_approve_date',
            ])
            ->first();

        if (! $cna) {
            return $this->sendError(404, 'NOT_FOUND', 'Credit note not found');
        }

        return $this->sendOk([
            'cnaId'           => (string) ($cna->cna_credit_note_ap_master_id ?? ''),
            'cnaCrnoteNo'     => $cna->cna_crnote_no ?? null,
            'cnaApproveDate'  => $cna->cna_approve_date ?? $cna->bim_approve_date ?? null,
            'bimBillsId'      => (string) ($cna->bim_bills_id ?? ''),
            'bimBillsNo'      => $cna->bim_bills_no ?? null,
            'bimPaytoId'      => $cna->bim_payto_id ?? null,
            'bimPaytoName'    => $cna->bim_payto_name ?? null,
            'bimPaytoType'    => $cna->bim_payto_type ?? null,
            'bimFactoringCode'=> null,
            'bimFactoringType'=> null,
            'bimRateType'     => null,
            'bimCurrencyUnit' => null,
            'bimCurrencyCode' => $cna->bim_currency_code ?? null,
            'bimCurrencyRate' => null,
            'bimEntAmt'       => (float) ($cna->bim_ent_amt ?? 0),
            'bimBillAmt'      => (float) ($cna->bim_bill_amt ?? 0),
            'bimCurrentRate'  => null,
            'cnaDescription'  => $cna->cna_description ?? null,
            'cnaStatusCd'     => $cna->cna_status_cd ?? 'DRAFT',
            'debitLines'      => [],
            'creditLines'     => [],
        ]);
    }

    /** Save (create/update) an AP Credit Note. */
    public function apCreditNoteFormSave(Request $request): JsonResponse
    {
        $cnaId      = $request->input('cnaId');
        $billsNo    = trim((string) ($request->input('bimBillsNo') ?? ''));
        $desc       = trim((string) ($request->input('cnaDescription') ?? ''));

        if ($billsNo === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'bimBillsNo is required');
        }

        $conn = $this->secondaryConn();
        $now  = now()->format('Y-m-d H:i:s');
        $debitLines  = (array) ($request->input('debitLines', []));
        $creditLines = (array) ($request->input('creditLines', []));
        $total = collect($debitLines)->sum(fn ($l) => (float) ($l['crdCnAmt'] ?? 0));
        $entTotal = collect($debitLines)->sum(fn ($l) => (float) ($l['crdCnEntAmt'] ?? 0));

        if ($cnaId) {
            $conn->table('credit_note_ap_master')
                ->where('cna_credit_note_ap_master_id', $cnaId)
                ->update([
                    'cna_crnote_desc'       => $desc,
                    'cna_cn_total_amount'   => $total,
                    'cna_ent_total_amount'  => $entTotal,
                    'cna_status_cd'         => 'DRAFT',
                ]);
            $crnoteNo = $conn->table('credit_note_ap_master')
                ->where('cna_credit_note_ap_master_id', $cnaId)
                ->value('cna_crnote_no');
        } else {
            $cnaId = $conn->table('credit_note_ap_master')->insertGetId([
                'bim_bills_no'          => $billsNo,
                'cna_crnote_desc'       => $desc,
                'cna_crnote_date'       => $now,
                'cna_cn_total_amount'   => $total,
                'cna_ent_total_amount'  => $entTotal,
                'cna_status_cd'         => 'DRAFT',
                'createddate'           => $now,
            ]);
            $crnoteNo = null;
        }

        return $this->sendOk([
            'cnaId'       => (string) $cnaId,
            'cnaCrnoteNo' => $crnoteNo,
            'cnaStatusCd' => 'DRAFT',
        ]);
    }

    /** Submit an AP Credit Note (flip status to ENTRY). */
    public function apCreditNoteFormSubmit(Request $request, string $id): JsonResponse
    {
        $updated = $this->secondaryConn()
            ->table('credit_note_ap_master')
            ->where('cna_credit_note_ap_master_id', $id)
            ->update(['cna_status_cd' => 'ENTRY']);

        if (! $updated) {
            return $this->sendError(404, 'NOT_FOUND', 'Credit note not found');
        }

        return $this->sendOk(['cnaStatusCd' => 'ENTRY', 'message' => 'Credit note submitted.']);
    }

    // ── AP Debit Note Form (MENUID 3548 / 3550) ──────────────────────────────

    public function apDebitNoteFormBillSearch(Request $request): JsonResponse
    {
        return $this->apCreditNoteFormBillSearch($request);
    }

    public function apDebitNoteFormBillDetails(Request $request): JsonResponse
    {
        $billNo = trim((string) $request->input('bill_no', ''));
        if ($billNo === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'bill_no is required');
        }

        $bill = $this->secondaryConn()
            ->table('bills_master as bm')
            ->where('bm.bim_bills_no', $billNo)
            ->select([
                'bm.bim_bills_id', 'bm.bim_bills_no',
                'bm.bim_payto_id', 'bm.bim_payto_name', 'bm.bim_payto_type',
                'bm.bim_ent_amt', 'bm.bim_bill_amt',
                'bm.bim_currency_code', 'bm.bim_rate_type', 'bm.bim_currency_unit', 'bm.bim_conversion_rate',
            ])
            ->first();

        if (! $bill) {
            return $this->sendError(404, 'NOT_FOUND', 'Bill not found');
        }

        $billId = (string) ($bill->bim_bills_id ?? '');
        $makeLines = function (string $transType) use ($billId): array {
            return $this->secondaryConn()
                ->table('bills_details as bd')
                ->where('bd.bim_bills_id', $billId)
                ->where('bd.bid_trans_type', $transType)
                ->select([
                    'bd.bid_bills_details_id',
                    'bd.fty_fund_type',
                    'bd.at_activity_code',
                    'bd.oun_code',
                    'bd.ccr_costcentre',
                    DB::raw('NULL AS so_code'),
                    'bd.itm_item_code',
                    'bd.acm_acct_code',
                    'bd.bdg_budget_code',
                    'bd.bid_ent_amt',
                    'bd.bid_amt',
                ])
                ->get()
                ->map(fn ($r) => [
                    'dedId'             => null,
                    'bidBillsDetailsId' => (string) ($r->bid_bills_details_id ?? ''),
                    'dedLineNo'         => null,
                    'ftyFundType'       => $r->fty_fund_type ?? null,
                    'atActivityCode'    => $r->at_activity_code ?? null,
                    'ounCode'           => $r->oun_code ?? null,
                    'ccrCostcentre'     => $r->ccr_costcentre ?? null,
                    'soCode'            => $r->so_code ?? null,
                    'itItemCode'        => $r->itm_item_code ?? null,
                    'acmAcctCode'       => $r->acm_acct_code ?? null,
                    'bdgBudgetCode'     => $r->bdg_budget_code ?? null,
                    'bidEntAmt'         => (float) ($r->bid_ent_amt ?? 0),
                    'bidAmt'            => (float) ($r->bid_amt ?? 0),
                    'dedDnEntAmt'       => 0,
                    'dedDnAmt'          => 0,
                    'dedBalEntAmt'      => (float) ($r->bid_ent_amt ?? 0),
                    'dedBalAmt'         => (float) ($r->bid_amt ?? 0),
                ])
                ->values()->all();
        };

        return $this->sendOk([
            'bimBillsId'      => (string) ($bill->bim_bills_id ?? ''),
            'bimBillsNo'      => (string) ($bill->bim_bills_no ?? ''),
            'bimPaytoId'      => $bill->bim_payto_id ?? null,
            'bimPaytoName'    => $bill->bim_payto_name ?? null,
            'bimPaytoType'    => $bill->bim_payto_type ?? null,
            'bimFactoringCode'=> null,
            'bimFactoringType'=> null,
            'bimRateType'     => $bill->bim_rate_type ?? null,
            'bimCurrencyUnit' => $bill->bim_currency_unit ?? null,
            'bimCurrencyCode' => $bill->bim_currency_code ?? null,
            'bimCurrencyRate' => $bill->bim_conversion_rate ?? null,
            'bimEntAmt'       => (float) ($bill->bim_ent_amt ?? 0),
            'bimBillAmt'      => (float) ($bill->bim_bill_amt ?? 0),
            'bimCurrentRate'  => $bill->bim_conversion_rate ?? null,
            'debitLines'      => $makeLines('DT'),
            'creditLines'     => $makeLines('CR'),
        ]);
    }

    public function apDebitNoteFormGet(Request $request, string $id): JsonResponse
    {
        $dna = $this->secondaryConn()
            ->table('debit_note_ap_master as dna')
            ->leftJoin('bills_master as bm', 'bm.bim_bills_no', '=', 'dna.bim_bills_no')
            ->where('dna.dna_debit_note_ap_master_id', $id)
            ->select([
                'dna.dna_debit_note_ap_master_id',
                'dna.dna_dnnote_no',
                'dna.dna_dnnote_desc',
                'dna.dna_approve_date',
                'dna.dna_status_dn',
                'dna.dna_cancel_by',
                'dna.dna_cancel_date',
                'dna.dna_cancel_reason',
                'dna.bim_bills_no',
                'bm.bim_bills_id',
                'bm.bim_payto_id',
                'bm.bim_payto_name',
                'bm.bim_payto_type',
                'bm.bim_ent_amt',
                'bm.bim_bill_amt',
                'bm.bim_currency_code',
                'bm.bim_rate_type',
                'bm.bim_currency_unit',
                'bm.bim_conversion_rate',
            ])
            ->first();

        if (! $dna) {
            return $this->sendError(404, 'NOT_FOUND', 'Debit note not found');
        }

        $detailsReq = Request::create('/', 'GET', ['bill_no' => $dna->bim_bills_no]);
        $details = $this->apDebitNoteFormBillDetails($detailsReq)->getData(true)['data'] ?? [];

        return $this->sendOk(array_merge($details, [
            'dnaId'           => (string) ($dna->dna_debit_note_ap_master_id ?? ''),
            'dnaDnnoteNo'     => $dna->dna_dnnote_no ?? null,
            'dnaApproveDate'  => $dna->dna_approve_date ?? null,
            'dnaDescription'  => $dna->dna_dnnote_desc ?? null,
            'dnaStatusDn'     => $dna->dna_status_dn ?? 'DRAFT',
            'dnaCancelBy'     => $dna->dna_cancel_by ?? null,
            'dnaCancelDate'   => $dna->dna_cancel_date ?? null,
            'dnaCancelReason' => $dna->dna_cancel_reason ?? null,
        ]));
    }

    public function apDebitNoteFormSave(Request $request): JsonResponse
    {
        $dnaId   = $request->input('dnaId');
        $billsNo = trim((string) ($request->input('bimBillsNo') ?? ''));
        $desc    = trim((string) ($request->input('dnaDescription') ?? ''));

        if ($billsNo === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'bimBillsNo is required');
        }

        $conn = $this->secondaryConn();
        $now = now()->format('Y-m-d H:i:s');
        $debitLines = (array) ($request->input('debitLines', []));
        $total = collect($debitLines)->sum(fn ($l) => (float) ($l['dedDnAmt'] ?? 0));
        $entTotal = collect($debitLines)->sum(fn ($l) => (float) ($l['dedDnEntAmt'] ?? 0));

        if ($dnaId) {
            $conn->table('debit_note_ap_master')
                ->where('dna_debit_note_ap_master_id', $dnaId)
                ->update([
                    'dna_dnnote_desc'      => $desc,
                    'dna_dn_total_amount'  => $total,
                    'dna_ent_total_amount' => $entTotal,
                    'dna_status_dn'        => 'DRAFT',
                ]);
            $dnnoteNo = $conn->table('debit_note_ap_master')
                ->where('dna_debit_note_ap_master_id', $dnaId)
                ->value('dna_dnnote_no');
        } else {
            $dnaId = $conn->table('debit_note_ap_master')->insertGetId([
                'bim_bills_no'          => $billsNo,
                'dna_dnnote_desc'       => $desc,
                'dna_dnnote_date'       => $now,
                'dna_dn_total_amount'   => $total,
                'dna_ent_total_amount'  => $entTotal,
                'dna_status_dn'         => 'DRAFT',
                'createddate'           => $now,
            ]);
            $dnnoteNo = null;
        }

        return $this->sendOk([
            'dnaId'       => (string) $dnaId,
            'dnaDnnoteNo' => $dnnoteNo,
            'dnaStatusDn' => 'DRAFT',
        ]);
    }

    public function apDebitNoteFormSubmit(Request $request, string $id): JsonResponse
    {
        $updated = $this->secondaryConn()
            ->table('debit_note_ap_master')
            ->where('dna_debit_note_ap_master_id', $id)
            ->update(['dna_status_dn' => 'ENTRY']);

        if (! $updated) {
            return $this->sendError(404, 'NOT_FOUND', 'Debit note not found');
        }

        return $this->sendOk(['dnaStatusDn' => 'ENTRY', 'message' => 'Debit note submitted.']);
    }

    public function apDebitNoteFormCancel(Request $request, string $id): JsonResponse
    {
        $reason = trim((string) ($request->input('cancelReason') ?? ''));
        if ($reason === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'cancelReason is required');
        }

        $updated = $this->secondaryConn()
            ->table('debit_note_ap_master')
            ->where('dna_debit_note_ap_master_id', $id)
            ->update([
                'dna_status_dn' => 'CANCEL',
                'dna_cancel_reason' => $reason,
                'dna_cancel_by' => $request->user()?->email ?? $request->user()?->name ?? 'system',
                'dna_cancel_date' => now()->format('Y-m-d H:i:s'),
            ]);

        if (! $updated) {
            return $this->sendError(404, 'NOT_FOUND', 'Debit note not found');
        }

        return $this->sendOk(['dnaStatusDn' => 'CANCEL', 'message' => 'Debit note cancelled.']);
    }

    // ── AP Voucher actions ──────────────────────────────────────────────────

    public function apVoucherDelete(Request $request, string $id): JsonResponse
    {
        $conn = $this->secondaryConn();
        $voucher = $conn->table('voucher_master')
            ->where('vma_voucher_id', $id)
            ->first();

        if (! $voucher) {
            return $this->sendError(404, 'NOT_FOUND', 'Voucher not found');
        }
        if (strtoupper((string) ($voucher->vma_vch_status ?? '')) !== 'DRAFT') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Only DRAFT vouchers can be deleted');
        }

        $conn->table('voucher_details')->where('vma_voucher_id', $id)->delete();
        $conn->table('voucher_master')->where('vma_voucher_id', $id)->delete();

        return $this->sendOk(['success' => true, 'message' => 'Voucher deleted.']);
    }

    public function apVoucherCancel(Request $request, string $id): JsonResponse
    {
        $reason = trim((string) ($request->input('cancelReason') ?? ''));
        if ($reason === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'cancelReason is required');
        }

        $conn = $this->secondaryConn();
        $voucher = $conn->table('voucher_master')
            ->where('vma_voucher_id', $id)
            ->first();

        if (! $voucher) {
            return $this->sendError(404, 'NOT_FOUND', 'Voucher not found');
        }

        $allowedStatuses = ['APPROVE', 'ENTRY', 'VERIFIED'];
        if (! in_array(strtoupper((string) ($voucher->vma_vch_status ?? '')), $allowedStatuses, true)) {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Voucher status does not allow cancellation');
        }

        $conn->table('voucher_master')->where('vma_voucher_id', $id)->update([
            'vma_vch_status'  => 'CANCEL',
            'vma_cancel_reason' => $reason,
            'vma_cancel_by'   => $request->user()?->email ?? $request->user()?->name ?? 'system',
            'vma_cancel_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return $this->sendOk(['vmaVchStatus' => 'CANCEL', 'message' => 'Voucher cancelled.']);
    }

    // ── AP Voucher Information Creditor (MENUID 3546) ─────────────────────────

    /** Autocomplete voucher numbers from voucher_master. */
    public function apVoucherSuggestVoucher(Request $request): JsonResponse
    {
        $q     = trim((string) $request->input('q', ''));
        $limit = min(30, max(1, (int) $request->input('limit', 20)));

        $query = $this->secondaryConn()
            ->table('voucher_master')
            ->select(['vma_voucher_no', 'vma_vch_status', 'vma_payto_name'])
            ->orderByDesc('vma_voucher_no')
            ->limit($limit);

        if ($q !== '') {
            $query->whereRaw('LOWER(IFNULL(vma_voucher_no,\'\')) LIKE ?', [$this->likeEsc($q)]);
        }

        return $this->sendOk(
            $query->get()->map(fn ($r) => [
                'id'    => (string) ($r->vma_voucher_no ?? ''),
                'text'  => (string) ($r->vma_voucher_no ?? ''),
                'desc'  => (string) ($r->vma_vch_status ?? ''),
                'payee' => (string) ($r->vma_payto_name ?? ''),
            ])->values()->all()
        );
    }

    /**
     * Return voucher master + debit + credit detail rows for 3546.
     * Debit  = voucher_details where vde_trans_type = 'DT'
     * Credit = voucher_details where vde_trans_type = 'CR'
     */
    public function apVoucherInfoCreditorDetail(Request $request): JsonResponse
    {
        $voucherNo = trim((string) $request->input('voucher_no', ''));
        if ($voucherNo === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'voucher_no is required');
        }

        $conn   = $this->secondaryConn();
        $master = $conn->table('voucher_master as vm')
            ->where('vm.vma_voucher_no', $voucherNo)
            ->select([
                'vm.vma_voucher_id',
                'vm.vma_voucher_no',
                'vm.vma_vch_status',
                'vm.vma_currency_code',
                'vm.vma_total_amt',
                'vm.vma_ent_amt',
                'vm.vma_payto_type',
                'vm.vma_payto_id',
                'vm.vma_payto_name',
                'vm.vma_exchange_type_code',
                'vm.vma_conversion_rate',
                'vm.vma_vch_description',
                'vm.vma_subsystem_code',
            ])
            ->first();

        if (! $master) {
            return $this->sendError(404, 'NOT_FOUND', 'Voucher not found');
        }

        $detailCols = [
            'vd.vde_voucher_detl_id',
            'vd.bim_bills_no',
            'vd.vde_payto_type',
            'vd.vde_payto_id',
            'vd.vde_payto_name',
            'vd.vde_bank_name',
            'vd.vde_bank_acctno',
            'vd.fty_fund_type',
            'ft.fty_fund_desc',
            'vd.at_activity_code',
            'at.at_activity_description_bm as at_activity_desc',
            'vd.oun_code',
            'ou.oun_desc',
            'vd.ccr_costcentre',
            'cc.ccr_costcentre_desc',
            'vd.acm_acct_code',
            'am.acm_acct_desc',
            'vd.vde_amount',
            'vd.vde_factoring_type',
            'vd.vde_factoring_id',
            'vd.vde_factoring_name',
            'vd.vde_fact_bank_name',
            'vd.vde_fact_bank_acctno',
            'vd.vde_status',
            'vd.vde_payment_no',
        ];

        $buildRows = function (string $transType) use ($conn, $master, $detailCols) {
            return $conn->table('voucher_details as vd')
                ->leftJoin('fund_type as ft',          'ft.fty_fund_type',      '=', 'vd.fty_fund_type')
                ->leftJoin('activity_type as at',       'at.at_activity_code',   '=', 'vd.at_activity_code')
                ->leftJoin('organization_unit as ou',   'ou.oun_code',           '=', 'vd.oun_code')
                ->leftJoin('costcentre as cc',          'cc.ccr_costcentre',     '=', 'vd.ccr_costcentre')
                ->leftJoin('account_main as am',        'am.acm_acct_code',      '=', 'vd.acm_acct_code')
                ->where('vd.vma_voucher_id', $master->vma_voucher_id)
                ->where('vd.vde_trans_type', $transType)
                ->select($detailCols)
                ->get();
        };

        $debit  = $buildRows('DT');
        $credit = $buildRows('CR');

        // Derive credit account label from first CR row
        $creditAcctLabel = null;
        foreach ($credit as $cr) {
            if ($cr->acm_acct_code) {
                $creditAcctLabel = trim(($cr->acm_acct_code ?? '') . ' - ' . ($cr->acm_acct_desc ?? ''), ' -');
                break;
            }
        }

        return $this->sendOk([
            'master' => array_merge((array) $master, ['credit_account_code' => $creditAcctLabel]),
            'debit'  => $debit,
            'credit' => $credit,
        ]);
    }

    /** Update creditor/bank/factoring info for a single voucher_details row. */
    public function apVoucherUpdateCreditorInfo(Request $request, int $detlId): JsonResponse
    {
        $conn = $this->secondaryConn();
        $row  = $conn->table('voucher_details')->where('vde_voucher_detl_id', $detlId)->first();
        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Voucher detail not found');
        }

        $conn->table('voucher_details')->where('vde_voucher_detl_id', $detlId)->update([
            'vde_payto_type'       => $request->input('vde_payto_type'),
            'vde_payto_id'         => $request->input('vde_payto_id'),
            'vde_payto_name'       => $request->input('vde_payto_name'),
            'vde_bank_name'        => $request->input('vde_bank_name'),
            'vde_bank_acctno'      => $request->input('vde_bank_acctno'),
            'vde_factoring_type'   => $request->input('vde_factoring_type'),
            'vde_factoring_id'     => $request->input('vde_factoring_id'),
            'vde_factoring_name'   => $request->input('vde_factoring_name'),
            'vde_fact_bank_name'   => $request->input('vde_fact_bank_name'),
            'vde_fact_bank_acctno' => $request->input('vde_fact_bank_acctno'),
            'updateddate'          => now()->format('Y-m-d H:i:s'),
            'updatedby'            => $request->user()?->email ?? 'system',
        ]);

        return $this->sendOk(['success' => true]);
    }

    // ── AP Voucher Process (MENUID 3535) — submit Process action ──────────────

    /**
     * Update one or more voucher_master rows with a new status + remark.
     * Status options match the legacy workflow: APPROVE / REJECT / RETURN.
     */
    public function apVoucherProcessSubmit(Request $request): JsonResponse
    {
        $voucherIds = $request->input('voucher_ids', []);
        if (! is_array($voucherIds)) {
            $voucherIds = array_filter(array_map('trim', explode(',', (string) $voucherIds)));
        }
        $voucherIds = array_values(array_filter(array_map('intval', $voucherIds), fn ($v) => $v > 0));

        $status = strtoupper(trim((string) $request->input('status', '')));
        $remark = trim((string) $request->input('remark', ''));

        if (empty($voucherIds)) {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Please select at least one voucher.');
        }
        if ($status === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Status is required.');
        }
        if (! in_array($status, ['APPROVE', 'REJECT', 'RETURN'], true)) {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Status must be APPROVE, REJECT or RETURN.');
        }
        if ($remark === '') {
            return $this->sendError(422, 'VALIDATION_ERROR', 'Remark is required.');
        }

        $conn  = $this->secondaryConn();
        $by    = $request->user()?->email ?? $request->user()?->name ?? 'system';
        $now   = now()->format('Y-m-d H:i:s');

        $update = [
            'vma_vch_status' => $status,
            'updateddate'    => $now,
            'updatedby'      => $by,
        ];
        if ($status === 'APPROVE') {
            $update['vma_approve_by']   = $by;
            $update['vma_approve_date'] = $now;
        }
        // Remark stored in vma_cancel_reason (legacy reuses this column for processing notes)
        $update['vma_cancel_reason'] = $remark;

        $affected = $conn->table('voucher_master')
            ->whereIn('vma_voucher_id', $voucherIds)
            ->update($update);

        return $this->sendOk([
            'success'       => true,
            'updated_count' => $affected,
            'status'        => $status,
        ]);
    }
}
