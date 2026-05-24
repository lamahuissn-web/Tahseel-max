<?php

namespace App\Http\Requests\Admin\Employees;

use Illuminate\Foundation\Http\FormRequest;

class AddEmployeeRequest extends FormRequest
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
            'emp_code'        => 'required|string|max:255|unique:tbl_employees,emp_code',
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            // 'email'           => 'required|email|unique:tbl_employees,email',
            // 'national_id'     => 'required|string|max:255',
            // 'religion'        => 'required|in:muslim,mese7y',
            'phone'           => 'required|numeric|min:10',
            'whatsapp_num'    => 'nullable|numeric|min:10',
            'address'         => 'nullable|string|max:255',
            // 'date_of_birth'   => 'required|date|before:today',
            // 'gender'          => 'required|in:male,female',
            // 'material_status' => 'required|in:single,married,divorced',
            'position'        => 'required|string|max:255',
            'salary'          => 'required|numeric|min:0',
            'personal_photo'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
