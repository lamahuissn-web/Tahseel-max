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
    use \Tests\Support\BonusDatabase;

    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapBonusDatabase();
        $this->seedRoles();
        BonusSettings::flush();

        // The shared phpunit.xml test key is not a valid AES length, which the web
        // (non-API) stack needs for cookies/session. Give this suite a real key.
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        $this->app->forgetInstance('encrypter');
    }

    protected function tearDown(): void
    {
        $this->teardownBonusDatabase();
        BonusSettings::flush();
        parent::tearDown();
    }

    public function test_the_list_screen_opens_and_states_the_rule(): void
    {
        $admin = $this->adminWithRole('Super-Admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk()
            ->assertSee('متابعة الزبائن', false)
            ->assertSee('مستحق الآن', false);
    }

    public function test_super_admin_can_open_the_section(): void
    {
        $admin = $this->adminWithRole('Super-Admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk()
            ->assertSee('متابعة الزبائن', false);
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

        foreach ([route('admin.bonus.index'), route('admin.bonus.settings')] as $url) {
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
                'confirmed'              => '1',
                'bonus_enabled'          => '1',
                'bonus_months_required'  => '24',
                'bonus_grace_days'       => '5',
                'bonus_count_start_date' => '2026-01-01',
            ])
            ->assertRedirect(route('admin.bonus.settings'));

        BonusSettings::flush();

        $this->assertSame(24, BonusSettings::monthsRequired(), 'The owner sets the threshold.');
        $this->assertSame(5, BonusSettings::graceDays(), 'The admin can change the grace period at any time.');
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

        BonusSettings::set('bonus_count_start_date', '2026-01-01');
        BonusSettings::set('bonus_months_required', 24);
        BonusSettings::flush();

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

    /**
     * Every bonus route must carry its guard. This walks the real route table, so a
     * route added later without the middleware fails here instead of silently
     * exposing the section — including `apply`, which issues a real invoice.
     */
    public function test_every_bonus_route_carries_the_bonus_guard(): void
    {
        $admin = [];
        $api   = [];

        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            $uri = $route->uri();

            if (str_starts_with($uri, 'api/') && str_contains($uri, 'bonus')) {
                $api[$uri] = $this->resolvedMiddleware($route);
            } elseif (str_contains($uri, 'admin/bonus')) {
                $admin[$uri] = $this->resolvedMiddleware($route);
            }
        }

        $this->assertGreaterThanOrEqual(10, count($admin), 'The admin bonus surface unexpectedly shrank.');
        $this->assertGreaterThanOrEqual(3, count($api), 'The bonus API surface unexpectedly shrank.');

        foreach ($admin as $uri => $middleware) {
            $this->assertContains(
                \App\Http\Middleware\EnsureBonusAccess::class,
                $middleware,
                "Route {$uri} is missing the EnsureBonusAccess guard."
            );
            $this->assertContains(
                'App\Http\Middleware\Authenticate:admin',
                $middleware,
                "Route {$uri} is missing the admin authentication guard."
            );
        }

        foreach ($api as $uri => $middleware) {
            $this->assertContains(
                \App\Http\Middleware\EnsureBonusApiAccess::class,
                $middleware,
                "Route {$uri} is missing the EnsureBonusApiAccess guard."
            );
            $this->assertContains(
                \App\Http\Middleware\JwtMiddleware::class,
                $middleware,
                "Route {$uri} is missing the JWT guard."
            );
        }
    }

    /**
     * The guard must actually refuse, not merely be listed: a collector is denied
     * on all 13 admin routes (the list, the writes and the log alike).
     */
    public function test_a_collector_is_denied_on_every_bonus_route(): void
    {
        $collector = $this->adminWithRole('collector');
        $checked   = 0;

        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            if (! str_contains($route->uri(), 'admin/bonus')) {
                continue;
            }

            $params = [];
            foreach ($route->parameterNames() as $name) {
                $params[$name] = 1;
            }

            $method = in_array('POST', $route->methods(), true) ? 'post' : 'get';

            $this->actingAs($collector, 'admin')
                ->{$method}(route($route->getName(), $params))
                ->assertStatus(403);

            $checked++;
        }

        $this->assertGreaterThanOrEqual(10, $checked, 'Every admin bonus route must be exercised.');
    }

    /**
     * The financial boundary: a refused issuance must leave no invoice behind.
     */
    public function test_a_refused_issuance_creates_no_invoice(): void
    {
        $collector = $this->adminWithRole('collector');

        $client = DB::table('tbl_clients')->insertGetId([
            'name' => 'عميل بونص', 'phone' => '0555000111', 'address1' => 'عنوان',
            'client_type' => 'internet', 'subscription_id' => 1, 'price' => 15,
            'subscription_date' => '2026-01-01', 'start_date' => '2026-01-01',
            'is_active' => '1', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $bonusId = DB::table('tbl_client_bonuses')->insertGetId([
            'client_id' => $client,
            'earned_at' => now()->toDateString(),
            'status'    => 'approved',
            'source'    => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $before = DB::table('tbl_invoices')->count();

        $this->actingAs($collector, 'admin')
            ->post(route('admin.bonus.apply', ['bonusId' => $bonusId]))
            ->assertStatus(403);

        $this->assertSame($before, DB::table('tbl_invoices')->count(), 'A refused issuance must not write an invoice.');
        $this->assertSame('approved', DB::table('tbl_client_bonuses')->where('id', $bonusId)->value('status'), 'A refused issuance must not touch the bonus row.');
    }

    /**
     * Route middleware are stored as aliases (`bonus.access`, `auth:admin`), so they
     * are resolved to class names here — asserting on the alias alone would pass even
     * if the alias were later re-pointed at another class.
     *
     * @return array<int, string>
     */
    private function resolvedMiddleware(\Illuminate\Routing\Route $route): array
    {
        $aliases = app('router')->getMiddleware();
        $resolved = [];

        foreach ($route->gatherMiddleware() as $entry) {
            if (! is_string($entry)) {
                $resolved[] = get_class($entry);

                continue;
            }

            [$alias, $params] = array_pad(explode(':', $entry, 2), 2, null);
            $class = $aliases[$alias] ?? $alias;
            $resolved[] = $params === null ? $class : $class.':'.$params;
        }

        return $resolved;
    }

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
