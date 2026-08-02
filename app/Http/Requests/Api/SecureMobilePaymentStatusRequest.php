<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SecureMobilePaymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth('api')->user();

        return $user !== null
            && (string) $user->status === '1'
            && $user->can('pay_invoice');
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'result' => false,
            'message' => 'غير مصرح بالاستعلام عن الدفعات',
            'data' => (object) [],
            'error' => ['code' => 'payment_forbidden'],
        ], 403));
    }
}
