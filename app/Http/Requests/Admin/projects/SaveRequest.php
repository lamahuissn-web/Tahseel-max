<?php

namespace App\Http\Requests\Admin\projects;

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
            'company_id'                 => 'required|string|max:255',
            'project_name'                 => 'required|string|max:255',

        ];
    }

    /***********************************************************/
    public function messages(): array
    {
        return [
            'client_code.unique'            => trans('clients.client_code_unique'),
            'company_id.required'                 => trans('clients.company_required'),
            'project_name.required'                 => trans('clients.name_required'),

        ];
    }
}
