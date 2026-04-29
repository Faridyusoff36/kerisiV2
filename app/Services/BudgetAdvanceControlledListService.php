<?php

namespace App\Services;

use App\Models\BudgetInAdvanceMaster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Budget in-advance listings on `budget_in_advance_master`.
 *
 * - **controlled** (PAGEID 1784 / MENUID 2160): `bam_total > 0` (legacy
 *   `SNA_API_BUDGET_ADVANCE` controlled list).
 * - **in_advance** (PAGEID 1737 / MENUID 2098): all rows (full “Budget In Advance” list).
 */
class BudgetAdvanceControlledListService
{
    public const SORTABLE = [
        'bam_id', 'bam_year', 'tarikh', 'bam_no', 'bam_endorse_doc', 'bam_total', 'bam_status',
    ];

    public function baseQuery(Request $request, string $mode = 'controlled'): Builder
    {
        $q = BudgetInAdvanceMaster::query();
        if ($mode === 'controlled') {
            $q->where('bam_total', '>', 0);
        }

        $needle = trim((string) $request->input('q', ''));
        if ($needle !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($needle, 'UTF-8')).'%';
            $q->whereRaw(
                'LOWER(CONCAT_WS(\'__\', bam_id, IFNULL(bam_no, \'\'), IFNULL(bam_year, \'\'), IFNULL(bam_endorse_doc, \'\'), bam_total, IFNULL(bam_status, \'\'), COALESCE(updateddate, createddate))) LIKE ?',
                [$like]
            );
        }

        return $q;
    }

    public function applySort(Builder $q, string $sortBy, string $sortDir): void
    {
        if ($sortBy === 'tarikh') {
            $q->orderByRaw('COALESCE(updateddate, createddate) '.$sortDir);

            return;
        }
        $q->orderBy($sortBy, $sortDir);
    }
}
