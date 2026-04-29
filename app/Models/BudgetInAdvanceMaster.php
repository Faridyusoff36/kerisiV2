<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * `budget_in_advance_master` on the secondary FIMS database.
 *
 * Used for read-only listings (e.g. Budget Advance Controlled, MENUID 2160).
 * Mutations remain in legacy workflow code paths.
 */
class BudgetInAdvanceMaster extends Model
{
    protected $connection = 'mysql_secondary';

    protected $table = 'budget_in_advance_master';

    protected $primaryKey = 'bam_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $casts = [
        'bam_total' => 'float',
        'createddate' => 'datetime',
        'updateddate' => 'datetime',
    ];

    protected $fillable = [
        'bam_id',
        'bam_no',
        'bam_year',
        'bam_endorse_doc',
        'bam_total',
        'bam_status',
        'createddate',
        'updateddate',
        'createdby',
        'updatedby',
    ];
}
