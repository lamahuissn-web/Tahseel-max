<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:admins,email,'.$this->route('user'),
            'phone' => 'nullable|unique:admins,phone,'.$this->route('user'),
            'is_employee' => 'required|in:0,1',
            'emp_id' => 'nullable|exists:tbl_employees,id|required_if:is_employee,1',
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'password' => 'nullable|string|min:8',
            'role' => 'required|exists:roles,id',
            'status' => 'required|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address'=> 'nullable|string|max:255',
        ];
    }
}
