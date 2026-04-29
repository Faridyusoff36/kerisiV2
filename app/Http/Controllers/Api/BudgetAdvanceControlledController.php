<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\BudgetAdvanceControlledListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Budget / Budget Advance Controlled (PAGEID 1784 / MENUID 2160).
 *
 * Read-only datatable backed by legacy `SNA_API_BUDGET_ADVANCE?dt_listbudgetadvance=1`.
 */
class BudgetAdvanceControlledController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BudgetAdvanceControlledListService $list,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));

        $sortBy = (string) $request->input('sort_by', 'tarikh');
        if (! in_array($sortBy, BudgetAdvanceControlledListService::SORTABLE, true)) {
            $sortBy = 'tarikh';
        }
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $base = $this->list->baseQuery($request, 'controlled');
        $total = (clone $base)->count();
        $footerTotal = (float) ((clone $base)->sum('bam_total') ?? 0);

        $rows = (clone $base)
            ->tap(fn ($q) => $this->list->applySort($q, $sortBy, $sortDir))
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(function ($row, int $i) use ($page, $limit): array {
            $tarikh = $row->updateddate ?? $row->createddate;
            $tarikhStr = null;
            if ($tarikh) {
                $tarikhStr = $tarikh instanceof \DateTimeInterface
                    ? $tarikh->format('Y-m-d')
                    : (string) $tarikh;
                if (strlen($tarikhStr) > 10) {
                    try {
                        $tarikhStr = (new \DateTime($tarikhStr))->format('Y-m-d');
                    } catch (\Throwable) {
                        // keep string as returned
                    }
                }
            }

            return [
                'index' => (($page - 1) * $limit) + $i + 1,
                'bam_id' => (int) $row->bam_id,
                'bam_year' => $row->bam_year,
                'tarikh' => $tarikhStr,
                'bam_no' => $row->bam_no,
                'bam_endorse_doc' => $row->bam_endorse_doc,
                'bam_total' => $row->bam_total !== null ? (float) $row->bam_total : null,
                'bam_status' => $row->bam_status,
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'footer' => ['bam_total_sum' => $footerTotal],
        ]);
    }
}
