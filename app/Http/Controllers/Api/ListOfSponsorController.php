<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Student Finance > Sponsor > Report > List of Sponsor (PAGEID 1583 / MENUID 1916).
 *
 * Legacy BL `API_SF_SPONSOR_LISTOFSPONSOR` (?dt_listofsponsor=1) selected from
 * {@see DB_SECOND_DATABASE}.`sponsor` using the same compound search surface as the
 * legacy PHP (CONCAT_WS across code, name, status label, claim label).
 *
 * Datatable columns (PAGE_MENUID1019_LEVEL3.json — “Datatable column details”):
 *   No | Code | Name | Status | Claim
 *
 * Uses the query builder only (no raw SELECT strings outside whereRaw wrappers).
 */
class ListOfSponsorController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'spn_sponsor_code',
        'spn_sponsor_name',
        'status',
        'claim',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'spn_sponsor_code');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (! in_array($sortBy, self::SORTABLE, true)) {
            $sortBy = 'spn_sponsor_code';
        }

        $base = $this->baseQuery();

        if ($q !== '') {
            $needle = $this->likeEscape(strtolower($q));
            $base->whereRaw(
                'LOWER(CONCAT_WS(\'__\', '
                    .'IFNULL(s.spn_sponsor_code, \'\'), '
                    .'IFNULL(s.spn_sponsor_name, \'\'), '
                    .'IF(s.spn_status_cd = \'1\', \'Active\', \'Inactive\'), '
                    .'IF(s.spn_status_invoice_cd = \'1\', \'Yes\', \'No\')'
                    .')) LIKE ?',
                [$needle]
            );
        }

        $total = (clone $base)->count();

        $statusExpr = DB::raw("IF(s.spn_status_cd = '1', 'Active', 'Inactive')");
        $claimExpr = DB::raw("IF(s.spn_status_invoice_cd = '1', 'Yes', 'No')");

        $orderColumn = match ($sortBy) {
            'spn_sponsor_code' => 's.spn_sponsor_code',
            'spn_sponsor_name' => 's.spn_sponsor_name',
            'status' => $statusExpr,
            'claim' => $claimExpr,
            default => 's.spn_sponsor_code',
        };

        $rows = (clone $base)
            ->select([
                's.spn_sponsor_id',
                's.spn_sponsor_code',
                's.spn_sponsor_name',
                DB::raw('IF(s.spn_status_cd = \'1\', \'Active\', \'Inactive\') AS status_label'),
                DB::raw('IF(s.spn_status_invoice_cd = \'1\', \'Yes\', \'No\') AS claim_label'),
            ])
            ->orderBy($orderColumn, $sortDir)
            ->orderBy('s.spn_sponsor_id', 'asc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(fn ($r, int $i) => [
            'index' => (($page - 1) * $limit) + $i + 1,
            'spnSponsorId' => (int) $r->spn_sponsor_id,
            'sponsorCode' => $r->spn_sponsor_code ?? '',
            'sponsorName' => $r->spn_sponsor_name ?? '',
            'status' => $r->status_label ?? '',
            'claim' => $r->claim_label ?? '',
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    private function baseQuery(): Builder
    {
        return DB::connection('mysql_secondary')->table('sponsor as s');
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
