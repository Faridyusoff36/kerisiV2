<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\TempRefundBillsMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CreditControlRefundBrIntegrationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $approved = filter_var($request->input('approved', false), FILTER_VALIDATE_BOOLEAN);
        $sortBy = (string) $request->input('sort_by', 'bim_bills_no');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortMap = [
            'bim_bills_no' => 'bim_bills_no',
            'createddate' => 'createddate',
            'bim_bill_amt' => 'bim_bill_amt',
            'bim_status' => 'bim_status',
        ];
        $orderCol = $sortMap[$sortBy] ?? 'bim_bills_no';

        $sf = $request->input('smart_filter', []) ?? [];
        if (! is_array($sf)) {
            $sf = [];
        }

        $base = TempRefundBillsMaster::query()->where('bim_system_id', 'REFUND_STAFF');
        if ($approved) {
            $base->where('bim_status', 'APPROVE');
        } else {
            $base->where('bim_status', '!=', 'APPROVE');
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(bim_bills_id,''),
                    IFNULL(bim_bills_no,''),
                    IFNULL(bim_bills_type,''),
                    IFNULL(bim_bills_desc,''),
                    IFNULL(bim_bill_amt,''),
                    IFNULL(bim_cust_invoice_no,''),
                    IFNULL(bim_payto_id,''),
                    IFNULL(bim_payto_name,''),
                    IFNULL(bim_status,''),
                    IFNULL(DATE_FORMAT(createddate, '%d/%m/%Y'),'')
                )) LIKE ?",
                [$like]
            );
        }

        $bn = trim((string) ($sf['bim_bills_no'] ?? ''));
        if ($bn !== '') {
            $base->where('bim_bills_no', 'like', '%'.$bn.'%');
        }
        foreach (['bim_bills_type' => 'bim_bills_type', 'bim_payto_id' => 'bim_payto_id', 'bim_bills_desc' => 'bim_bills_desc', 'bim_status' => 'bim_status'] as $k => $col) {
            $v = trim((string) ($sf[$k] ?? ''));
            if ($v !== '') {
                $base->where($col, $v);
            }
        }
        $pn = trim((string) ($sf['bim_payto_name'] ?? ''));
        if ($pn !== '') {
            $base->where('bim_payto_name', 'like', '%'.$pn.'%');
        }
        if (isset($sf['bim_bill_amt']) && $sf['bim_bill_amt'] !== '' && $sf['bim_bill_amt'] !== null) {
            $base->where('bim_bill_amt', $sf['bim_bill_amt']);
        }
        $created = trim((string) ($sf['createddate'] ?? ''));
        if ($created !== '') {
            try {
                $d = Carbon::createFromFormat('d/m/Y', $created)->format('Y-m-d');
                $base->whereRaw('DATE(createddate) = ?', [$d]);
            } catch (\Throwable) {
            }
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'bim_bills_id',
                'bim_bills_no',
                DB::raw("IF(bim_bills_type = 'I', 'INDIVIDU', 'BERKELOMPOK') AS bim_bills_type_label"),
                'bim_bills_desc',
                'bim_bill_amt',
                'bim_cust_invoice_no',
                'bim_cust_invoice_date',
                'bim_payto_id',
                'bim_payto_name',
                'bim_status',
                'createddate',
            ])
            ->orderBy($orderCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(function ($r, int $i) use ($page, $limit) {
            $id = (int) $r->bim_bills_id;

            return [
                'index' => ($page - 1) * $limit + $i + 1,
                'bimBillsId' => $id,
                'bimBillsNo' => $r->bim_bills_no,
                'bimBillsType' => $r->bim_bills_type_label,
                'bimPaytoId' => $r->bim_payto_id,
                'bimPaytoName' => $r->bim_payto_name,
                'bimBillsDesc' => $r->bim_bills_desc,
                'bimBillAmt' => $r->bim_bill_amt !== null ? (float) $r->bim_bill_amt : null,
                'bimStatus' => $r->bim_status,
                'createddate' => $r->createddate,
                'viewUrl' => '/admin/kerisi/m/2289?bimBillsId='.$id.'&mode=view',
                'editUrl' => '/admin/kerisi/m/2289?bimBillsId='.$id.'&mode=edit',
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'approved' => $approved,
        ]);
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
