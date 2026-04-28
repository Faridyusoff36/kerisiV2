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
 * Student Finance > Sponsor > PTPTN (PAGEID 1231 / MENUID 1507).
 *
 * Source: FIMS BL `V2_PTPTN_API` (?listing=1). Read-only datatable
 * scoped to sponsors with `spn_sponsor_type='05'` (PTPTN), joining
 * `student` × `stud_sponsor` × `sponsor` from DB_SECOND_DATABASE.
 *
 * Smart-filter keys preserve the legacy contract:
 *   - std_student_id    — LIKE %...%
 *   - std_student_name  — LIKE %...%
 *   - ic_passport       — LIKE %...% on IFNULL(IF(std_ic_no='',NULL,std_ic_no), std_passport)
 *   - std_program_level — LIKE %...% (legacy uses LIKE, not exact — preserved)
 *   - std_status        — LIKE %...% (legacy uses LIKE, not exact — preserved)
 *   - ssp_reference_no  — LIKE %...%
 *   - ssp_warrant_no    — LIKE %...%
 *
 * Global search (`q`) mirrors the legacy `CONCAT_WS('__', ...) LIKE %?%`
 * surface across student id/name, IC, status_desc, program level,
 * reference_no, warrant_no/amt and PTPTN deduction/balance.
 *
 * The legacy COMPONENT_JS marks the Action column as `d-none` (hidden)
 * because the View deep-link points to legacy menuID=1479 (Sponsor Form)
 * which is NOT migrated yet. We honour that — no Action column on the
 * frontend.
 *
 * Per project policy (legacy COMPONENT_JS has no `printout` field) we
 * surface PDF / CSV / Excel exports for the rendered page set.
 */
class SponsorPtptnController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'std_student_id',
        'std_student_name',
        'ic_passport',
        'std_program_level',
        'ssp_reference_no',
        'ssp_warrant_no',
        'ssp_warrant_amt',
        'deduction',
        'balance',
    ];

    public function options(): JsonResponse
    {
        $programLevel = LookupDetail::query()
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

        $studentStatus = LookupDetail::query()
            ->where('lma_code_name', 'like', '%STUDENT_STATUS%')
            ->orderBy('lma_code_name')
            ->orderBy('lde_value')
            ->get(['lde_value', 'lde_description'])
            ->map(fn ($r) => [
                'id' => (string) $r->lde_value,
                'label' => $r->lde_description
                    ? $r->lde_value.' - '.$r->lde_description
                    : (string) $r->lde_value,
            ])
            ->values();

        return $this->sendOk([
            'programLevel' => $programLevel,
            'studentStatus' => $studentStatus,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'std_student_id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'std_student_id';
        }

        $studentId = trim((string) $request->input('std_student_id', ''));
        $studentName = trim((string) $request->input('std_student_name', ''));
        $icPassport = trim((string) $request->input('ic_passport', ''));
        $programLevel = trim((string) $request->input('std_program_level', ''));
        $studentStatus = trim((string) $request->input('std_status', ''));
        $referenceNo = trim((string) $request->input('ssp_reference_no', ''));
        $warrantNo = trim((string) $request->input('ssp_warrant_no', ''));

        $base = $this->baseQuery();

        if ($q !== '') {
            $needle = $this->likeEscape($q);
            $base->whereRaw(
                "CONCAT_WS('__', "
                ."IFNULL(A.std_student_id, ''), "
                ."IFNULL(A.std_student_name, ''), "
                ."IFNULL(IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), A.std_passport), ''), "
                ."IFNULL(A.std_extended_field->>'$.std_status_desc', ''), "
                ."IFNULL(A.std_program_level, ''), "
                ."IFNULL(B.ssp_reference_no, ''), "
                ."IFNULL(B.ssp_warrant_no, ''), "
                .'IFNULL(B.ssp_warrant_amt, 0), '
                .'IFNULL(B.ssp_ptptn_deduction_amt, 0), '
                .'IFNULL(B.ssp_ptptn_balance_amt, 0)'
                .') LIKE ?',
                [$needle]
            );
        }
        if ($studentId !== '') {
            $base->whereRaw('A.std_student_id LIKE ?', [$this->likeEscape($studentId)]);
        }
        if ($studentName !== '') {
            $base->whereRaw('A.std_student_name LIKE ?', [$this->likeEscape($studentName)]);
        }
        if ($icPassport !== '') {
            $base->whereRaw(
                "IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), A.std_passport) LIKE ?",
                [$this->likeEscape($icPassport)]
            );
        }
        if ($programLevel !== '') {
            $base->whereRaw('A.std_program_level LIKE ?', [$this->likeEscape($programLevel)]);
        }
        if ($studentStatus !== '') {
            $base->whereRaw('A.std_status LIKE ?', [$this->likeEscape($studentStatus)]);
        }
        if ($referenceNo !== '') {
            $base->whereRaw('B.ssp_reference_no LIKE ?', [$this->likeEscape($referenceNo)]);
        }
        if ($warrantNo !== '') {
            $base->whereRaw('B.ssp_warrant_no LIKE ?', [$this->likeEscape($warrantNo)]);
        }

        $total = (clone $base)->count();

        $orderColumn = match ($sortBy) {
            'std_student_id' => 'A.std_student_id',
            'std_student_name' => 'A.std_student_name',
            'ic_passport' => DB::raw("IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), A.std_passport)"),
            'std_program_level' => 'A.std_program_level',
            'ssp_reference_no' => 'B.ssp_reference_no',
            'ssp_warrant_no' => 'B.ssp_warrant_no',
            'ssp_warrant_amt' => 'B.ssp_warrant_amt',
            'deduction' => 'B.ssp_ptptn_deduction_amt',
            'balance' => 'B.ssp_ptptn_balance_amt',
            default => 'A.std_student_id',
        };

        $rows = (clone $base)
            ->select([
                'A.std_student_id',
                'A.std_student_name',
                DB::raw("IFNULL(IF(A.std_ic_no='', NULL, A.std_ic_no), A.std_passport) AS ic_passport"),
                DB::raw("A.std_extended_field->>'\$.std_status_desc' AS stud_status"),
                'A.std_program_level',
                'B.ssp_id',
                'B.ssp_reference_no',
                'B.ssp_warrant_no',
                'B.ssp_warrant_amt',
                'B.ssp_ptptn_deduction_amt',
                'B.ssp_ptptn_balance_amt',
            ])
            ->orderBy($orderColumn, $sortDir)
            ->orderBy('A.std_student_id', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(fn ($r, int $i) => [
            'index' => (($page - 1) * $limit) + $i + 1,
            'stdStudentId' => (string) $r->std_student_id,
            'stdStudentName' => $r->std_student_name,
            'icPassport' => $r->ic_passport,
            'studStatus' => $r->stud_status,
            'stdProgramLevel' => $r->std_program_level,
            'sspReferenceNo' => $r->ssp_reference_no,
            'sspWarrantNo' => $r->ssp_warrant_no,
            'sspWarrantAmt' => $r->ssp_warrant_amt !== null ? (float) $r->ssp_warrant_amt : null,
            'deduction' => $r->ssp_ptptn_deduction_amt !== null ? (float) $r->ssp_ptptn_deduction_amt : null,
            'balance' => $r->ssp_ptptn_balance_amt !== null ? (float) $r->ssp_ptptn_balance_amt : null,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /**
     * Replicates the legacy `FROM/WHERE` from `V2_PTPTN_API`.
     */
    private function baseQuery(): Builder
    {
        return DB::connection('mysql_secondary')
            ->table('student as A')
            ->join('stud_sponsor as B', 'A.std_student_id', '=', 'B.std_student_id')
            ->join('sponsor as C', 'B.spn_sponsor_code', '=', 'C.spn_sponsor_code')
            ->where('C.spn_sponsor_type', '05');
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
