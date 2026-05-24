<?php

namespace App\Http\Requests\Admin\masrofat;

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
            'emp_id' => 'required|integer|exists:tbl_employees,id',
            'band_id' => 'required|integer|exists:tbl_sarf_bands,id',
            'value' => 'required|string|max:255',
            'notes' => 'required|string|max:255',
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
