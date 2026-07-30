<?php

namespace App\Http\Requests\Admin;

use App\Services\WhatsApp\WhatsAppSafetySettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWhatsAppSafetySettingsRequest extends FormRequest
{
    public const PERMISSION = 'update_whatsapp_safety_settings';

    public function authorize(): bool
    {
        return $this->user('admin')?->can(self::PERMISSION) === true;
    }

    public function rules(): array
    {
        $custom = fn (): bool => $this->input('preset') === 'custom';

        return [
            'preset' => ['required', Rule::in(['very_safe', 'balanced', 'custom'])],
            'base_delay' => [Rule::requiredIf($custom), 'nullable', 'integer', 'between:'.WhatsAppSafetySettings::MIN_BASE_DELAY.','.WhatsAppSafetySettings::MAX_BASE_DELAY, $this->effectiveDelayRule()],
            'jitter_percent' => [Rule::requiredIf($custom), 'nullable', 'integer', 'between:'.WhatsAppSafetySettings::MIN_JITTER_PERCENT.','.WhatsAppSafetySettings::MAX_JITTER_PERCENT],
            'hourly_limit' => [Rule::requiredIf($custom), 'nullable', 'integer', 'between:'.WhatsAppSafetySettings::MIN_HOURLY_LIMIT.','.WhatsAppSafetySettings::MAX_HOURLY_LIMIT],
            'daily_limit' => [Rule::requiredIf($custom), 'nullable', 'integer', 'between:'.WhatsAppSafetySettings::MIN_DAILY_LIMIT.','.WhatsAppSafetySettings::MAX_DAILY_LIMIT],
            'batch_pause_every' => [Rule::requiredIf($custom), 'nullable', 'integer', 'between:'.WhatsAppSafetySettings::MIN_BATCH_SIZE.','.WhatsAppSafetySettings::MAX_BATCH_SIZE],
            'batch_pause_min_seconds' => [Rule::requiredIf($custom), 'nullable', 'integer', 'between:'.WhatsAppSafetySettings::MIN_BATCH_PAUSE.','.WhatsAppSafetySettings::MAX_BATCH_PAUSE],
            'batch_pause_max_seconds' => [Rule::requiredIf($custom), 'nullable', 'integer', 'between:'.WhatsAppSafetySettings::MIN_BATCH_PAUSE.','.WhatsAppSafetySettings::MAX_BATCH_PAUSE, 'gte:batch_pause_min_seconds'],
        ];
    }

    public function messages(): array
    {
        return [
            'preset.required' => 'يرجى اختيار مستوى الحماية.',
            'preset.in' => 'مستوى الحماية المحدد غير صالح.',
            '*.required' => 'هذا الحقل مطلوب عند اختيار الإعداد المخصص.',
            '*.integer' => 'يجب إدخال رقم صحيح.',
            '*.between' => 'القيمة خارج الحدود الآمنة المسموح بها.',
            'batch_pause_max_seconds.gte' => 'الحد الأقصى للاستراحة يجب أن يساوي أو يتجاوز الحد الأدنى.',
        ];
    }

    private function effectiveDelayRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($this->input('preset') !== 'custom') {
                return;
            }

            $jitter = (int) $this->input('jitter_percent', 0);
            $jitterSeconds = (int) round((int) $value * ($jitter / 100));
            $minimumDelay = (int) $value - $jitterSeconds;
            if ($minimumDelay < 4) {
                $fail('نطاق التأخير الفعلي يجب ألا يقل عن 4 ثوانٍ لحماية رقم WhatsApp.');
            }
        };
    }
}
