<?php

namespace App\Http\Requests\Admin\account;

use Illuminate\Foundation\Http\FormRequest;

class SaveAccountSettingsRequest  extends FormRequest
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
            'general_account_id' => 'required|exists:tbl_accounts,id',
            'masrofat_account_id' => 'nullable|exists:tbl_accounts,id',
            'employee_account_id' => 'nullable|exists:tbl_accounts,id',
            'accountant_account_id' => 'nullable|exists:tbl_accounts,id',
        ];
    }
}
