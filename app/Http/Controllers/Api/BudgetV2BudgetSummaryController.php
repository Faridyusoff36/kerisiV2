<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\BudgetV2BudgetSummaryQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Legacy api/V2_BUDGET_SUMMARY_API (?dt_listing=1) — Budget Summary By Date /
 * Variation / By PTJ (menus 3382, 3389, 3393 — HIDDEN_PAGE_LEVEL4).
 */
class BudgetV2BudgetSummaryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BudgetV2BudgetSummaryQueryService $query,
    ) {}

    public function listing(Request $request): JsonResponse
    {
        if (! $request->filled('bdg_year')) {
            return $this->sendError(422, 'VALIDATION_ERROR', 'bdg_year is required.', [
                'field' => 'bdg_year',
            ]);
        }

        $result = $this->query->listing($request);

        $mapped = $result['rows']->values()->map(function (object $o): array {
            $a = (array) $o;

            return [
                'indexing' => $a['indexing'] ?? null,
                'acct_code' => $a['acct_code'] ?? null,
                'acm_acct_desc' => $a['acm_acct_desc'] ?? null,
                'PTJ' => $a['PTJ'] ?? null,
                'costcentre' => $a['costcentre'] ?? null,
                'cost_centre_dup' => $a['cost_centre_dup'] ?? null,
                'description' => $a['description'] ?? null,
                'fund_type_display' => $a['fund_type_display'] ?? null,
                'project_no' => $a['project_no'] ?? null,
                'initial' => isset($a['initial']) ? (float) $a['initial'] : null,
                'virement' => isset($a['virement']) ? (float) $a['virement'] : null,
                'additional' => isset($a['additional']) ? (float) $a['additional'] : null,
                'topup' => isset($a['topup']) ? (float) $a['topup'] : null,
                'opening' => isset($a['opening']) ? (float) $a['opening'] : null,
                'request' => isset($a['request']) ? (float) $a['request'] : null,
                'commit' => isset($a['commit']) ? (float) $a['commit'] : null,
                'expenses' => isset($a['expenses']) ? (float) $a['expenses'] : null,
                'locked' => isset($a['locked']) ? (float) $a['locked'] : null,
                'pre_request' => isset($a['pre_request']) ? (float) $a['pre_request'] : null,
                'allocated' => isset($a['allocated']) ? (float) $a['allocated'] : null,
                'balance' => isset($a['balance']) ? (float) $a['balance'] : null,
                'total' => isset($a['total']) ? (float) $a['total'] : null,
                'expenses_percent' => $a['expenses_percent'] ?? null,
            ];
        });

        return $this->sendOk($mapped, [
            'aggregate_expenses_percent' => $result['aggregate_expenses_percent'],
        ]);
    }
}
