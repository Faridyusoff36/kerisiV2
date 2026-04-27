<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBudgetPlanningNewRequest;
use App\Http\Traits\ApiResponse;
use App\Models\AccountMain;
use App\Models\ActivityType;
use App\Models\BudgetPlanningDetails;
use App\Models\BudgetPlanningMaster;
use App\Models\BudgetPlanningSchedule;
use App\Models\CostCentre;
use App\Models\FundType;
use App\Models\LookupDetail;
use App\Models\OrganizationUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Budget > Planning > New Application (PAGEID 1236 / MENUID 1516).
 *
 * The legacy JSON dump for this page has no `Business Logic (BL) Details`
 * and the embedded `COMPONENT_JS` is the generic Fund Type stub used as a
 * scaffolding placeholder across the codebase, so this controller is
 * derived from the visible component contract (Planning Info form,
 * Account Activity CSV upload card, Review File table, and the "Leave
 * some remark" popup modal) and from the columns the existing migrated
 * controllers (`BudgetPlanningListController`, `BudgetPlanningSchedule
 * Controller`, `StructureBudgetListController`) already read on
 * `budget_planning_master` / `budget_planning_details`.
 *
 * Endpoints:
 *   - GET  /api/budget/planning-new/options
 *       Lookups for the form: active planning year, PTJ list, cost
 *       centres, fund types, activity types, and Type values from the
 *       VOTTYPE lookup (with safe fallbacks).
 *
 *   - GET  /api/budget/planning-new/accounts
 *       Account candidates for the chosen Fund (joined via
 *       `account_main_fund`). Drives both the CSV template that the
 *       user downloads and the in-page line table.
 *
 *   - POST /api/budget/planning-new
 *       Creates a `budget_planning_master` row with `bpm_status='DRAFT'`
 *       (the "Leave some remark" → Submit confirmation flow only
 *       captures the remark; legacy endorsement / posting workflows are
 *       not part of this page) plus one `budget_planning_details` row
 *       per submitted line.
 *
 * Caveats / not migrated:
 *   - The legacy backend `?fn=generatecsv` endpoint produced a CSV with
 *     pre-filtered chart-of-account rows. We expose the equivalent JSON
 *     feed via `/accounts` and let the SPA build the CSV in-browser to
 *     keep the API consistent with the rest of the codebase (no binary
 *     download endpoints elsewhere).
 *   - The legacy `Reference No` field is generated server-side on save:
 *     `BPN{year}/{bpm_id padded to 6}`. This mirrors the format used by
 *     the duplicate flow in BudgetPlanningListController.
 */
class BudgetPlanningNewController extends Controller
{
    use ApiResponse;

    /**
     * Reasonable fallback Type values when the VOTTYPE lookup is empty.
     * Mirrors the values the existing list pages filter on.
     */
    private const DEFAULT_TYPES = ['YEARLY', 'ALLOCATION 2', 'ALLOCATION 3', 'ONE OFF'];

    public function options(): JsonResponse
    {
        $activeYear = BudgetPlanningSchedule::query()
            ->where('bps_status', 1)
            ->whereNotNull('bps_year_budget')
            ->orderByDesc('bps_year_budget')
            ->value('bps_year_budget');

        $latestYear = BudgetPlanningSchedule::query()
            ->whereNotNull('bps_year_budget')
            ->orderByDesc('bps_year_budget')
            ->value('bps_year_budget');

        $defaultYear = (int) ($activeYear ?? $latestYear ?? date('Y'));

        $ptjs = OrganizationUnit::query()
            ->where('oun_status', 1)
            ->orderBy('oun_code')
            ->get(['oun_code', 'oun_desc'])
            ->map(fn ($r) => [
                'id' => (string) $r->oun_code,
                'label' => $r->oun_code.' — '.($r->oun_desc ?? ''),
                'ounCode' => (string) $r->oun_code,
                'ounDesc' => (string) ($r->oun_desc ?? ''),
            ])
            ->values();

        $costCentres = CostCentre::query()
            ->where('ccr_status', 1)
            ->orderBy('ccr_costcentre')
            ->get(['ccr_costcentre', 'ccr_costcentre_desc', 'oun_code'])
            ->map(fn ($r) => [
                'id' => (string) $r->ccr_costcentre,
                'label' => $r->ccr_costcentre.' — '.($r->ccr_costcentre_desc ?? ''),
                'ccrCostcentre' => (string) $r->ccr_costcentre,
                'ccrCostcentreDesc' => (string) ($r->ccr_costcentre_desc ?? ''),
                'ounCode' => (string) ($r->oun_code ?? ''),
            ])
            ->values();

        $funds = FundType::query()
            ->where('fty_status', 1)
            ->orderBy('fty_fund_type')
            ->get(['fty_fund_type', 'fty_fund_desc'])
            ->map(fn ($r) => [
                'id' => (string) $r->fty_fund_type,
                'label' => $r->fty_fund_type.' — '.($r->fty_fund_desc ?? ''),
            ])
            ->values();

        $activities = ActivityType::query()
            ->where('at_status', 1)
            ->orderBy('at_activity_code')
            ->get(['at_activity_code', 'at_activity_description_bm'])
            ->map(fn ($r) => [
                'id' => (string) $r->at_activity_code,
                'label' => $r->at_activity_code.' — '.($r->at_activity_description_bm ?? ''),
            ])
            ->values();

        $votTypes = LookupDetail::query()
            ->where('lma_code_name', 'VOTTYPE')
            ->where('lde_status', 1)
            ->orderBy('lde_sorting')
            ->orderBy('lde_value')
            ->pluck('lde_description', 'lde_value')
            ->all();

        if (empty($votTypes)) {
            $types = collect(self::DEFAULT_TYPES)
                ->map(fn ($v) => ['id' => $v, 'label' => $v])
                ->values();
        } else {
            $types = collect($votTypes)
                ->map(fn ($desc, $val) => [
                    'id' => (string) $val,
                    'label' => trim((string) ($desc ?: $val)),
                ])
                ->values();
        }

        return $this->sendOk([
            'defaults' => [
                'year' => $defaultYear,
            ],
            'ptjs' => $ptjs,
            'costCentres' => $costCentres,
            'funds' => $funds,
            'activities' => $activities,
            'types' => $types,
        ]);
    }

    /**
     * List candidate account codes for the selected Fund (and optionally
     * filtered by Activity).
     */
    public function accounts(Request $request): JsonResponse
    {
        $fund = (string) $request->input('fund', '');
        if ($fund === '') {
            return $this->sendError(400, 'BAD_REQUEST', 'Fund is required.');
        }

        $activity = trim((string) $request->input('activity', ''));

        $query = AccountMain::query()
            ->from('account_main as AM')
            ->join('account_main_fund as AMF', 'AMF.acm_acct_code', '=', 'AM.acm_acct_code')
            ->where('AMF.fty_fund_type', $fund)
            ->where('AM.acm_acct_status', 1);

        if ($activity !== '') {
            $query->where(function ($w) use ($activity) {
                $w->whereNull('AM.acm_acct_activity')
                    ->orWhere('AM.acm_acct_activity', '')
                    ->orWhere('AM.acm_acct_activity', $activity);
            });
        }

        $rows = $query
            ->select([
                'AM.acm_acct_code',
                'AM.acm_acct_desc',
                'AM.acm_acct_activity',
                'AM.acm_acct_level',
            ])
            ->orderBy('AM.acm_acct_code')
            ->limit(2000)
            ->get();

        $data = $rows->map(fn ($r, $idx) => [
            'index' => $idx + 1,
            'acmAcctCode' => (string) $r->acm_acct_code,
            'acmAcctDesc' => (string) ($r->acm_acct_desc ?? ''),
            'acmAcctActivity' => (string) ($r->acm_acct_activity ?? ''),
            'acmAcctLevel' => $r->acm_acct_level !== null ? (int) $r->acm_acct_level : null,
            'bpdAmt' => 0,
        ])->values();

        return $this->sendOk($data, [
            'fund' => $fund,
            'activity' => $activity,
            'total' => $data->count(),
        ]);
    }

    public function store(StoreBudgetPlanningNewRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userId = (int) ($request->user()->id ?? 0);

        $conn = DB::connection('mysql_secondary');

        return $conn->transaction(function () use ($data, $userId) {
            $nextMasterId = (int) (BudgetPlanningMaster::query()->max('bpm_id') ?? 0) + 1;
            $year = (int) $data['bpm_year'];
            $planningNo = sprintf('BPN%d/%06d', $year, $nextMasterId);

            $totalAmt = collect($data['lines'])->sum(fn ($line) => (float) ($line['bpd_amt'] ?? 0));

            $master = new BudgetPlanningMaster();
            $master->bpm_id = $nextMasterId;
            $master->bpm_planning_no = $planningNo;
            $master->bpm_year = (string) $year;
            $master->bdg_year = (string) $year;
            $master->fty_fund_type = (string) $data['fty_fund_type'];
            $master->at_activity_code = (string) $data['at_activity_code'];
            $master->bpm_oun_code = (string) $data['bpm_oun_code'];
            $master->bpm_ccr_costcentre = (string) $data['bpm_ccr_costcentre'];
            $master->bpm_remark = (string) $data['bpm_remark'];
            $master->bpm_total_amt = $totalAmt;
            $master->bpm_status = 'DRAFT';
            $master->bpm_type = (string) $data['bpm_type'];
            $master->duplicate_count = 0;
            $master->createdby = $userId;
            $master->createddate = now();
            $master->updatedby = $userId;
            $master->updateddate = now();
            $master->save();

            $nextDetailId = (int) (BudgetPlanningDetails::query()->max('bpd_id') ?? 0);
            foreach ($data['lines'] as $line) {
                $nextDetailId++;
                $detail = new BudgetPlanningDetails();
                $detail->bpd_id = $nextDetailId;
                $detail->bpm_id = $nextMasterId;
                $detail->bpm_year = (string) $year;
                $detail->oun_code = (string) $data['bpm_oun_code'];
                $detail->fty_fund_type = (string) $data['fty_fund_type'];
                $detail->ccr_costcentre_budget = (string) $data['bpm_ccr_costcentre'];
                $detail->at_activity_code_budget = (string) $data['at_activity_code'];
                $detail->acm_acct_code = (string) $line['acm_acct_code'];
                $detail->bpd_amt = (float) $line['bpd_amt'];
                $detail->bpd_status = 'DRAFT';
                $detail->createdby = $userId;
                $detail->createddate = now();
                $detail->updatedby = $userId;
                $detail->updateddate = now();
                $detail->save();
            }

            return $this->sendCreated([
                'bpmId' => $master->bpm_id,
                'bpmPlanningNo' => $master->bpm_planning_no,
                'bpmYear' => (int) $master->bpm_year,
                'bpmStatus' => $master->bpm_status,
                'bpmType' => $master->bpm_type,
                'bpmTotalAmt' => (float) $master->bpm_total_amt,
                'lineCount' => count($data['lines']),
                'successMessage' => "Planning record {$master->bpm_planning_no} created.",
            ]);
        });
    }
}
