<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy FIMS `asset_dispose_method` (DB_SECOND_DATABASE).
 */
class AssetDisposeMethod extends Model
{
    use Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'asset_dispose_method';

    protected $primaryKey = 'adt_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'adt_id',
        'adt_code',
        'adt_name',
        'adt_status',
        'adt_extended_field',
        'createdby',
        'updatedby',
    ];

    protected function casts(): array
    {
        return [
            'adt_status' => 'integer',
        ];
    }
}
