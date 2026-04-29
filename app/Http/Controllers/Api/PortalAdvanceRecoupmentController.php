<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\PortalAdvanceRecoupmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Portal / Advance Staff — Recoupment (LEVEL5 menus 2442, 2714, 2712, 2716).
 */
class PortalAdvanceRecoupmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PortalAdvanceRecoupmentService $recoupment,
    ) {}

    /** Legacy `generateBillList` batches (endorsed cash advance batches). */
    public function generateBillBatches(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));

        $result = $this->recoupment->listGenerateBillBatches($request);
        $total = (int) $result['total'];
        $indexed = $result['rows']->values()->map(function (object $r, int $i) use ($page, $limit): array {
            return array_merge((array) $r, ['index' => (($page - 1) * $limit) + $i + 1]);
        });

        return $this->sendOk($indexed, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /** Legacy `dt_RecoupList` / `dt_RecoupListApproved`. */
    public function recoupBillsIndex(Request $request): JsonResponse
    {
        $section = (string) $request->query('section', 'pending');

        try {
            $page = max(1, (int) $request->input('page', 1));
            $limit = max(1, min(100, (int) $request->input('limit', 10)));

            $result = $this->recoupment->listRecoupmentMaster($request, $section);
        } catch (InvalidArgumentException) {
            return $this->sendError(400, 'BAD_REQUEST', 'Invalid section. Use `pending` or `approved`.', [
                'allowed' => PortalAdvanceRecoupmentService::RECOUP_MASTER_SECTIONS,
            ]);
        }

        $total = (int) $result['total'];

        $indexed = $result['rows']->values()->map(function (object $r, int $i) use ($page, $limit): array {
            $row = (array) $r;
            $bid = $row['bim_bills_id'] ?? null;
            $row['detail_url_kerisi_path'] = $bid !== null && $bid !== ''
                ? '/admin/kerisi/m/2712?bim_bills_id='.$bid
                : null;
            $row['draft_detail_url_kerisi_path'] = $bid !== null && $bid !== ''
                ? '/admin/kerisi/m/2716?bim_bills_id='.$bid
                : null;
            $row['index'] = (($page - 1) * $limit) + $i + 1;

            return $row;
        });

        return $this->sendOk($indexed, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
            'section' => $section,
        ]);
    }

    public function header(string $bimBillsId): JsonResponse
    {
        $header = $this->recoupment->getRecoupmentHeader($bimBillsId);
        if ($header === null) {
            return $this->sendError(404, 'NOT_FOUND', 'Recoup bill not found.', ['bim_bills_id' => $bimBillsId]);
        }

        return $this->sendOk($header);
    }

    /** Legacy grouped debit rows (`dt_debitRecoup`). */
    public function debitLines(Request $request, string $bimBillsId): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(200, (int) $request->input('limit', 10)));

        $result = $this->recoupment->listDebitRecoupLines($bimBillsId, $request);

        $indexed = [];
        foreach ($result['rows'] as $i => $r) {
            $indexed[] = array_merge($r, ['index' => (($page - 1) * $limit) + $i + 1]);
        }

        $grpTotal = (int) ($result['total'] ?? 0);

        return $this->sendOk($indexed, [
            'page' => $page,
            'limit' => $limit,
            'total' => $grpTotal,
            'totalPages' => (int) ceil($grpTotal / max(1, $limit)),
            'footer' => ['bid_amt' => $result['footer_bid_amt'] ?? null],
        ]);
    }
}
