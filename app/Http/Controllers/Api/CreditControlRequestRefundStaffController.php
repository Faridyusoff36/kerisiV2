<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\TempRefundApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Credit Control / Refund (Staff) / Request Refund.
 *
 * Legacy: {@see SNA_API_CREDITCONTROL_REQUESTREFUNDSTAFF} — `dt_listapply` datatable
 * (APPLY + staff pay-to `B`; no `refund_prefix_setup` join; does not filter `tra_process`).
 */
class CreditControlRequestRefundStaffController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'application_no' => 'tra.tra_application_no',
        'amount' => 'tra.tra_amt_refund',
        'staff_id' => 'tra.vcs_vendor_code',
        'staff_name' => 'tra.tra_vendor_name',
        'reference' => 'tra.tra_ref_no',
        'fund_type' => 'tra.fty_fund_type',
        'activity_code' => 'tra.at_activity_code',
        'ptj' => 'tra.oun_code',
        'cost_center' => 'tra.ccr_costcentre',
        'account_code' => 'tra.acm_acct_code',
        'status' => 'tra.tra_status',
        'request_by' => 'tra.createdby',
        'request_date' => 'tra.createddate',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'request_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderCol = self::SORTABLE[$sortBy] ?? 'tra.createddate';

        $base = $this->scopedBaseQuery();

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $conn = 'mysql_secondary';
            $createdByExpr = Schema::connection($conn)->hasColumn('temp_refund_application', 'createdby')
                ? "IFNULL(tra.createdby,'')" : "''";

            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(tra.tra_id,''),
                    IFNULL(tra.tra_application_no,''),
                    IFNULL(tra.vcs_vendor_code,''),
                    IFNULL(tra.tra_vendor_name,''),
                    IFNULL(tra.tra_ref_no,''),
                    IFNULL(tra.tra_ref_no_note,''),
                    IFNULL(tra.fty_fund_type,''),
                    IFNULL(tra.at_activity_code,''),
                    IFNULL(tra.oun_code,''),
                    IFNULL(tra.ccr_costcentre,''),
                    IFNULL(tra.acm_acct_code,''),
                    IFNULL(tra.tra_amt_refund,''),
                    IFNULL(tra.tra_status,''),
                    IFNULL(DATE_FORMAT(tra.createddate, '%d/%m/%Y'),''),
                    {$createdByExpr}
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();

        $refExpr = "TRIM(CONCAT(IFNULL(tra.tra_ref_no,''), IF(tra.tra_ref_no IS NOT NULL AND tra.tra_ref_no_note IS NOT NULL AND tra.tra_ref_no_note != '', ' - ', ''), IFNULL(tra.tra_ref_no_note,'')))";

        $select = [
            'tra.tra_id',
            'tra.tra_application_no',
            'tra.vcs_vendor_code',
            'tra.tra_vendor_name',
            'tra.fty_fund_type',
            'tra.at_activity_code',
            'tra.oun_code',
            'tra.ccr_costcentre',
            'tra.acm_acct_code',
            'tra.tra_amt_refund',
            'tra.tra_status',
            'tra.createddate',
            DB::raw("{$refExpr} AS reference_concat"),
        ];
        if (Schema::connection('mysql_secondary')->hasColumn('temp_refund_application', 'createdby')) {
            $select[] = 'tra.createdby';
        }

        $rows = (clone $base)
            ->select($select)
            ->orderBy($orderCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(function ($r, int $i) use ($page, $limit) {
            $ref = $r->reference_concat ?? '';
            $reference = $ref !== '' ? $ref : null;

            return [
                'index' => ($page - 1) * $limit + $i + 1,
                'traId' => (int) $r->tra_id,
                'applicationNo' => $r->tra_application_no,
                'amountRm' => $r->tra_amt_refund !== null ? (float) $r->tra_amt_refund : null,
                'staffId' => $r->vcs_vendor_code,
                'staffName' => $r->tra_vendor_name,
                'reference' => $reference,
                'fundType' => $r->fty_fund_type,
                'activityCode' => $r->at_activity_code,
                'ptj' => $r->oun_code,
                'costCenter' => $r->ccr_costcentre,
                'accountCode' => $r->acm_acct_code,
                'status' => $r->tra_status,
                'requestBy' => isset($r->createdby) ? $r->createdby : null,
                'requestDate' => $r->createddate
                    ? $r->createddate->format('d/m/Y')
                    : null,
                'actionUrl' => '/admin/kerisi/m/2286?q='.rawurlencode(
                    trim((string) ($r->tra_application_no ?? '')) !== ''
                        ? (string) $r->tra_application_no
                        : (string) $r->tra_id
                ),
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    private function scopedBaseQuery(): Builder
    {
        return TempRefundApplication::query()
            ->from('temp_refund_application AS tra')
            ->where('tra.tra_status', 'APPLY')
            ->where('tra.tra_payto_type', 'B');
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
