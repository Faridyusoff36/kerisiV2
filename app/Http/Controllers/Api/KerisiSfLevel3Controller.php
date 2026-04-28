<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\KerisiSfShellListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Canonical list payload for PAGE_MENUID1019_LEVEL3 Student Finance screens that
 * do not yet have a bespoke controller. The registry + UI are migrated; each
 * dataset is wired by adding a dedicated query/handler (ORM on `mysql_secondary`)
 * in a follow-up, reusing the same `menuId` and list contract.
 */
class KerisiSfLevel3Controller extends Controller
{
    use ApiResponse;

    public function index(Request $request, int $menuId): JsonResponse
    {
        /** @var array<int, int> $sfMenus */
        $sfMenus = config('kerisi_student_finance.menu_ids', []);
        if (! in_array($menuId, $sfMenus, true)) {
            return $this->sendError(404, 'NOT_FOUND', 'Not a Student Finance Kerisi menu');
        }

        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));

        $filters = [];
        foreach ($request->except(['page', 'limit', 'q', 'sort_by', 'sort_dir']) as $key => $value) {
            if (preg_match('/^sf_\\d+$/', (string) $key)) {
                $filters[(string) $key] = $value;
            }
            if (preg_match('/^tf_\\d+$/', (string) $key)) {
                $filters[(string) $key] = $value;
            }
        }

        $pack = app(KerisiSfShellListService::class)->fetch($menuId, $request);
        $rows = $pack['rows'] ?? [];
        $total = (int) ($pack['total'] ?? 0);
        $connector = (string) ($pack['connector'] ?? 'registry_shell');

        $meta = [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $total > 0 ? (int) ceil($total / $limit) : 0,
            'menuId' => $menuId,
            'filtersEcho' => $filters,
            'connector' => $connector,
        ];

        if (! empty($pack['shellError'])) {
            $meta['shellError'] = $pack['shellError'];
        }

        return $this->sendOk($rows, $meta);
    }
}
