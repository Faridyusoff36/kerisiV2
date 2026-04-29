<?php

namespace App\Http\Requests;

/**
 * PATCH /project-monitoring/projects/{cpaProjectNo}.
 *
 * Partial update of legacy `capital_project` profile dimensions (PAGEID 1327 —
 * Profile Setup → Project Profile So Code, MENUID 1615). Incoming keys are
 * camelCased then normalized to snake_case by CamelCaseMiddleware.
 */
class UpdateCapitalProjectProfileRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cpa_project_desc' => 'sometimes|nullable|string|max:512',
            'fty_fund_type' => 'sometimes|nullable|string|max:80',
            'lat_activity_code' => 'sometimes|nullable|string|max:80',
            'oun_code' => 'sometimes|nullable|string|max:80',
            'ccr_costcentre' => 'sometimes|nullable|string|max:80',
            'so_code' => 'sometimes|nullable|string|max:80',
            'cpa_project_type' => 'sometimes|nullable|string|max:80',
            'cpa_start_date' => 'sometimes|nullable|date_format:d/m/Y',
            'cpa_end_date' => 'sometimes|nullable|date_format:d/m/Y',
            'cpa_source' => 'sometimes|nullable|string|max:100',
            'cpa_project_status' => 'sometimes|nullable|string|max:80',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach ([
            'cpa_project_desc', 'fty_fund_type', 'lat_activity_code', 'oun_code',
            'ccr_costcentre', 'so_code', 'cpa_project_type', 'cpa_source', 'cpa_project_status',
            'cpa_start_date', 'cpa_end_date',
        ] as $k) {
            if ($this->has($k)) {
                $v = $this->input($k);
                if ($v === '') {
                    $this->merge([$k => null]);
                }
            }
        }
    }
}
