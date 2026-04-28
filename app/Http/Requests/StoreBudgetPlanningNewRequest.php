<?php

namespace App\Http\Requests;

/**
 * Validates the payload for the Budget Planning New Application form
 * (PAGEID 1236 / MENUID 1516). The legacy form had no `Business Logic
 * (BL) Details` documented — these rules are inferred from the visible
 * "Planning Info" / "Account Activity" / "Review File" / "Leave some
 * remark" component fields and from the columns the migrated controllers
 * read on `budget_planning_master` / `budget_planning_details`.
 */
class StoreBudgetPlanningNewRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bpm_year' => 'required|integer|min:2000|max:2100',
            'bpm_oun_code' => 'required|string|max:50',
            'bpm_ccr_costcentre' => 'required|string|max:50',
            'fty_fund_type' => 'required|string|max:50',
            'at_activity_code' => 'required|string|max:50',
            'bpm_type' => 'required|string|max:50',
            'bpm_remark' => 'required|string|min:1|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.acm_acct_code' => 'required|string|max:50',
            'lines.*.bpd_amt' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'lines.required' => 'At least one account line is required.',
            'lines.min' => 'At least one account line is required.',
            'lines.*.acm_acct_code.required' => 'Account code is required for every line.',
            'lines.*.bpd_amt.required' => 'Amount is required for every line.',
            'lines.*.bpd_amt.min' => 'Amount must be 0 or greater.',
        ];
    }
}
