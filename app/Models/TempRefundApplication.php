<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempRefundApplication extends Model
{
    use HasFactory, Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'temp_refund_application';

    protected $primaryKey = 'tra_id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'tra_application_no',
        'vcs_vendor_code',
        'tra_vendor_name',
        'tra_ref_no',
        'tra_ref_no_note',
        'fty_fund_type',
        'at_activity_code',
        'oun_code',
        'ccr_costcentre',
        'acm_acct_code',
        'tra_amt_refund',
        'tra_amt',
        'tra_status',
        'tra_payto_type',
        'tra_process',
        'tra_status_process',
        'dpm_deposit_no',
        'tra_reason_reject',
        'dz_path',
        'createddate',
        'createdby',
        'updateddate',
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
