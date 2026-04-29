<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\BudgetUmumAllocationPtjQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Budget / Report / Total Allocation / Umum Allocation, Expenditure & Balance by PTJ
 * (PAGEID 2515, MENUID 3044 — HIDDEN_PAGE_LEVEL4). Read-only aggregated list + exports.
 *
 * Legacy: api/AS_PHP_BUDGET_UMUM_ALLOCATION_PTJ (?dt_list=1).
 */
class BudgetUmumAllocationPtjController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BudgetUmumAllocationPtjQueryService $query,
    ) {}

    public function options(): JsonResponse
    {
        $conn = DB::connection('mysql_secondary');

        $years = $conn->table('budget')
            ->select('bdg_year')
            ->distinct()
            ->whereNotNull('bdg_year')
            ->orderByDesc('bdg_year')
            ->pluck('bdg_year')
            ->filter()
            ->map(fn ($y) => ['id' => (string) $y, 'label' => (string) $y])
            ->values();

        $ouns = $conn->table('organization_unit')
            ->select('oun_code', 'oun_desc')
            ->whereNotNull('oun_code')
            ->orderBy('oun_code')
            ->limit(2000)
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->oun_code,
                'label' => trim($r->oun_code.' - '.($r->oun_desc ?? '')),
            ])
            ->values();

        $activities = $conn->table('activity_type')
            ->select('at_activity_code', 'at_activity_description_bm')
            ->orderBy('at_activity_code')
            ->limit(2000)
            ->get()
            ->map(fn ($r) => [
                'id' => (string) $r->at_activity_code,
                'label' => trim($r->at_activity_code.' - '.($r->at_activity_description_bm ?? '')),
            ])
            ->values();

        return $this->sendOk([
            'topFilter' => [
                'year' => $years,
                'ptj' => $ouns,
                'activity' => $activities,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(500, (int) $request->input('limit', 10)));

        $rows = $this->query->rows($request, $page, $limit);
        $total = $this->query->total($request);
        $footer = $this->query->footer($request);

        $base = ($page - 1) * $limit;
        $num = static function (object $r, string $a, string $b): ?float {
            if (property_exists($r, $a) && $r->{$a} !== null) {
                return (float) $r->{$a};
            }
            if (property_exists($r, $b) && $r->{$b} !== null) {
                return (float) $r->{$b};
            }

            return null;
        };

        $data = $rows->values()->map(function (object $r, int $i) use ($base, $num): array {
            return [
                'index' => $base + $i + 1,
                'at_activity_code' => $r->at_activity_code ?? null,
                'at_activity_description_bm' => $r->at_activity_description_bm ?? null,
                'oun_code' => $r->oun_code ?? null,
                'oun_desc' => $r->oun_desc ?? null,
                'ccr_costcentre' => $r->ccr_costcentre ?? null,
                'ccr_costcentre_desc' => $r->ccr_costcentre_desc ?? null,
                'allocation' => isset($r->allocation) ? (float) $r->allocation : null,
                'lock' => $num($r, 'Lock', 'lock'),
                'request' => $num($r, 'Request', 'request'),
                'commitment' => $num($r, 'Commitment', 'commitment'),
                'expenses' => $num($r, 'Expenses', 'expenses'),
                'total_expenses' => $num($r, 'Total_Expenses', 'total_expenses'),
                'balance' => isset($r->balance) ? (float) $r->balance : null,
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil(max(1, $total) / max(1, $limit)),
            'footer' => $footer,
        ]);
    }
}
