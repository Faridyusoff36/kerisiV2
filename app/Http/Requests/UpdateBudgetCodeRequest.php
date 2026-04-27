<?php

namespace App\Http\Requests;

class UpdateBudgetCodeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Level / budget_code are read-only on edit per legacy BL
            // MM_API_BUDGET_SETUP_BUDGETCODE.saveItem (UPDATE only touches
            // lbc_status), but we still accept them for parity with the
            // popup payload — they are ignored server-side.
            'lbc_level' => 'nullable|integer|min:3',
            'lbc_budget_code' => 'nullable|string|max:30',
            'lbc_description' => 'nullable|string',
            'lbc_status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
