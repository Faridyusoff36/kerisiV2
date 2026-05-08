<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\CcontrollerMaster;
use App\Models\CcontrollerReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Credit Control — Successful Generated Reminder (PAGEID 2213 / MENUID 2669).
 * Legacy: SNA_API_CREDITCONTROL_REMINDERSTATUS.
 */
class CreditControlReminderStatusController extends Controller
{
    use ApiResponse;

    public function debtorCreditorTypes(): JsonResponse
    {
        $rows = CcontrollerMaster::query()
            ->select('cm_debtor_creditor')
            ->whereNotIn('cm_debtor_creditor', ['CREDITOR', 'PELAJAR', 'STAF'])
            ->whereNotNull('cm_debtor_creditor')
            ->where('cm_debtor_creditor', '!=', '')
            ->distinct()
            ->orderBy('cm_debtor_creditor')
            ->pluck('cm_debtor_creditor')
            ->values()
            ->all();

        return $this->sendOk(array_map(fn ($v) => ['id' => $v, 'label' => $v], $rows));
    }

    public function businessTypes(Request $request): JsonResponse
    {
        $type = (string) $request->input('type', '');
        if ($type === '') {
            return $this->sendError(400, 'BAD_REQUEST', 'type is required');
        }

        $rows = CcontrollerMaster::query()
            ->select('cm_business_type')
            ->whereIn('cm_business_type', ['INVOICE', 'BOC', 'LOAN', 'ACTIVITYADV'])
            ->where('cm_debtor_creditor', $type)
            ->whereNotNull('cm_business_type')
            ->distinct()
            ->orderBy('cm_business_type')
            ->pluck('cm_business_type')
            ->values()
            ->all();

        return $this->sendOk(array_map(fn ($v) => ['id' => $v, 'label' => $v], $rows));
    }

    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));
        $q = trim((string) $request->input('q', ''));
        $type = (string) $request->input('type', '');
        $businessType = (string) $request->input('business_type', '');
        $sortBy = (string) $request->input('sort_by', 'crm_reminder_date');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($type === '' || $businessType === '') {
            return $this->sendError(400, 'BAD_REQUEST', 'type and business_type are required');
        }

        $sortable = [
            'crm_reminder_date' => 'cr.crm_reminder_date',
            'crm_debtor_id' => 'cr.crm_debtor_id',
            'crm_debtor_name' => 'cr.crm_debtor_name',
            'crm_amt_outstanding' => 'cr.crm_amt_outstanding',
        ];
        $orderCol = $sortable[$sortBy] ?? 'cr.crm_reminder_date';

        $loanCat = "JSON_UNQUOTE(JSON_EXTRACT(cr.crm_extended_field, '$.loan_category'))";

        $categoryExpr = DB::raw("CASE
            WHEN cm.cm_business_type = 'INVOICE' THEN 'OUTSTANDING'
            WHEN cm.cm_business_type = 'BOC' THEN ''
            WHEN cm.cm_business_type = 'LOAN' THEN CASE
                WHEN {$loanCat} = 'C' THEN 'COMPUTER / SMARTPHONE'
                WHEN {$loanCat} = 'V' THEN 'VEHICLE'
                WHEN {$loanCat} = 'H' THEN 'HOUSING'
                END
            WHEN cm.cm_business_type = 'ACTIVITYADV' THEN 'OUTSTANDING'
            END AS category_label");

        $loanoExpr = DB::raw("CASE
            WHEN cm.cm_business_type = 'BOC' THEN ssmin.ss_loan_id
            WHEN cm.cm_business_type = 'LOAN' THEN la.lap_loan_no
            ELSE ''
            END AS loano");

        $noinvExpr = DB::raw("CASE
            WHEN cm.cm_business_type IN ('INVOICE', 'ACTIVITYADV') THEN cr.crm_invoice_no
            ELSE ''
            END AS noinv");

        $ssMin = DB::connection('mysql_secondary')->table('staff_study')
            ->select('stf_staff_id', DB::raw('MIN(ss_loan_id) AS ss_loan_id'))
            ->whereNotNull('ss_loan_id')
            ->groupBy('stf_staff_id');

        $base = CcontrollerReminder::query()
            ->from('ccontroller_reminder AS cr')
            ->join('ccontroller_master AS cm', 'cm.cm_id', '=', 'cr.cm_id')
            ->leftJoinSub($ssMin, 'ssmin', function ($join) {
                $join->on('ssmin.stf_staff_id', '=', 'cr.crm_debtor_id');
            })
            ->leftJoin('loan_application AS la', function ($join) use ($loanCat) {
                $join->on('la.stf_staff_id', '=', 'cr.crm_debtor_id')
                    ->whereRaw("la.lap_loan_type = {$loanCat}");
            })
            ->where('cr.crm_confirm_status', 'Y')
            ->where('cm.cm_debtor_creditor', $type)
            ->where('cm.cm_business_type', $businessType);

        if ($q !== '') {
            $like = $this->likeEscape(mb_strtolower($q, 'UTF-8'));
            $base->whereRaw(
                "LOWER(CONCAT_WS('__',
                    IFNULL(cr.crm_id,''),
                    IFNULL(cr.crm_debtor_id,''),
                    IFNULL(cr.crm_debtor_name,''),
                    IFNULL(cr.crm_amt_outstanding,''),
                    IFNULL(cr.crm_reminder_bil,''),
                    IFNULL(DATE_FORMAT(cr.crm_reminder_date, '%d/%m/%Y'),''),
                    IFNULL(cr.crm_handphone_no,''),
                    IFNULL(cr.crm_reference,''),
                    IFNULL({$loanCat},''),
                    IFNULL(cr.crm_email_addr,''),
                    IFNULL(cm.cm_business_type,''),
                    IFNULL(cr.crm_invoice_no,'')
                )) LIKE ?",
                [$like]
            );
        }

        $total = (clone $base)->count();

        $rows = (clone $base)
            ->select([
                'cr.crm_id AS seqid',
                'cr.crm_debtor_id AS debtorid',
                'cr.crm_debtor_name AS debtorname',
                'cr.crm_amt_outstanding AS outstandingamt',
                'cr.crm_reminder_bil AS reminderbill',
                'cr.crm_reminder_date AS reminderdate',
                'cr.crm_reference AS referenceno',
                'cm.cm_business_type AS type2',
                $categoryExpr,
                $loanoExpr,
                $noinvExpr,
            ])
            ->orderBy($orderCol, $sortDir)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = $rows->values()->map(fn ($r, int $i) => [
            'seqid' => (int) $r->seqid,
            'debtorid' => $r->debtorid,
            'debtorname' => $r->debtorname,
            'type2' => $r->type2,
            'category' => $r->category_label,
            'loano' => $r->loano,
            'noinv' => $r->noinv,
            'outstandingAmt' => $r->outstandingamt !== null ? (float) $r->outstandingamt : null,
            'reminderBill' => $r->reminderbill,
            'referenceNo' => $r->referenceno,
            'reminderDate' => $r->reminderdate,
            'index' => ($page - 1) * $limit + $i + 1,
        ]);

        return $this->sendOk($data, [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => (int) ceil($total / max(1, $limit)),
        ]);
    }

    private function likeEscape(string $needle): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle).'%';
    }
}
