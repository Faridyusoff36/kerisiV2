<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student Finance > Sponsor > Advance Payment (PAGEID 1669 / MENUID 2020).
 *
 * Source: FIMS BL `V2_SAP_LIST_API` (datatable listing via ?dt_listing=1;
 * Excel download via ?download=1). Reads from `deposit_master`,
 * `deposit_details`, `lookup_parameter_main`, `stud_sponsor_amount` and
 * `cust_invoice_master` in DB_SECOND_DATABASE.
 *
 * Smart filters (legacy `smartFilter` keys are kept intact so the frontend
 * does not need to convert):
 *   - vcs_vendor_code      — exact match on dpm.vcs_vendor_code
 *   - dpm_vendor_name      — LIKE %...% on dpm.dpm_vendor_name
 *   - advance_amount_from  — having clause >= (numeric, comma-stripped)
 *   - advance_amount_to    — having clause <= (numeric, comma-stripped)
 *   - invoice_balance_from — having clause >= (numeric, comma-stripped)
 *   - invoice_balance_to   — having clause <= (numeric, comma-stripped)
 *
 * Global search (`q`) mirrors the legacy
 *   CONCAT_WS('__', dpm.vcs_vendor_code, dpm.dpm_vendor_name) LIKE %?%
 * surface so the cross-column substring query behaves the same as the
 * FIMS UI.
 *
 * Action buttons in the legacy COMPONENT_JS deep-link to:
 *   - Knockoff Invoice (menuID=2021)
 *   - Transfer To Student (menuID=2354)
 * Neither destination page is migrated yet; the frontend renders both
 * action buttons as disabled with a "not migrated" tooltip until those
 * editors are ported.
 *
 * The legacy COMPONENT_JS does not declare a `printout` field, so per
 * project policy the frontend exposes PDF / CSV / Excel exports backed
 * by the same query (full filtered set, no extra endpoint).
 */
class AdvancePaymentController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'vcs_vendor_code',
        'dpm_vendor_name',
        'dpm_deposit_no',
        'advance_amount',
        'invoice_balance',
    ];

    public function options(): JsonResponse
    {
        // Sponsor (vendor) options used by the smart-filter dropdown.
        // Legacy autosuggest is not wired here — we surface a distinct
        // list of vendor codes/names actually present in deposit_master
        // so the dropdown stays accurate without pulling in another BL.
        $sponsors = DB::connection('mysql_secondary')
            ->table('deposit_master')
            ->select('vcs_vendor_code', 'dpm_vendor_name')
            ->whereNotNull('vcs_vendor_code')
            ->where('vcs_vendor_code', '!=', '')
            ->where('dpm_payto_type', 'E')
            ->whereIn('dpm_status', ['APPROVE', '1'])
            ->distinct()
            ->orderBy('vcs_vendor_code')
            ->limit(500)
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->vcs_vendor_code,
                'label' => $r->dpm_vendor_name
                    ? $r->vcs_vendor_code.' - '.$r->dpm_vendor_name
                    : (string) $r->vcs_vendor_code,
            ])
            ->values();

        return $this->sendOk([
            'sponsors' => $sponsors,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'vcs_vendor_code');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'vcs_vendor_code';
        }

        $vendorCode = trim((string) $request->input('vcs_vendor_code', ''));
        $vendorName = trim((string) $request->input('dpm_vendor_name', ''));
        $advanceFrom = $this->numeric($request->input('advance_amount_from'));
        $advanceTo = $this->numeric($request->input('advance_amount_to'));
        $invBalFrom = $this->numeric($request->input('invoice_balance_from'));
        $invBalTo = $this->numeric($request->input('invoice_balance_to'));

        $base = $this->baseQuery();

        if ($q !== '') {
            $needle = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';
            $base->whereRaw(
                "CONCAT_WS('__', IFNULL(dpm.vcs_vendor_code, ''), IFNULL(dpm.dpm_vendor_name, '')) LIKE ?",
                [$needle]
            );
        }
        if ($vendorCode !== '') {
            $base->where('dpm.vcs_vendor_code', $vendorCode);
        }
        if ($vendorName !== '') {
            $needle = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $vendorName).'%';
            $base->whereRaw('dpm.dpm_vendor_name LIKE ?', [$needle]);
        }

        $advanceExpr = "SUM(CASE WHEN ddt.ddt_type = 'CR' THEN ddt.ddt_amt WHEN ddt.ddt_type = 'DT' THEN -ddt.ddt_amt ELSE 0 END)";
        $invoiceExpr = '('
            ."(SELECT IFNULL(SUM(ssa.ssa_sponsor_amt), 0) FROM stud_sponsor_amount ssa WHERE ssa.spn_sponsor_code = dpm.vcs_vendor_code) + "
            ."(SELECT IFNULL(SUM(cim.cim_bal_amt), 0) FROM cust_invoice_master cim WHERE cim.cim_cust_id = dpm.vcs_vendor_code AND cim.cim_status = 'APPROVE' AND cim.cim_bal_amt > 0)"
            .')';

        // GROUP BY drives the row identity and aggregate eligibility for
        // HAVING; mirror the legacy SQL exactly.
        $grouped = $base->select([
            'dpm.vcs_vendor_code',
            'dpm.dpm_vendor_name',
            'dpm.dpm_deposit_no',
            DB::raw("$advanceExpr AS advance_amount"),
            DB::raw("$invoiceExpr AS invoice_balance"),
        ])
            ->groupBy('dpm.vcs_vendor_code', 'dpm.dpm_vendor_name', 'dpm.dpm_deposit_no')
            ->havingRaw("$advanceExpr > 0");

        if ($advanceFrom !== null) {
            $grouped->havingRaw("$advanceExpr >= ?", [$advanceFrom]);
        }
        if ($advanceTo !== null) {
            $grouped->havingRaw("$advanceExpr <= ?", [$advanceTo]);
        }
        if ($invBalFrom !== null) {
            $grouped->havingRaw("$invoiceExpr >= ?", [$invBalFrom]);
        }
        if ($invBalTo !== null) {
            $grouped->havingRaw("$invoiceExpr <= ?", [$invBalTo]);
        }

        // Total via wrapping subquery — the legacy code re-runs the query
        // and PHP-counts the rows; we use SQL COUNT(*) over the same
        // grouped+having set to avoid the round-trip.
        $totalQuery = DB::connection('mysql_secondary')
            ->query()
            ->fromSub($grouped, 't');
        $total = (int) $totalQuery->count();

        $orderColumn = match ($sortBy) {
            'vcs_vendor_code' => 'vcs_vendor_code',
            'dpm_vendor_name' => 'dpm_vendor_name',
            'dpm_deposit_no' => 'dpm_deposit_no',
            'advance_amount' => DB::raw('advance_amount'),
            'invoice_balance' => DB::raw('invoice_balance'),
            default => 'vcs_vendor_code',
        };

        $rows = $grouped
            ->orderBy($orderColumn, $sortDir)
            ->orderBy('vcs_vendor_code', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(fn ($r, int $i) => [
            'index' => (($page - 1) * $limit) + $i + 1,
            'vcsVendorCode' => (string) $r->vcs_vendor_code,
            'dpmVendorName' => $r->dpm_vendor_name,
            'dpmDepositNo' => $r->dpm_deposit_no,
            'advanceAmount' => $r->advance_amount !== null ? (float) $r->advance_amount : 0.0,
            'invoiceBalance' => $r->invoice_balance !== null ? (float) $r->invoice_balance : 0.0,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /**
     * Replicates the legacy `FROM/WHERE` from `V2_SAP_LIST_API`.
     */
    private function baseQuery(): Builder
    {
        return DB::connection('mysql_secondary')
            ->table('deposit_master as dpm')
            ->join('deposit_details as ddt', 'dpm.dpm_deposit_master_id', '=', 'ddt.dpm_deposit_master_id')
            ->join('lookup_parameter_main as lpm', 'ddt.acm_acct_code', '=', 'lpm.lpm_value')
            ->where('lpm.lpm_code', 'ACCT_CODE_DT_INV_SPON')
            ->where('dpm.dpm_payto_type', 'E')
            ->whereIn('dpm.dpm_status', ['APPROVE', '1']);
    }

    /**
     * Strip thousand separators and parse as float; returns null when the
     * string is empty or non-numeric. Mirrors the legacy
     *   str_replace(',', '', $smartFilter[...])*1
     * coercion.
     */
    private function numeric(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string) $value);
        if ($str === '') {
            return null;
        }
        $cleaned = str_replace([',', ' '], '', $str);
        if (! is_numeric($cleaned)) {
            return null;
        }

        return (float) $cleaned;
    }
}
