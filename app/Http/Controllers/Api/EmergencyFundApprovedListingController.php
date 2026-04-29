<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Credit Control / Advance / Emergency Fund / Report / Listing — PAGEID 1683 / MENUID 2038.
 *
 * Legacy: `NAD_API_CC_EF_LISTING` with `$_GET['Listing']=1` on `emergency_fund`,
 * `staff`, and `ccontroller_reminder` (`DB_SECOND_DATABASE`).
 */
class EmergencyFundApprovedListingController extends Controller
{
    use ApiResponse;

    private const SORT_MAP = [
        'emf_emergency_fund_no' => 'ef.emf_emergency_fund_no',
        'emf_apply_date' => 'ef.emf_apply_date',
        'emf_taken_amt' => 'ef.emf_taken_amt',
        'emf_status' => 'ef.emf_status',
        'pmt_posting_no' => 'ef.pmt_posting_no',
        'pmt_posting_no_clearance' => 'ef.pmt_posting_no_clearance',
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
            ->join('ccontroller_reminder AS cr', 'ef.emf_apply_by', '=', 'cr.crm_debtor_id')
            ->whereIn('ef.emf_status', ['APPROVED', 'PAID']);

        if ($pattern !== null) {
            $base->whereRaw(
                "UPPER(CONCAT_WS('__',
                    IFNULL(ef.emf_id,''),
                    IFNULL(ef.emf_emergency_fund_no,''),
                    IFNULL(DATE_FORMAT(ef.emf_apply_date, '%d/%m/%Y'),''),
                    IFNULL(CONCAT_WS(' - ', ef.emf_apply_by, st.stf_staff_name),''),
                    IFNULL(CASE ef.emf_category WHEN 'A' THEN 'A - Student' ELSE 'B - Staff' END,''),
                    IFNULL(ef.emf_correspondence_add,''),
                    IFNULL(CONCAT_WS(' - ', ef.emf_applyfor_id, ef.emf_applyfor_name),''),
                    IFNULL(ef.emf_taken_amt,''),
                    IFNULL(ef.emf_status,''),
                    IFNULL(DATE_FORMAT(ef.emf_clearance_date, '%d/%m/%Y'),''),
                    IFNULL(ef.pmt_posting_no,''),
                    IFNULL(ef.pmt_posting_no_clearance,''),
                    IFNULL(cr.crm_reminder_bil,'')
                )) LIKE ?",
                [$pattern]
            );
        }

        $countQuery = clone $base;

        // Select mirrors legacy NAD_API_CC_EF_LISTING column semantics.
        $listQuery = (clone $base)
            ->select([
                'ef.emf_id',
                'ef.emf_emergency_fund_no',
                DB::raw("DATE_FORMAT(ef.emf_apply_date, '%d/%m/%Y') AS apply_date_disp"),
                DB::raw("CONCAT_WS(' - ', ef.emf_apply_by, st.stf_staff_name) AS apply_by_disp"),
                DB::raw("CASE ef.emf_category WHEN 'A' THEN 'A - Student' ELSE 'B - Staff' END AS category_disp"),
                'ef.emf_correspondence_add',
                DB::raw("CONCAT_WS(' - ', ef.emf_applyfor_id, ef.emf_applyfor_name) AS apply_for_disp"),
                'ef.emf_taken_amt',
                'ef.emf_status',
                DB::raw("DATE_FORMAT(ef.emf_clearance_date, '%d/%m/%Y') AS clearance_date_disp"),
                'ef.pmt_posting_no',
                'ef.pmt_posting_no_clearance',
                'cr.crm_reminder_bil',
            ]);

        $listQuery->orderBy(DB::raw($sortCol), $sortDir);

        /** @phpstan-ignore-next-line */
        $total = $countQuery->count();

        $rows = $listQuery
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $data = [];
        foreach ($rows as $index => $row) {
            /** @var object $row */
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
