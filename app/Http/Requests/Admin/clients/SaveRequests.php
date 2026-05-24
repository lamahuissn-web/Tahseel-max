<?php

namespace App\Http\Requests\Admin\clients;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequests extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'client_code'          => 'required|string|max:255|unique:tbl_clients,client_code',
            'name'                 => 'required|string|max:255',
            'phone'                => 'nullable|string|max:15|regex:/^\+\d{1,3}\d{6,}$/',
            // 'email'                => 'nullable|email|max:255',
            'user'                 => 'nullable|string|max:255',
            'address1'             => 'nullable|string|max:255',
            // 'address2'             => 'nullable|string|max:255',
            'box_switch'           => 'nullable|string|max:255',
            'client_type'          => 'required|in:satellite,internet',
            'image'                => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'commercial_register'  => 'nullable|numeric|digits_between:1,15',
            'subscription_id'      => 'required|integer',
            'price'                => 'required',
            'subscription_date'    => 'required|date',
            'start_date'           => 'required|date',
            'is_active'            => 'nullable|in:0,1',
            'notes'                => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'client_code.required'          => trans('clients.client_code_required'),
            'client_code.unique'            => trans('clients.client_code_unique'),
            'name.required'                 => trans('clients.name_required'),
            'phone.required'                => trans('clients.phone_required'),
            'phone.regex'                   => trans('clients.phone_invalid_format'),
            'email.email'                   => trans('clients.email_invalid'),
//            'image.image'                   => trans('clients.image_invalid'),
//            'image.mimes'                   => trans('clients.image_format_invalid'),
//            'image.max'                     => trans('clients.image_size_invalid'),
            // 'commercial_register.numeric'   => trans('clients.commercial_register_numeric'),
            'city.exists'                => trans('clients.area_invalid'),
            'start_date.after_or_equal' => trans('clients.start_date_must_be_after_or_equal_subscription_date'),
        ];
    }
}
