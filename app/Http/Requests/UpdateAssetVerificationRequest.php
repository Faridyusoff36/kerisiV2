<?php

namespace App\Http\Requests;

class UpdateAssetVerificationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'real_cur_building' => 'nullable|string|max:64',
            'real_cur_room' => 'nullable|string|max:64',
            'real_cur_building_desc' => 'nullable|string|max:255',
            'real_cur_room_desc' => 'nullable|string|max:255',
            'asset_status' => 'nullable|string|max:64',
        ];
    }
}
