<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Credit Control / Post Dated Cheque (PAGEID 1436 / MENUID 1755).
 *
 * Source: FIMS `ZR_CREDITCONTROL_POSTDATEDCHEQUE_BL` on `cheque_registry`
 * (`DB_SECOND_DATABASE`). Listing excludes cleared rows (`cr_flag = 'Y'`) and
 * only shows cheques with `cr_cheque_date` still in the future vs server time.
 */
class PostDatedChequeController extends Controller
{
    use ApiResponse;

    /** Maps `sort_by` query values to real column names for ORDER BY. */
    private const SORTABLE = [
        'cr_cust_id',
        'cr_drawer_name',
        'cr_issuer_bank',
        'cr_branch_name',
        'cr_cheque_no',
        'cr_cheque_date',
        'cr_cheque_amt',
        'cr_received_date',
        'cr_invoice_no',
        'cr_validity',
        'cr_remark',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'cr_cust_id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'cr_cust_id';
        }

        $base = DB::connection('mysql_secondary')
            ->table('cheque_registry')
            ->whereRaw('cr_cheque_date > NOW()')
            ->where(function ($w) {
                $w->where('cr_flag', '<>', 'Y')
                    ->orWhereNull('cr_flag');
            });

        if ($q !== '') {
            $needle = mb_strtoupper($q, 'UTF-8');
            $pattern = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
            $base->whereRaw(
                "UPPER(CONCAT_WS('__',
                    IFNULL(cr_cust_id,''),
                    IFNULL(cr_drawer_name,''),
                    IFNULL(cr_issuer_bank,''),
                    IFNULL(cr_branch_name,''),
                    IFNULL(cr_cheque_no,''),
                    IFNULL(cr_cheque_date,''),
                    IFNULL(cr_cheque_amt,''),
                    IFNULL(cr_invoice_no,''),
                    IFNULL(cr_validity,''),
                    IFNULL(cr_remark,''),
                    IFNULL(cr_flag,''),
                    IFNULL(cr_received_date,''),
                    IFNULL(cr_is_receipted,''),
                    IFNULL(cr_extended_field,''),
                    IFNULL(createddate,''),
                    IFNULL(createdby,''),
                    IFNULL(updateddate,''),
                    IFNULL(updatedby,''),
                    IFNULL(cr_release_date,'')
                )) LIKE ?",
                [$pattern]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'cr_cust_id',
                'cr_drawer_name',
                'cr_issuer_bank',
                'cr_branch_name',
                'cr_cheque_no',
                DB::raw("DATE_FORMAT(cr_cheque_date, '%d/%m/%Y') as cr_cheque_date"),
                'cr_cheque_amt',
                DB::raw("DATE_FORMAT(cr_received_date, '%d/%m/%Y') as cr_received_date"),
                'cr_invoice_no',
                'cr_validity',
                'cr_remark',
            ])
            ->orderBy($sortBy, $sortDir)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $data = $rows->values()->map(function (object $r, int $i) use ($page, $limit): array {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'cr_cust_id' => $r->cr_cust_id,
                'cr_drawer_name' => $r->cr_drawer_name,
                'cr_issuer_bank' => $r->cr_issuer_bank,
                'cr_branch_name' => $r->cr_branch_name,
                'cr_cheque_no' => $r->cr_cheque_no,
                'cr_cheque_date' => $r->cr_cheque_date,
                'cr_cheque_amt' => $r->cr_cheque_amt !== null ? (float) $r->cr_cheque_amt : null,
                'cr_received_date' => $r->cr_received_date,
                'cr_invoice_no' => $r->cr_invoice_no,
                'cr_validity' => $r->cr_validity,
                'cr_remark' => $r->cr_remark,
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }
}
