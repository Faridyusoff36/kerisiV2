<?php

namespace App\Http\Requests;

class StoreKerisiPaymentRejectBatchActionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['required', 'integer', 'min:1'],
        ];
    }
}
