<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Purchasing / Setup / Item Main (legacy PAGEID 1499 / menu 1820).
 *
 * Mirrors legacy APIs that read lookup_details (ITEM_CATEGORY) for main category,
 * item_subcategory, optional item_subsiri, then item_main for the deepest grid.
 */
class PurchasingItemMainService
{
    private const ITEM_CATEGORY_LOOKUP = 'ITEM_CATEGORY';

    private function conn(): Connection
    {
        return DB::connection('mysql_secondary');
    }

    /** Case-insensitive LIKE pattern for user's q. */
    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($needle, 'UTF-8')).'%';
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function groupLookupOptions(): array
    {
        $rows = $this->conn()
            ->table('lookup_details')
            ->where('lma_code_name', self::ITEM_CATEGORY_LOOKUP)
            ->whereNotNull('lde_group')
            ->where('lde_group', '!=', '')
            ->selectRaw('DISTINCT TRIM(lde_group) AS g')
            ->orderBy('g')
            ->pluck('g');

        return $rows->map(fn ($g) => [
            'value' => (string) $g,
            'label' => (string) $g,
        ])->values()->all();
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function mainCategories(Request $request, int $page, int $limit, string $q): array
    {
        $group = trim((string) $request->input('grouplookup', $request->input('group_lookup', '')));
        if ($group === '') {
            return ['rows' => [], 'total' => 0];
        }

        $base = $this->conn()
            ->table('lookup_details as ld')
            ->where('ld.lma_code_name', self::ITEM_CATEGORY_LOOKUP)
            ->where('ld.lde_group', $group)
            ->select([
                'ld.lde_id',
                'ld.lde_value',
                'ld.lde_description',
                DB::raw("(CASE WHEN ld.lde_status IN ('1','Y','ACTIVE','y') THEN 'ACTIVE' ELSE 'INACTIVE' END) AS lde_status"),
                'ld.lde_group',
            ])
            ->orderBy('ld.lde_value');

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(ld.lde_value,''), IFNULL(ld.lde_description,''))) LIKE ?",
                [$like]
            );
        }

        return $this->paginateQuery($base, $page, $limit);
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function subcategories(Request $request, int $page, int $limit, string $q): array
    {
        $categoryCode = trim((string) $request->input('category_code', $request->input('categoryCode', '')));
        if ($categoryCode === '') {
            return ['rows' => [], 'total' => 0];
        }

        $base = $this->conn()
            ->table('item_subcategory as isc')
            ->where('isc.isc_category_code', $categoryCode)
            ->select([
                'isc.isc_subcategory_id',
                'isc.isc_category_code',
                'isc.isc_subcategory_code',
                'isc.isc_subcategory_desc',
                DB::raw("(CASE WHEN isc.isc_status IN ('1','Y','ACTIVE','y') THEN 'ACTIVE' ELSE 'INACTIVE' END) AS isc_status"),
            ])
            ->orderBy('isc.isc_subcategory_code');

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(isc.isc_subcategory_code,''), IFNULL(isc.isc_subcategory_desc,''))) LIKE ?",
                [$like]
            );
        }

        return $this->paginateQuery($base, $page, $limit);
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function subsiri(Request $request, int $page, int $limit, string $q): array
    {
        $cat = trim((string) $request->input('category_code', $request->input('categoryCode', '')));
        $sub = trim((string) $request->input('subcategory_code', $request->input('subcategoryCode', '')));
        if ($cat === '' || $sub === '') {
            return ['rows' => [], 'total' => 0];
        }

        $c = $this->conn();
        $hasSubsiri = Schema::connection('mysql_secondary')->hasTable('item_subsiri');

        if ($hasSubsiri) {
            $base = $c->table('item_subsiri as iss')
                ->where('iss.isc_category_code', $cat)
                ->where('iss.isc_subcategory_code', $sub)
                ->select([
                    'iss.iss_subsiri_id',
                    'iss.isc_category_code',
                    'iss.isc_subcategory_code',
                    'iss.iss_subsiri_code',
                    'iss.iss_subsiri_desc',
                    DB::raw("(CASE WHEN iss.iss_status IN ('1','Y','ACTIVE','y') THEN 'ACTIVE' ELSE 'INACTIVE' END) AS iss_status"),
                ])
                ->orderBy('iss.iss_subsiri_code');
            try {
                (clone $base)->limit(1)->get();
            } catch (\Throwable) {
                $hasSubsiri = false;
            }
        }
        if (! $hasSubsiri) {
            // Fallback: distinct subsiri codes from item_main (description may match any child item).
            $base = $c->table('item_main as im')
                ->where('im.itm_category_code', $cat)
                ->where('im.isc_subcategory_code', $sub)
                ->whereNotNull('im.iss_subsiri_code')
                ->where('im.iss_subsiri_code', '!=', '')
                ->groupBy(['im.iss_subsiri_code'])
                ->select([
                    DB::raw('MIN(im.itm_item_id) AS iss_subsiri_id'),
                    DB::raw('MAX(im.itm_category_code) AS isc_category_code'),
                    DB::raw('MAX(im.isc_subcategory_code) AS isc_subcategory_code'),
                    'im.iss_subsiri_code',
                    DB::raw('MAX(im.itm_item_desc) AS iss_subsiri_desc'),
                    DB::raw("'ACTIVE' AS iss_status"),
                ])
                ->orderBy('im.iss_subsiri_code');
        }

        if ($q !== '') {
            $like = $this->likeEscape($q);
            if ($hasSubsiri) {
                $base->whereRaw(
                    "LOWER(CONCAT_WS('|', IFNULL(iss.iss_subsiri_code,''), IFNULL(iss.iss_subsiri_desc,''))) LIKE ?",
                    [$like]
                );
            } else {
                $base->whereRaw(
                    "LOWER(CONCAT_WS('|', IFNULL(im.iss_subsiri_code,''), IFNULL(im.itm_item_desc,''))) LIKE ?",
                    [$like]
                );
            }
        }

        return $this->paginateQuery($base, $page, $limit);
    }

    /**
     * Bottom grid — item_master rows scoped to hierarchy.
     *
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function itemLines(Request $request, int $page, int $limit, string $q): array
    {
        $cat = trim((string) $request->input('category_code', $request->input('categoryCode', '')));
        $sub = trim((string) $request->input('subcategory_code', $request->input('subcategoryCode', '')));
        $ssi = trim((string) $request->input('subsiri_code', $request->input('subsiriCode', '')));
        if ($cat === '' || $sub === '' || $ssi === '') {
            return ['rows' => [], 'total' => 0];
        }

        $base = $this->conn()
            ->table('item_main as im')
            ->where('im.itm_category_code', $cat)
            ->where('im.isc_subcategory_code', $sub)
            ->where('im.iss_subsiri_code', $ssi)
            ->select([
                'im.itm_item_id',
                'im.itm_item_code',
                'im.itm_item_desc',
                'im.acm_acct_code',
                'im.itm_myfislite_flag',
                DB::raw("(CASE WHEN im.itm_status IN ('1','Y','ACTIVE','y') THEN 'ACTIVE' ELSE 'INACTIVE' END) AS itm_status"),
            ])
            ->orderBy('im.itm_item_code');

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                "LOWER(CONCAT_WS('|', IFNULL(im.itm_item_code,''), IFNULL(im.itm_item_desc,''), IFNULL(im.acm_acct_code,''))) LIKE ?",
                [$like]
            );
        }

        return $this->paginateQuery($base, $page, $limit);
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    private function paginateQuery(Builder $base, int $page, int $limit): array
    {
        $limit = max(1, min(200, $limit));
        $page = max(1, $page);

        try {
            $paginator = $base->paginate($limit, ['*'], 'page', $page);
        } catch (\Throwable) {
            return ['rows' => [], 'total' => 0];
        }

        return [
            'rows' => collect($paginator->items())->map(fn ($r) => (array) $r)->values()->all(),
            'total' => (int) $paginator->total(),
        ];
    }
}
