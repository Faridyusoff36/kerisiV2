<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student Finance > Sponsor > Student Journal Approval (PAGEID 1954 / MENUID 2390).
 *
 * Source: FIMS BL `MZ_BL_SF_APPROVAL`. The page composes:
 *   - GET ?details=1                       -> read-only master (manual_journal_master).
 *   - GET ?dt_listing=1&dt_credit_debit=CR -> read-only Credit datatable.
 *   - GET ?dt_listing=1&dt_credit_debit=DT -> read-only Debit datatable.
 *   - GET ?approve=1 / ?getWFDetail=1      -> workflow CALLs (workflowUpdate /
 *                                            workflowGetDetails). NOT migrated.
 *
 * The Approve/Reject workflow depends on stored procedures
 * `workflowUpdate` / `workflowGetDetails` and a workflow task table
 * (`wf_task`) that have not been ported. This controller exposes ONLY
 * the three read-only branches; the frontend renders the Process panel
 * as disabled with a "not migrated" tooltip until the workflow engine
 * is migrated.
 *
 * Per project policy (legacy COMPONENT_JS has no `printout` field) the
 * frontend surfaces PDF / CSV / Excel exports for the rendered Credit
 * and Debit datatables.
 */
class StudentJournalApprovalController extends Controller
{
    use ApiResponse;

    private const SORTABLE_CR = [
        'cim_cust',
        'fty_fund_type',
        'at_activity_code',
        'oun_code',
        'ccr_costcentre',
        'acm_acct_code',
        'mjd_trans_amt',
        'saf_invoice_amt',
        'mjd_document_no',
    ];

    private const SORTABLE_DT = [
        'fty_fund_type',
        'at_activity_code',
        'oun_code',
        'ccr_costcentre',
        'acm_acct_code',
        'mjd_document_no',
    ];

    public function show(int $id): JsonResponse
    {
        $row = DB::connection('mysql_secondary')
            ->table('manual_journal_master')
            ->select([
                'mjm_journal_id',
                'mjm_journal_no',
                'mjm_total_amt',
                'mjm_journal_desc',
                'mjm_typeofjournal',
                'mjm_status',
                DB::raw("DATE_FORMAT(mjm_enterdate, '%d/%m/%Y') AS mjm_enterdate"),
                'dpm_deposit_no',
                DB::raw("mjm_extended_field->>'\$.advance_amount' AS advance_amount"),
                DB::raw("mjm_extended_field->>'\$.semester' AS semester"),
                DB::raw(
                    "IF(mjm_extended_field->>'\$.advance_category'='1', "
                    ."'Bayaran Melalui Penaja', 'Bayaran One Off') AS advance_category"
                ),
            ])
            ->where('mjm_journal_id', $id)
            ->first();

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Journal not found.');
        }

        return $this->sendOk([
            'mjmJournalId' => (int) $row->mjm_journal_id,
            'mjmJournalNo' => $row->mjm_journal_no,
            'mjmTotalAmt' => $row->mjm_total_amt !== null ? (float) $row->mjm_total_amt : null,
            'mjmJournalDesc' => $row->mjm_journal_desc,
            'mjmTypeofjournal' => $row->mjm_typeofjournal,
            'mjmStatus' => $row->mjm_status,
            'mjmEnterdate' => $row->mjm_enterdate,
            'dpmDepositNo' => $row->dpm_deposit_no,
            'advanceAmount' => $row->advance_amount !== null && $row->advance_amount !== ''
                ? (float) $row->advance_amount
                : null,
            'semester' => $row->semester,
            'advanceCategory' => $row->advance_category,
        ]);
    }

    public function credit(Request $request, int $id): JsonResponse
    {
        return $this->detailListing($request, $id, 'CR', self::SORTABLE_CR);
    }

    public function debit(Request $request, int $id): JsonResponse
    {
        return $this->detailListing($request, $id, 'DT', self::SORTABLE_DT);
    }

    /**
     * Shared listing for both Credit (?dt_credit_debit=CR) and Debit
     * (?dt_credit_debit=DT) branches. Mirrors the legacy global search
     * surface and the saf_invoice_amt subselect.
     */
    private function detailListing(Request $request, int $id, string $crDt, array $sortable): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', $sortable[0] ?? 'mjd_journal_detl_id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, $sortable, true)) {
            $sortBy = $sortable[0] ?? 'mjd_journal_detl_id';
        }

        $base = $this->detailQuery($id, $crDt);

        if ($q !== '') {
            $needle = $this->likeEscape($q);
            $base->whereRaw(
                "CONCAT_WS('__', "
                ."IFNULL(mjd.mjd_journal_detl_id, ''), "
                ."IFNULL(mjd.oun_code, ''), "
                ."IFNULL(mjd.oun_desc, ''), "
                ."IFNULL(mjd.fty_fund_type, ''), "
                ."IFNULL(mjd.ft_fund_desc, ''), "
                ."IFNULL(mjd.at_activity_code, ''), "
                ."IFNULL(mjd.ccr_costcentre, ''), "
                ."IFNULL(mjd.ccr_costcentre_desc, ''), "
                ."IFNULL(mjd.acm_acct_code, ''), "
                ."IFNULL(mjd.acm_acct_desc, ''), "
                ."IFNULL(mjd.code_so, ''), "
                ."IFNULL(mjd.cpa_project_no, ''), "
                ."IFNULL(mjd.mjd_document_no, ''), "
                .'IFNULL(mjd.mjd_trans_amt, 0), '
                .'IFNULL((SELECT MAX(mjm_total_amt) FROM manual_journal_master mjm WHERE mjm.mjm_journal_id=mjd.mjm_journal_no), 0), '
                ."CONCAT(IFNULL(mjd.mjd_payto_name, ''), ' (', IFNULL(mjd.mjd_payto_id, ''), ')')"
                .') LIKE ?',
                [$needle]
            );
        }

        $total = (clone $base)->count();

        $orderColumn = match ($sortBy) {
            'cim_cust' => DB::raw("CONCAT(IFNULL(mjd.mjd_payto_name, ''), ' (', IFNULL(mjd.mjd_payto_id, ''), ')')"),
            'fty_fund_type' => 'mjd.fty_fund_type',
            'at_activity_code' => 'mjd.at_activity_code',
            'oun_code' => 'mjd.oun_code',
            'ccr_costcentre' => 'mjd.ccr_costcentre',
            'acm_acct_code' => 'mjd.acm_acct_code',
            'mjd_trans_amt' => 'mjd.mjd_trans_amt',
            'saf_invoice_amt' => DB::raw('saf_invoice_amt'),
            'mjd_document_no' => 'mjd.mjd_document_no',
            default => 'mjd.mjd_journal_detl_id',
        };

        $rows = (clone $base)
            ->select([
                'mjd.mjd_journal_detl_id',
                'mjd.oun_code',
                'mjd.oun_desc',
                'mjd.fty_fund_type',
                'mjd.ft_fund_desc',
                'mjd.at_activity_code',
                'mjd.ccr_costcentre',
                'mjd.ccr_costcentre_desc',
                'mjd.acm_acct_code',
                'mjd.acm_acct_desc',
                'mjd.code_so',
                'mjd.cpa_project_no',
                'mjd.mjd_document_no',
                'mjd.mjd_trans_amt',
                'mjd.mjd_payto_id',
                'mjd.mjd_payto_name',
                DB::raw('(SELECT MAX(mjm_total_amt) FROM manual_journal_master mjm WHERE mjm.mjm_journal_id = mjd.mjm_journal_no) AS mjm_total_amt'),
                DB::raw("CONCAT(IFNULL(mjd.mjd_payto_name, ''), ' (', IFNULL(mjd.mjd_payto_id, ''), ')') AS cim_cust"),
                DB::raw('(SELECT saf.saf_invoice_amt FROM stud_advance_from_sponsor saf WHERE saf.mjm_journal_no = mjd.mjm_journal_no AND saf.std_student_id = mjd.mjd_payto_id LIMIT 1) AS saf_invoice_amt'),
            ])
            ->orderBy($orderColumn, $sortDir)
            ->orderBy('mjd.mjd_journal_detl_id', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Footer total: SUM(mjd_trans_amt) over the unfiltered journal
        // detail set (legacy ignores `q` for footer; we preserve that).
        $footerAmt = (float) DB::connection('mysql_secondary')
            ->table('manual_journal_details as mjd')
            ->where('mjd.mjd_trans_type', $crDt)
            ->where('mjd.mjm_journal_no', $id)
            ->sum('mjd_trans_amt');

        $data = $rows->values()->map(fn ($r) => [
            'mjdJournalDetlId' => (int) $r->mjd_journal_detl_id,
            'ounCode' => $r->oun_code !== null && $r->oun_desc !== null
                ? trim($r->oun_code.' - '.$r->oun_desc)
                : ($r->oun_code ?? null),
            'ftyFundType' => $r->fty_fund_type !== null && $r->ft_fund_desc !== null
                ? trim($r->fty_fund_type.' - '.$r->ft_fund_desc)
                : ($r->fty_fund_type ?? null),
            'atActivityCode' => $r->at_activity_code,
            'ccrCostcentre' => $r->ccr_costcentre !== null && $r->ccr_costcentre_desc !== null
                ? trim($r->ccr_costcentre.' - '.$r->ccr_costcentre_desc)
                : ($r->ccr_costcentre ?? null),
            'acmAcctCode' => $r->acm_acct_code !== null && $r->acm_acct_desc !== null
                ? trim($r->acm_acct_code.' - '.$r->acm_acct_desc)
                : ($r->acm_acct_code ?? null),
            'mjdDocumentNo' => $r->mjd_document_no,
            'mjdTransAmt' => (float) ($r->mjd_trans_amt ?? 0),
            'mjmTotalAmt' => $r->mjm_total_amt !== null ? (float) $r->mjm_total_amt : null,
            'cimCust' => $r->cim_cust,
            'safInvoiceAmt' => $r->saf_invoice_amt !== null ? (float) $r->saf_invoice_amt : null,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'footer' => [
                'mjdTransAmt' => $footerAmt,
                'mjmTotalAmt' => $footerAmt,
            ],
        ]);
    }

    /**
     * Replicates the legacy detail filter — by master id and CR/DT.
     */
    private function detailQuery(int $id, string $crDt): Builder
    {
        return DB::connection('mysql_secondary')
            ->table('manual_journal_details as mjd')
            ->where('mjd.mjd_trans_type', $crDt)
            ->where('mjd.mjm_journal_no', $id);
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
