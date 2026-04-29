<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Legacy BL: AS_PHP_BUDGET_UMUM_ALLOCATION_PTJ (Umum Allocation, Expenditure &
 * Balance by PTJ). Heavy SQL with rolling budget_transaction buckets is kept
 * here so the controller stays thin — same rationale as BudgetMonitoringQueryService.
 *
 * Tables live on mysql_secondary (`DB_SECOND_DATABASE`).
 */
class BudgetUmumAllocationPtjQueryService
{
    /**
     * @return array<int, mixed>
     */
    public function bindings(Request $request, ?string $searchOverride = null): array
    {
        $filter2 = '';
        $filter3 = '';
        $filter = '';
        $bindings = [];

        $df = $request->input('date_from');
        $dt = $request->input('date_to');

        if ($df) {
            $filter2 .= " AND DATE(bt.bgt_trans_date) >= STR_TO_DATE(?, '%d/%m/%Y')";
            $bindings[] = (string) $df;
        }
        if ($dt) {
            $filter3 .= " AND DATE(bt.bgt_trans_date) <= STR_TO_DATE(?, '%d/%m/%Y')";
            $bindings[] = (string) $dt;
        }

        $y = $request->input('bdg_year');
        if ($y !== null && $y !== '') {
            $filter .= ' AND b.bdg_year = ? ';
            $bindings[] = (string) $y;
        }

        $oun = $request->input('oun_code');
        if ($oun !== null && $oun !== '') {
            $filter .= ' AND sb.oun_code = ? ';
            $bindings[] = (string) $oun;
        }

        $act = $request->input('at_activity_code');
        if ($act !== null && $act !== '') {
            $filter .= ' AND sb.at_activity_code = ? ';
            $bindings[] = (string) $act;
        }

        $qRaw = $searchOverride !== null ? $searchOverride : (string) $request->input('q', '');
        if ($qRaw !== '') {
            $needle = mb_strtolower($qRaw);
            $needle = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle);
            $bindings[] = '%'.$needle.'%';
        } else {
            $bindings[] = '%';
        }

        return [$filter2, $filter3, $filter, $bindings];
    }

    private function concatLikeExpression(): string
    {
        return "LOWER(CONCAT_WS('__',
					sb.at_activity_code,
					at.at_activity_description_bm,
					sb.oun_code,
					ou.oun_desc,
					sb.ccr_costcentre,
					cc.ccr_costcentre_desc
				)) LIKE ? ";
    }

    private function commonFromSql(string $filter2, string $filter3, string $filter): string
    {
        return "
				FROM budget b 
				JOIN structure_budget sb ON sb.sbg_budget_id = b.sbg_budget_id AND sb.lbc_budget_code = '00000000'
				JOIN lkp_budget_code lbc ON sb.lbc_budget_code = lbc.lbc_budget_code
				JOIN activity_type at ON at.at_activity_code = sb.at_activity_code
				JOIN organization_unit ou ON ou.oun_code = sb.oun_code
				JOIN costcentre cc ON cc.ccr_costcentre = sb.ccr_costcentre
				left join
				(
					SELECT bdg_budget_id, sbg_budget_id,
					sum(commits) commits, sum(request) request, sum(lock_amt) lock_amt, sum(expenses) expenses, sum(increment) increment, sum(decrement) decrement, sum(virement) virement
					FROM(
						SELECT DISTINCT 
							bt.bgt_budget_detl_id,
							bt.bdg_budget_id,
							bt.sbg_budget_id,
							if(bt.bgt_system_id = 'PO',bt.bgt_trans_amt,0) commits,
							if(bt.bgt_system_id IN ('RQUISITION') ,bt.bgt_trans_amt,0) request,
							if(bt.bgt_system_id IN ('LOCK') ,bt.bgt_trans_amt,0) lock_amt,
							if(bt.bgt_system_id IN ('ACCR_PO','ACCR_PR','ACCRUAL','JOURNAL','JOURNAL_PY','RECEIPT','ACTIVITY','INVOICE') ,bt.bgt_trans_amt,0) expenses,
							if(bt.bgt_system_id = 'INCREMENT',bt.bgt_trans_amt,0) increment,
							if(bt.bgt_system_id = 'DECREMENT',bt.bgt_trans_amt,0) decrement,
							if(bt.bgt_system_id = 'VIREMENT',bt.bgt_trans_amt,0) virement
						FROM budget_transaction bt
						where 1=1
						{$filter2}
						{$filter3}
					) w1 GROUP BY 1,2
				)s1 ON (b.bdg_budget_id = s1.bdg_budget_id AND b.sbg_budget_id = s1.sbg_budget_id)
				left join (
					select bdg_budget_id,sum(initial_amt) as initial
					from (
						SELECT bad.bad_detl_id, bad.bdg_budget_id,bad.initial_amt
						FROM budget_allocation_detl bad
						) initial   
					group by bdg_budget_id 
				)s2 on s2.bdg_budget_id=b.bdg_budget_id
				where sb.fty_fund_type = 'E01'
				{$filter}
				AND ".$this->concatLikeExpression().'
				';
    }

    /**
     * @return Collection<int, object>
     */
    public function rows(Request $request, int $page, int $limit): Collection
    {
        [$filter2, $filter3, $filter, $bindings] = $this->bindings($request);
        $offset = max(0, ($page - 1) * $limit);

        $common = $this->commonFromSql($filter2, $filter3, $filter);

        $sql = "
			SELECT 
				sb.at_activity_code,
				at.at_activity_description_bm,
				sb.oun_code,
				ou.oun_desc,
				sb.ccr_costcentre,
				cc.ccr_costcentre_desc,
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) as allocation,
				sum(ifnull(s1.lock_amt,0)) as `Lock`,
				sum(ifnull(s1.request,0)) as `Request`,
				sum(ifnull(s1.commits,0)) as `Commitment`,
				sum(ifnull(s1.expenses,0)) as `Expenses`,
				sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) as `Total_Expenses`,
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) - sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) as balance
			{$common}
			GROUP BY 1,2,3,4,5,6
			ORDER BY sb.at_activity_code
			LIMIT {$offset}, {$limit}
		";

        $rows = DB::connection('mysql_secondary')->select($sql, $bindings);

        return collect($rows);
    }

    public function total(Request $request): int
    {
        [$filter2, $filter3, $filter, $bindings] = $this->bindings($request);
        $common = $this->commonFromSql($filter2, $filter3, $filter);

        $sql = "SELECT COUNT(*) AS C FROM (Select 
				sb.at_activity_code,
				at.at_activity_description_bm,
				sb.oun_code,
				ou.oun_desc,
				sb.ccr_costcentre,
				cc.ccr_costcentre_desc,
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) as allocation,
				sum(ifnull(s1.lock_amt,0)) as `Lock`, 
				sum(ifnull(s1.request,0)) as `Request`, 
				sum(ifnull(s1.commits,0)) as `Commitment`,
				sum(ifnull(s1.expenses,0)) as `Expenses`,
				sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) as `Total_Expenses`,
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) - sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) as balance
				{$common}
				GROUP BY 1,2,3,4,5,6
			) tbl ";

        $r = DB::connection('mysql_secondary')->selectOne($sql, $bindings);

        return (int) ($r->C ?? 0);
    }

    /**
     * @return array<string, float|null>
     */
    public function footer(Request $request): array
    {
        [$filter2, $filter3, $filter, $bindings] = $this->bindings($request);
        $common = $this->commonFromSql($filter2, $filter3, $filter);

        $sql = "SELECT
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) G,
				sum(ifnull(s1.lock_amt,0)) H,
				sum(ifnull(s1.request,0)) I, 
				sum(ifnull(s1.commits,0)) J,
				sum(ifnull(s1.expenses,0)) K,
				sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) L,
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) - sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) M
			{$common}";

        $grand = DB::connection('mysql_secondary')->selectOne($sql, $bindings);
        if (! $grand) {
            return [
                'allocation' => null,
                'lock' => null,
                'request' => null,
                'commitment' => null,
                'expenses' => null,
                'total_expenses' => null,
                'balance' => null,
            ];
        }

        return [
            'allocation' => isset($grand->G) ? (float) $grand->G : null,
            'lock' => isset($grand->H) ? (float) $grand->H : null,
            'request' => isset($grand->I) ? (float) $grand->I : null,
            'commitment' => isset($grand->J) ? (float) $grand->J : null,
            'expenses' => isset($grand->K) ? (float) $grand->K : null,
            'total_expenses' => isset($grand->L) ? (float) $grand->L : null,
            'balance' => isset($grand->M) ? (float) $grand->M : null,
        ];
    }

    /**
     * All matching rows without LIMIT (CSV / Excel exports).
     *
     * @return Collection<int, object>
     */
    public function allFiltered(Request $request): Collection
    {
        [$filter2, $filter3, $filter, $bindings] = $this->bindings($request);
        $common = $this->commonFromSql($filter2, $filter3, $filter);

        $sql = "
			SELECT 
				sb.at_activity_code,
				at.at_activity_description_bm,
				sb.oun_code,
				ou.oun_desc,
				sb.ccr_costcentre,
				cc.ccr_costcentre_desc,
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) as allocation,
				sum(ifnull(s1.lock_amt,0)) as `Lock`,
				sum(ifnull(s1.request,0)) as `Request`,
				sum(ifnull(s1.commits,0)) as `Commitment`,
				sum(ifnull(s1.expenses,0)) as `Expenses`,
				sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) as `Total_Expenses`,
				sum(ifnull(s2.initial,0) + ifnull(s1.increment,0) + ifnull(s1.decrement,0) + ifnull(s1.virement,0)) - sum(ifnull(s1.commits,0) + ifnull(s1.lock_amt,0) + ifnull(s1.request,0) + ifnull(s1.expenses,0)) as balance
			{$common}
			GROUP BY 1,2,3,4,5,6
			ORDER BY sb.at_activity_code
		";

        return collect(DB::connection('mysql_secondary')->select($sql, $bindings));
    }
}
