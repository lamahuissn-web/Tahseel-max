<?php

namespace App\Http\Requests\Admin\projects;

use Illuminate\Foundation\Http\FormRequest;

class CompanyClientRequest extends FormRequest
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
            'client_id'     => 'required|exists:tbl_clients,id',
            'project_code'  => 'required|string|max:50',
            'project_name'  => 'required|string|max:255',

        ];
    }

}
