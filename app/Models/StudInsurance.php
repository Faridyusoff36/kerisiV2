<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FIMS `stud_insurance` (DB_SECOND_DATABASE) — student policy rows joined from
 * {@see Student} for Insurance list screens (PAGE_MENUID1019_LEVEL3).
 */
class StudInsurance extends Model
{
    use Auditable, HasFactory;

    protected $connection = 'mysql_secondary';

    protected $table = 'stud_insurance';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'std_student_id',
        'sin_ins_policy_no',
        'vcs_vendor_code_insuran',
    ];
}
