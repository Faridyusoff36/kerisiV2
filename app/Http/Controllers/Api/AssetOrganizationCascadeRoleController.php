<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kerisi menu 3148 — read-only reference of distinct `organization_authorization.ora_cascade_role`
 * values used in asset workflows (broader prefix filter when q is empty).
 */
class AssetOrganizationCascadeRoleController extends Controller
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

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'q' => ['sometimes', 'string', 'max:200'],
        ]);

        if (! Schema::connection('mysql_secondary')->hasTable('organization_authorization')) {
            return $this->sendOk([], [
                'page' => 1,
                'limit' => 15,
                'total' => 0,
                'totalPages' => 1,
            ]);
        }

        $page = max(1, (int) ($validated['page'] ?? 1));
        $limit = max(1, min(200, (int) ($validated['limit'] ?? 15)));
        $q = trim((string) ($validated['q'] ?? ''));

        $query = $this->cx()->table('organization_authorization')
            ->whereNotNull('ora_cascade_role')
            ->where('ora_cascade_role', '!=', '')
            ->select('ora_cascade_role')
            ->distinct();

        if ($q === '') {
            $query->where(function ($w) {
                $w->where('ora_cascade_role', 'like', '%ASSET%')
                    ->orWhere('ora_cascade_role', 'like', '%INSPECT%')
                    ->orWhere('ora_cascade_role', 'like', '%PTJ%');
            });
        } else {
            $like = $this->likeEscape($q);
            $query->whereRaw('LOWER(ora_cascade_role) LIKE ?', [$like]);
        }

        $total = (int) (clone $query)->count();
        if ($total === 0 && $q === '') {
            $query = $this->cx()->table('organization_authorization')
                ->whereNotNull('ora_cascade_role')
                ->where('ora_cascade_role', '!=', '')
                ->select('ora_cascade_role')
                ->distinct();
            $total = (int) (clone $query)->count();
        }

        $rows = (clone $query)->orderBy('ora_cascade_role')->forPage($page, $limit)->pluck('ora_cascade_role');

        $data = $rows->values()->map(fn (string $code, int $i) => [
            'index' => (($page - 1) * $limit) + $i + 1,
            'role_code' => $code,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => $limit > 0 ? (int) ceil($total / $limit) : 1,
        ]);
    }
}
