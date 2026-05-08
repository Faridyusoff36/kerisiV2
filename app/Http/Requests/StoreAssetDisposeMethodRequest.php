<?php

namespace App\Http\Requests;

class StoreAssetDisposeMethodRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adt_code' => 'required|string|min:1|max:100',
            'adt_name' => 'required|string|min:1|max:500',
            'adt_status' => 'required|integer|in:0,1',
        ];
    }
}
