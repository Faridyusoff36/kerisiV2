<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Detail listing refund process lines (MENUID **2290** / Kerisi Classic ~PAGE **1872** legacy slot).
 *
 * Mirrors legacy `{@see SNA_API_CC_REFUNDSTAFF_DETAILISTINGPROCESS}`: same joins plus full
 * {@code CONCAT_WS} haystack search as Kerisi Classic.
 */
class CreditControlRefundStaffDetailController extends Controller
{
    use ApiResponse;

    /** Query params: {@code q}, pagination, {@code sort_by}, {@code sort_dir}, nested {@code smart_filter[…]} / flat duplicates. */
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortByUser = strtolower((string) $request->input('sort_by', 'bim_bills_no'));
        $sortDirUser = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortMap = [
            'bim_bills_no' => 'bim.bim_bills_no',
            'createddate' => 'bim.createddate',
            'bid_amt' => 'bid.bid_amt',
        ];
        $orderSql = ($sortMap[$sortByUser] ?? 'bim.bim_bills_no').' '.$sortDirUser;

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

        if ($q !== '') {
            $needle = $this->sqlLikeNeedle(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw($this->legacyHaystackExpression().' LIKE ?', [$needle]);
        }

        $this->applySmartFilters($base, $this->mergeSmartFilter($request));

        $total = (clone $base)->count();

        $voucherCase = "CASE WHEN vm.vma_voucher_no LIKE '%VCR%' THEN vm.vma_voucher_no WHEN vm.vma_voucher_no LIKE '%temp%' THEN bim.bim_voucher_no END";

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
                'bid.bid_payto_type',
                'bid.bid_payto_id',
                'bid.bid_payto_name',
                'bid.fty_fund_type',
                'bid.at_activity_code',
                'bid.oun_code',
                'bid.ccr_costcentre',
                'bid.acm_acct_code',
                'bid.bid_amt',
                'bid.vsa_vendor_bank',
                DB::raw("CONCAT(IFNULL(bid.vsa_bank_accno,''), '\n', SUBSTRING_INDEX(JSON_UNQUOTE(JSON_EXTRACT(bid.bid_extended_field, '$.debtorbankName')), '-', -1)) AS vsa_bank_accno"),
                DB::raw("CONCAT(IFNULL(bid.bid_factoring_id,''), ' - ', JSON_UNQUOTE(JSON_EXTRACT(bid.bid_extended_field, '$.trirdPartyName'))) AS third_party_info"),
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(bid.bid_extended_field, '$.thirdPartyBankName')) AS third_party_bank_name"),
                'bid.bid_fact_bank_acctno AS tra_3rd_bank_acc_no',
                DB::raw("{$voucherCase} AS novoucher"),
                'vd.updateddate AS voucherdate',
                'vd.vde_paymode AS paymode',
                'vd.vde_payment_no AS eftno',
                'pr.updateddate AS eftdate',
                'bid.tra_application_no',
            ])
            ->orderByRaw($orderSql)
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

    /**
     * Full Kerisi haystack identical in shape to Classic {@code dt_list}.
     *
     * @internal
     */
    private function legacyHaystackExpression(): string
    {
        $vc = 'CASE WHEN vm.vma_voucher_no LIKE \'%VCR%\' THEN vm.vma_voucher_no WHEN vm.vma_voucher_no LIKE \'%temp%\' THEN bim.bim_voucher_no END';

        return <<<SQL
LOWER(CONCAT_WS('__',
    IFNULL(CAST(bim.bim_bills_id AS CHAR), ''),
    IFNULL(bim.bim_bills_no, ''),
    IFNULL(bim.bim_bills_type, ''),
    IFNULL(bim.bim_bills_desc, ''),
    IFNULL(CAST(bim.bim_bill_amt AS CHAR), ''),
    IFNULL(bim.bim_cust_invoice_no, ''),
    IFNULL(CAST(bim.bim_cust_invoice_date AS CHAR), ''),
    IFNULL(bim.bim_payto_id, ''),
    IFNULL(bim.bim_payto_name, ''),
    IFNULL(bim.bim_status, ''),
    IFNULL(DATE_FORMAT(bim.createddate, '%d/%m/%Y'), ''),
    IFNULL(bid.bid_payto_type, ''),
    IFNULL(bid.bid_payto_id, ''),
    IFNULL(bid.bid_payto_name, ''),
    IFNULL(bid.fty_fund_type, ''),
    IFNULL(bid.at_activity_code, ''),
    IFNULL(bid.oun_code, ''),
    IFNULL(bid.ccr_costcentre, ''),
    IFNULL(bid.acm_acct_code, ''),
    IFNULL(CAST(bid.bid_amt AS CHAR), ''),
    IFNULL(bid.vsa_vendor_bank, ''),
    IFNULL(bid.bid_trans_type, ''),
    IFNULL({$vc}, ''),
    IFNULL(CAST(vd.updateddate AS CHAR), ''),
    IFNULL(vd.vde_paymode, ''),
    IFNULL(vd.vde_payment_no, ''),
    IFNULL(CAST(pr.updateddate AS CHAR), ''),
    IFNULL(bid.tra_application_no, '')
))
SQL;
    }

    /**
     * @param  Builder|\Illuminate\Contracts\Database\Query\Builder  $base
     */
    private function mergeSmartFilter(Request $request): array
    {
        $sf = $request->input('smart_filter', []);
        $sf = is_array($sf) ? $sf : [];

        $flatKeys = [
            'bim_bills_no',
            'bim_payto_id',
            'bim_payto_name',
            'bim_status',
            'bim_cust_invoice_no',
            'bim_cust_invoice_date',
            'createddate',
            'bid_amt',
            'novoucher',
            'tra_application_no',
        ];
        foreach ($flatKeys as $k) {
            if (($sf[$k] ?? '') === '' && $request->filled($k)) {
                $sf[$k] = $request->input($k);
            }
        }

        return $sf;
    }

    /**
     * @param  Builder|\Illuminate\Contracts\Database\Query\Builder  $base
     */
    private function applySmartFilters($base, array $sf): void
    {
        $bn = trim((string) ($sf['bim_bills_no'] ?? ''));
        if ($bn !== '') {
            $base->where('bim.bim_bills_no', 'like', '%'.$this->literalLikeFragment($bn).'%');
        }
        /** Legacy UI label; filters line pay-to id. */
        $pid = trim((string) ($sf['bim_payto_id'] ?? ''));
        if ($pid !== '') {
            $base->where('bid.bid_payto_id', $pid);
        }
        $pn = trim((string) ($sf['bim_payto_name'] ?? ''));
        if ($pn !== '') {
            $base->where('bim.bim_payto_name', 'like', '%'.$this->literalLikeFragment($pn).'%');
        }
        $invNo = trim((string) ($sf['bim_cust_invoice_no'] ?? ''));
        if ($invNo !== '') {
            $base->where('bim.bim_cust_invoice_no', 'like', '%'.$this->literalLikeFragment($invNo).'%');
        }
        $st = trim((string) ($sf['bim_status'] ?? ''));
        if ($st !== '') {
            $base->where('bim.bim_status', $st);
        }
        $amt = trim((string) ($sf['bid_amt'] ?? ''));
        if ($amt !== '') {
            $base->where('bid.bid_amt', 'like', '%'.$this->literalLikeFragment($amt).'%');
        }
        $invDt = trim((string) ($sf['bim_cust_invoice_date'] ?? ''));
        if ($invDt !== '') {
            try {
                $d = Carbon::createFromFormat('d/m/Y', $invDt)->format('Y-m-d');
                $base->whereRaw('DATE(bim.bim_cust_invoice_date) = ?', [$d]);
            } catch (\Throwable) {
            }
        }
        $cd = trim((string) ($sf['createddate'] ?? ''));
        if ($cd !== '') {
            try {
                $base->whereRaw(
                    "DATE_FORMAT(bim.createddate, '%d/%m/%Y') = ?",
                    [Carbon::createFromFormat('d/m/Y', $cd)->format('d/m/Y')]
                );
            } catch (\Throwable) {
            }
        }
        $vn = trim((string) ($sf['novoucher'] ?? ''));
        if ($vn !== '') {
            $fragment = $this->literalLikeFragment($vn);
            $base->whereRaw(
                "(CASE WHEN vm.vma_voucher_no LIKE '%VCR%' THEN vm.vma_voucher_no WHEN vm.vma_voucher_no LIKE '%temp%' THEN bim.bim_voucher_no END) LIKE ?",
                ['%'.$fragment.'%']
            );
        }
        $tra = trim((string) ($sf['tra_application_no'] ?? ''));
        if ($tra !== '') {
            $base->where('bid.tra_application_no', 'like', '%'.$this->literalLikeFragment($tra).'%');
        }
    }

    private function literalLikeFragment(string $s): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s);
    }

    /** Lowercase needle with wildcards for {@code LOWER(CONCAT_WS(...)) LIKE ?}. */
    private function sqlLikeNeedle(string $loweredQ): string
    {
        return '%'.$this->literalLikeFragment($loweredQ).'%';
    }
}
