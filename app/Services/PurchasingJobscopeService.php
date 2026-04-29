<?php

namespace App\Services;

use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Purchasing / Setup / List Of Jobscope (menu 1932).
 *
 * Backed by `jobscope` + `jobscope_category` on `mysql_secondary` (legacy FIMS).
 */
class PurchasingJobscopeService
{
    private function conn(): Connection
    {
        return DB::connection('mysql_secondary');
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($needle, 'UTF-8')).'%';
    }

    /**
     * Human-readable status for API (matches legacy ACTIVE/INACTIVE labels).
     */
    public function displayStatus(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return 'INACTIVE';
        }
        $s = strtoupper(trim((string) $raw));

        return in_array($s, ['1', 'Y', 'YES', 'ACTIVE', 'A'], true) ? 'ACTIVE' : 'INACTIVE';
    }

    /**
     * Store as legacy-friendly single char / digit.
     */
    public function statusToDb(string $ui): string
    {
        $u = strtoupper(trim($ui));

        return in_array($u, ['1', 'ACTIVE', 'Y', 'YES'], true) ? '1' : '0';
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function paginateList(Request $request, int $page, int $limit, string $q): array
    {
        $limit = max(1, min(200, $limit));
        $page = max(1, $page);

        $c = $this->conn();

        try {
            $base = $c->table('jobscope as j')
                ->select([
                    'j.jbs_id',
                    'j.jbs_jobscope_code',
                    'j.jbs_job_name',
                    'j.jbs_level',
                    'j.jbs_job_type',
                    'j.jbc_category',
                    'j.jbs_job_code_parent',
                    'j.jbs_status',
                ]);
        } catch (\Throwable) {
            return ['rows' => [], 'total' => 0];
        }

        $sfCode = trim((string) $request->input('sf_0', ''));
        $sfLevel = trim((string) $request->input('sf_1', ''));
        $sfCat = trim((string) $request->input('sf_2', ''));
        $sfStatus = trim((string) $request->input('sf_3', ''));

        if ($sfCode !== '') {
            $like = $this->likeEscape($sfCode);
            $base->whereRaw('LOWER(IFNULL(j.jbs_jobscope_code,\'\')) LIKE ?', [$like]);
        }
        if ($sfLevel !== '') {
            $base->where('j.jbs_level', $sfLevel);
        }
        if ($sfCat !== '') {
            $base->where('j.jbc_category', $sfCat);
        }
        if ($sfStatus !== '') {
            $wantActive = strtoupper($sfStatus) === 'ACTIVE';
            if ($wantActive) {
                $base->whereRaw(
                    "(UPPER(TRIM(CAST(IFNULL(j.jbs_status,'') AS CHAR))) IN ('1','Y','ACTIVE') OR TRIM(CAST(IFNULL(j.jbs_status,'') AS CHAR)) = '1')"
                );
            } else {
                $base->whereRaw(
                    "(UPPER(TRIM(CAST(IFNULL(j.jbs_status,'') AS CHAR))) NOT IN ('1','Y','ACTIVE') AND TRIM(CAST(IFNULL(j.jbs_status,'') AS CHAR)) NOT IN ('1'))"
                );
            }
        }

        if ($q !== '') {
            $like = $this->likeEscape($q);
            $base->whereRaw(
                'LOWER(CONCAT_WS("|",
                  IFNULL(j.jbs_jobscope_code,\'\'),
                  IFNULL(j.jbs_job_name,\'\'),
                  IFNULL(CAST(j.jbs_level AS CHAR),\'\'),
                  IFNULL(j.jbs_job_type,\'\'),
                  IFNULL(j.jbc_category,\'\'),
                  IFNULL(j.jbs_job_code_parent,\'\'),
                  IFNULL(CAST(j.jbs_status AS CHAR),\'\'))) LIKE ?',
                [$like]
            );
        }

        try {
            $base->orderBy('j.jbs_jobscope_code');
            $paginator = $base->paginate($limit, ['*'], 'page', $page);
        } catch (\Throwable) {
            return ['rows' => [], 'total' => 0];
        }

        $rows = collect($paginator->items())->map(function ($row) {
            /** @var object $row */
            $arr = (array) $row;
            $raw = $arr['jbs_status'] ?? null;
            $arr['jbs_status'] = $this->displayStatus($raw);

            return $arr;
        })->values()->all();

        return [
            'rows' => $rows,
            'total' => (int) $paginator->total(),
        ];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int, connector: string}
     */
    public function listForShell(Request $request, int $page, int $limit, string $q): array
    {
        $pack = $this->paginateList($request, $page, $limit, $q);

        return [
            'rows' => $pack['rows'],
            'total' => $pack['total'],
            'connector' => 'purchasing_list_of_jobscope',
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function levelOptions(): array
    {
        try {
            $rows = $this->conn()
                ->table('jobscope')
                ->whereNotNull('jbs_level')
                ->selectRaw('DISTINCT CAST(jbs_level AS CHAR) AS lvl')
                ->orderBy('lvl')
                ->pluck('lvl');
        } catch (\Throwable) {
            $rows = collect(['1', '2', '3']);
        }

        if ($rows->isEmpty()) {
            $rows = collect(['1', '2', '3']);
        }

        return $rows->map(fn ($v) => [
            'value' => trim((string) $v),
            'label' => trim((string) $v),
        ])->values()->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function categoryOptions(): array
    {
        $hasCat = Schema::connection('mysql_secondary')->hasTable('jobscope_category');

        try {
            if ($hasCat) {
                $rows = $this->conn()
                    ->table('jobscope_category as jc')
                    ->whereNotNull('jbc_category')
                    ->where('jbc_category', '!=', '')
                    ->orderBy('jbc_category')
                    ->get(['jbc_category', 'jbc_desc']);
                $opts = [];
                foreach ($rows as $r) {
                    $code = (string) ($r->jbc_category ?? '');
                    $d = isset($r->jbc_desc) ? trim((string) $r->jbc_desc) : '';
                    $opts[] = [
                        'value' => $code,
                        'label' => $d !== '' ? $code.' — '.$d : $code,
                    ];
                }

                return $opts;
            }
        } catch (\Throwable) {
            // fallback below
        }

        try {
            $distinct = $this->conn()->table('jobscope')
                ->whereNotNull('jbc_category')
                ->where('jbc_category', '!=', '')
                ->selectRaw('DISTINCT TRIM(jbc_category) AS c')
                ->orderBy('c')
                ->pluck('c');
        } catch (\Throwable) {
            return [];
        }

        return $distinct->map(fn ($v) => [
            'value' => (string) $v,
            'label' => (string) $v,
        ])->values()->all();
    }

    /**
     * Parents for hierarchical codes: Level 2 → choose among Level 1 in same category; Level 3 → Level 2 in same category.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function parentOptions(Request $request): array
    {
        $category = trim((string) $request->input('category', ''));
        $level = trim((string) $request->input('level', ''));
        if ($category === '' || $level === '') {
            return [];
        }

        $parentLevel = match ($level) {
            '2' => '1',
            '3' => '2',
            default => '',
        };

        if ($parentLevel === '') {
            return [['value' => '', 'label' => '— Top level —']];
        }

        try {
            $rows = $this->conn()->table('jobscope as j')
                ->where('j.jbc_category', $category)
                ->whereRaw('CAST(j.jbs_level AS CHAR) = ?', [$parentLevel])
                ->orderBy('j.jbs_jobscope_code')
                ->get(['j.jbs_jobscope_code', 'j.jbs_job_name']);
        } catch (\Throwable) {
            return [];
        }

        $opts = [['value' => '', 'label' => '— Select —']];
        foreach ($rows as $r) {
            $code = (string) ($r->jbs_jobscope_code ?? '');
            $nm = trim((string) ($r->jbs_job_name ?? ''));
            $opts[] = [
                'value' => $code,
                'label' => $nm !== '' ? $code.' — '.$nm : $code,
            ];
        }

        return $opts;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function statusDropdownOptions(): array
    {
        return [
            ['value' => '1', 'label' => 'ACTIVE'],
            ['value' => '0', 'label' => 'INACTIVE'],
        ];
    }

    /**
     * Validates parent existence and depth against level (matches legacy hierarchies).
     *
     * @throws ValidationException
     */
    public function assertParentHierarchy(string $level, string $category, mixed $parent): void
    {
        $category = trim($category);
        $level = trim($level);
        $parentTrim = isset($parent) ? trim((string) $parent) : '';

        if ($level === '') {
            return;
        }

        if ($level === '1') {
            if ($parentTrim !== '') {
                throw ValidationException::withMessages([
                    'parent' => ['Level 1 records must have an empty parent.'],
                ]);
            }

            return;
        }

        if ($parentTrim === '') {
            throw ValidationException::withMessages([
                'parent' => ['Parent is required for Level 2 and Level 3.'],
            ]);
        }

        try {
            $p = $this->conn()->table('jobscope')
                ->where('jbc_category', $category)
                ->where('jbs_jobscope_code', $parentTrim)
                ->first();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'parent' => ['Unable to verify parent in jobscope table.'],
            ]);
        }

        if (! $p) {
            throw ValidationException::withMessages([
                'parent' => ['Parent code does not exist for this category.'],
            ]);
        }

        $pl = trim((string) ($p->jbs_level ?? ''));
        if ($level === '2' && $pl !== '1') {
            throw ValidationException::withMessages([
                'parent' => ['Level 2 parent must reference a Level 1 jobscope row.'],
            ]);
        }
        if ($level === '3' && $pl !== '2') {
            throw ValidationException::withMessages([
                'parent' => ['Level 3 parent must reference a Level 2 jobscope row.'],
            ]);
        }
    }

    /**
     * Payload for SPA jobscope popup (camelCase outbound via middleware).
     *
     * @return array{id:int,level:string,category:string,parent:string,code:string,name:string,status:string}|null
     */
    public function rowToFormPayload(?object $row): ?array
    {
        if ($row === null) {
            return null;
        }
        $rawStat = $row->jbs_status ?? null;

        return [
            'id' => (int) ($row->jbs_id ?? 0),
            'level' => trim((string) ($row->jbs_level ?? '')),
            'category' => (string) ($row->jbc_category ?? ''),
            'parent' => isset($row->jbs_job_code_parent) && $row->jbs_job_code_parent !== null
                ? (string) $row->jbs_job_code_parent : '',
            'code' => (string) ($row->jbs_jobscope_code ?? ''),
            'name' => (string) ($row->jbs_job_name ?? ''),
            'status' => $this->statusToDb($this->displayStatus($rawStat)),
        ];
    }

    public function findById(int $id): ?object
    {
        try {
            $row = $this->conn()->table('jobscope')->where('jbs_id', $id)->first();
        } catch (\Throwable) {
            return null;
        }

        return $row;
    }

    public function codeExistsForCategory(string $code, string $category, ?int $exceptId): bool
    {
        $q = $this->conn()->table('jobscope')
            ->where('jbs_jobscope_code', $code)
            ->where('jbc_category', $category);
        if ($exceptId !== null) {
            $q->where('jbs_id', '!=', $exceptId);
        }

        try {
            return $q->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array{level:string,category:string,parent:?string,code:string,name:string,status:string}  $data
     */
    public function create(array $data): int
    {
        $level = trim($data['level']);
        $cat = trim($data['category']);
        $parent = isset($data['parent']) ? trim((string) $data['parent']) : '';
        $code = trim($data['code']);
        $name = trim($data['name']);

        $row = [
            'jbs_jobscope_code' => $code,
            'jbs_job_name' => $name,
            'jbs_level' => $level,
            'jbc_category' => $cat,
            'jbs_job_code_parent' => $parent !== '' ? $parent : null,
            'jbs_status' => $this->statusToDb($data['status'] ?? '0'),
        ];

        /** @var object|null $schema */
        if (Schema::connection('mysql_secondary')->hasColumn('jobscope', 'jbs_job_type')) {
            $row['jbs_job_type'] = $data['job_type'] ?? null;
        }

        return (int) $this->conn()->table('jobscope')->insertGetId($row, 'jbs_id');
    }

    /**
     * @param  array{level:string,category:string,parent:?string,code:string,name:string,status:string}  $data
     */
    public function update(int $id, array $data): void
    {
        $level = trim($data['level']);
        $cat = trim($data['category']);
        $parent = isset($data['parent']) ? trim((string) $data['parent']) : '';
        $code = trim($data['code']);
        $name = trim($data['name']);

        $row = [
            'jbs_jobscope_code' => $code,
            'jbs_job_name' => $name,
            'jbs_level' => $level,
            'jbc_category' => $cat,
            'jbs_job_code_parent' => $parent !== '' ? $parent : null,
            'jbs_status' => $this->statusToDb($data['status'] ?? '0'),
        ];

        if (Schema::connection('mysql_secondary')->hasColumn('jobscope', 'jbs_job_type')) {
            $row['jbs_job_type'] = $data['job_type'] ?? null;
        }

        $this->conn()->table('jobscope')->where('jbs_id', $id)->update($row);
    }

    /**
     * Used by smart-filter UI: distinct categories for filter dropdown.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function categoryFilterOptions(): array
    {
        return $this->categoryOptions();
    }
}
