<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Emergency Fund — release queue (post accrual posting, pending clearance release).
 * PAGEID 1676 / MENUID 2028 and PAGEID 2182 / MENUID 1983 (tab) — identical legacy BL.
 * Legacy: `NAD_API_CC_EF_RELEASE` with `$_GET['dt_emergencyFundRelease']=1`.
 */
class EmergencyFundReleaseQueueListingController extends Controller
{
    use ApiResponse;

    private const SORT_MAP = [
        'emf_emergency_fund_no' => 'ef.emf_emergency_fund_no',
        'emf_paid_date' => 'ef.emf_paid_date',
        'emf_taken_amt' => 'ef.emf_taken_amt',
        'emf_status' => 'ef.emf_status',
        'emf_clearance_date' => 'ef.emf_clearance_date',
        'pmt_posting_no' => 'ef.pmt_posting_no',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $qRaw = trim((string) $request->input('q', ''));

        $sortKey = (string) $request->input('sort_by', 'emf_emergency_fund_no');
        $sortCol = self::SORT_MAP[$sortKey] ?? self::SORT_MAP['emf_emergency_fund_no'];
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'desc' ? 'desc' : 'asc';

        $needle = mb_strtoupper($qRaw, 'UTF-8');
        $pattern = $needle === '' ? null : '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';

        $base = DB::connection('mysql_secondary')
            ->table('emergency_fund AS ef')
            ->join('staff AS st', 'ef.emf_apply_by', '=', 'st.stf_staff_id')
            ->whereIn('ef.emf_status', ['ENDORSE', 'RESUBMIT'])
            ->whereNotNull('ef.pmt_posting_no')
            ->whereNull('ef.pmt_posting_no_clearance')
            ->whereNotNull('ef.emf_clearance_date');

        if ($pattern !== null) {
            $base->whereRaw(
                "UPPER(CONCAT_WS('__',
                    IFNULL(ef.emf_id,''),
                    IFNULL(ef.emf_emergency_fund_no,''),
                    IFNULL(DATE_FORMAT(ef.emf_apply_date, '%d/%m/%Y'),''),
                    IFNULL(DATE_FORMAT(ef.emf_paid_date, '%d/%m/%Y'),''),
                    IFNULL(CONCAT_WS(' - ', ef.emf_apply_by, st.stf_staff_name),''),
                    IFNULL(CASE ef.emf_category WHEN 'A' THEN 'A - Student' ELSE 'B - Staff' END,''),
                    IFNULL(ef.emf_remark_by_query,''),
                    IFNULL(ef.emf_taken_amt,''),
                    IFNULL(ef.emf_status,''),
                    IFNULL(DATE_FORMAT(ef.emf_clearance_date, '%d/%m/%Y'),''),
                    IFNULL(ef.pmt_posting_no,'')
                )) LIKE ?",
                [$pattern]
            );
        }

        /** @phpstan-ignore-next-line */
        $total = (clone $base)->count();

        $listQuery = (clone $base)
            ->select([
                'ef.emf_id',
                'ef.emf_emergency_fund_no',
                DB::raw("DATE_FORMAT(ef.emf_paid_date, '%d/%m/%Y') AS paid_date_disp"),
                DB::raw("CONCAT_WS(' - ', ef.emf_apply_by, st.stf_staff_name) AS apply_by_disp"),
                DB::raw("CASE ef.emf_category WHEN 'A' THEN 'A - Student' ELSE 'B - Staff' END AS category_disp"),
                'ef.emf_category',
                'ef.emf_remark_by_query',
                'ef.emf_taken_amt',
                'ef.emf_status',
                DB::raw("DATE_FORMAT(ef.emf_clearance_date, '%d/%m/%Y') AS clearance_date_disp"),
                'ef.pmt_posting_no',
            ]);

        $listQuery->orderBy(DB::raw($sortCol), $sortDir);

        /** @phpstan-ignore-next-line */
        $rows = $listQuery
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($rows as $index => $row) {
            $r = json_decode(json_encode($row), true);
            $r['list_index'] = ($page - 1) * $limit + $index + 1;
            $data[] = $r;
        }

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / $limit ?: 1),
        ]);
    }
}
