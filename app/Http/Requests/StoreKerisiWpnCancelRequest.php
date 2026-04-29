<?php

namespace App\Http\Requests;

/**
 * Purchasing / Work Progress Note Cancel (menu 2082) —
 * mirrors legacy POST `processcancelwpn_entry` (cbox = wpm_progress_id + '_' + wpm_progress_no).
 */
class StoreKerisiWpnCancelRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_id' => ['required', 'string', 'max:255'],
        ];
    }
}
