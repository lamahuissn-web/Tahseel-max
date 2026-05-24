<?php

namespace App\Exports;

use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AccountTransfer;
use App\Models\Admin\Employee;
use App\Models\Admin\FinancialTransaction;
use App\Models\Admin\Invoice;
use App\Models\Admin\Masrofat;
use App\Models\Admin\Revenue;
use App\Models\Clients;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AllDataExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ClientsSheet(),
            new InvoicesSheet(),
            new UsersSheet(),
            new EmployeesSheet(),
            new ExpensesSheet(),
            new RevenuesSheet(),
            new TransfersSheet(),
            new FinancialTransactionsSheet(),
            new AccountsSheet(),
        ];
    }
}

class ClientsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Clients::select(
            'id',
            'client_code',
            'name',
            'phone',
            'email',
            'address1',
            'address2',
            'client_type',
            'subscription_id',
            'price',
            'subscription_date',
            'start_date',
            'notes',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'كود العميل',
            'الاسم',
            'الهاتف',
            'البريد الإلكتروني',
            'العنوان 1',
            'العنوان 2',
            'نوع العميل',
            'رقم الاشتراك',
            'السعر',
            'تاريخ الاشتراك',
            'تاريخ البدء',
            'ملاحظات',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'العملاء';
    }
}

class InvoicesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Invoice::select(
            'id',
            'invoice_number',
            'client_id',
            'subscription_id',
            'amount',
            'paid_amount',
            'remaining_amount',
            'due_date',
            'paid_date',
            'status',
            'notes',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'رقم الفاتورة',
            'رقم العميل',
            'رقم الاشتراك',
            'المبلغ',
            'المبلغ المدفوع',
            'المبلغ المتبقي',
            'تاريخ الاستحقاق',
            'تاريخ الدفع',
            'الحالة',
            'ملاحظات',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'الفواتير';
    }
}

class UsersSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Admin::select(
            'id',
            'name',
            'email',
            'phone',
            'status',
            'emp_id',
            'account_id',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'الاسم',
            'البريد الإلكتروني',
            'الهاتف',
            'الحالة',
            'رقم الموظف',
            'رقم الحساب',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'المستخدمين';
    }
}

class EmployeesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Employee::select(
            'id',
            'emp_code',
            'first_name',
            'last_name',
            'phone',
            'whatsapp_num',
            'address',
            'position',
            'salary',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'كود الموظف',
            'الاسم الأول',
            'الاسم الأخير',
            'الهاتف',
            'واتساب',
            'العنوان',
            'المنصب',
            'الراتب',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'الموظفين';
    }
}

class ExpensesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Masrofat::select(
            'id',
            'emp_id',
            'band_id',
            'value',
            'notes',
            'created_by',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'رقم الموظف',
            'رقم البند',
            'القيمة',
            'ملاحظات',
            'أنشئ بواسطة',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'المصروفات';
    }
}

class RevenuesSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Revenue::select(
            'id',
            'invoice_id',
            'client_id',
            'amount',
            'collected_by',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'رقم الفاتورة',
            'رقم العميل',
            'المبلغ',
            'جمع بواسطة',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'الإيرادات';
    }
}

class TransfersSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return AccountTransfer::select(
            'id',
            'from_account',
            'to_account',
            'amount',
            'notes',
            'created_by',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'من الحساب',
            'إلى الحساب',
            'المبلغ',
            'ملاحظات',
            'أنشئ بواسطة',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'التحويلات';
    }
}

class FinancialTransactionsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return FinancialTransaction::select(
            'id',
            'account_id',
            'amount',
            'type',
            'notes',
            'created_by',
            'created_at',
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'رقم الحساب',
            'المبلغ',
            'النوع',
            'ملاحظات',
            'أنشئ بواسطة',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'الحركات المالية';
    }
}

class AccountsSheet implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        $accounts = Account::withSum('financialTransactions', 'amount')
            ->with(['children' => function($query) {
                $query->withSum('financialTransactions', 'amount')
                      ->with(['children' => function($q) {
                          $q->withSum('financialTransactions', 'amount');
                      }]);
            }])
            ->get();
        
        return $accounts->map(function($account) {
            // استخدام method totalAmount() لحساب المبلغ الإجمالي (يتضمن الحسابات الفرعية بشكل متكرر)
            $totalAmount = $account->totalAmount();
            
            return [
                'id' => $account->id,
                'name' => $account->name,
                'parent_id' => $account->parent_id,
                'total_amount' => $totalAmount,
                'created_by' => $account->created_by,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'الاسم',
            'الحساب الأب',
            'المبلغ الإجمالي',
            'أنشئ بواسطة',
            'تاريخ الإنشاء',
            'تاريخ التحديث'
        ];
    }

    public function title(): string
    {
        return 'الدليل المحاسبي';
    }
}

