<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;

/**
 * Purchasing / Purchase Requisition Cancel — update header (`requisition_master`) + cancel reason.
 * Same core fields as New PR; Agreement is omitted on the SPA (forced N); cancellation reason required on submit.
 */
class UpdatePurchasingPrCancelRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * CamelCaseMiddleware exposes snake_case to PHP rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rqm_request_by' => ['required', 'string', 'max:32'],
            'rqm_request_date' => ['required', 'date'],
            'rqm_requisition_title' => ['required', 'string', 'max:500'],
            'rqm_tender_scope' => ['required', 'string', 'max:1000'],
            'rqm_isagreement_exist' => ['nullable', 'string', 'in:Y,N'],
            'rqm_agg_no' => ['nullable', 'string', 'max:100'],
            'oun_code' => ['required', 'string', 'max:32'],
            'ccr_costcentre' => ['required', 'string', 'max:32'],
            'fty_fund_type' => ['required', 'string', 'max:32'],
            'at_activity_code' => ['required', 'string', 'max:32'],
            'rqm_contact_person' => ['required', 'string', 'max:32'],
            'rqm_payee_code' => ['nullable', 'string', 'max:32'],
            'so_code' => ['nullable', 'string', 'max:64'],
            'rqm_cancel_remark' => ['required', 'string', 'max:8000'],
            'rqm_ent_amt' => ['nullable'],
            'rqm_amount' => ['nullable'],
            'rqm_rate_date' => ['nullable', 'date'],
            'rqm_currency_code' => ['nullable', 'string', 'max:16'],
            'rqm_rate_type' => ['nullable', 'string', 'max:32'],
            'rqm_currency_unit' => ['nullable', 'string', 'max:32'],
            'rqm_conversion_rate' => ['nullable', 'string', 'max:64'],
            'rqm_ref_no' => ['nullable', 'string', 'max:200'],
            'rqm_quotation_receive' => ['nullable', 'max:64'],
            'rqm_doc_receive_date' => ['nullable', 'date'],
            'rqm_jenis_tender' => ['nullable', 'string', 'max:15'],
            'rqm_tender_type' => ['nullable', 'string', 'max:30'],
            'rqm_status' => ['sometimes', 'string', 'max:32'],
            'org_code' => ['nullable', 'string', 'max:32'],
            'next_receiver' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $d = $v->getData();
            if (($d['rqm_isagreement_exist'] ?? 'N') === 'Y' && trim((string) ($d['rqm_agg_no'] ?? '')) === '') {
                $v->errors()->add('rqm_agg_no', 'Agreement No is required when Agreement is Yes.');
            }
        });
    }

    /** @phpstan-return array<string, mixed> */
    public function sanitizedForPersist(): array
    {
        $d = $this->validated();

        $d['rqm_isagreement_exist'] = $d['rqm_isagreement_exist'] ?? 'N';

        foreach (['rqm_ent_amt', 'rqm_amount', 'rqm_conversion_rate'] as $k) {
            if (! array_key_exists($k, $d) || $d[$k] === null || $d[$k] === '') {
                continue;
            }
            $d[$k] = is_numeric($d[$k]) ? $d[$k] : null;
        }
        $qRecv = $d['rqm_quotation_receive'] ?? null;
        if ($qRecv !== null && $qRecv !== '' && is_numeric($qRecv)) {
            $d['rqm_quotation_receive'] = $qRecv;
        } elseif ($qRecv === '' || $qRecv === null) {
            $d['rqm_quotation_receive'] = null;
        }

        unset($d['next_receiver']);

        return $d;
    }
}
