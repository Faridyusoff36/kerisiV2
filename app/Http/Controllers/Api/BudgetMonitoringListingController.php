<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\BudgetMonitoringListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Budget / Monitoring / Budget Listing (PAGEID 1510 / MENUID 1831) — detail
 * tabs backed by legacy `API_BDG_MONITORING_LISTING` modes.
 */
class BudgetMonitoringListingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BudgetMonitoringListingService $listing,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $section = (string) $request->input('section', '');
        if (! in_array($section, BudgetMonitoringListingService::SECTIONS, true)) {
            return $this->sendError(400, 'BAD_REQUEST', 'Invalid or missing section.', [
                'allowed' => BudgetMonitoringListingService::SECTIONS,
            ]);
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));

        $result = $this->listing->run($section, $request);
        $rows = $result['rows'];
        $total = (int) $result['total'];
        $footer = $result['footer'];

        $indexed = $rows->values()->map(function (object $r, int $i) use ($page, $limit): array {
            $arr = (array) $r;
            $arr['index'] = (($page - 1) * $limit) + $i + 1;

            return $arr;
        });

        return $this->sendOk($indexed, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'footer' => $footer,
        ]);
    }
}
