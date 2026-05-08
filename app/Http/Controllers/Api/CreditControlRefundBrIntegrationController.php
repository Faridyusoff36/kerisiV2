<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\TempRefundBillsMaster;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Kerisi Classic — Page 1871 / Menu 2289 (`SNA_API_CC_REFUNDSTAFF_BRINTEGRATION`).
 * Approved + non-approved REFUND_STAFF bill masters; grid search matches legacy `CONCAT_WS` haystack.
 */
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
            'bim_cust_invoice_date' => 'bim_cust_invoice_date',
        ];
        /** Legacy Kerisi Classic (Page 1871): non-approved default `createddate` desc; approved default `bim_bills_no` asc. */
        if (! $request->filled('sort_by')) {
            if ($approved) {
                $sortBy = 'bim_bills_no';
                $sortDir = 'asc';
            } else {
                $sortBy = 'createddate';
                $sortDir = 'desc';
            }
        }
        $orderCol = $sortMap[$sortBy] ?? ($approved ? 'bim_bills_no' : 'createddate');

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

        /** Same haystack shape as Classic `CONCAT_WS` for REFUND_STAFF BRI grids. */
        $concatHaystack = "CONCAT_WS('__',
            IFNULL(CAST(bim_bills_id AS CHAR), ''),
            IFNULL(bim_bills_no, ''),
            IFNULL(IF(bim_bills_type = 'I', 'INDIVIDU', 'BERKELOMPOK'), ''),
            IFNULL(bim_bills_desc, ''),
            IFNULL(CAST(bim_bill_amt AS CHAR), ''),
            IFNULL(bim_cust_invoice_no, ''),
            IFNULL(CAST(bim_cust_invoice_date AS CHAR), ''),
            IFNULL(bim_payto_id, ''),
            IFNULL(bim_payto_name, ''),
            IFNULL(bim_status, ''),
            IFNULL(CAST(createddate AS CHAR), '')
        )";

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw($concatHaystack.' LIKE ?', [$like]);
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
        $invNo = trim((string) ($sf['bim_cust_invoice_no'] ?? ''));
        if ($invNo !== '') {
            $base->where('bim_cust_invoice_no', 'like', '%'.$invNo.'%');
        }
        $invDt = trim((string) ($sf['bim_cust_invoice_date'] ?? ''));
        if ($invDt !== '') {
            try {
                $d = Carbon::createFromFormat('d/m/Y', $invDt)->format('Y-m-d');
                $base->whereRaw('DATE(bim_cust_invoice_date) = ?', [$d]);
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

            $custInvDate = $r->bim_cust_invoice_date ?? null;

            return [
                'index' => ($page - 1) * $limit + $i + 1,
                'bimBillsId' => $id,
                'bimBillsNo' => $r->bim_bills_no,
                'bimBillsType' => $r->bim_bills_type_label,
                'bimPaytoId' => $r->bim_payto_id,
                'bimPaytoName' => $r->bim_payto_name,
                'bimBillsDesc' => $r->bim_bills_desc,
                'bimBillAmt' => $r->bim_bill_amt !== null ? (float) $r->bim_bill_amt : null,
                'bimCustInvoiceNo' => $r->bim_cust_invoice_no ?? null,
                'bimCustInvoiceDate' => $custInvDate instanceof CarbonInterface ? $custInvDate->format('Y-m-d') : null,
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

    /** Classic uses `LIKE CONCAT('%', :q, '%')` (case-preserving collation / raw match). */
    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
