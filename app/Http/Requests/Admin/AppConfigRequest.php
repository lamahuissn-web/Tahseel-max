<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AppConfigRequest extends FormRequest
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
            'phone_service'=>'required',
            'currency'=>'required',
            'telegram_backup_frequency' => 'nullable|in:hourly,every_2_hours,every_4_hours,every_6_hours,every_12_hours,daily,weekly,monthly,custom',
            'telegram_backup_time' => 'nullable|date_format:H:i',
            'telegram_backup_custom_cron' => 'nullable|string|max:100',
        ];
    }
}
