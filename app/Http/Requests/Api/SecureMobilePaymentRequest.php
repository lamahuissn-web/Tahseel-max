<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SecureMobilePaymentRequest extends FormRequest
{
    private bool $bodyContainedIdempotencyKey = false;

    protected function prepareForValidation(): void
    {
        $this->bodyContainedIdempotencyKey = $this->request->has('idempotency_key')
            || $this->json()->has('idempotency_key');
        $this->merge([
            'idempotency_key' => $this->header('Idempotency-Key'),
        ]);
    }

    public function authorize(): bool
    {
        $user = auth('api')->user();

        return $user !== null
            && (string) $user->status === '1'
            && $user->can('pay_invoice');
    }

    public function rules(): array
    {
        return [
            'expected_remaining' => ['required', 'string', 'regex:/^\d{1,8}(?:\.\d{1,2})?$/'],
            'idempotency_key' => ['required', 'uuid'],
            'amount' => ['prohibited'],
            'note' => ['prohibited'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = ['expected_remaining', 'idempotency_key', 'amount', 'note'];
            $unknown = array_diff(array_keys($this->all()), $allowed);
            if ($unknown !== []) {
                $validator->errors()->add('request', 'Unsupported payment fields were provided.');
            }
            if ($this->bodyContainedIdempotencyKey) {
                $validator->errors()->add('idempotency_key', 'Idempotency-Key must be provided as a header only.');
            }
        });
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'result' => false,
            'message' => 'غير مصرح بتسجيل الدفعات',
            'data' => (object) [],
            'error' => ['code' => 'payment_forbidden'],
        ], 403));
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'result' => false,
            'message' => 'بيانات الدفع غير صالحة',
            'data' => (object) [],
            'error' => [
                'code' => 'payment_validation_failed',
                'fields' => $validator->errors(),
            ],
        ], 422));
    }
}
