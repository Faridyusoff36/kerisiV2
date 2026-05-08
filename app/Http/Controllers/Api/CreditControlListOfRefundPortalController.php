<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\TempRefundApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * List of Refund Application / Portal (MENUID **2604**); Admin "List of Refund" (**2287**)
 * reuses this API in Kerisi 2.0.
 *
 * Legacy: {@see SNA_API_LIST_OF_REFUND_CC_STAFF} (`ListOfRefund` datatable).
 * Submit / state transition aligns with {@see SNA_API_CREDITCONTROL_REQUESTREFUNDSTAFF}
 * (`process_details` batch concept + `MM_API_LIST_OF_REFUND_CC_STUDENT` `checkReject`).
 */
class CreditControlListOfRefundPortalController extends Controller
{
    use ApiResponse;

    private const SORTABLE = [
        'application_no' => 'tra.tra_application_no',
        'id' => 'tra.vcs_vendor_code',
        'name' => 'tra.tra_vendor_name',
        'account_code' => 'tra.acm_acct_code',
        'reference_no' => 'tra.tra_ref_no',
        'application_date' => 'tra.createddate',
        'amount_eligible_refund' => 'tra.tra_amt_refund',
        'amount' => 'tra.tra_amt',
        'status' => 'tra.tra_status',
    ];

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $sortBy = (string) $request->input('sort_by', 'application_no');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $orderCol = self::SORTABLE[$sortBy] ?? 'tra.tra_application_no';

        $base = $this->scopedBaseQuery($request);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(tra.tra_id,''),
                    IFNULL(tra.tra_application_no,''),
                    IFNULL(tra.vcs_vendor_code,''),
                    IFNULL(tra.tra_vendor_name,''),
                    IFNULL(tra.acm_acct_code,''),
                    IFNULL(am.acm_acct_desc,''),
                    IFNULL(tra.tra_ref_no,''),
                    IFNULL(tra.tra_ref_no_note,''),
                    IFNULL(DATE_FORMAT(tra.createddate, '%d/%m/%Y'),''),
                    IFNULL(tra.tra_status,''),
                    IFNULL(tra.dpm_deposit_no,''),
                    IFNULL(tra.tra_reason_reject,''),
                    IFNULL(tra.tra_amt,''),
                    IFNULL(tra.tra_amt_refund,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();

        $refExpr = "TRIM(CONCAT(IFNULL(tra.tra_ref_no,''), IF(tra.tra_ref_no IS NOT NULL AND tra.tra_ref_no_note IS NOT NULL AND tra.tra_ref_no_note != '', ' - ', ''), IFNULL(tra.tra_ref_no_note,'')))";

        $select = [
            'tra.tra_id',
            'tra.tra_application_no',
            'tra.vcs_vendor_code',
            'tra.tra_vendor_name',
            'tra.acm_acct_code',
            'tra.tra_ref_no',
            'tra.tra_ref_no_note',
            DB::raw("{$refExpr} AS reference_no"),
            DB::raw('DATE_FORMAT(tra.createddate, \'%d/%m/%Y\') AS application_date_disp'),
            'tra.createddate',
            'tra.tra_amt',
            'tra.tra_amt_refund',
            'tra.tra_status',
            'tra.tra_reason_reject',
            'tra.dpm_deposit_no',
            DB::raw('CASE WHEN am.acm_acct_desc IS NOT NULL AND am.acm_acct_desc != \'\' THEN CONCAT(tra.acm_acct_code, \' - \', am.acm_acct_desc) ELSE tra.acm_acct_code END AS account_label'),
        ];
        if (Schema::connection('mysql_secondary')->hasColumn('temp_refund_application', 'dz_path')) {
            $select[] = 'tra.dz_path';
        }

        $rows = (clone $base)
            ->select($select)
            ->orderBy($orderCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(function ($r, int $i) use ($page, $limit) {
            $dzRaw = isset($r->dz_path) && $r->dz_path !== null && (string) $r->dz_path !== ''
                ? (string) $r->dz_path
                : null;

            return [
                'index' => ($page - 1) * $limit + $i + 1,
                'traId' => (int) $r->tra_id,
                'applicationNo' => $r->tra_application_no,
                'id' => $r->vcs_vendor_code,
                'name' => $r->tra_vendor_name,
                'accountCode' => $r->acm_acct_code,
                'accountLabel' => $r->account_label,
                'referenceNo' => $r->reference_no !== '' ? $r->reference_no : null,
                'applicationDate' => $r->application_date_disp,
                'amountEligibleRefund' => $r->tra_amt_refund !== null ? (float) $r->tra_amt_refund : null,
                'traAmt' => $r->tra_amt !== null ? (float) $r->tra_amt : null,
                'status' => $r->tra_status,
                'remark' => $r->tra_reason_reject,
                'dzPath' => $dzRaw,
                'supportingDocumentUrl' => $this->resolveSupportingDocumentUrl($dzRaw),
                'dpmDepositNo' => $r->dpm_deposit_no,
                'reportUrl' => '/admin/kerisi/m/2604?traId='.$r->tra_id,
            ];
        });

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    /**
     * Legacy MM_API_LIST_OF_REFUND_CC_STUDENT `checkReject`: distinct application count for selection.
     */
    public function submitCheck(Request $request): JsonResponse
    {
        $ids = $this->normalizeTraIds($request->input('tra_ids'));
        if ($ids === []) {
            return $this->sendError(400, 'BAD_REQUEST', 'tra_ids is required.');
        }

        $allowed = $this->allowedTraIdsForUser($request, $ids);
        if (count($allowed) !== count(array_unique($ids))) {
            return $this->sendError(400, 'BAD_REQUEST', 'One or more rows are not visible for your scope.');
        }

        $distinct = TempRefundApplication::query()
            ->whereIn('tra_id', $allowed)
            ->whereNotNull('tra_application_no')
            ->distinct()
            ->pluck('tra_application_no')
            ->filter()
            ->values();

        return $this->sendOk([
            'ok' => $distinct->count() === 1,
            'distinct_application_count' => $distinct->count(),
            'tra_application_no' => $distinct->count() === 1 ? $distinct->first() : null,
        ]);
    }

    /**
     * Batch submit: move rows into staff draft processing (legacy `process_details` fields).
     */
    public function submit(Request $request): JsonResponse
    {
        $ids = $this->normalizeTraIds($request->input('tra_ids'));
        if ($ids === []) {
            return $this->sendError(400, 'BAD_REQUEST', 'tra_ids is required.');
        }

        $allowed = $this->allowedTraIdsForUser($request, $ids);
        if (count($allowed) !== count(array_unique($ids))) {
            return $this->sendError(400, 'BAD_REQUEST', 'One or more rows are not visible for your scope.');
        }

        $rows = TempRefundApplication::query()
            ->whereIn('tra_id', $allowed)
            ->get(['tra_id', 'tra_application_no', 'tra_status']);

        $appNos = $rows->pluck('tra_application_no')->filter()->unique()->values();
        if ($appNos->count() !== 1) {
            return $this->sendError(
                400,
                'BAD_REQUEST',
                'Selected rows must belong to exactly one application number.',
                ['distinct_application_count' => $appNos->count()],
            );
        }

        foreach ($rows as $r) {
            if ((string) $r->tra_status !== 'APPLY') {
                return $this->sendError(400, 'BAD_REQUEST', 'Only APPLY rows can be submitted.', ['tra_id' => $r->tra_id]);
            }
        }

        $userStamp = $this->auditUserStamp($request);

        DB::connection('mysql_secondary')->transaction(function () use ($allowed, $userStamp) {
            $update = [
                'tra_status' => '1',
                'tra_process' => 'Y',
                'tra_status_process' => 'DRAFT',
            ];
            if (Schema::connection('mysql_secondary')->hasColumn('temp_refund_application', 'updateddate')) {
                $update['updateddate'] = Carbon::now();
            }
            if (Schema::connection('mysql_secondary')->hasColumn('temp_refund_application', 'updatedby')) {
                $update['updatedby'] = $userStamp;
            }
            TempRefundApplication::query()->whereIn('tra_id', $allowed)->update($update);
        });

        return $this->sendOk([
            'updated' => count($allowed),
            'tra_application_no' => $appNos->first(),
        ]);
    }

    /**
     * Staff reject: mark APPLY portal lines as REJECT with reason (legacy portal workflow).
     */
    public function reject(Request $request): JsonResponse
    {
        $ids = $this->normalizeTraIds($request->input('tra_ids'));
        if ($ids === []) {
            return $this->sendError(400, 'BAD_REQUEST', 'tra_ids is required.');
        }

        $remark = trim((string) $request->input('remark', ''));
        if ($remark === '') {
            return $this->sendError(400, 'BAD_REQUEST', 'remark is required for rejection.');
        }

        $allowed = $this->allowedTraIdsForUser($request, $ids);
        if (count($allowed) !== count(array_unique($ids))) {
            return $this->sendError(400, 'BAD_REQUEST', 'One or more rows are not visible for your scope.');
        }

        $rows = TempRefundApplication::query()
            ->whereIn('tra_id', $allowed)
            ->get(['tra_id', 'tra_application_no', 'tra_status']);

        $appNos = $rows->pluck('tra_application_no')->filter()->unique()->values();
        if ($appNos->count() !== 1) {
            return $this->sendError(
                400,
                'BAD_REQUEST',
                'Selected rows must belong to exactly one application number.',
                ['distinct_application_count' => $appNos->count()],
            );
        }

        foreach ($rows as $r) {
            if ((string) $r->tra_status !== 'APPLY') {
                return $this->sendError(400, 'BAD_REQUEST', 'Only APPLY rows can be rejected.', ['tra_id' => $r->tra_id]);
            }
        }

        $userStamp = $this->auditUserStamp($request);

        DB::connection('mysql_secondary')->transaction(function () use ($allowed, $remark, $userStamp) {
            $update = [
                'tra_status' => 'REJECT',
                'tra_reason_reject' => $remark,
            ];
            if (Schema::connection('mysql_secondary')->hasColumn('temp_refund_application', 'updateddate')) {
                $update['updateddate'] = Carbon::now();
            }
            if (Schema::connection('mysql_secondary')->hasColumn('temp_refund_application', 'updatedby')) {
                $update['updatedby'] = $userStamp;
            }
            TempRefundApplication::query()->whereIn('tra_id', $allowed)->update($update);
        });

        return $this->sendOk([
            'updated' => count($allowed),
            'tra_application_no' => $appNos->first(),
        ]);
    }

    /**
     * @return Builder
     */
    private function scopedBaseQuery(Request $request): Builder
    {
        $base = TempRefundApplication::query()
            ->from('temp_refund_application AS tra')
            ->join('refund_prefix_setup AS rps', 'rps.acm_acct_code', '=', 'tra.acm_acct_code')
            ->leftJoin('account_main AS am', 'am.acm_acct_code', '=', 'tra.acm_acct_code')
            ->whereNotNull('tra.tra_application_no')
            ->where('tra.tra_status', 'APPLY')
            ->whereRaw('(tra.tra_process IS NULL OR tra.tra_process <> ?)', ['Y'])
            ->whereNull('tra.tra_status_process')
            ->where('tra.tra_payto_type', 'B');

        $conn = 'mysql_secondary';
        if (Schema::connection($conn)->hasColumn('refund_prefix_setup', 'rps_payto_type')) {
            $base->where('rps.rps_payto_type', 'B');
        }

        $csv = trim((string) env('REFUND_PORTAL_RPS_GROUP_CODES', ''));
        if ($csv !== '' && Schema::connection($conn)->hasColumn('refund_prefix_setup', 'rps_group_desc')) {
            $groups = array_values(array_filter(array_map('trim', explode(',', $csv))));
            if ($groups !== []) {
                $base->whereIn('rps.rps_group_desc', $groups);
            }
        }

        return $base;
    }

    /**
     * @param  list<int>  $ids
     * @return list<int>
     */
    private function allowedTraIdsForUser(Request $request, array $ids): array
    {
        $unique = array_values(array_unique(array_filter($ids)));

        return $this->scopedBaseQuery($request)
            ->whereIn('tra.tra_id', $unique)
            ->pluck('tra.tra_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    /**
     * @return list<int>
     */
    private function normalizeTraIds(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $v) {
            $n = (int) $v;
            if ($n > 0) {
                $out[] = $n;
            }
        }

        return $out;
    }

    private function auditUserStamp(Request $request): string
    {
        $u = $request->user();
        if ($u === null) {
            return 'system';
        }

        return (string) ($u->email ?? $u->name ?? 'user:'.$u->getAuthIdentifier());
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }

    private function resolveSupportingDocumentUrl(?string $dzPath): ?string
    {
        if ($dzPath === null || trim($dzPath) === '') {
            return null;
        }
        $t = trim($dzPath);
        if (preg_match('#^https?://#i', $t) === 1) {
            return $t;
        }
        $base = rtrim((string) env('REFUND_SUPPORTING_DOC_BASE_URL', ''), '/');
        if ($base !== '') {
            return $base.'/'.ltrim($t, '/');
        }

        return $t;
    }
}
