<?php

namespace App\Http\Requests\Admin\account;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:tbl_accounts,id',
            // 'user_id' => 'nullable|exists:admins,id|unique:admins,account_id',
        ];
    }
}
