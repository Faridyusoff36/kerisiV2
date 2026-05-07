<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Asset Cancellation asset pick-list (PAGEIDs 2139 / 2221) and journal
 * listing (PAGEID 2267). Legacy BL: `ZJ_ASSET_LIST_FOR_CANCELLATION_API`.
 *
 * Full workflow (draft journal, debit/credit lines, approvals) is not
 * reproduced — read-only listings and filters only.
 */
class AssetCancellationController extends Controller
{
    use ApiResponse;

    private const CONN = 'mysql_secondary';

    private const ASSET_SORTABLE = [
        'aim_asset_code',
        'aim_asset_desc',
        'aim_asset_type',
        'itm_item_code',
        'aim_install_cost',
        'aim_status',
    ];

    private const JOURNAL_SORTABLE = [
        'createddate',
        'mjm_journal_no',
        'mjm_journal_desc',
        'mjm_total_amt',
        'mjm_status',
        'createdby',
        'mjm_approveby',
    ];

    public function assets(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'aim_asset_code');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::ASSET_SORTABLE, true)) {
            $sortBy = 'aim_asset_code';
        }

        $forVerification = filter_var($request->input('for_verification', false), FILTER_VALIDATE_BOOLEAN);
        $mjmJournalId = $request->input('mjm_journal_id');
        $assetId = $request->input('aim_asset_id');

        $base = $this->assetsBaseQuery($request, $forVerification, $mjmJournalId, $assetId);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(aim.aim_asset_code,''),
                    IFNULL(aim.aim_asset_desc,''),
                    IFNULL(aim.aim_asset_type,''),
                    IFNULL(aim.acm_acct_code,''),
                    IFNULL(aim.fty_fund_type,''),
                    IFNULL(aim.at_activity_code,''),
                    IFNULL(aim.oun_code_payment,''),
                    IFNULL(aim.cpa_project_no,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'aim.aim_asset_id',
                'aim.aim_asset_code',
                'aim.aim_asset_desc',
                'aim.aim_asset_type',
                'aim.itm_item_code',
                DB::raw("CONCAT_WS(' - ', aim.fty_fund_type, ft.fty_fund_desc) AS fund"),
                DB::raw("CONCAT_WS(' - ', aim.at_activity_code, att.at_activity_description_bm) AS activity"),
                DB::raw("CONCAT_WS(' - ', aim.oun_code_payment, oupay.oun_desc) AS ptj_payment"),
                DB::raw("CONCAT_WS(' - ', aim.ccr_costcentre_payment, ccpay.ccr_costcentre_desc) AS cc_payment"),
                'aim.cpa_project_no',
                DB::raw("CONCAT_WS(' - ', aim.acm_acct_code, am.acm_acct_desc) AS acct"),
                'aim.aim_install_cost',
                'aim.aim_status',
            ])
            ->orderBy('aim.'.$sortBy, $sortDir)
            ->orderBy('aim.aim_asset_id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->map(function ($r, int $i) use ($page, $limit, $forVerification) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'assetId' => (int) $r->aim_asset_id,
                'assetCode' => $r->aim_asset_code,
                'assetDesc' => $r->aim_asset_desc,
                'assetType' => $r->aim_asset_type,
                'itemCode' => $r->itm_item_code,
                'fund' => $r->fund,
                'activity' => $r->activity,
                'ptjPayment' => $r->ptj_payment,
                'costcentrePayment' => $r->cc_payment,
                'projectNo' => $r->cpa_project_no,
                'accountCode' => $r->acct,
                'installCost' => $r->aim_install_cost !== null ? (float) $r->aim_install_cost : null,
                'status' => $r->aim_status,
                'selectable' => ! $forVerification,
            ];
        })->all();

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    public function journals(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'createddate');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (! in_array($sortBy, self::JOURNAL_SORTABLE, true)) {
            $sortBy = 'createddate';
        }

        $base = DB::connection(self::CONN)
            ->table('manual_journal_master as mjm')
            ->join('manual_journal_details as mjd', 'mjm.mjm_journal_id', '=', 'mjd.mjm_journal_no')
            ->join('asset_inventory_main as aim', 'mjd.mjd_reference', '=', 'aim.aim_asset_code')
            ->where('mjm.mjm_system_id', 'ASSET_CANCEL');

        $dateFrom = trim((string) $request->input('createddate', ''));
        $dateTo = trim((string) $request->input('createddate_end', ''));
        $desc = trim((string) $request->input('mjm_journal_desc', ''));
        $amt = $request->input('mjm_total_amt');
        $status = trim((string) $request->input('mjm_status', ''));
        $createdBy = trim((string) $request->input('createdby', ''));

        if ($dateFrom !== '') {
            $base->whereRaw("mjm.createddate >= STR_TO_DATE(?, '%d/%m/%Y')", [$dateFrom]);
        }
        if ($dateTo !== '') {
            $base->whereRaw("mjm.createddate <= STR_TO_DATE(?, '%d/%m/%Y %H:%i:%s')", [$dateTo.' 23:59:59']);
        }
        if ($desc !== '') {
            $base->where('mjm.mjm_journal_desc', 'like', $this->likeEscape($desc));
        }
        if ($amt !== null && $amt !== '') {
            $n = is_numeric(str_replace(',', '', (string) $amt))
                ? (float) str_replace(',', '', (string) $amt) : null;
            if ($n !== null) {
                $base->where('mjm.mjm_total_amt', $n);
            }
        }
        if ($status !== '') {
            $base->where('mjm.mjm_status', $status);
        }
        if ($createdBy !== '') {
            $base->where('mjm.createdby', $createdBy);
        }

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    DATE_FORMAT(mjm.createddate, '%d/%m/%Y'),
                    IFNULL(mjm.mjm_journal_no,''),
                    IFNULL(mjm.mjm_journal_desc,''),
                    IFNULL(mjm.mjm_total_amt,''),
                    IFNULL(mjm.mjm_status,''),
                    IFNULL(mjm.createdby,''),
                    IFNULL(mjm.mjm_approveby,'')
                )) LIKE ?",
                [$like]
            );
        }

        $cntRow = (clone $base)->selectRaw('COUNT(DISTINCT mjm.mjm_journal_id) AS cnt')->first();
        $total = (int) ($cntRow->cnt ?? 0);

        $sortExpr = match ($sortBy) {
            'mjm_journal_no' => 'MAX(mjm.mjm_journal_no)',
            'mjm_journal_desc' => 'MAX(mjm.mjm_journal_desc)',
            'mjm_total_amt' => 'MAX(mjm.mjm_total_amt)',
            'mjm_status' => 'MAX(mjm.mjm_status)',
            'createdby' => 'MAX(mjm.createdby)',
            'mjm_approveby' => 'MAX(mjm.mjm_approveby)',
            default => 'MAX(mjm.createddate)',
        };

        $ids = (clone $base)
            ->select('mjm.mjm_journal_id')
            ->selectRaw($sortExpr.' AS sort_key')
            ->groupBy('mjm.mjm_journal_id')
            ->orderBy('sort_key', $sortDir)
            ->orderBy('mjm.mjm_journal_id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->pluck('mjm.mjm_journal_id')
            ->all();

        if ($ids === []) {
            return $this->sendOk([], [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / max(1, $limit)),
            ]);
        }

        $orderIds = collect($ids)->map(fn ($id) => (int) $id)->implode(',');
        $rows = DB::connection(self::CONN)
            ->table('manual_journal_master as mjm')
            ->join('manual_journal_details as mjd', 'mjm.mjm_journal_id', '=', 'mjd.mjm_journal_no')
            ->join('asset_inventory_main as aim', 'mjd.mjd_reference', '=', 'aim.aim_asset_code')
            ->whereIn('mjm.mjm_journal_id', $ids)
            ->select([
                'mjm.mjm_journal_id',
                'mjm.createddate',
                'mjm.mjm_journal_no',
                'mjm.mjm_journal_desc',
                'mjm.mjm_total_amt',
                'mjm.mjm_status',
                'mjm.createdby',
                'mjm.mjm_approveby',
                DB::raw('MAX(aim.aim_asset_desc) AS aim_asset_desc'),
                DB::raw('MAX(aim.aim_asset_id) AS aim_asset_id'),
            ])
            ->groupBy([
                'mjm.mjm_journal_id',
                'mjm.createddate',
                'mjm.mjm_journal_no',
                'mjm.mjm_journal_desc',
                'mjm.mjm_total_amt',
                'mjm.mjm_status',
                'mjm.createdby',
                'mjm.mjm_approveby',
            ])
            ->orderByRaw('FIELD(mjm.mjm_journal_id, '.$orderIds.')')
            ->get();

        $data = $rows->values()->map(function ($r, int $i) use ($page, $limit) {
            $st = (string) ($r->mjm_status ?? '');
            $draft = in_array($st, ['DRAFT', 'ENTRY', 'VERIFIED', 'APPROVE'], true);

            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'journalId' => (int) $r->mjm_journal_id,
                'createdDate' => $r->createddate,
                'journalNo' => $r->mjm_journal_no,
                'description' => $r->mjm_journal_desc,
                'itemDescription' => $r->aim_asset_desc,
                'amount' => $r->mjm_total_amt !== null ? (float) $r->mjm_total_amt : null,
                'status' => $r->mjm_status,
                'createdBy' => $r->createdby,
                'approveBy' => $r->mjm_approveby,
                'assetId' => (int) $r->aim_asset_id,
                'isDraft' => $draft,
            ];
        })->all();

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    private function assetsBaseQuery(Request $request, bool $forVerification, $mjmJournalId, $assetId): QueryBuilder
    {
        $b = DB::connection(self::CONN)
            ->table('asset_inventory_main as aim')
            ->leftJoin('organization_unit as ou', 'aim.oun_code_current', '=', 'ou.oun_code')
            ->leftJoin('fund_type as ft', 'aim.fty_fund_type', '=', 'ft.fty_fund_type')
            ->leftJoin('activity_type as att', 'aim.at_activity_code', '=', 'att.at_activity_code')
            ->leftJoin('account_main as am', 'aim.acm_acct_code', '=', 'am.acm_acct_code')
            ->leftJoin('cost_centre as cc', 'aim.ccr_costcentre_current', '=', 'cc.ccr_costcentre')
            ->leftJoin('organization_unit as oupay', 'aim.oun_code_payment', '=', 'oupay.oun_code')
            ->leftJoin('cost_centre as ccpay', 'aim.ccr_costcentre_payment', '=', 'ccpay.ccr_costcentre')
            ->where('aim.aim_status', 'APPROVED')
            ->where(function ($q) {
                $q->whereIn('aim.aim_reg_source', ['DM', 'JOURNAL', 'WIP', 'DONATION'])
                    ->orWhere(function ($q2) {
                        $q2->where('aim.aim_reg_source', 'GRN')
                            ->whereExists(function ($sub) {
                                $sub->selectRaw('1')
                                    ->from('bills_master as bm')
                                    ->whereColumn('bm.grm_receive_no', 'aim.grm_receive_no')
                                    ->where('bm.bim_status', 'APPROVE')
                                    ->whereNotNull('bm.pmt_posting_no');
                            });
                    })
                    ->orWhere(function ($q3) {
                        $q3->where('aim.aim_reg_source', 'BILL')
                            ->whereExists(function ($sub) {
                                $sub->selectRaw('1')
                                    ->from('bills_master as bm')
                                    ->whereColumn('bm.bim_bills_no', 'aim.bim_bills_no')
                                    ->where('bm.bim_status', 'APPROVE')
                                    ->whereNotNull('bm.pmt_posting_no');
                            });
                    });
            });

        if ($assetId !== null && $assetId !== '' && is_numeric($assetId)) {
            $b->where('aim.aim_asset_id', (int) $assetId);
        } elseif ($forVerification && $mjmJournalId !== null && $mjmJournalId !== '' && is_numeric($mjmJournalId)) {
            $jid = (int) $mjmJournalId;
            $b->whereIn('aim.aim_asset_code', function ($sub) use ($jid) {
                $sub->select('mjd.mjd_reference')
                    ->from('manual_journal_details as mjd')
                    ->where('mjd.mjm_journal_no', $jid);
            });
        } elseif (! $forVerification) {
            $b->whereNotIn('aim.aim_asset_code', function ($sub) {
                $sub->select('mjd.mjd_reference')
                    ->from('manual_journal_master as mjm')
                    ->join('manual_journal_details as mjd', 'mjm.mjm_journal_id', '=', 'mjd.mjm_journal_no')
                    ->where('mjm.mjm_system_id', 'ASSET_CANCEL')
                    ->where('mjm.mjm_status', '<>', 'REJECT');
            });
        }

        $assetCode = trim((string) $request->input('aim_asset_code', ''));
        $assetType = trim((string) $request->input('aim_asset_type', ''));
        $fund = trim((string) $request->input('fty_fund_type', ''));
        $activity = trim((string) $request->input('at_activity_code', ''));
        $acct = trim((string) $request->input('acm_acct_code', ''));
        $project = trim((string) $request->input('cpa_project_no', ''));
        $ptjPay = trim((string) $request->input('oun_code_payment', ''));
        $install = $request->input('aim_install_cost');
        $regYear = trim((string) $request->input('aim_registered_date', ''));
        $assetDesc = trim((string) $request->input('aim_asset_desc', ''));

        if ($assetCode !== '') {
            $b->where('aim.aim_asset_code', 'like', $this->likeEscape($assetCode));
        }
        if ($assetDesc !== '') {
            $b->where('aim.aim_asset_desc', 'like', $this->likeEscape($assetDesc));
        }
        if ($assetType !== '') {
            $b->where('aim.aim_asset_type', $assetType);
        }
        if ($fund !== '') {
            $b->where('aim.fty_fund_type', 'like', $this->likeEscape($fund));
        }
        if ($activity !== '') {
            $b->where('aim.at_activity_code', 'like', $this->likeEscape($activity));
        }
        if ($acct !== '') {
            $b->where('aim.acm_acct_code', 'like', $this->likeEscape($acct));
        }
        if ($project !== '') {
            $b->where('aim.cpa_project_no', 'like', $this->likeEscape($project));
        }
        if ($ptjPay !== '') {
            $b->where('aim.oun_code_payment', 'like', $this->likeEscape($ptjPay));
        }
        if ($install !== null && $install !== '') {
            $b->where('aim.aim_install_cost', 'like', $this->likeEscape((string) $install));
        }
        if ($regYear !== '' && ctype_digit($regYear)) {
            $b->whereRaw('YEAR(aim.aim_registered_date) = ?', [(int) $regYear]);
        }

        return $b;
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
