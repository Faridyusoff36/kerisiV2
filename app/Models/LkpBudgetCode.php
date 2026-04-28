<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy FIMS table: lkp_budget_code (kerisiv2 schema).
 *
 * Backs the Budget Setup > Budget Code page (PAGEID 1475 / MENUID 1796).
 * Source BL: MM_API_BUDGET_SETUP_BUDGETCODE.
 */
class LkpBudgetCode extends Model
{
    use HasFactory, Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'lkp_budget_code';

    protected $primaryKey = 'lbc_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'lbc_id',
        'lbc_level',
        'lbc_budget_code',
        'lbc_description',
        'lbc_status',
        'createddate',
        'updateddate',
        'createdby',
        'updatedby',
    ];

    protected function casts(): array
    {
        return [
            'createddate' => 'datetime',
            'updateddate' => 'datetime',
        ];
    }
}
