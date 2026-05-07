<?php

namespace App\Http\Requests;

use App\Models\LookupDetail;
use App\Models\Staff;
use Illuminate\Validation\Rule;

class StoreAssetSecretariatRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'isc_type' => ['required', 'string', 'max:100', Rule::exists(LookupDetail::class, 'lde_value')->where(
                fn ($q) => $q->where('lma_code_name', 'ITEM_SUBCAT_TYPE')->where('lde_status', 1)
            )],
            'stf_staff_id' => ['required', 'string', 'max:32', Rule::exists(Staff::class, 'stf_staff_id')],
            'stf_staff_id_superior' => ['required', 'string', 'max:32', Rule::exists(Staff::class, 'stf_staff_id')],
            'stf_staff_id_hod' => ['required', 'string', 'max:32', Rule::exists(Staff::class, 'stf_staff_id')],
            'ast_status' => 'required|integer|in:0,1',
        ];
    }
}