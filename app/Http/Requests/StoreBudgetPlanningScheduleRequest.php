<?php

namespace App\Http\Requests;

class StoreBudgetPlanningScheduleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bps_year_budget' => 'required|integer|min:2000|max:9999',
            // Frontend sends ISO `YYYY-MM-DD` strings; the legacy BL stored
            // them after a STR_TO_DATE in `dd/mm/yyyy`, but here we keep the
            // canonical ISO form on the wire and convert in the controller.
            'bps_plan_start_date' => 'required|date_format:Y-m-d',
            'bps_plan_end_date' => 'required|date_format:Y-m-d|after_or_equal:bps_plan_start_date',
            'bps_status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
