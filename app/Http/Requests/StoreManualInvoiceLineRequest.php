<?php

namespace App\Http\Requests;

/**
 * Validates a Debit (DT) or Credit (CR) detail row insert for Manual Invoice
 * (cust_invoice_details) scoped to STUD_INV / invoice type 12.
 */
class StoreManualInvoiceLineRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Inbound JSON uses camelCase; CamelCaseMiddleware maps to snake_case before validation.
     */
    public function rules(): array
    {
        return [
            'transaction_type' => 'required|string|in:DT,CR',
            'total_amt' => 'required|numeric|min:0',
            'tax_amt' => 'nullable|numeric|min:0',
            'item_category' => 'nullable|string|max:191',
            'item_code' => 'nullable|string|max:191',
            'fund_type' => 'nullable|string|max:191',
            'activity_code' => 'nullable|string|max:191',
            'acct_code' => 'nullable|string|max:191',
            'oun_code' => 'nullable|string|max:191',
            'cost_centre' => 'nullable|string|max:191',
            'project_no' => 'nullable|string|max:191',
            'tax_code' => 'nullable|string|max:191',
        ];
    }
}
