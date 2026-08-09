<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Admin\Account;
use App\Models\Admin\AccountSettings;
use App\Models\Admin\FinancialTransaction;
use App\Models\AppConfig;
use App\Models\Role;
use App\Services\CollectorBalancesService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class CollectorBalancesTest extends TestCase
{
    /**
     * Migration is performed once per class, then every test runs inside a
     * transaction that is rolled back in tearDown.
     *
     * RefreshDatabase is intentionally NOT used here: it flips Laravel's
     * process-wide RefreshDatabaseState::$migrated flag, and this class is the
     * first suite alphabetically, which would make later RefreshDatabase
     * suites (e.g. SecureMobilePaymentTest) skip their own migrate:fresh and
     * inherit rows left behind by non-rolling-back suites. Using the same
     * guarded migrate:fresh pattern as SecureMobilePaymentAfterCommitTest keeps
     * this class isolated without touching global test state.
     */
    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! str_contains((string) config('database.connections.'.config('database.default').'.database'), '_test')) {
            throw new \RuntimeException('Refusing migrate:fresh outside a dedicated test database.');
        }

        if (! self::$migrated) {
            Artisan::call('migrate:fresh', ['--force' => true]);
            self::$migrated = true;
        }

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    // ---------------------------------------------------------------- auth

    public function test_unauthenticated_caller_receives_401(): void
    {
        $response = $this->getJson('/api/v1/admin/collector-balances');

        $response
            ->assertStatus(401)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'authentication_required');
    }

    public function test_collector_role_is_forbidden_on_collector_balances(): void
    {
        $collector = $this->admin('محصل أول', 'collector-one@example.test', 'collector', null);

        $response = $this->withToken(auth('api')->login($collector))
            ->getJson('/api/v1/admin/collector-balances');

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'collector_balances_forbidden');
    }

    public function test_accounting_role_is_forbidden_on_collector_balances(): void
    {
        $accounting = $this->admin('محاسب أول', 'accounting-one@example.test', 'accounting', null);

        $response = $this->withToken(auth('api')->login($accounting))
            ->getJson('/api/v1/admin/collector-balances');

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'collector_balances_forbidden');
    }

    public function test_user_without_any_role_is_forbidden_on_collector_balances(): void
    {
        $unknown = $this->admin('بلا صلاحية', 'unknown-one@example.test', '', null);

        $response = $this->withToken(auth('api')->login($unknown))
            ->getJson('/api/v1/admin/collector-balances');

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'collector_balances_forbidden');
    }

    public function test_legacy_collectors_endpoint_forbids_collector_role(): void
    {
        $collector = $this->admin('محصل ثان', 'collector-two@example.test', 'collector', null);

        $response = $this->withToken(auth('api')->login($collector))
            ->postJson('/api/v1/collectors');

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'collectors_forbidden');
    }

    public function test_legacy_collectors_endpoint_forbids_accounting_role(): void
    {
        $accounting = $this->admin('محاسب ثان', 'accounting-two@example.test', 'accounting', null);

        $response = $this->withToken(auth('api')->login($accounting))
            ->postJson('/api/v1/collectors');

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'collectors_forbidden');
    }

    public function test_legacy_collectors_endpoint_remains_available_to_super_admin(): void
    {
        $superAdmin = $this->admin('مدير عام', 'super-admin@example.test', 'Super-Admin', null);

        $response = $this->withToken(auth('api')->login($superAdmin))
            ->postJson('/api/v1/collectors');

        // Guard must not over-restrict: Super-Admin passes through to the legacy flow.
        $response->assertStatus(200);
    }

    // ---------------------------------------------------------- accounts

    public function test_accounts_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/accounts');

        $response
            ->assertStatus(401)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'authentication_required');
    }

    public function test_accounts_endpoint_forbids_collector_role(): void
    {
        $collector = $this->admin('محصل حسابات', 'collector-accounts@example.test', 'collector', null);

        $response = $this->withToken(auth('api')->login($collector))
            ->getJson('/api/v1/accounts');

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'accounts_forbidden');
    }

    public function test_accounts_endpoint_forbids_accounting_role(): void
    {
        $accounting = $this->admin('محاسب حسابات', 'accounting-accounts@example.test', 'accounting', null);

        $response = $this->withToken(auth('api')->login($accounting))
            ->getJson('/api/v1/accounts');

        $response
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'accounts_forbidden');
    }

    public function test_accounts_endpoint_remains_available_to_super_admin(): void
    {
        $superAdmin = $this->admin('مدير عام حسابات', 'super-admin-accounts@example.test', 'Super-Admin', null);

        $response = $this->withToken(auth('api')->login($superAdmin))
            ->getJson('/api/v1/accounts');

        // Guard must not over-restrict: Super-Admin passes through to the legacy flow.
        $response->assertStatus(200);
    }

    // ------------------------------------------------------------- snapshot

    public function test_super_admin_receives_exact_collector_balances_snapshot(): void
    {
        $fixture = $this->balanceFixture();
        $sa = $fixture['superAdmin'];

        $response = $this->withToken(auth('api')->login($sa))
            ->getJson('/api/v1/admin/collector-balances');

        $response->assertOk()->assertJsonPath('result', true);

        $data = $response->json('data');

        // strict top-level shape
        $this->assertSame(['summary', 'accountant_account', 'collectors'], array_keys($data));
        $this->assertSame(
            ['collectors_count', 'total_collected', 'currency'],
            array_keys($data['summary']),
        );

        // summary totals match the /ar/admin/users?mobile=collectors calculation
        $this->assertSame(4, $data['summary']['collectors_count']);
        $this->assertSame('250.00', $data['summary']['total_collected']);
        $this->assertSame('$', $data['summary']['currency']);

        // ordering: descending by total_collected
        $collectors = $data['collectors'];
        $this->assertSame(
            [$fixture['c1']->id, $fixture['c2']->id, $fixture['c3']->id, $fixture['sa']->id],
            array_column($collectors, 'id'),
        );

        // exact entries, contract keys only
        $this->assertSame(
            ['id', 'name', 'role_name', 'role_label', 'account_name', 'total_collected', 'account_balance'],
            array_keys($collectors[0]),
        );

        $first = $collectors[0];
        $this->assertSame($fixture['c1']->id, $first['id']);
        $this->assertSame('محصل أول', $first['name']);
        $this->assertSame('collector', $first['role_name']);
        $this->assertSame('محصل', $first['role_label']);
        $this->assertSame('حساب محصل أول', $first['account_name']);
        $this->assertSame('200.00', $first['total_collected']);
        $this->assertSame('200.00', $first['account_balance']);

        $second = $collectors[1];
        $this->assertSame($fixture['c2']->id, $second['id']);
        $this->assertSame('collector', $second['role_name']);
        $this->assertSame('محصل', $second['role_label']);
        $this->assertSame('40.00', $second['total_collected']);

        $third = $collectors[2];
        $this->assertSame($fixture['c3']->id, $third['id']);
        $this->assertSame('accounting', $third['role_name']);
        $this->assertSame('محاسب', $third['role_label']);
        $this->assertSame('10.00', $third['total_collected']);

        // Super-Admin user without an account: nullable account fields
        $last = $collectors[3];
        $this->assertSame($fixture['sa']->id, $last['id']);
        $this->assertSame('Super-Admin', $last['role_name']);
        $this->assertSame('مدير عام', $last['role_label']);
        $this->assertNull($last['account_name']);
        $this->assertNull($last['account_balance']);
        $this->assertSame('0.00', $last['total_collected']);

        // accountant account summary (configured, no linked user)
        $this->assertSame(
            ['id', 'name', 'balance'],
            array_keys($data['accountant_account']),
        );
        $this->assertSame($fixture['accountantAccount']->id, $data['accountant_account']['id']);
        $this->assertSame('حساب المحاسب الرئيسي', $data['accountant_account']['name']);
        $this->assertSame('150.00', $data['accountant_account']['balance']);

        // every id is a positive integer and every money value is a 2-decimal string
        foreach ($collectors as $entry) {
            $this->assertIsInt($entry['id']);
            $this->assertGreaterThan(0, $entry['id']);
            $this->assertMatchesRegularExpression('/^-?[0-9]+\.[0-9]{2}$/', $entry['total_collected']);
            if ($entry['account_balance'] !== null) {
                $this->assertMatchesRegularExpression('/^-?[0-9]+\.[0-9]{2}$/', $entry['account_balance']);
            }
        }
        $this->assertMatchesRegularExpression('/^-?[0-9]+\.[0-9]{2}$/', $data['summary']['total_collected']);
        $this->assertMatchesRegularExpression('/^-?[0-9]+\.[0-9]{2}$/', $data['accountant_account']['balance']);
    }

    public function test_configured_accountant_user_is_included_once_when_applicable(): void
    {
        $fixture = $this->accountantUserFixture();
        $sa = $fixture['superAdmin'];

        $response = $this->withToken(auth('api')->login($sa))
            ->getJson('/api/v1/admin/collector-balances');

        $response->assertOk();

        $data = $response->json('data');

        // accountant user (inactive admin) is appended with the accountant account sum
        $this->assertSame(3, $data['summary']['collectors_count']);
        $this->assertSame('175.00', $data['summary']['total_collected']);

        $collectors = $data['collectors'];
        $this->assertSame(
            [$fixture['cc1']->id, $fixture['accountantUser']->id, $fixture['superAdmin']->id],
            array_column($collectors, 'id'),
        );

        $pushed = $collectors[1];
        $this->assertSame($fixture['accountantUser']->id, $pushed['id']);
        $this->assertSame('accounting', $pushed['role_name']);
        $this->assertSame('محاسب', $pushed['role_label']);
        $this->assertSame('75.00', $pushed['total_collected']);
        $this->assertSame('75.00', $pushed['account_balance']);

        $this->assertSame($fixture['accountantAccount']->id, $data['accountant_account']['id']);
        $this->assertSame('75.00', $data['accountant_account']['balance']);
    }

    public function test_configured_accountant_user_already_in_list_is_not_duplicated(): void
    {
        $fixture = $this->accountantUserAlreadyListedFixture();
        $sa = $fixture['superAdmin'];

        $response = $this->withToken(auth('api')->login($sa))
            ->getJson('/api/v1/admin/collector-balances');

        $response->assertOk();

        $data = $response->json('data');

        $this->assertSame(2, $data['summary']['collectors_count']);
        $this->assertSame('150.00', $data['summary']['total_collected']);

        $collectors = $data['collectors'];
        $this->assertSame(
            [$fixture['au']->id, $fixture['superAdmin']->id],
            array_column($collectors, 'id'),
        );

        // accountant user appears exactly once, with the accountant account sum
        $this->assertSame('150.00', $collectors[0]['total_collected']);
        $this->assertSame('150.00', $collectors[0]['account_balance']);

        $this->assertSame($fixture['accountantAccount']->id, $data['accountant_account']['id']);
        $this->assertSame('150.00', $data['accountant_account']['balance']);
    }

    public function test_active_admin_without_role_uses_web_role_fallback(): void
    {
        AppConfig::create(['key' => 'currency', 'value' => '$']);
        $this->role('Super-Admin', 'مدير عام');
        $this->role('collector', 'محصل');

        $account = $this->account('حساب بلا دور');
        $roleless = $this->admin('موظف بلا دور', 'roleless@example.test', '', $account->id);
        $sa = $this->admin('مدير عام', 'super-admin-roleless@example.test', 'Super-Admin', null);
        $this->transaction($account->id, 20.00);

        $response = $this->withToken(auth('api')->login($sa))
            ->getJson('/api/v1/admin/collector-balances');

        $response->assertOk();

        $collectors = $response->json('data.collectors');
        $this->assertSame([$roleless->id, $sa->id], array_column($collectors, 'id'));

        // Web page falls back to the Arabic accountant label for role-less rows.
        $entry = $collectors[0];
        $this->assertSame('accounting', $entry['role_name']);
        $this->assertSame('محاسب', $entry['role_label']);
        $this->assertSame('20.00', $entry['total_collected']);
    }

    public function test_missing_currency_configuration_returns_safe_500(): void
    {
        $this->role('Super-Admin', 'مدير عام');
        $sa = $this->admin('مدير عام', 'super-admin-nocurrency@example.test', 'Super-Admin', null);

        $response = $this->withToken(auth('api')->login($sa))
            ->getJson('/api/v1/admin/collector-balances');

        $response
            ->assertStatus(500)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'collector_balances_internal_error')
            ->assertJsonStructure(['error' => ['correlation_id']]);
        $this->assertStringNotContainsString('Currency configuration is missing', $response->getContent());
    }

    public function test_response_exposes_only_contract_fields_and_no_pii(): void
    {
        $fixture = $this->balanceFixture();
        $sa = $fixture['superAdmin'];

        $response = $this->withToken(auth('api')->login($sa))
            ->getJson('/api/v1/admin/collector-balances');

        $response->assertOk();
        $content = $response->getContent();

        $forbiddenKeys = ['email', 'phone', 'password', 'real_password', 'token', 'remember_token', 'onesignal_id', 'address', 'transactions'];
        foreach ($forbiddenKeys as $key) {
            $this->assertArrayNotHasKey($key, $response->json('data'));
            $this->assertStringNotContainsString('"'.$key.'"', $content);
        }

        foreach ($fixture['pii'] as $pii) {
            $this->assertStringNotContainsString($pii, $content);
        }
    }

    public function test_internal_failure_returns_safe_envelope_without_exception_detail(): void
    {
        $sa = $this->admin('مدير عام', 'super-admin-err@example.test', 'Super-Admin', null);
        $service = Mockery::mock(CollectorBalancesService::class);
        $service->shouldReceive('snapshot')->once()->andThrow(new \RuntimeException('sensitive-detail-xyz'));
        $this->app->instance(CollectorBalancesService::class, $service);

        $response = $this->withToken(auth('api')->login($sa))
            ->getJson('/api/v1/admin/collector-balances');

        $response
            ->assertStatus(500)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'collector_balances_internal_error')
            ->assertJsonStructure(['error' => ['correlation_id']]);
        $this->assertStringNotContainsString('sensitive-detail-xyz', $response->getContent());
    }

    // -------------------------------------------------------------- helpers

    private function balanceFixture(): array
    {
        AppConfig::create(['key' => 'currency', 'value' => '$']);

        $this->role('Super-Admin', 'مدير عام');
        $this->role('collector', 'محصل');
        $this->role('accounting', 'محاسب');

        $a1 = $this->account('حساب محصل أول');
        $a2 = $this->account('حساب محصل ثان');
        $a3 = $this->account('حساب محاسب أول');
        $aInactive = $this->account('حساب محصل موقوف');
        $aDeleted = $this->account('حساب محصل محذوف');
        $accountantAccount = $this->account('حساب المحاسب الرئيسي');

        $c1 = $this->admin('محصل أول', 'collector-one@example.test', 'collector', $a1->id);
        $c2 = $this->admin('محصل ثان', 'collector-two@example.test', 'collector', $a2->id);
        $c3 = $this->admin('محاسب أول', 'accounting-one@example.test', 'accounting', $a3->id);
        $sa = $this->admin('مدير عام', 'super-admin@example.test', 'Super-Admin', null);

        $inactive = $this->admin('محصل موقوف', 'inactive@example.test', 'collector', $aInactive->id, '0');
        $deleted = $this->admin('محصل محذوف', 'deleted@example.test', 'collector', $aDeleted->id, '1', true);

        $this->transaction($a1->id, 200.00);
        $this->transaction($a2->id, 40.00);
        $this->transaction($a3->id, 10.00);
        $this->transaction($aInactive->id, 999.00);
        $this->transaction($aDeleted->id, 999.00);
        $this->transaction($accountantAccount->id, 150.00);

        AccountSettings::create(['accountant_account_id' => $accountantAccount->id]);

        return [
            'superAdmin' => $sa,
            'c1' => $c1,
            'c2' => $c2,
            'c3' => $c3,
            'sa' => $sa,
            'inactive' => $inactive,
            'deleted' => $deleted,
            'accountantAccount' => $accountantAccount,
            'pii' => [
                'collector-one@example.test',
                'collector-two@example.test',
                'accounting-one@example.test',
                'super-admin@example.test',
                'inactive@example.test',
                'deleted@example.test',
            ],
        ];
    }

    private function accountantUserFixture(): array
    {
        AppConfig::create(['key' => 'currency', 'value' => '$']);

        $this->role('Super-Admin', 'مدير عام');
        $this->role('collector', 'محصل');
        $this->role('accounting', 'محاسب');

        $aCc1 = $this->account('حساب محصل رئيسي');
        $accountantAccount = $this->account('حساب المحاسب المخصص');

        $cc1 = $this->admin('محصل رئيسي', 'collector-main@example.test', 'collector', $aCc1->id);
        $sa = $this->admin('مدير عام', 'super-admin-main@example.test', 'Super-Admin', null);
        // inactive accountant user: not part of the initial active list, so the
        // accountant logic appends them with the accountant account sum
        $accountantUser = $this->admin('المحاسب المخصص', 'accountant-custom@example.test', 'accounting', $accountantAccount->id, '0');

        $this->transaction($aCc1->id, 100.00);
        $this->transaction($accountantAccount->id, 75.00);

        AccountSettings::create(['accountant_account_id' => $accountantAccount->id]);

        return [
            'superAdmin' => $sa,
            'cc1' => $cc1,
            'accountantUser' => $accountantUser,
            'accountantAccount' => $accountantAccount,
        ];
    }

    private function accountantUserAlreadyListedFixture(): array
    {
        AppConfig::create(['key' => 'currency', 'value' => '$']);

        $this->role('Super-Admin', 'مدير عام');
        $this->role('accounting', 'محاسب');

        $accountantAccount = $this->account('حساب المحاسب المعتمد');

        // active accountant user whose own account is the configured accountant account
        $au = $this->admin('المحاسب النشط', 'accountant-active@example.test', 'accounting', $accountantAccount->id);
        $sa = $this->admin('مدير عام', 'super-admin-active@example.test', 'Super-Admin', null);

        $this->transaction($accountantAccount->id, 150.00);

        AccountSettings::create(['accountant_account_id' => $accountantAccount->id]);

        return [
            'superAdmin' => $sa,
            'au' => $au,
            'accountantAccount' => $accountantAccount,
        ];
    }

    private function role(string $name, string $labelAr): Role
    {
        return Role::create([
            'name' => $name,
            'guard_name' => 'admin',
            'title' => ['ar' => $labelAr, 'en' => $name],
        ]);
    }

    private function account(string $name): Account
    {
        return Account::create(['name' => $name]);
    }

    private function admin(
        string $name,
        string $email,
        string $roleName,
        ?int $accountId,
        string $status = '1',
        bool $deleted = false,
    ): Admin {
        $admin = Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('test-password'),
            'status' => $status,
            'account_id' => $accountId,
        ]);

        if ($deleted) {
            $admin->delete();
        }

        if ($roleName !== '') {
            Role::findOrCreate($roleName, 'admin');
            $admin->assignRole($roleName);
        }

        return $admin;
    }

    private function transaction(int $accountId, float $amount): void
    {
        FinancialTransaction::create([
            'account_id' => $accountId,
            'amount' => $amount,
            'date' => now()->toDateString(),
            'time' => now()->toTimeString(),
            'month' => now()->month,
            'year' => now()->year,
            'type' => 'qapd',
        ]);
    }
}
