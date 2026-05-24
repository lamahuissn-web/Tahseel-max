<?php

namespace App\Http\Requests\Admin\company;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

  /***********************************************************/
    public function rules(): array
    {
        return [
            'name'                 => 'required|string|max:255',
            'phone'                => 'required|string|max:15',
            'email'                => 'nullable|email|max:255',
            'address1'             => 'nullable|string|max:255',
            'balance'              => 'nullable|numeric|min:0',
        ];
    }

    /***********************************************************/
    public function messages(): array
    {
        return [
            'client_code.unique'            => trans('clients.client_code_unique'),
            'name.required'                 => trans('clients.name_required'),
            'phone.required'                => trans('clients.phone_required'),
            'email.email'                   => trans('clients.email_invalid'),
        ];
    }
}
