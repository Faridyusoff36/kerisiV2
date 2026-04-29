<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\OrgUnitCostCentre;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Listing for Setup and Maintenance → General Ledger Structure → Floating Point for Profile Setup
 * (PAGEID 1943 / MENUID 2375).
 *
 * Mirrors legacy BL `NAD_API_PTJCOSTCENTER_LISTING` (`dt_listingProfile=1`): `org_unit_costcentre`
 * rows excluding those that already resolve to a capital project cascade.
 */
class ProfileFloatingPointListingController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'ouc_ounit_costcentre_id',
        'fty_fund_type',
        'fty_fund_desc',
        'at_activity_code',
        'at_activity_description_bm',
        'oun_code',
        'oun_desc',
        'ccr_costcentre',
        'ccr_costcentre_desc',
        'ouc_status_display',
        'datecreate',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = min(500, max(1, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));

        $sortBy = (string) $request->input('sort_by', 'ouc_ounit_costcentre_id');
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'ouc_ounit_costcentre_id';
        }
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $filterFund = trim((string) $request->input('fty_fund_type_filter', ''));
        $filterActivity = trim((string) $request->input('at_activity_code_filter', ''));
        $filterPtj = trim((string) $request->input('oun_code_filter', ''));
        $filterCc = trim((string) $request->input('ccr_costcentre_filter', ''));
        $filterStatus = strtoupper(trim((string) $request->input('ouc_status_filter', '')));

        /** @phpstan-ignore-next-line Relation — base table only; descriptions via subselects like legacy BL. */
        $query = OrgUnitCostCentre::query()->from('org_unit_costcentre AS OU')
            ->select([
                'OU.ouc_ounit_costcentre_id',
                'OU.fty_fund_type',
                DB::raw('(
                    SELECT FTY.fty_fund_desc FROM fund_type FTY WHERE OU.fty_fund_type = FTY.fty_fund_type LIMIT 1
                ) AS fty_fund_desc'),
                'OU.at_activity_code',
                DB::raw('(
                    SELECT AT.at_activity_description_bm FROM activity_type AT WHERE OU.at_activity_code = AT.at_activity_code LIMIT 1
                ) AS at_activity_description_bm'),
                'OU.oun_code',
                DB::raw('(
                    SELECT OUN.oun_desc FROM organization_unit OUN WHERE OU.oun_code = OUN.oun_code LIMIT 1
                ) AS oun_desc'),
                'OU.ccr_costcentre',
                DB::raw('(
                    SELECT CCR.ccr_costcentre_desc FROM costcentre CCR WHERE OU.ccr_costcentre = CCR.ccr_costcentre LIMIT 1
                ) AS ccr_costcentre_desc'),
                DB::raw("IF(OU.ouc_status = '1','ACTIVE', 'INACTIVE') AS ouc_status_display"),
                DB::raw('COALESCE(OU.updateddate, OU.createddate) AS datecreate'),
            ])
            ->where('OU.fty_fund_type', '!=', 'E01')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('capital_project AS CP')
                    ->whereColumn('CP.fty_fund_type', 'OU.fty_fund_type')
                    ->whereColumn('CP.ccr_costcentre', 'OU.ccr_costcentre')
                    ->whereColumn('CP.lat_activity_code', 'OU.at_activity_code');
            });

        if ($q !== '') {
            $needle = mb_strtolower($q, 'UTF-8');
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';

            /** @phpstan-ignore-next-line */
            $query->whereRaw(
                "LOWER(CONCAT_WS('__',
                    OU.fty_fund_type,
                    (SELECT FTY.fty_fund_desc FROM fund_type FTY WHERE OU.fty_fund_type = FTY.fty_fund_type),
                    OU.at_activity_code,
                    (SELECT AT.at_activity_description_bm FROM activity_type AT WHERE OU.at_activity_code = AT.at_activity_code),
                    OU.oun_code,
                    (SELECT OUN.oun_desc FROM organization_unit OUN WHERE OU.oun_code = OUN.oun_code),
                    OU.ccr_costcentre,
                    (SELECT CCR.ccr_costcentre_desc FROM costcentre CCR WHERE OU.ccr_costcentre = CCR.ccr_costcentre),
                    IF(OU.ouc_status = '1','ACTIVE', 'INACTIVE'),
                    DATE_FORMAT(IFNULL(OU.updateddate, OU.createddate), '%d/%m/%Y')
                )) LIKE ?",
                [$like],
            );
        }

        if ($filterFund !== '') {
            $query->where('OU.fty_fund_type', $filterFund);
        }
        if ($filterActivity !== '') {
            $query->where('OU.at_activity_code', $filterActivity);
        }
        if ($filterPtj !== '') {
            $query->where('OU.oun_code', $filterPtj);
        }
        if ($filterCc !== '') {
            $query->where('OU.ccr_costcentre', $filterCc);
        }
        if ($filterStatus !== '') {
            $query->whereRaw("IF(OU.ouc_status = '1','ACTIVE', 'INACTIVE') = ?", [$filterStatus]);
        }

        $total = (clone $query)->count('OU.ouc_ounit_costcentre_id');

        $orderMap = [
            'ouc_ounit_costcentre_id' => 'OU.ouc_ounit_costcentre_id',
            'fty_fund_type' => 'OU.fty_fund_type',
            'fty_fund_desc' => DB::raw('(SELECT FTY.fty_fund_desc FROM fund_type FTY WHERE OU.fty_fund_type = FTY.fty_fund_type)'),
            'at_activity_code' => 'OU.at_activity_code',
            'at_activity_description_bm' => DB::raw('(SELECT AT.at_activity_description_bm FROM activity_type AT WHERE OU.at_activity_code = AT.at_activity_code)'),
            'oun_code' => 'OU.oun_code',
            'oun_desc' => DB::raw('(SELECT OUN.oun_desc FROM organization_unit OUN WHERE OU.oun_code = OUN.oun_code)'),
            'ccr_costcentre' => 'OU.ccr_costcentre',
            'ccr_costcentre_desc' => DB::raw('(SELECT CCR.ccr_costcentre_desc FROM costcentre CCR WHERE OU.ccr_costcentre = CCR.ccr_costcentre)'),
            'ouc_status_display' => DB::raw("IF(OU.ouc_status = '1','ACTIVE', 'INACTIVE')"),
            'datecreate' => DB::raw('COALESCE(OU.updateddate, OU.createddate)'),
        ];

        $orderExpr = $orderMap[$sortBy];

        /** @phpstan-ignore-next-line */
        $base = clone $query;
        /** @phpstan-ignore-next-line */
        $base->orderBy($orderExpr, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit);

        /** @phpstan-ignore-next-line */
        $rows = $base->get()->map(static function ($row): array {
            $dateFormatted = '';
            if (! empty($row->datecreate)) {
                try {
                    $dateFormatted = Carbon::parse((string) $row->datecreate)->format('d/m/Y');
                } catch (\Throwable) {
                    $dateFormatted = '';
                }
            }

            return [
                'ouc_ounit_costcentre_id' => (int) $row->ouc_ounit_costcentre_id,
                'fty_fund_type' => $row->fty_fund_type ?? '',
                'fty_fund_desc' => $row->fty_fund_desc ?? '',
                'at_activity_code' => $row->at_activity_code ?? '',
                'at_activity_description_bm' => $row->at_activity_description_bm ?? '',
                'oun_code' => $row->oun_code ?? '',
                'oun_desc' => $row->oun_desc ?? '',
                'ccr_costcentre' => $row->ccr_costcentre ?? '',
                'ccr_costcentre_desc' => $row->ccr_costcentre_desc ?? '',
                'ouc_status' => $row->ouc_status_display ?? '',
                'datecreate' => $dateFormatted,
            ];
        });

        return $this->sendOk($rows->values()->all(), [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $total > 0 ? (int) ceil($total / $limit) : 0,
        ]);
    }
}
