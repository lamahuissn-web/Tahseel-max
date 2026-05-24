<?php

namespace App\Http\Requests\Admin\Members;

use Illuminate\Foundation\Http\FormRequest;

class SaveInbodyRequest extends FormRequest
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
            'member_id' => 'required',
            'height' => 'required',
            'weight' => 'required',
           // 'body_status' => 'required',
            'muscle_mass_percentage' => 'required',
            'fat_percentage' => 'required',
            'date' => 'required',

        ];
    }
}
