<?php

namespace App\Imports;

use App\Models\Finance\Account;
use DB;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;


class AccountImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    protected $validParentIds;

    public function __construct()
    {
        // Cache valid parent IDs for better performance
        $this->validParentIds = DB::table('fr_accounts')->pluck('id')->toArray();
    }

    public function model(array $row)
    {
        // Extract code from parent field
        $string = $row['parent'];
        preg_match('/^\d+/', $string, $matches);
//        $matches = explode('-',$string);
        $code = isset($matches[0]) ? (int)$matches[0] : null;

        return new Account([
            'name' => ['ar' => $row['name_ar'], 'en' => $row['name_ar']],
            'description' => $row['description'],
            'code' => $row['code'],
            'parent_id' => optional($this->get_account($code))->id,
        ]);
    }

    protected function get_account($code)
    {
        return Account::where('code', $code)->first();
    }

    public function rules(): array
    {
        return [
            'name_ar' => 'required|unique:fr_accounts,name->ar',
//            'name_en' => 'required|unique:fr_accounts,name_en',
            'code' => 'required|numeric|min:0|unique:fr_accounts,code',
//            'account_type' => ['required', 'exists:fr_account_types,name'],

        ];
    }
}
