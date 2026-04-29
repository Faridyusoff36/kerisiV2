<?php

namespace App\Http\Requests;

use App\Services\PurchasingJobscopeService;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class UpdateJobscopeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'level' => ['required', 'string', 'in:1,2,3'],
            'category' => ['required', 'string', 'max:100'],
            'parent' => ['nullable', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:500'],
            'status' => ['required', 'string', 'regex:/^[01]$/'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $data = $v->getData();
            $exceptId = (int) $this->route('id');

            /** @var PurchasingJobscopeService $svc */
            $svc = app(PurchasingJobscopeService::class);

            try {
                $svc->assertParentHierarchy(
                    trim((string) $data['level']),
                    trim((string) $data['category']),
                    $data['parent'] ?? null
                );
            } catch (ValidationException $e) {
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $msg) {
                        $v->errors()->add($field, $msg);
                    }
                }

                return;
            }

            $code = trim((string) ($data['code'] ?? ''));
            $cat = trim((string) ($data['category'] ?? ''));
            if ($code !== '' && $cat !== '' && $svc->codeExistsForCategory($code, $cat, $exceptId)) {
                $v->errors()->add('code', 'This code already exists for the selected category.');
            }
        });
    }
}
