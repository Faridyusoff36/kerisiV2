<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Legacy FIMS table: budget_planning_details (kerisiv2 schema).
 *
 * Detail rows hanging off `budget_planning_master`.
 */
class BudgetPlanningDetails extends Model
{
    use HasFactory, Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'budget_planning_details';

    protected $primaryKey = 'bpd_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'bpd_id',
        'bpm_id',
        'bpm_year',
        'oun_code',
        'fty_fund_type',
        'ccr_costcentre_budget',
        'at_activity_code_budget',
        'acm_acct_code',
        'bdg_budget_code',
        'bpd_amt',
        'sbg_budget_id',
        'bpd_status',
        'createdby',
        'createddate',
        'updatedby',
        'updateddate',
    ];

    protected function casts(): array
    {
        return [
            'bpd_amt' => 'decimal:2',
            'createddate' => 'datetime',
            'updateddate' => 'datetime',
        ];
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(BudgetPlanningMaster::class, 'bpm_id', 'bpm_id');
    }
}
