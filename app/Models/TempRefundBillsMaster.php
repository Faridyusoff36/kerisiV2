<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * FIMS `temp_refund_bills_master` — staff refund bill registration (BRI), DB_SECOND_DATABASE.
 */
class TempRefundBillsMaster extends Model
{
    use HasFactory, Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'temp_refund_bills_master';

    protected $primaryKey = 'bim_bills_id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'bim_bills_no',
        'bim_bills_type',
        'bim_bills_desc',
        'bim_bill_amt',
        'bim_cust_invoice_no',
        'bim_cust_invoice_date',
        'bim_payto_id',
        'bim_payto_name',
        'bim_status',
        'bim_system_id',
        'bim_voucher_no',
        'createddate',
        'createdby',
        'updateddate',
        'updatedby',
    ];

    protected function casts(): array
    {
        return [
            'bim_cust_invoice_date' => 'date',
            'createddate' => 'datetime',
            'updateddate' => 'datetime',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(TempRefundBillsDetail::class, 'bim_bills_id', 'bim_bills_id');
    }
}
