<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Legacy V2_BUDGET_SUMMARY_API (FLC BL `V2_BUDGET_SUMMARY_API`) dt_listing.
 *
 * Mirrors `getList()` behaviour against `mysql_secondary` (`DB_SECOND_DATABASE`).
 * Activity subgroup/group filters applied on joined `activity_type` columns (legacy
 * referred to unprefixed `activity_*` on the budget predicate; ACT is safer).
 */
class BudgetV2BudgetSummaryQueryService
{
    private const EXCLUDED_TASK = ['ERROR'];

    /**
     * @return array{rows: Collection<int, object>, aggregate_expenses_percent: string|null}
     */
    public function listing(Request $request): array
    {
        $conn = DB::connection('mysql_secondary');

        $whereBgt = '';
        $whereSbg = '';
        $whereAct = '';
        $params = [];

        if ($request->filled('bgt_trans_date_from')) {
            $whereBgt .= ' AND bgt.bgt_trans_date >= STR_TO_DATE(?, \'%d/%m/%Y\')';
            $params[] = $request->input('bgt_trans_date_from');
        }
        if ($request->filled('bgt_trans_date_to')) {
            $whereBgt .= ' AND bgt.bgt_trans_date <= STR_TO_DATE(?, \'%d/%m/%Y %H:%i:%s\')';
            $params[] = $request->input('bgt_trans_date_to').' 23:59:59';
        }
        if ($request->filled('oun_code')) {
            $whereSbg .= ' AND sbg.oun_code IN (
                SELECT oun_code FROM organization_unit ou
                WHERE oun_code_parent = ?
            )';
            $params[] = $request->input('oun_code');
        }
        if ($request->filled('fty_fund_type')) {
            $whereSbg .= ' AND sbg.fty_fund_type = ?';
            $params[] = $request->input('fty_fund_type');
        }
        if ($request->filled('ccr_costcentre')) {
            $whereSbg .= ' AND sbg.ccr_costcentre = ?';
            $params[] = $request->input('ccr_costcentre');
        }
        if ($request->filled('cpa_project_no')) {
            $whereSbg .= ' AND sbg.cpa_project_no = ?';
            $params[] = $request->input('cpa_project_no');
        }

        $whereBdg = '';
        if ($request->filled('bdg_year')) {
            $whereBdg .= ' AND bdg.bdg_year = ?';
            $params[] = $request->input('bdg_year');
        }

        $tg = trim((string) $request->input('tf_activity_group', ''));
        $ts = trim((string) $request->input('tf_activity_subgroup', ''));
        if ($tg !== '' && $ts !== '') {
            $whereAct .= ' AND act.activity_group_code = ? AND act.activity_subgroup_code = ?';
            $params[] = $tg;
            $params[] = $ts;
        } elseif ($tg !== '') {
            $whereAct .= ' AND act.activity_group_code = ?';
            $params[] = $tg;
        } elseif ($ts !== '') {
            $whereAct .= ' AND act.activity_subgroup_code = ?';
            $params[] = $ts;
        }

        $excluded = implode("','", self::EXCLUDED_TASK);

        $joinBgt = "
        SELECT 
            bdg_budget_id, 
            sbg_budget_id,
            CASE WHEN bgt_system_id = 'VIREMENT' THEN IFNULL(bgt_trans_amt,0) ELSE 0 END virement,
            CASE WHEN bgt_system_id IN ('INCREMENT','DECREMENT') THEN IFNULL(bgt_trans_amt,0) ELSE 0 END additional,
            CASE WHEN bgt_system_id = 'REVENUE' THEN IFNULL(bgt_trans_amt,0) ELSE 0 END topup,
            CASE WHEN bgt_system_id = 'PRE_REQ' THEN IFNULL(bgt_trans_amt,0) ELSE 0 END pre_request,
            CASE WHEN bgt_system_id = 'RQUISITION' THEN IFNULL(bgt_trans_amt,0) ELSE 0 END request,
            CASE WHEN bgt_system_id = 'PO' THEN IFNULL(bgt_trans_amt,0) ELSE 0 END commit,
            CASE WHEN bgt_system_id IN ('LOCK','JNL_ENTRY') THEN IFNULL(bgt_trans_amt,0) ELSE 0 END locked,
            CASE WHEN bgt_system_id IN ('ACCR_PO','ACCR_PR','ACCRUAL','ACTIVITY','JOURNAL','JOURNAL_PY','RECEIPT','INVOICE','VOUCHER_PY','IMPREST','VOUCHER','TT','BILL_CN') THEN IFNULL(bgt_trans_amt,0) ELSE 0 END expenses
        FROM budget_transaction bgt
        WHERE 1 = 1
        AND bgt.bgt_task_id NOT IN ('$excluded')
        $whereBgt";

        $joinSbg = "
        SELECT
            bdg.bdg_budget_id,
            bdg.sbg_budget_id,
            sbg.fty_fund_type,
            sbg.at_activity_code,
            act.at_activity_description_bm,
            sbg.oun_code,
            oun.oun_desc,
            sbg.ccr_costcentre,
            ccr.ccr_costcentre_desc,
            sbg.cpa_project_no,
            fty_fund_desc,
            lbc_budget_code,
            acm.acm_acct_code,
            acm.acm_acct_desc,
            IFNULL(bdg_bal_carryforward,0) opening,
            IFNULL(bdg_initial_amt,0) initial,
            SUM(IFNULL(virement,0)) virement,
            SUM(IFNULL(additional,0)) additional,
            SUM(IFNULL(topup,0)) topup,
            SUM(IFNULL(pre_request,0)) pre_request,
            SUM(IFNULL(request,0)) request,
            SUM(IFNULL(commit,0)) commit,
            SUM(IFNULL(locked,0)) locked,
            SUM(IFNULL(expenses,0)) expenses
        FROM budget bdg
            LEFT JOIN ($joinBgt) bgt
                ON bdg.bdg_budget_id = bgt.bdg_budget_id
                AND bdg.sbg_budget_id = bgt.sbg_budget_id
            INNER JOIN structure_budget sbg ON sbg.sbg_budget_id = bdg.sbg_budget_id
            {$whereSbg}
            LEFT JOIN costcentre ccr ON ccr.ccr_costcentre = sbg.ccr_costcentre
            LEFT JOIN organization_unit oun ON oun.oun_code = sbg.oun_code
            LEFT JOIN fund_type fty ON fty.fty_fund_type = sbg.fty_fund_type
            LEFT JOIN activity_type act ON act.at_activity_code = sbg.at_activity_code
            LEFT JOIN account_main acm ON acm.acm_acct_code = sbg.lbc_budget_code
        WHERE bdg_status IN ('05','APPROVED','APPROVE')
        {$whereBdg}
        {$whereAct}
        GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16
        ORDER BY acm.acm_acct_code ASC
        ";

        $sql = "
        SELECT
            1 AS indexing,
            x.lbc_budget_code AS acct_code,
            MAX(x.acm_acct_desc) AS acm_acct_desc,
            MAX(CONCAT_WS(' - ', x.oun_code, x.oun_desc)) AS PTJ,
            MAX(CONCAT(x.ccr_costcentre, ' - ', x.ccr_costcentre_desc)) AS costcentre,
            MAX(CONCAT(x.ccr_costcentre, ' - ', x.ccr_costcentre_desc)) AS cost_centre_dup,
            MAX(x.at_activity_description_bm) AS description,
            MAX(CONCAT_WS(' - ', x.fty_fund_type, x.fty_fund_desc)) AS fund_type_display,
            MAX(x.cpa_project_no) AS project_no,
            SUM(IFNULL(x.initial,0)) AS initial,
            SUM(IFNULL(x.virement,0)) AS virement,
            SUM(IFNULL(x.additional,0)) AS additional,
            SUM(IFNULL(x.topup,0)) AS topup,
            SUM(IFNULL(x.opening,0)) AS opening,
            SUM(IFNULL(x.request,0)) AS request,
            SUM(IFNULL(x.commit,0)) AS commit,
            SUM(IFNULL(x.expenses,0)) AS expenses,
            SUM(IFNULL(x.locked,0)) AS locked,
            SUM(IFNULL(x.pre_request,0)) AS pre_request,
            (SUM(IFNULL(x.expenses,0)) / NULLIF(
                SUM(IFNULL(x.opening,0)) + SUM(IFNULL(x.initial,0))
                + SUM(IFNULL(x.virement,0)) + SUM(IFNULL(x.additional,0)), 0
            )) * 100 AS percent_raw
        FROM ($joinSbg) x
        WHERE x.ccr_costcentre IS NOT NULL
        AND (
            x.opening <> 0 OR x.initial <> 0 OR x.virement <> 0 OR x.additional <> 0
            OR x.locked <> 0 OR x.pre_request <> 0 OR x.request <> 0 OR x.commit <> 0
            OR x.expenses <> 0
        )
        GROUP BY x.lbc_budget_code";

        /** @var list<object>|array<object> */
        $raw = $conn->select($sql, $params);

        $tOpening = 0;
        $tInitial = 0;
        $tVirement = 0;
        $tIncDec = 0;
        $tExpenses = 0;

        $out = collect($raw)->map(function (object $d) use (
            &$tOpening,
            &$tInitial,
            &$tVirement,
            &$tIncDec,
            &$tExpenses
        ): object {
            $opening = (float) ($d->opening ?? 0);
            $initial = (float) ($d->initial ?? 0);
            $virement = (float) ($d->virement ?? 0);
            $additional = (float) ($d->additional ?? 0);
            $topup = (float) ($d->topup ?? 0);
            $preRequest = (float) ($d->pre_request ?? 0);
            $request = (float) ($d->request ?? 0);
            $commit = (float) ($d->commit ?? 0);
            $locked = (float) ($d->locked ?? 0);
            $expenses = (float) ($d->expenses ?? 0);

            $allocated = $initial + $virement + $additional + $topup + $opening;
            $whatever = $locked + $preRequest + $request + $commit + $expenses;
            $balance = $allocated - $whatever;
            $total = $request + $commit + $expenses;
            $allocNonZero = $allocated != 0.0;
            $expensesPercent = $allocNonZero
                ? number_format($total / $allocated, 6, '.', '')
                : '0.000000';

            $tOpening += $opening;
            $tInitial += $initial;
            $tVirement += $virement;
            $tIncDec += $additional;
            $tExpenses += $expenses;

            $d->allocated = $allocated;
            $d->balance = $balance;
            $d->total = $total;
            $d->expenses_percent = $expensesPercent;
            unset($d->percent_raw);

            return $d;
        });

        $den = $tOpening + $tInitial + $tVirement + $tIncDec;
        $aggPct = $den > 0
            ? number_format(($tExpenses / $den) * 100, 2, '.', '')
            : null;

        return [
            'rows' => $out,
            'aggregate_expenses_percent' => $aggPct,
        ];
    }
}
