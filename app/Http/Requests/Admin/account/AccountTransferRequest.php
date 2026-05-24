<?php

namespace App\Http\Requests\Admin\account;

use Illuminate\Foundation\Http\FormRequest;

class AccountTransferRequest extends FormRequest
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
            'from_account' => 'required|exists:tbl_accounts,id',
            'to_account' => 'required|exists:tbl_accounts,id|different:from_account',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'band_id' => 'nullable|exists:tbl_sarf_bands,id',
        ];
    }
}
