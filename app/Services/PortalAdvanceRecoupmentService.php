<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Portal / Advance Staff / Recoupment (HIDDEN LEVEL5 menus 2442, 2714, 2712, 2716).
 *
 * Mirrors legacy BL in `API_AAD_RECOUP_BILL` and `API_AAD_DETAILSRECOUPMENTDRAFT`
 * against `cash_advance_batch`, `temp_advance_bills_master`, `temp_advance_bills_details`,
 * {@see mysql_secondary} (`DB_SECOND_DATABASE`).
 */
class PortalAdvanceRecoupmentService
{
    private const RECOUP_SYSTEM_ID = 'RECOUP_ADVANCE';

    /** @var list<string> */
    public const RECOUP_MASTER_SECTIONS = ['pending', 'approved'];

    /**
     * Bills suitable for generating a draft recoup (legacy `generateBillList`).
     *
     * @return array{rows: Collection<int, object>, total: int}
     */
    public function listGenerateBillBatches(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = mb_strtolower(trim((string) $request->input('q', '')), 'UTF-8');
        $like = $q !== '' ? '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%' : null;

        $sortBy = (string) $request->input('sort_by', 'cab_recoup_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderMap = [
            'cab_batch_id' => 'cab.cab_batch_id',
            'cab_trans_no' => 'cab.cab_trans_no',
            'cab_batch_amt' => 'cab.cab_batch_amt',
            'cab_recoup_date' => 'cab.createddate',
            'bim_status_display' => 'bim.bim_status',
        ];
        $orderExpr = $orderMap[$sortBy] ?? 'cab.createddate';

        /** @phpstan-ignore-next-line dynamic call */
        $base = DB::connection('mysql_secondary')
            ->table('cash_advance_batch as cab')
            ->leftJoin('temp_advance_bills_master as bim', function ($join): void {
                $join->on('cab.bim_bills_no', '=', 'bim.bim_bills_no')
                    ->where('bim.bim_system_id', '=', self::RECOUP_SYSTEM_ID);
            })
            ->where('cab.cab_status', 'ENDORSE')
            ->where(function ($w): void {
                $w->whereNull('cab.bim_bills_no')->orWhere(function ($w2): void {
                    $w2->whereNotNull('cab.bim_bills_no')
                        ->where('bim.bim_status', 'REJECT');
                });
            });

        if ($like !== null) {
            $base->whereRaw(
                "LOWER(CONCAT_WS(0x1F, COALESCE(cab.cab_batch_id,''), COALESCE(cab.cab_status,''))) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count('cab.cab_id');

        /** @phpstan-ignore-next-line */
        $rows = (clone $base)
            ->select([
                'cab.cab_id',
                'cab.cab_batch_id',
                'cab.cab_trans_no',
                'cab.cab_batch_amt',
                'cab.bim_bills_no as cab_linked_bills_no',
                'bim.bim_status as recoup_master_status_raw',
                'cab.createddate as cab_recoup_date',
                DB::raw('('.$this->wfDescSql().') as bim_status_label'),
            ])
            ->orderByRaw($orderExpr.' '.$sortDir)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }

    /**
     * Status label compatible with legacy `wf_lookup.wfl_desc` (fallback: raw status code).
     */
    private function wfDescSql(): string
    {
        return 'COALESCE((SELECT UPPER(wfl_desc) FROM wf_lookup WHERE wfl_code = bim.bim_status LIMIT 1), bim.bim_status)';
    }

    /**
     * Non-approved (`pending`) vs `approved` recoup drafts (legacy `dt_RecoupList` / `dt_RecoupListApproved`).
     *
     * @return array{rows: Collection<int, object>, total: int}
     */
    public function listRecoupmentMaster(Request $request, string $section): array
    {
        if (! in_array($section, self::RECOUP_MASTER_SECTIONS, true)) {
            throw new InvalidArgumentException('Invalid recoup_master section.');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = mb_strtolower(trim((string) $request->input('q', '')), 'UTF-8');
        $likeGeneral = $q !== '' ? '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%' : null;

        $sortBy = (string) $request->input('sort_by', 'createddate');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortMap = [
            'bim_bills_id' => 'm.bim_bills_id',
            'bim_bills_no' => 'm.bim_bills_no',
            'bim_bills_desc' => 'm.bim_bills_desc',
            'bim_bill_amt' => 'm.bim_bill_amt',
            'bim_cust_invoice_no' => 'm.bim_cust_invoice_no',
            'bim_cust_invoice_date' => 'm.bim_cust_invoice_date',
            'bim_payto_id' => 'm.bim_payto_id',
            'bim_payto_name' => 'm.bim_payto_name',
            'bim_status' => 'm.bim_status',
            'createddate' => 'm.createddate',
        ];
        $orderExpr = $sortMap[$sortBy] ?? 'm.createddate';

        /** @phpstan-ignore-next-line dynamic call */
        $base = DB::connection('mysql_secondary')
            ->table('temp_advance_bills_master as m')
            ->when($section === 'pending', function ($q2): void {
                $q2->where('m.bim_status', '!=', 'APPROVE');
            })
            ->when($section === 'approved', function ($q2): void {
                $q2->where('m.bim_status', 'APPROVE');
            });

        if ($likeGeneral !== null) {
            $base->whereRaw(
                "LOWER(CONCAT_WS(0x1F,
                    IFNULL(m.bim_bills_id,''),
                    IFNULL(m.bim_bills_no,''),
                    IFNULL(m.bim_bills_desc,''),
                    IFNULL(m.bim_bill_amt,''),
                    IFNULL(m.bim_cust_invoice_no,''),
                    IFNULL(m.bim_cust_invoice_date,''),
                    IFNULL(m.bim_payto_id,''),
                    IFNULL(m.bim_payto_name,''),
                    IFNULL(m.bim_status,''),
                    IFNULL(m.createddate,''))) LIKE ?",
                [$likeGeneral]
            );
        }

        // Smart filters (legacy smartFilter[*]) — optional.
        foreach ([
            'sf_bim_bills_no' => ['col' => 'm.bim_bills_no', 'op' => 'like_prefix'],
            'sf_bim_payto_id' => ['col' => 'm.bim_payto_id', 'op' => 'like_prefix'],
            'sf_bim_payto_name' => ['col' => 'm.bim_payto_name', 'op' => 'like_prefix'],
            'sf_bim_bills_desc' => ['col' => 'm.bim_bills_desc', 'op' => 'eq'],
            'sf_bim_bill_amt' => ['col' => 'm.bim_bill_amt', 'op' => 'eq'],
            'sf_bim_status' => ['col' => 'm.bim_status', 'op' => 'eq'],
        ] as $key => $def) {
            $v = trim((string) $request->input($key, ''));
            if ($v === '') {
                continue;
            }
            if ($def['op'] === 'eq') {
                $base->where($def['col'], $v);
            } elseif ($def['op'] === 'like_prefix') {
                $base->whereRaw(
                    $def['col'].' LIKE ?',
                    ['%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $v).'%']
                );
            }
        }

        $sfDate = trim((string) $request->input('sf_createddate', ''));
        if ($sfDate !== '' && preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $sfDate, $m)) {
            $base->whereRaw('DATE(m.createddate) = ?', [sprintf('%s-%s-%s', $m[3], $m[2], $m[1])]);
        }

        $total = (clone $base)->count('m.bim_bills_id');

        /** @phpstan-ignore-next-line */
        $rows = (clone $base)
            ->select([
                'm.bim_bills_id',
                'm.bim_bills_no',
                'm.bim_bills_desc',
                'm.bim_bill_amt',
                'm.bim_cust_invoice_no',
                'm.bim_cust_invoice_date',
                'm.bim_payto_id',
                'm.bim_payto_name',
                'm.bim_status',
                'm.createddate',
            ])
            ->orderByRaw($orderExpr.' '.$sortDir)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'rows' => $rows,
            'total' => $total,
        ];
    }

    /**
     * Header block (legacy `recoup_details`).
     *
     * @return array<string, mixed>|null
     */
    public function getRecoupmentHeader(string $bimBillsId): ?array
    {
        $row = DB::connection('mysql_secondary')
            ->table('temp_advance_bills_master as tabm')
            ->leftJoin('cash_advance_batch as cab', 'tabm.bim_bills_no', '=', 'cab.bim_bills_no')
            ->where('tabm.bim_bills_id', $bimBillsId)
            ->select([
                'tabm.bim_bills_id as id',
                'tabm.bim_bills_no as no_brc',
                'tabm.bim_bills_desc as desc_brc',
                'tabm.bim_bill_amt as brc_amt',
                'tabm.bim_cust_invoice_no as inv_no',
                DB::raw("DATE_FORMAT(tabm.bim_cust_invoice_date, '%d/%m/%Y') as inv_date"),
                'tabm.bim_payto_id as payee_code',
                'tabm.bim_payto_name as payee_name',
                'tabm.bim_status as brc_status',
                'cab.cab_batch_id as batch_no',
            ])
            ->first();

        if ($row === null) {
            return null;
        }

        return (array) $row;
    }

    /**
     * Grouped debit lines (legacy `dt_debitRecoup`).
     *
     * @return array{rows: array<int, array<string, mixed>>, total: float, footer_bid_amt: float|string|null}
     */
    public function listDebitRecoupLines(string $bimBillsId, Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = mb_strtolower(trim((string) $request->input('q', '')), 'UTF-8');
        $searchLike = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q).'%';

        $sortBy = (string) $request->input('sort_by', 'bid_amt');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';
        $sortWhitelist = [
            'bid_payto_type',
            'bid_payto_id',
            'bid_payto_name',
            'fty_fund_type',
            'at_activity_code',
            'oun_code',
            'ccr_costcentre',
            'acm_acct_code',
            'bid_amt',
            'so_code',
        ];
        if (! in_array($sortBy, $sortWhitelist, true)) {
            $sortBy = 'bid_amt';
        }

        $common = "
            FROM temp_advance_bills_details
            WHERE bid_trans_type = 'DT'
            AND bim_bills_id = ?
            AND CONCAT_WS('__',
                bid_payto_type,
                bid_payto_id,
                bid_payto_name,
                fty_fund_type,
                at_activity_code,
                oun_code,
                ccr_costcentre,
                acm_acct_code,
                bid_amt,
                IFNULL(vsa_vendor_bank,''),
                IFNULL(vsa_bank_accno,'')
            ) LIKE ?
        ";

        $countSql = "SELECT COUNT(*) AS c FROM (SELECT bid_payto_type,
                bid_payto_id,
                bid_payto_name,
                fty_fund_type,
                at_activity_code,
                oun_code,
                ccr_costcentre,
                acm_acct_code,
                SUM(bid_amt) bid_amt,
                vsa_vendor_bank,
                vsa_bank_accno,
                concat_ws('-', vsa_vendor_bank, vsa_bank_accno) bank,
                substring(cpa_project_no,17) so_code,
                GROUP_CONCAT( REPLACE(CONCAT_WS('-', fty_fund_type, at_activity_code, oun_code,ccr_costcentre, substring(cpa_project_no,17), acm_acct_code),'--','-')) xx
            {$common}
            GROUP BY bid_payto_type, bid_payto_id, bid_payto_name,
                fty_fund_type, at_activity_code,
                oun_code, ccr_costcentre, acm_acct_code,
                vsa_vendor_bank, vsa_bank_accno,
                substring(cpa_project_no,17)
        ) grp";

        $countRow = DB::connection('mysql_secondary')->selectOne($countSql, [$bimBillsId, $searchLike]);

        $mainSql = "SELECT bid_payto_type,
                bid_payto_id,
                bid_payto_name,
                fty_fund_type,
                at_activity_code,
                oun_code,
                ccr_costcentre,
                acm_acct_code,
                SUM(bid_amt) bid_amt,
                vsa_vendor_bank,
                vsa_bank_accno,
                concat_ws('-', vsa_vendor_bank, vsa_bank_accno) bank,
                substring(cpa_project_no,17) so_code,
                GROUP_CONCAT( REPLACE(CONCAT_WS('-', fty_fund_type, at_activity_code, oun_code,ccr_costcentre, substring(cpa_project_no,17), acm_acct_code),'--','-')) xx
            {$common}
            GROUP BY bid_payto_type, bid_payto_id, bid_payto_name,
                fty_fund_type, at_activity_code,
                oun_code, ccr_costcentre, acm_acct_code,
                vsa_vendor_bank, vsa_bank_accno,
                substring(cpa_project_no,17)
            ORDER BY {$sortBy} {$sortDir}
            LIMIT ? OFFSET ?";

        $offset = ($page - 1) * $limit;
        /** @var list<object> $data */
        $data = DB::connection('mysql_secondary')->select($mainSql, [$bimBillsId, $searchLike, $limit, $offset]);

        $sumSql = "SELECT SUM(bid_amt) d {$common}";
        $footer = DB::connection('mysql_secondary')->selectOne($sumSql, [$bimBillsId, $searchLike]);

        /** @phpstan-ignore-next-line */
        $grpCount = (int) (($countRow->c ?? $countRow->C ?? 0));

        return [
            'rows' => array_map(static fn (object $o): array => (array) $o, $data),
            'total' => $grpCount,
            'footer_bid_amt' => $footer->d ?? $footer->D ?? null,
        ];
    }
}
