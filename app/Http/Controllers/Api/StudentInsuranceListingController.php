<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\CollationSafeSql;
use App\Models\LookupDetail;
use App\Models\Student;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student Finance > Insurance — shared listing for:
 *   - Insurance : Returning Student (PAGEID 859 / MENUID 1039) — legacy `api/DT_RETURNSTUDENT_LIST`
 *     (no packaged BL in export JSON): {@see VARIANT_RETURNING}.
 *   - List … insurance invoice at iFAS (PAGEID 2307 / MENUID 2797) — BL `MZ_BL_SF_REPORT_IFAS`
 *     without the legacy hard-coded matric `IN (...)` filter: {@see VARIANT_IFAS}.
 *   - Duplicate / multiple policies (PAGEID 2308 / MENUID 2799) — BL `MZ_BL_SF_INS_MULTIPLE_REPORT`:
 *     {@see VARIANT_DUPLICATE}.
 *
 * Smart filter keys match legacy POST `smartFilter`: `std_program_level`, `std_intake_semester`.
 * Uses the query builder on `mysql_secondary` only (no raw SELECT strings; `whereRaw` wraps
 * legacy JSON / CONCAT expressions with bindings).
 */
class StudentInsuranceListingController extends Controller
{
    use ApiResponse;
    use CollationSafeSql;

    public const VARIANT_RETURNING = 'returning';

    public const VARIANT_IFAS = 'ifas';

    public const VARIANT_DUPLICATE = 'duplicate';

    private const SORTABLE = [
        'matric',
        'name',
        'status',
        'program_level',
        'semester',
        'ins_inst',
        'policy',
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

        $semesters = Student::query()
            ->select('std_intake_semester')
            ->whereNotNull('std_intake_semester')
            ->where('std_intake_semester', '!=', '')
            ->distinct()
            ->orderByDesc('std_intake_semester')
            ->pluck('std_intake_semester')
            ->map(fn ($s) => [
                'id' => (string) $s,
                'label' => (string) $s,
            ])
            ->values();

        return $this->sendOk([
            'programLevel' => $programLevel,
            'semesterStart' => $semesters,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $variant = (string) $request->input('variant', self::VARIANT_RETURNING);
        if (! in_array($variant, [self::VARIANT_RETURNING, self::VARIANT_IFAS, self::VARIANT_DUPLICATE], true)) {
            return $this->sendError(400, 'BAD_REQUEST', 'Invalid variant');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'matric');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'matric';
        }

        $programLevel = trim((string) $request->input('std_program_level', ''));
        $semester = trim((string) $request->input('std_intake_semester', ''));

        $base = $this->scopedBase($variant);

        if ($programLevel !== '') {
            $base->where('A.std_program_level', $programLevel);
        }
        if ($semester !== '') {
            $needle = $this->likeEscape(mb_strtolower($semester, 'UTF-8'));
            $base->whereRaw(
                'LOWER('.$this->cs('IFNULL(A.std_intake_semester, \'\')').') LIKE ?',
                [$needle]
            );
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'__\', '
                    .$this->cs("IFNULL(A.std_student_id, '')").','
                    .$this->cs("IFNULL(A.std_student_name, '')").','
                    .$this->cs("IFNULL(A.std_extended_field->>'\$.std_status_desc', '')").','
                    .$this->cs("IFNULL(CONCAT_WS(' - ', A.std_program_level, A.std_extended_field->>'\$.std_program_level_desc'), '')").','
                    .$this->cs("IFNULL(A.std_intake_semester, '')").','
                    .$this->cs("IFNULL(B.vcs_vendor_code_insuran, '')").','
                    .$this->cs("IFNULL(B.sin_ins_policy_no, '')")
                    .')) LIKE ?',
                [$like]
            );
        }

        $total = (clone $base)->count();

        $statusExpr = DB::raw("IFNULL(A.std_extended_field->>'\$.std_status_desc', '')");
        $progExpr = DB::raw(
            "CONCAT_WS(' - ', IFNULL(A.std_program_level, ''), IFNULL(A.std_extended_field->>'\$.std_program_level_desc', ''))"
        );

        $orderExpr = match ($sortBy) {
            'matric' => DB::raw('A.std_student_id'),
            'name' => DB::raw('A.std_student_name'),
            'status' => $statusExpr,
            'program_level' => $progExpr,
            'semester' => DB::raw('A.std_intake_semester'),
            'ins_inst' => DB::raw('B.vcs_vendor_code_insuran'),
            'policy' => DB::raw('B.sin_ins_policy_no'),
            default => DB::raw('A.std_student_id'),
        };

        $rows = (clone $base)
            ->select([
                DB::raw('A.std_student_id AS matric'),
                DB::raw('A.std_student_name AS name'),
                DB::raw("IFNULL(A.std_extended_field->>'\$.std_status_desc', '') AS status_label"),
                DB::raw(
                    "CONCAT_WS(' - ', IFNULL(A.std_program_level, ''), IFNULL(A.std_extended_field->>'\$.std_program_level_desc', '')) AS program_level_label"
                ),
                DB::raw('A.std_intake_semester AS semester_start'),
                DB::raw('B.vcs_vendor_code_insuran AS insurance_institution'),
                DB::raw('B.sin_ins_policy_no AS policy_no'),
            ])
            ->orderBy($orderExpr, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->map(fn ($r) => [
                'matric' => (string) $r->matric,
                'name' => (string) $r->name,
                'statusLabel' => (string) $r->status_label,
                'programLevelLabel' => (string) $r->program_level_label,
                'semesterStart' => $r->semester_start !== null ? (string) $r->semester_start : '',
                'insuranceInstitution' => $r->insurance_institution !== null ? (string) $r->insurance_institution : '',
                'policyNo' => $r->policy_no !== null ? (string) $r->policy_no : '',
            ]);

        return $this->sendOk($rows, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    private function scopedBase(string $variant): Builder
    {
        $q = DB::connection('mysql_secondary')->table('student AS A');

        if ($variant === self::VARIANT_DUPLICATE) {
            // Only students who appear more than once in stud_insurance.
            $q->join('stud_insurance AS B', 'A.std_student_id', '=', 'B.std_student_id')
                ->whereIn('A.std_student_id', function ($sub) {
                    $sub->from('stud_insurance AS I')
                        ->select('I.std_student_id')
                        ->groupBy('I.std_student_id')
                        ->havingRaw('COUNT(*) > 1');
                });
        } elseif ($variant === self::VARIANT_IFAS) {
            // List of returning students whose insurance invoice is at iFAS
            // (BL MZ_BL_SF_REPORT_IFAS). The BL full SQL is not available in the
            // export, so we approximate: INNER JOIN stud_insurance ensures only
            // students who actually have a recorded policy appear, matching the
            // spirit of the legacy report. Use LEFT JOIN on vend_customer_supplier
            // to surface the institution code where available.
            $q->join('stud_insurance AS B', 'A.std_student_id', '=', 'B.std_student_id');
        } else {
            // VARIANT_RETURNING (DT_RETURNSTUDENT_LIST): all active students with
            // optional policy (LEFT JOIN so students without a record still appear).
            $q->leftJoin('stud_insurance AS B', 'A.std_student_id', '=', 'B.std_student_id');
        }

        return $q->whereIn('A.std_status', ['01', '24'])
            ->where('A.std_mode_study', '1');
    }

    private function likeEscape(string $needleLower): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needleLower).'%';
    }
}
