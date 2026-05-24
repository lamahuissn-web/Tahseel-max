<?php

namespace App\Http\Requests\Admin\Setting;

use Illuminate\Foundation\Http\FormRequest;

class SaveSiteDataRequest extends FormRequest
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
            'row_id' => 'required|exists:site_data,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'branch_id' => 'required|exists:tbl_branches,id',
            'address' => 'required|string|max:255',
            'fax' => 'required|string|max:20',
            'phone' => 'required|string|max:20',
            'description' => 'required|string',
            'maplocation' => 'required|string|max:255',
            'short_about' => 'required|string|max:255',
            'video' => 'required|string|max:255',
            'discount_ratio' => 'required|numeric|min:0|max:100',
            'tax_number' => 'required|string|max:50',
            'commercial_registration_number' => 'required|string|max:50',
            'contract_terms' => 'required|string',
            'image_print' => 'required_if:image,null|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
