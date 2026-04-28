<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy FIMS table: budget_planning_master (kerisiv2 schema).
 *
 * Master record for the Budget Planning workflow (Yearly / Allocation 2 /
 * Allocation 3 / Dasar Baru / Dasar Sedia Ada). Drives multiple admin
 * pages:
 *   - PAGEID 2056 / MENUID 2506  Dasar Sedia Ada           (BL MM_API_BUDGET_BUDGETPLANNINGLIST)
 *   - PAGEID 2489 / MENUID 3012  Allocation 2 List         (BL MM_API_BUDGET_BUDGETPLANNINGALLO2LIST)
 *   - PAGEID 2490 / MENUID 3013  Allocation 3 List         (BL MM_API_BUDGET_BUDGETPLANNINGALLO3LIST)
 *   - PAGEID 2635 / MENUID 3196  Dasar Baru / One Off      (BL CH9_BUDGET_ONE_OFF)
 *   - PAGEID 2713 / MENUID 3279  Planning to Initial       (BL CH9_PLANNING_TO_INITIAL)
 *   - PAGEID 1236 / MENUID 1516  Planning new application  (form view)
 */
class BudgetPlanningMaster extends Model
{
    use HasFactory, Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'budget_planning_master';

    protected $primaryKey = 'bpm_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'bpm_id',
        'bpm_planning_no',
        'bpm_planning_no_prev',
        'bpm_year',
        'bdg_year',
        'fty_fund_type',
        'at_activity_code',
        'bpm_oun_code',
        'bpm_ccr_costcentre',
        'bpm_remark',
        'bpm_objective',
        'bpm_staffing',
        'bpm_total_amt',
        'bpm_status',
        'bpm_type',
        'bpm_allo_2',
        'bpm_allo_3',
        'bdg_budget_id',
        'sbg_budget_id',
        'acm_acct_code',
        'acm_acct_parent',
        'duplicate_count',
        'bpa_extended_field',
        'createdby',
        'createddate',
        'updatedby',
        'updateddate',
    ];

    protected function casts(): array
    {
        return [
            'bpm_total_amt' => 'decimal:2',
            'bpa_extended_field' => 'array',
            'createddate' => 'datetime',
            'updateddate' => 'datetime',
        ];
    }
}
