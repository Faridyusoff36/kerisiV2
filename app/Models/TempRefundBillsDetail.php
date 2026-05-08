<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TempRefundBillsDetail extends Model
{
    use HasFactory, Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'temp_refund_bills_details';

    protected $primaryKey = 'bid_id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'bim_bills_id',
        'bid_payto_type',
        'bid_payto_id',
        'bid_payto_name',
        'fty_fund_type',
        'at_activity_code',
        'oun_code',
        'ccr_costcentre',
        'acm_acct_code',
        'bid_amt',
        'bid_trans_type',
        'vsa_vendor_bank',
        'vsa_bank_accno',
        'bid_extended_field',
        'bid_factoring_id',
        'bid_fact_bank_acctno',
        'tra_application_no',
    ];

    protected function casts(): array
    {
        return [
            'bid_extended_field' => 'array',
        ];
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(TempRefundBillsMaster::class, 'bim_bills_id', 'bim_bills_id');
    }
}
