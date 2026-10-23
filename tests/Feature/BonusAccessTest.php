<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use App\Services\Bonus\BonusSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature 021 — the bonus section is for the admin staff and management only.
 *
 * Super-Admin (role 1) and accounting (role 7) get in; collector (role 6) gets 403
 * even by typing the URL directly.
 */
class BonusAccessTest extends TestCase
{
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
        $this->seedRoles();
        BonusSettings::flush();

        // The shared phpunit.xml test key is not a valid AES length, which the web
        // (non-API) stack needs for cookies/session. Give this suite a real key.
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        $this->app->forgetInstance('encrypter');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        BonusSettings::flush();
        parent::tearDown();
    }

    public function test_the_dashboard_counts_every_status(): void
    {
        $admin = $this->adminWithRole('Super-Admin');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk();

        foreach (['ممتاز', 'منتظم', 'عائد للانتظام', 'متأخر', 'دوامة', 'متعثر', 'بلا ديون', 'عليهم ديون'] as $label) {
            $response->assertSee($label, false);
        }

        $response->assertSee('خارج البرنامج', false);
        $response->assertSee('CSV', false);
        $response->assertSee('بلا ديون', false);
    }

    public function test_the_filtered_list_can_be_downloaded_as_csv(): void
    {
        $admin = $this->adminWithRole('Super-Admin');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index', ['filter' => 'compliant', 'export' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_super_admin_can_open_the_section(): void
    {
        $admin = $this->adminWithRole('Super-Admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk()
            ->assertSee('البونص والمكافآت');
    }

    public function test_accounting_can_open_the_section(): void
    {
        $admin = $this->adminWithRole('accounting');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk();
    }

    public function test_collector_is_forbidden_on_every_bonus_route(): void
    {
        $admin = $this->adminWithRole('collector');

        foreach ([route('admin.bonus.index'), route('admin.bonus.spiral'), route('admin.bonus.settings')] as $url) {
            $this->actingAs($admin, 'admin')->get($url)->assertForbidden();
        }
    }

    public function test_collector_cannot_open_a_customer_panel(): void
    {
        $admin = $this->adminWithRole('collector');
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'زبون', 'phone' => '0500', 'address1' => 'x', 'client_type' => 'internet',
            'subscription_id' => 1, 'price' => 15, 'subscription_date' => '2026-01-01',
            'start_date' => '2026-01-01', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.show', $clientId))
            ->assertForbidden();
    }

    public function test_settings_screen_changes_the_stored_value(): void
    {
        $admin = $this->adminWithRole('Super-Admin');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.bonus.settings.save'), [
                'bonus_enabled'                 => '1',
                'bonus_months_required'         => '24',
                'bonus_grace_days'              => '5',
                'bonus_catchup_per_cycle'       => '1',
                'bonus_recovery_months'         => '4',
                'bonus_hard_reset_after_months' => '2',
                'bonus_spiral_after_months'     => '2',
                'bonus_count_start_date'        => '2026-01-01',
                'bonus_require_zero_balance'    => '1',
                'bonus_invoice_notes'           => 'شهر مجاني',
                'bonus_client_types'            => 'internet',
            ])
            ->assertRedirect(route('admin.bonus.settings'));

        BonusSettings::flush();

        $this->assertSame(5, BonusSettings::graceDays(), 'The admin can change the grace period at any time.');
        $this->assertSame(4, BonusSettings::recoveryMonths(), 'Clean months needed to become منتظم is editable.');
        $this->assertTrue(BonusSettings::enabled());
    }

    public function test_the_bonus_telegram_switches_live_on_the_telegram_settings_page(): void
    {
        $admin = $this->adminWithRole('Super-Admin');

        // listed with the other Telegram events
        $this->actingAs($admin, 'admin')
            ->get(route('admin.app_config'))
            ->assertOk()
            ->assertSee('telegram_notify_bonus_earned', false)
            ->assertSee('🏆 استحقاق شهر مجاني', false)
            ->assertSee('⏸ تأجيل شهر مجاني', false)
            ->assertSee('🎉 إصدار شهر مجاني', false);

        // and not duplicated on the bonus settings screen
        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.settings'))
            ->assertOk()
            ->assertDontSee('telegram_notify_bonus_earned', false);
    }

    public function test_customer_panel_shows_the_computed_progress(): void
    {
        $admin = $this->adminWithRole('accounting');

        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'عامر تجريبي', 'phone' => '0511', 'address1' => 'x', 'client_type' => 'internet',
            'subscription_id' => 1, 'price' => 15, 'subscription_date' => '2026-01-01',
            'start_date' => '2026-01-01', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('tbl_invoices')->insert([
            'invoice_number' => 'ACC-1', 'client_id' => $clientId, 'subscription_id' => 1,
            'amount' => '15.00', 'paid_amount' => '15.00', 'remaining_amount' => '0.00',
            'enshaa_date' => '2026-01-16', 'due_date' => '2026-01-16', 'status' => 'paid',
            'paid_date' => '2026-01-16 10:00:00',
            'invoice_type' => 'subscription', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.show', $clientId))
            ->assertOk()
            ->assertSee('عامر تجريبي')
            ->assertSee('1 / 24');
    }

    // ---------------------------------------------------------------- helpers

    private function seedRoles(): void
    {
        foreach ([['id' => 1, 'name' => 'Super-Admin'], ['id' => 6, 'name' => 'collector'], ['id' => 7, 'name' => 'accounting']] as $role) {
            DB::table('roles')->updateOrInsert(
                ['id' => $role['id']],
                ['name' => $role['name'], 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function adminWithRole(string $roleName): Admin
    {
        $roleId = DB::table('roles')->where('name', $roleName)->value('id');

        $admin = Admin::create([
            'name'      => 'مستخدم '.$roleName,
            'email'     => strtolower(str_replace(' ', '', $roleName)).'-'.uniqid().'@example.test',
            'password'  => bcrypt('secret'),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id'    => $roleId,
            'model_type' => Admin::class,
            'model_id'   => $admin->id,
        ]);

        return $admin->fresh();
    }
}
