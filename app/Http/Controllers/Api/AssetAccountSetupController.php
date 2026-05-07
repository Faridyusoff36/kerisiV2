<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Legacy API_ASSET_INVENTORY_ACCOUNTCODE_SETUP / asset_depr_setup (Kerisi menu 1645).
 */
class AssetAccountSetupController extends Controller
{
    use ApiResponse;

    private function cx()
    {
        return DB::connection('mysql_secondary');
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($needle, 'UTF-8')).'%';
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function baseListingQuery(?string $q)
    {
        $base = $this->cx()->table('asset_depr_setup as ads')
            ->leftJoin('account_main as am1', 'ads.ads_depr_code', '=', 'am1.acm_acct_code')
            ->leftJoin('account_main as am2', 'ads.acm_acct_code', '=', 'am2.acm_acct_code')
            ->leftJoin('account_main as am3', 'ads.acm_accm_acct_code', '=', 'am3.acm_acct_code')
            ->leftJoin('account_main as am4', 'ads.ads_disposal_acct', '=', 'am4.acm_acct_code')
            ->leftJoin('account_main as am5', 'ads.ads_writeoff_acct', '=', 'am5.acm_acct_code')
            ->leftJoin('lookup_details as lde1', function ($j) {
                $j->on('ads.itm_category_code', '=', 'lde1.lde_value')
                    ->where('lde1.lma_code_name', '=', 'ITEM_CATEGORY');
            })
            ->leftJoin('item_subcategory as isc', function ($j) {
                $j->on('ads.itm_category_code', '=', 'isc.isc_category_code')
                    ->whereColumn('ads.itm_subcategory_code', 'isc.isc_subcategory_code');
            })
            ->leftJoin('lookup_details as lde2', function ($j) {
                $j->on('ads.ads_depr_group', '=', 'lde2.lde_value')
                    ->where('lde2.lma_code_name', '=', 'DEPR_GROUP');
            })
            ->leftJoin('lookup_details as ldstatus', function ($j) {
                $j->whereColumn('ads.ads_status', 'ldstatus.lde_value')
                    ->where('ldstatus.lma_code_name', '=', 'RECORDSTATUS');
            });

        if ($q !== null && trim($q) !== '') {
            $needle = mb_strtolower(trim($q), 'UTF-8');
            $like = $this->likeEscape($needle);
            $notesExpr = Schema::connection('mysql_secondary')->hasColumn('asset_depr_setup', 'ads_extended_field')
                ? 'LOWER(IFNULL(JSON_UNQUOTE(JSON_EXTRACT(ads.ads_extended_field, "$.notes")), ""))'
                : "''";

            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
IFNULL(CAST(ads.ads_depr_id AS CHAR),""),
IFNULL(ads.ads_type,""),
IFNULL(concat_ws("-", ads.itm_category_code, lde1.lde_description),""),
IFNULL(concat_ws("-", ads.itm_subcategory_code, isc.isc_subcategory_desc),""),
IFNULL(ads.ads_depr_code,""),
IFNULL(am1.acm_acct_desc,""),
IFNULL(ads.acm_acct_code,""),
IFNULL(am2.acm_acct_desc,""),
IFNULL(ads.acm_accm_acct_code,""),
IFNULL(am3.acm_acct_desc,""),
IFNULL(ads.ads_disposal_acct,""),
IFNULL(am4.acm_acct_desc,""),
IFNULL(concat_ws("-", ads.ads_depr_group, lde2.lde_description),""),
IFNULL(ads.ads_estimated_life,""),
IFNULL(ads.ads_depreciation_percent,""),
IFNULL(CAST(IFNULL(ads.ads_residual_value,0) AS CHAR),""),
IFNULL(CAST(IFNULL(ads.min_amt,0) AS CHAR),""),
IFNULL(CAST(IFNULL(ads.max_amt,0) AS CHAR),""),
IFNULL(ads.ads_status,""),
'.$notesExpr.')) LIKE ?',
                [$like]
            );
        }

        return $base;
    }

    private function notesSelect()
    {
        return Schema::connection('mysql_secondary')->hasColumn('asset_depr_setup', 'ads_extended_field')
            ? DB::raw('IFNULL(JSON_UNQUOTE(JSON_EXTRACT(ads.ads_extended_field, "$.notes")), "") AS notes')
            : DB::raw('"" AS notes');
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'q' => ['sometimes', 'string', 'max:200'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
        ]);

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(100, (int) ($validated['limit'] ?? 15)));
        $sortDir = (string) ($validated['sort_dir'] ?? 'asc');
        $q = trim((string) ($validated['q'] ?? ''));

        $total = (int) $this->baseListingQuery($q === '' ? null : $q)->count();

        $rows = $this->baseListingQuery($q === '' ? null : $q)
            ->select([
                'ads.ads_depr_id',
                'ads.ads_type',
                DB::raw("concat_ws('-', ads.itm_category_code, lde1.lde_description) AS itm_category_display"),
                DB::raw("concat_ws('-', ads.itm_subcategory_code, isc.isc_subcategory_desc) AS itm_subcategory_display"),
                'ads.ads_depr_code',
                DB::raw("concat_ws('-', ads.ads_depr_code, am1.acm_acct_desc) AS ads_depr_code_display"),
                DB::raw("concat_ws('-', ads.acm_acct_code, am2.acm_acct_desc) AS acm_acct_code_display"),
                DB::raw("concat_ws('-', ads.acm_accm_acct_code, am3.acm_acct_desc) AS accum_display"),
                DB::raw("concat_ws('-', ads.ads_disposal_acct, am4.acm_acct_desc) AS disposal_display"),
                DB::raw("concat_ws('-', ads.ads_writeoff_acct, am5.acm_acct_desc) AS writeoff_display"),
                DB::raw("concat_ws('-', ads.ads_depr_group, lde2.lde_description) AS depr_group_display"),
                'ads.ads_estimated_life',
                'ads.ads_depreciation_percent',
                'ads.ads_residual_value',
                'ads.min_amt',
                'ads.max_amt',
                DB::raw('IFNULL(ldstatus.lde_description2, ads.ads_status) AS ads_status_display'),
                $this->notesSelect(),
            ])
            ->orderBy('ads.ads_depr_id', $sortDir === 'desc' ? 'desc' : 'asc')
            ->forPage($page, $limit)
            ->get();

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit) {
            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'ads_depr_id' => (int) $row->ads_depr_id,
                'ads_type' => (string) ($row->ads_type ?? ''),
                'itm_category_display' => (string) ($row->itm_category_display ?? ''),
                'itm_subcategory_display' => (string) ($row->itm_subcategory_display ?? ''),
                'ads_depr_code' => (string) ($row->ads_depr_code ?? ''),
                'ads_depr_code_display' => (string) ($row->ads_depr_code_display ?? ''),
                'acm_acct_code_display' => (string) ($row->acm_acct_code_display ?? ''),
                'accum_display' => (string) ($row->accum_display ?? ''),
                'disposal_display' => (string) ($row->disposal_display ?? ''),
                'writeoff_display' => (string) ($row->writeoff_display ?? ''),
                'depr_group_display' => (string) ($row->depr_group_display ?? ''),
                'ads_estimated_life' => (string) ($row->ads_estimated_life ?? ''),
                'ads_depreciation_percent' => (string) ($row->ads_depreciation_percent ?? ''),
                'ads_residual_value' => (string) ($row->ads_residual_value ?? ''),
                'min_amt' => $row->min_amt,
                'max_amt' => $row->max_amt,
                'ads_status_display' => (string) ($row->ads_status_display ?? ''),
                'notes' => (string) ($row->notes ?? ''),
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $row = $this->baseListingQuery(null)
            ->where('ads.ads_depr_id', $id)
            ->select([
                'ads.ads_depr_id',
                'ads.ads_type',
                DB::raw("concat_ws('-', ads.itm_category_code, lde1.lde_description) AS itm_category_display"),
                DB::raw("concat_ws('-', ads.itm_subcategory_code, isc.isc_subcategory_desc) AS itm_subcategory_display"),
                'ads.ads_depr_code',
                DB::raw("concat_ws('-', ads.ads_depr_code, am1.acm_acct_desc) AS ads_depr_code_display"),
                DB::raw("concat_ws('-', ads.acm_acct_code, am2.acm_acct_desc) AS acm_acct_code_display"),
                DB::raw("concat_ws('-', ads.acm_accm_acct_code, am3.acm_acct_desc) AS accum_display"),
                DB::raw("concat_ws('-', ads.ads_disposal_acct, am4.acm_acct_desc) AS disposal_display"),
                DB::raw("concat_ws('-', ads.ads_writeoff_acct, am5.acm_acct_desc) AS writeoff_display"),
                DB::raw("concat_ws('-', ads.ads_depr_group, lde2.lde_description) AS depr_group_display"),
                'ads.ads_estimated_life',
                'ads.ads_depreciation_percent',
                'ads.ads_residual_value',
                'ads.min_amt',
                'ads.max_amt',
                DB::raw('IFNULL(ldstatus.lde_description2, ads.ads_status) AS ads_status_display'),
                $this->notesSelect(),
            ])
            ->first();

        if (! $row) {
            return $this->sendError(404, 'NOT_FOUND', 'Setup not found');
        }

        return $this->sendOk([
            'ads_depr_id' => (int) $row->ads_depr_id,
            'ads_type' => (string) ($row->ads_type ?? ''),
            'itm_category_display' => (string) ($row->itm_category_display ?? ''),
            'itm_subcategory_display' => (string) ($row->itm_subcategory_display ?? ''),
            'ads_depr_code' => (string) ($row->ads_depr_code ?? ''),
            'ads_depr_code_display' => (string) ($row->ads_depr_code_display ?? ''),
            'acm_acct_code_display' => (string) ($row->acm_acct_code_display ?? ''),
            'accum_display' => (string) ($row->accum_display ?? ''),
            'disposal_display' => (string) ($row->disposal_display ?? ''),
            'writeoff_display' => (string) ($row->writeoff_display ?? ''),
            'depr_group_display' => (string) ($row->depr_group_display ?? ''),
            'ads_estimated_life' => (string) ($row->ads_estimated_life ?? ''),
            'ads_depreciation_percent' => (string) ($row->ads_depreciation_percent ?? ''),
            'ads_residual_value' => (string) ($row->ads_residual_value ?? ''),
            'min_amt' => $row->min_amt,
            'max_amt' => $row->max_amt,
            'ads_status_display' => (string) ($row->ads_status_display ?? ''),
            'notes' => (string) ($row->notes ?? ''),
        ]);
    }
}
