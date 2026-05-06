<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Detail listing refund process (PAGEID 1872 / MENUID 2290).
 * Legacy SNA_API_CC_REFUNDSTAFF_DETAILISTINGPROCESS dt_list.
 */
class CreditControlRefundStaffDetailController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $base = DB::connection('mysql_secondary')
            ->table('temp_refund_bills_master AS bim')
            ->join('temp_refund_bills_details AS bid', 'bim.bim_bills_id', '=', 'bid.bim_bills_id')
            ->leftJoin('voucher_details AS vd', function ($j) {
                $j->on('vd.bim_bills_no', '=', 'bim.bim_bills_no')
                    ->on('bid.bid_trans_type', '=', 'vd.vde_trans_type')
                    ->whereColumn('vd.vde_payto_id', 'bid.bid_payto_id');
            })
            ->leftJoin('voucher_master AS vm', 'vd.vma_voucher_id', '=', 'vm.vma_voucher_id')
            ->leftJoin('payment_record AS pr', function ($j) {
                $j->on('pr.pre_voucher_no', '=', 'vm.vma_voucher_no')
                    ->whereRaw('IFNULL(vd.vde_factoring_id, vd.vde_payto_id) = pr.pre_payto_id');
            })
            ->where('bid.bid_trans_type', 'DT')
            ->where('bim.bim_system_id', 'REFUND_STAFF');

        $this->applyLegacyTopFilters($base, $request);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(bim.bim_bills_id,''),
                    IFNULL(bim.bim_bills_no,''),
                    IFNULL(bim.bim_bills_type,''),
                    IFNULL(bim.bim_bills_desc,''),
                    IFNULL(bim.bim_bill_amt,''),
                    IFNULL(bim.bim_cust_invoice_no,''),
                    IFNULL(bim.bim_payto_id,''),
                    IFNULL(bim.bim_payto_name,''),
                    IFNULL(bim.bim_status,''),
                    IFNULL(DATE_FORMAT(bim.createddate, '%d/%m/%Y'),''),
                    IFNULL(bid.bid_payto_id,''),
                    IFNULL(bid.bid_payto_name,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();

        $efDate = 'pr.updateddate';

        $rows = (clone $base)
            ->select([
                'bim.bim_bills_id',
                'bim.bim_bills_no',
                DB::raw("IF(bim.bim_bills_type = 'I', 'INDIVIDU', 'BERKELOMPOK') AS bim_bills_type_label"),
                'bim.bim_bills_desc',
                'bim.bim_bill_amt',
                'bim.bim_cust_invoice_no',
                'bim.bim_cust_invoice_date',
                'bim.bim_payto_id',
                'bim.bim_payto_name',
                'bim.bim_status',
                'bim.createddate',
                'bid.bid_payto_id',
                'bid.bid_payto_name',
                DB::raw("CONCAT(IFNULL(bid.vsa_bank_accno,''), '\n', SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(bid.bid_extended_field, '$.debtorbankName')), '-', -1)) AS vsa_bank_accno"),
                'bid.fty_fund_type',
                'bid.at_activity_code',
                'bid.oun_code',
                'bid.ccr_costcentre',
                'bid.acm_acct_code',
                'bid.bid_amt',
                DB::raw("CONCAT(IFNULL(bid.bid_factoring_id,''), ' - ', JSON_UNQUOTE(JSON_EXTRACT(bid.bid_extended_field, '$.trirdPartyName'))) AS third_party_info"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(bid.bid_extended_field, '$.thirdPartyBankName')) AS third_party_bank_name"),
                'bid.bid_fact_bank_acctno AS tra_3rd_bank_acc_no',
                DB::raw("CASE WHEN vm.vma_voucher_no LIKE '%VCR%' THEN vm.vma_voucher_no WHEN vm.vma_voucher_no LIKE '%temp%' THEN bim.bim_voucher_no END AS novoucher"),
                'vd.updateddate AS voucherdate',
                'vd.vde_paymode AS paymode',
                DB::raw($efDate.' AS eftdate'),
                'pr.pre_payment_no AS eftno',
            ])
            ->orderBy('bim.bim_bills_no')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(fn ($r, int $i) => array_merge(
            ['index' => ($page - 1) * $limit + $i + 1],
            json_decode(json_encode($r), true)
        ));

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    private function applyLegacyTopFilters($base, Request $request): void
    {
        $bn = trim((string) $request->input('bim_bills_no', ''));
        if ($bn !== '') {
            $base->where('bim.bim_bills_no', 'like', '%'.$bn.'%');
        }
        $pid = trim((string) $request->input('bim_payto_id', ''));
        if ($pid !== '') {
            $base->where('bid.bid_payto_id', $pid);
        }
        $pn = trim((string) $request->input('bim_payto_name', ''));
        if ($pn !== '') {
            $base->where('bim.bim_payto_name', 'like', '%'.$pn.'%');
        }
        $st = trim((string) $request->input('bim_status', ''));
        if ($st !== '') {
            $base->where('bim.bim_status', $st);
        }
        $amt = trim((string) $request->input('bid_amt', ''));
        if ($amt !== '') {
            $base->where('bid.bid_amt', 'like', '%'.$amt.'%');
        }
        $cd = trim((string) $request->input('createddate', ''));
        if ($cd !== '') {
            try {
                $base->whereRaw(
                    "DATE_FORMAT(bim.createddate, '%d/%m/%Y') = ?",
                    [Carbon::createFromFormat('d/m/Y', $cd)->format('d/m/Y')]
                );
            } catch (\Throwable) {
            }
        }
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
