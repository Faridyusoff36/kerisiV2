<?php

namespace App\Http\Requests;

class UpdateBudgetPlanningScheduleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // bps_year_budget is immutable on edit (mirrors the legacy BL,
            // which only updates dates + status when mode != 'new'). It's
            // accepted optionally for parity with the popup payload.
            'bps_year_budget' => 'nullable|integer|min:2000|max:9999',
            'bps_plan_start_date' => 'required|date_format:Y-m-d',
            'bps_plan_end_date' => 'required|date_format:Y-m-d|after_or_equal:bps_plan_start_date',
            'bps_status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
