<?php

namespace App\Http\Requests;

class StoreBudgetCodeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lbc_level' => 'required|integer|min:3',
            'lbc_budget_code' => 'required|string|min:1|max:30',
            'lbc_description' => 'nullable|string',
            'lbc_status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
