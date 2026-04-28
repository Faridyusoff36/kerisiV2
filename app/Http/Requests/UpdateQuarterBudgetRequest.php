<?php

namespace App\Http\Requests;

/**
 * Update a quarter_budget row from the Budget > Setup > Allocation popup
 * (PAGEID 1035 / MENUID 1294). The legacy SWS_DT_SETUP_QUARTER BL only
 * documents the read paths (mode=datatable / mode=form); the popup form,
 * however, exposes Year / Description / Start Date / End Date / Status,
 * so the migrated controller mirrors that surface and updates those
 * columns. The primary key (qbu_quarter_id) is immutable.
 */
class UpdateQuarterBudgetRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'qbu_year' => 'required|integer|min:2000|max:9999',
            'qbu_description' => 'required|string|max:200',
            'qbu_start_date' => 'required|date_format:Y-m-d',
            'qbu_end_date' => 'required|date_format:Y-m-d|after_or_equal:qbu_start_date',
            'qbu_status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
