<?php

namespace App\Models;

use App\Http\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Disposal Secretariat setup (PAGEID 2571 / MENUID 3118).
 *
 * Table name aligns with legacy FIMS conventions; verify against your `DB_SECOND_DATABASE` schema.
 */
class AssetSecretariat extends Model
{
    use Auditable;

    protected $connection = 'mysql_secondary';

    protected $table = 'asset_secretariat';

    protected $primaryKey = 'ast_id';

    public $incrementing = false;

    public const CREATED_AT = 'createddate';

    public const UPDATED_AT = null;

    protected $fillable = [
        'ast_id',
        'isc_type',
        'stf_staff_id',
        'stf_staff_id_superior',
        'stf_staff_id_hod',
        'ast_status',
        'createdby',
        'createddate',
    ];

    protected function casts(): array
    {
        return [
            'ast_status' => 'integer',
            'createddate' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'stf_staff_id', 'stf_staff_id');
    }

    public function superior(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'stf_staff_id_superior', 'stf_staff_id');
    }

    public function hod(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'stf_staff_id_hod', 'stf_staff_id');
    }
}
