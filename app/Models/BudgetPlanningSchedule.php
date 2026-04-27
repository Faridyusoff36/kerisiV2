<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy FIMS table: budget_planning_schedule (kerisiv2 schema).
 *
 * Backs the Budget Setup > Budget Planning Schedule page
 * (PAGEID 2872 / MENUID 3456). Source BL: SNA_API_BUDGET_SETUP_BDGPLANNINGSCHEDULE.
 */
class BudgetPlanningSchedule extends Model
{
    use HasFactory, Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'budget_planning_schedule';

    protected $primaryKey = 'bps_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'bps_id',
        'bps_year_budget',
        'bps_plan_startDate',
        'bps_plan_endDate',
        'bps_status',
        'createddate',
        'createdby',
        'updateddate',
        'updatedby',
    ];

    protected function casts(): array
    {
        return [
            'bps_plan_startDate' => 'date',
            'bps_plan_endDate' => 'date',
            'createddate' => 'datetime',
            'updateddate' => 'datetime',
        ];
    }
}
