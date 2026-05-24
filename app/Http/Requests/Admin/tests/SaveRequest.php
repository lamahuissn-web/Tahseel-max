<?php

namespace App\Http\Requests\Admin\tests;

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
            'client_id' => 'required|exists:tbl_clients,id',
            'company_id' => 'required|exists:tbl_clients_companies,id',
            'project_id' => 'required|exists:tbl_clients_projects,id',
            'test_code' => 'required|string|max:255',
            'talab_number' => 'required|string|max:255',
            'talab_title' => 'required|string|max:255',
            'talab_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'talab_date' => 'required|date',
            'talab_end_date' => 'required|date|after:talab_date',
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
