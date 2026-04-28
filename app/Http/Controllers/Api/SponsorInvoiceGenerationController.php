<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\LookupDetail;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student Finance > Sponsor > Invoice Generation (PAGEID 1218 / MENUID 1491).
 *
 * Source: FIMS BL `V2_SFSI_API`. The legacy page combines:
 *   - a top form (Sponsor / Program Level / Semester) — REQUIRED;
 *     the legacy `?listing=1` branch returns an empty draw and only
 *     `?listing=2` (with all three keys present) returns rows.
 *   - a smart filter (matric, name, status, outstanding range, claim
 *     range).
 *   - a datatable of sponsored students with outstanding amount.
 *   - a "Generate" action that picks a checkbox set and calls
 *     CALL DB2.create_invoice_sponsor SP.
 *
 * Migration parity vs deviations:
 *   - The Generate flow depends on `create_invoice_sponsor` /
 *     `get_sponsorAmt` stored procedures and the `sp_out` polling
 *     pattern. None of those are migrated yet — this controller exposes
 *     ONLY the read-only listing. The frontend renders the Generate
 *     button as disabled with a "not migrated" tooltip.
 *   - Smart-filter keys preserve the legacy contract, including the
 *     numeric range coercion (`str_replace(',', '', $val) * 1`).
 *   - Listing filtering is mandatory: if any of sponsor/program
 *     level/semester is missing, we return an empty page (matching
 *     legacy `?listing=1` behaviour) so the frontend never shows
 *     unfiltered, performance-heavy joins.
 *
 * Per project policy (legacy COMPONENT_JS has no `printout` field) we
 * surface PDF / CSV / Excel exports for the rendered page set on the
 * frontend; the legacy `?listing=download` branch is replaced by the
 * frontend exports backed by the same listing endpoint.
 */
class SponsorInvoiceGenerationController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'std_student_id',
        'std_student_name',
        'std_status_desc',
        'spn_sponsor_name',
        'spc_date_from',
        'spc_date_to',
        'cim_invoice_no',
        'outstanding_amt',
        'ssp_limit_bal',
        'cim_nett_amt',
    ];

    public function options(): JsonResponse
    {
        $sponsors = DB::connection('mysql_secondary')
            ->table('sponsor')
            ->select('spn_sponsor_code', 'spn_sponsor_name')
            ->whereNotNull('spn_sponsor_code')
            ->where('spn_sponsor_code', '!=', '')
            ->orderBy('spn_sponsor_code')
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->spn_sponsor_code,
                'label' => $r->spn_sponsor_name
                    ? $r->spn_sponsor_code.' - '.$r->spn_sponsor_name
                    : (string) $r->spn_sponsor_code,
            ])
            ->values();

        $programLevels = LookupDetail::query()
            ->where('lma_code_name', 'PROGRAM_LEVEL')
            ->orderBy('lde_value')
            ->get(['lde_value', 'lde_description'])
            ->map(fn ($r) => [
                'id' => (string) $r->lde_value,
                'label' => $r->lde_description
                    ? $r->lde_value.' - '.$r->lde_description
                    : (string) $r->lde_value,
            ])
            ->values();

        $semesters = DB::connection('mysql_secondary')
            ->table('academic_calendar')
            ->select('acl_semester_code', 'acl_semester_name')
            ->whereNotNull('acl_semester_code')
            ->where('acl_semester_code', '!=', '')
            ->orderBy('acl_semester_code', 'desc')
            ->distinct()
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->acl_semester_code,
                'label' => $r->acl_semester_name
                    ? $r->acl_semester_code.' - '.$r->acl_semester_name
                    : (string) $r->acl_semester_code,
            ])
            ->values();

        return $this->sendOk([
            'sponsors' => $sponsors,
            'programLevels' => $programLevels,
            'semesters' => $semesters,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $sortBy = (string) $request->input('sort_by', 'std_student_id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'std_student_id';
        }

        // Top-filter (REQUIRED — empty page when any is missing).
        $sponsorCode = trim((string) $request->input('spn_sponsor_code', ''));
        $programLevel = trim((string) $request->input('std_program', ''));
        $semesterId = trim((string) $request->input('cim_semester_id', ''));

        if ($sponsorCode === '' || $programLevel === '' || $semesterId === '') {
            return $this->sendOk([], [
                'page' => $page,
                'limit' => $limit,
                'total' => 0,
                'totalPages' => 1,
                'totalStudent' => 0,
                'footer' => ['outstandingAmt' => 0, 'cimNettAmt' => 0],
            ]);
        }

        $q = trim((string) $request->input('q', ''));
        $studentId = trim((string) $request->input('std_student_id', ''));
        $studentName = trim((string) $request->input('std_student_name', ''));
        $studStatus = trim((string) $request->input('std_status_desc', ''));
        $outstandingFrom = $this->numeric($request->input('outstanding_amt_from'));
        $outstandingTo = $this->numeric($request->input('outstanding_amt_to'));
        $cimNettFrom = $this->numeric($request->input('cim_nett_amt_from'));
        $cimNettTo = $this->numeric($request->input('cim_nett_amt_to'));

        $base = $this->baseQuery()
            ->where('B.spn_sponsor_code', $sponsorCode)
            ->where('A.std_program_level', $programLevel)
            ->where('cim.cim_semester_id', $semesterId);

        if ($q !== '') {
            $base->whereRaw(
                "CONCAT_WS('__', "
                ."IFNULL(A.std_student_id, ''), "
                ."IFNULL(A.std_student_name, ''), "
                ."CONCAT_WS(' - ', IFNULL(C.spn_sponsor_code, ''), IFNULL(C.spn_sponsor_name, ''))"
                .') LIKE ?',
                [$this->likeEscape($q)]
            );
        }
        if ($studentId !== '') {
            $base->where('A.std_student_id', $studentId);
        }
        if ($studentName !== '') {
            $base->whereRaw('A.std_student_name LIKE ?', [$this->likeEscape($studentName)]);
        }
        if ($studStatus !== '') {
            $base->where('A.std_status', $studStatus);
        }

        // Subquery used for both the outer SELECT and the cim_nett_amt
        // smart-filter range — kept as a single string so cloning works.
        $cimNettExpr = '(SELECT ssa.ssa_sponsor_amt FROM stud_sponsor_amount ssa '
            ."WHERE ssa.spn_sponsor_code = C.spn_sponsor_code AND ssa.cim_cust_id = A.std_student_id "
            ."AND ssa.cim_invoice_no = cim.cim_invoice_no ORDER BY ssa.createddate DESC LIMIT 1)";

        $groupBy = [
            'A.std_student_id',
            'A.std_student_name',
            'A.std_status',
            'C.spn_sponsor_code',
            'D.spc_date_from',
            'D.spc_date_to',
            'cim.cim_cust_invoice_id',
        ];

        $aggregate = (clone $base)
            ->select([
                'A.std_student_id',
                'A.std_student_name',
                'A.std_status',
                'B.ssp_limit_bal',
                'cim.cim_invoice_no',
                'cim.cim_cust_invoice_id',
                'C.spn_sponsor_code',
                'C.spn_sponsor_name',
                'C.spn_status_invoice_cd',
                'D.spc_date_from',
                'D.spc_date_to',
                DB::raw("CONCAT_WS(' - ', A.std_status, IFNULL(A.std_extended_field->>'\$.std_status_desc', ' ')) AS std_status_desc"),
                DB::raw("CONCAT_WS(' - ', C.spn_sponsor_code, C.spn_sponsor_name) AS spn_sponsor_name"),
                DB::raw("DATE_FORMAT(D.spc_date_from, '%d/%m/%Y') AS spc_date_from_fmt"),
                DB::raw("DATE_FORMAT(D.spc_date_to, '%d/%m/%Y') AS spc_date_to_fmt"),
                DB::raw('SUM(IFNULL(cid.cid_bal_amt, 0)) AS outstanding_amt'),
                DB::raw($cimNettExpr.' AS cim_nett_amt'),
            ])
            ->groupBy(...$groupBy);

        if ($outstandingFrom !== null) {
            $aggregate->havingRaw('SUM(IFNULL(cid.cid_bal_amt, 0)) >= ?', [$outstandingFrom]);
        }
        if ($outstandingTo !== null) {
            $aggregate->havingRaw('SUM(IFNULL(cid.cid_bal_amt, 0)) <= ?', [$outstandingTo]);
        }
        if ($cimNettFrom !== null) {
            $aggregate->havingRaw($cimNettExpr.' >= ?', [$cimNettFrom]);
        }
        if ($cimNettTo !== null) {
            $aggregate->havingRaw($cimNettExpr.' <= ?', [$cimNettTo]);
        }

        // Total = COUNT of grouped rows, mirrors legacy `count($data)`.
        $totalQuery = DB::connection('mysql_secondary')
            ->query()
            ->fromSub($aggregate, 't');
        $total = (int) $totalQuery->count();

        // Distinct student count, mirrors legacy `COUNT(DISTINCT std_student_id)`.
        $totalStudent = (int) (clone $base)
            ->distinct()
            ->count('A.std_student_id');

        // Footer grand totals (computed on the full filtered set).
        $footer = DB::connection('mysql_secondary')
            ->query()
            ->fromSub($aggregate, 't2')
            ->selectRaw('SUM(t2.outstanding_amt) AS outstanding_amt, SUM(t2.cim_nett_amt) AS cim_nett_amt')
            ->first();

        $orderColumn = match ($sortBy) {
            'std_student_id' => 'A.std_student_id',
            'std_student_name' => 'A.std_student_name',
            'std_status_desc' => DB::raw("CONCAT_WS(' - ', A.std_status, IFNULL(A.std_extended_field->>'\$.std_status_desc', ' '))"),
            'spn_sponsor_name' => DB::raw("CONCAT_WS(' - ', C.spn_sponsor_code, C.spn_sponsor_name)"),
            'spc_date_from' => 'D.spc_date_from',
            'spc_date_to' => 'D.spc_date_to',
            'cim_invoice_no' => 'cim.cim_invoice_no',
            'outstanding_amt' => DB::raw('outstanding_amt'),
            'ssp_limit_bal' => 'B.ssp_limit_bal',
            'cim_nett_amt' => DB::raw('cim_nett_amt'),
            default => 'A.std_student_id',
        };

        $rows = (clone $aggregate)
            ->orderBy($orderColumn, $sortDir)
            ->orderBy('cim.cim_cust_invoice_id', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $cimInvoiceIds = $rows->pluck('cim_cust_invoice_id')->all();
        $tellMeWhy = [];
        if (! empty($cimInvoiceIds)) {
            // Only rows where cim_nett_amt is null/zero need the
            // tell-me-why probe; matches legacy `if(!cim_nett_amt)`.
            $needs = $rows->filter(fn ($r) => ! ((float) ($r->cim_nett_amt ?? 0)))->pluck('cim_cust_invoice_id')->all();
            if (! empty($needs)) {
                $hasKnockoff = DB::connection('mysql_secondary')
                    ->table('cust_invoice_details')
                    ->whereIn('cim_cust_invoice_id', $needs)
                    ->whereNotNull('cid_sponsor_amt')
                    ->pluck('cim_cust_invoice_id')
                    ->all();
                $hasKnockoffSet = array_flip($hasKnockoff);
                foreach ($needs as $invId) {
                    $tellMeWhy[$invId] = isset($hasKnockoffSet[$invId])
                        ? 'Invoice already knockoff'
                        : 'Please check Fee Cover';
                }
            }
        }

        $data = $rows->values()->map(fn ($r, int $i) => [
            'index' => (($page - 1) * $limit) + $i + 1,
            'cimCustInvoiceId' => (int) $r->cim_cust_invoice_id,
            'stdStudentId' => (string) $r->std_student_id,
            'stdStudentName' => $r->std_student_name,
            'stdStatusDesc' => $r->std_status_desc,
            'spnSponsorName' => $r->spn_sponsor_name,
            'spcDateFrom' => $r->spc_date_from_fmt,
            'spcDateTo' => $r->spc_date_to_fmt,
            'cimInvoiceNo' => $r->cim_invoice_no,
            'outstandingAmt' => (float) ($r->outstanding_amt ?? 0),
            'sspLimitBal' => $r->ssp_limit_bal !== null ? (float) $r->ssp_limit_bal : null,
            'cimNettAmt' => $r->cim_nett_amt !== null ? (float) $r->cim_nett_amt : null,
            'isJournal' => ($r->spn_status_invoice_cd ?? '') === '2',
            'tellMeWhy' => $tellMeWhy[$r->cim_cust_invoice_id] ?? null,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'totalStudent' => $totalStudent,
            'footer' => [
                'outstandingAmt' => (float) ($footer->outstanding_amt ?? 0),
                'cimNettAmt' => (float) ($footer->cim_nett_amt ?? 0),
            ],
        ]);
    }

    /**
     * Replicates the legacy implicit-join FROM clause of `V2_SFSI_API`,
     * including the existence check on sponsor_invoice_details.
     */
    private function baseQuery(): Builder
    {
        return DB::connection('mysql_secondary')
            ->table('student as A')
            ->join('stud_sponsor as B', 'A.std_student_id', '=', 'B.std_student_id')
            ->join('sponsor as C', 'B.spn_sponsor_code', '=', 'C.spn_sponsor_code')
            ->join('cust_invoice_master as cim', 'cim.cim_cust_id', '=', 'A.std_student_id')
            ->join('cust_invoice_details as cid', 'cim.cim_cust_invoice_id', '=', 'cid.cim_cust_invoice_id')
            ->join('cust_invoice_item as cii', function ($j) {
                $j->on('cii.cii_item_code', '=', 'cid.cii_item_code')
                    ->on('cii.cii_item_category', '=', 'cid.cii_item_category');
            })
            ->join('stud_sponsor_period_cover as D', 'D.ssp_id', '=', 'B.ssp_id')
            ->where('cim.cim_status', 'APPROVE')
            ->whereRaw('(IFNULL(cid.cid_total_amt, 0) - IFNULL(cid.cid_sponsor_amt, 0)) > 0')
            ->where('cid.cid_transaction_type', 'CR')
            ->whereRaw('NOT EXISTS (SELECT 1 FROM sponsor_invoice_details sid WHERE sid.cim_invoice_no = cim.cim_invoice_no)')
            ->whereRaw('NOW() >= D.spc_date_from')
            ->whereRaw('(NOW() <= IFNULL(D.spc_date_to, NOW()) OR D.spc_sems_to >= ?)', [$this->currentSem()]);
    }

    private function currentSem(): string
    {
        // Legacy quirk: `$p_current_sem = explode(' ', $cim_semester_id)[0]`
        // is set inside the listing branch with cim_semester_id known;
        // we just read the current request semester here.
        $sem = (string) request()->input('cim_semester_id', '');

        return explode(' ', trim($sem))[0] ?: 'xxx';
    }

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

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
