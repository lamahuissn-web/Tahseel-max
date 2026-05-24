<?php

namespace App\Http\Requests\Admin\clients;

use Illuminate\Foundation\Http\FormRequest;

class ImportClientsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'file' => 'required|file|mimes:xlsx,xls',
            // 'subscription_date' => 'required|date',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'subscription_date' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => trans('clients.import_file_required'),
            'file.mimes' => trans('clients.import_file_format'),
            'subscription_date.required' => trans('clients.subscription_date_required'),
        ];
    }
}
