<?php

namespace App\Http\Requests\Admin\Members;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'member_name' => 'required|string|max:255|unique:tbl_members,member_name',
            'goal_id' => 'required|array',
            'goal_id.*' => 'required',
        /*    'kt_docs_repeater_advanced.*.discount' => 'required',
            'kt_docs_repeater_advanced.*.start_date' => 'required',
            'kt_docs_repeater_advanced.*.subscription_id' => 'required',
            'kt_docs_repeater_advanced.*.type' => 'required',*/
            'health_status_id' => 'required',
            'birth_date' => 'required|date',
            'email' => 'required|email|max:255|unique:tbl_members,email',
            'phone' => 'required|unique:tbl_members,phone',
            'member_image.*' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ];
    }

}
