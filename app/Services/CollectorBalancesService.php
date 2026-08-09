<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AccountSettings;

/**
 * Read-only snapshot of collector/account balances.
 *
 * Reproduces the exact calculation and ordering of the current web page
 * /ar/admin/users?mobile=collectors for the same database state:
 * active (status=1) non-deleted Admin users with their role/account sums,
 * the configured accountant user appended once when applicable, and a
 * separate accountant-account summary. No writes, no schema changes.
 */
class CollectorBalancesService
{
    public function snapshot(): array
    {
        $collectors = Admin::query()
            ->with([
                'roles',
                'employee',
                'account' => function ($query) {
                    $query->withSum('financialTransactions', 'amount');
                },
            ])
            ->withSum('financialTransactions', 'amount')
            ->whereNull('deleted_at')
            ->where('status', '1')
            ->orderByDesc('financial_transactions_sum_amount')
            ->get();

        $settings = AccountSettings::first();
        $accountantAccount = $settings ? Account::query()
            ->withSum('financialTransactions', 'amount')
            ->find($settings->accountant_account_id) : null;

        if ($accountantAccount) {
            $accountantUser = $accountantAccount->user;

            if ($accountantUser) {
                $accountantUser->load([
                    'roles',
                    'employee',
                    'account' => function ($query) {
                        $query->withSum('financialTransactions', 'amount');
                    },
                ]);
                $accountantUser->financial_transactions_sum_amount = $accountantAccount->financial_transactions_sum_amount ?? 0;

                if (! $collectors->contains('id', $accountantUser->id)) {
                    $collectors->push($accountantUser);
                }
            }
        }

        $collectors = $collectors->sortByDesc('financial_transactions_sum_amount')->values();

        $entries = $collectors->map(function (Admin $admin): array {
            $role = $admin->roles->first();

            // Web semantics: the collectors page renders the Arabic accountant
            // label for entries without a role; keep a stable nonempty
            // role_name fallback so strict mobile parsers never see blanks.
            if ($role) {
                $roleName = (string) $role->name;
                $roleLabel = (string) ($role->getTranslation('title', 'ar') ?: $role->name);
            } else {
                $roleName = 'accounting';
                $roleLabel = 'محاسب';
            }

            return [
                'id' => (int) $admin->id,
                'name' => (string) ($admin->name ?? ''),
                'role_name' => $roleName,
                'role_label' => $roleLabel,
                'account_name' => $admin->account ? (string) $admin->account->name : null,
                'total_collected' => $this->money($admin->financial_transactions_sum_amount),
                'account_balance' => $admin->account ? $this->money($admin->account->financial_transactions_sum_amount ?? 0) : null,
            ];
        })->values()->all();

        $currency = get_app_config_data('currency');
        if (! is_string($currency) || trim($currency) === '') {
            throw new \RuntimeException('Currency configuration is missing or blank.');
        }

        return [
            'summary' => [
                'collectors_count' => $collectors->count(),
                'total_collected' => $this->money($collectors->sum('financial_transactions_sum_amount')),
                'currency' => $currency,
            ],
            'accountant_account' => $accountantAccount ? [
                'id' => (int) $accountantAccount->id,
                'name' => (string) $accountantAccount->name,
                'balance' => $this->money($accountantAccount->financial_transactions_sum_amount ?? 0),
            ] : null,
            'collectors' => $entries,
        ];
    }

    private function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
