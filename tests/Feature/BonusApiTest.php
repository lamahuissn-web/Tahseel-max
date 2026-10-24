<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppConfig;
use App\Models\ClientBonus;
use App\Services\Bonus\BonusLedger;
use App\Services\Bonus\BonusSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature 021 Phase 4 — the bonus section of the mobile app.
 *
 * Read-only and administration-only: role 6 must be refused by the server,
 * not merely hidden in the app.
 */
class BonusApiTest extends TestCase
{
    use \Tests\Support\BonusDatabase;

    private static bool $migrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapBonusDatabase();
        $this->seedRoles();
        $this->seedSettings();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        $this->teardownBonusDatabase();
        BonusSettings::flush();
        Cache::flush();
        parent::tearDown();
    }

    // ------------------------------------------------------------- access

    public function test_a_collector_is_refused_by_the_server(): void
    {
        $collector = $this->adminWithRole('collector');

        $this->withToken($this->tokenFor($collector))
            ->getJson('/api/v1/bonus/summary')
            ->assertStatus(403)
            ->assertJsonPath('result', false)
            ->assertJsonPath('error.code', 'bonus_forbidden');
    }

    public function test_accounting_and_super_admin_are_allowed(): void
    {
        foreach (['Super-Admin', 'accounting'] as $role) {
            $admin = $this->adminWithRole($role);

            $this->withToken($this->tokenFor($admin))
                ->getJson('/api/v1/bonus/summary')
                ->assertOk()
                ->assertJsonPath('result', true);
        }
    }

    public function test_a_request_without_a_token_is_rejected(): void
    {
        $this->getJson('/api/v1/bonus/summary')->assertStatus(401);
    }

    // ------------------------------------------------------------- data

    public function test_the_summary_counts_the_program_customers(): void
    {
        $this->clientWithMonths(24);            // one excellent customer
        $this->clientWithMonths(0, 'satellite'); // out of scope

        $admin = $this->adminWithRole('Super-Admin');

        $response = $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/summary')
            ->assertOk()
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.required', 24);

        $this->assertArrayHasKey('clean', $response->json('data'));
        $this->assertArrayHasKey('late', $response->json('data'));
        $this->assertArrayHasKey('eligible', $response->json('data'));
    }

    public function test_the_list_returns_the_progress_and_the_two_states(): void
    {
        $client = $this->clientWithMonths(24);
        $admin  = $this->adminWithRole('Super-Admin');

        $row = $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients')
            ->assertOk()
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.meta.total', 1)
            ->json('data.rows.0');

        $this->assertSame($client, $row['id']);
        $this->assertSame(24, $row['progress']);
        $this->assertSame(24, $row['required']);
        $this->assertArrayHasKey('statusLabel', $row);
        $this->assertArrayHasKey('nextEligibleMonth', $row);
        $this->assertNull($row['openBonus']);
        // the removed metrics must not creep back into the contract
        $this->assertArrayNotHasKey('habitLabel', $row);
        $this->assertArrayNotHasKey('score', $row);
        $this->assertArrayNotHasKey('pattern', $row);
    }

    public function test_the_list_shows_an_open_postponed_bonus(): void
    {
        $client = $this->clientWithMonths(24);
        $admin  = $this->adminWithRole('Super-Admin');
        ClientBonus::create([
            'client_id' => $client, 'earned_at' => now()->toDateString(),
            'status' => ClientBonus::STATUS_POSTPONED, 'source' => 'auto',
            'cycle_months' => 24, 'postpone_reason' => 'وضع الشركة',
        ]);

        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients')
            ->assertOk()
            ->assertJsonPath('data.rows.0.openBonus', 'postponed')
            ->assertJsonPath('data.rows.0.openBonusLabel', 'مؤجّل');
    }

    public function test_the_eligible_and_clean_filters_are_honest(): void
    {
        $a = $this->clientWithMonths(24);
        $admin = $this->adminWithRole('Super-Admin');

        // an open bonus (even a postponed one) means "already has one" — never eligible
        ClientBonus::create([
            'client_id' => $a, 'earned_at' => now()->toDateString(),
            'status' => ClientBonus::STATUS_POSTPONED, 'source' => 'auto',
            'cycle_months' => 24, 'postpone_reason' => 'وضع الشركة',
        ]);
        Cache::flush();

        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients?filter=eligible')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0);

        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients?filter=clean')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.rows.0.openBonus', 'postponed');

        // the retired filters are refused instead of silently returning everything
        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients?filter=postponed')
            ->assertStatus(422);
    }

    public function test_the_client_details_expose_the_months_grid_read_only(): void
    {
        $client = $this->clientWithMonths(6);
        $admin  = $this->adminWithRole('Super-Admin');

        $data = $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/client/'.$client)
            ->assertOk()
            ->assertJsonPath('data.readOnly', true)
            ->assertJsonPath('data.progress', 6)
            ->json('data');

        $this->assertNotEmpty($data['months']);
        $this->assertArrayHasKey('credited', $data['months'][0]);
        $this->assertArrayHasKey('balance', $data['months'][0]);
        $this->assertArrayNotHasKey('habitSentence', $data);
        $this->assertSame(0, $data['balance']);
    }

    public function test_the_section_still_shows_the_numbers_when_the_program_is_off(): void
    {
        $client = $this->clientWithMonths(24);
        AppConfig::query()->updateOrCreate(['key' => 'bonus_enabled'], ['value' => '0']);
        BonusSettings::flush();
        Cache::flush();

        $admin = $this->adminWithRole('Super-Admin');

        // the owner wants the section visible even with the switch off:
        // `enabled` is informational, the data must still be there
        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/summary')
            ->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.total', 1);

        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients')
            ->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonPath('data.rows.0.id', $client);

        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/client/'.$client)
            ->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.progress', 24);
    }

    public function test_the_list_never_issues_or_changes_anything(): void
    {
        $client = $this->clientWithMonths(24);
        $admin  = $this->adminWithRole('Super-Admin');

        $invoices = DB::table('tbl_invoices')->where('client_id', $client)->count();
        $revenues = DB::table('tbl_revenues')->where('client_id', $client)->count();

        $this->withToken($this->tokenFor($admin))->getJson('/api/v1/bonus/clients')->assertOk();
        $this->withToken($this->tokenFor($admin))->getJson('/api/v1/bonus/client/'.$client)->assertOk();

        $this->assertSame($invoices, DB::table('tbl_invoices')->where('client_id', $client)->count());
        $this->assertSame($revenues, DB::table('tbl_revenues')->where('client_id', $client)->count());
        $this->assertSame(0, ClientBonus::where('client_id', $client)->count());
    }

    // ------------------------------------------------------------- helpers

    private function tokenFor(Admin $admin): string
    {
        return (string) auth('api')->login($admin);
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
            'name'     => 'مستخدم '.$roleName,
            'email'    => strtolower(str_replace(' ', '', $roleName)).'-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => $roleId, 'model_type' => Admin::class, 'model_id' => $admin->id,
        ]);

        return $admin->fresh();
    }

    private function seedSettings(): void
    {
        $defaults = [
            'bonus_enabled'                 => '1',
            'bonus_months_required'         => '24',
            'bonus_grace_days'              => '0',
            'bonus_catchup_per_cycle'       => '1',
            'bonus_hard_reset_after_months' => '2',
            'bonus_spiral_after_months'     => '2',
            'bonus_count_start_date'        => '2026-01-01',
            'bonus_require_zero_balance'    => '1',
            'bonus_client_types'            => 'internet',
        ];

        foreach ($defaults as $key => $value) {
            AppConfig::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        BonusSettings::flush();
    }

    public function test_the_list_sorts_many_rows_and_honours_the_sort_param(): void
    {
        // one row never exercises the comparator — this is how the "credits" crash shipped
        AppConfig::query()->updateOrCreate(
            ['key' => 'bonus_count_start_date'],
            ['value' => \Carbon\Carbon::today()->startOfMonth()->subMonthsNoOverflow(6)->toDateString()],
        );
        AppConfig::query()->updateOrCreate(['key' => 'bonus_enabled'], ['value' => '1']);
        AppConfig::query()->updateOrCreate(['key' => 'bonus_months_required'], ['value' => '3']);
        BonusSettings::flush();

        $tag  = 'SORTTAG'.random_int(1000, 9999);
        $two  = $this->settledMonths($tag, 2);
        $six  = $this->settledMonths($tag, 6);
        $four = $this->settledMonths($tag, 4);

        $admin = $this->adminWithRole('Super-Admin');

        $desc = $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients?search='.$tag.'&sort=progress_desc&per_page=50')
            ->assertOk()
            ->json('data.rows');
        $this->assertCount(3, $desc);
        $this->assertSame([$six, $four, $two], array_column($desc, 'id'));
        $this->assertSame([6, 4, 2], array_column($desc, 'progress'));

        $asc = $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients?search='.$tag.'&sort=progress_asc&per_page=50')
            ->assertOk()
            ->json('data.rows');
        $this->assertSame([$two, $four, $six], array_column($asc, 'id'));

        // an unknown sort value must fall back to the default, never blow up
        $this->withToken($this->tokenFor($admin))
            ->getJson('/api/v1/bonus/clients?search='.$tag.'&sort=nonsense')
            ->assertOk();
    }

    /** creates a client with $months fully settled months, tagged for isolation */
    private function settledMonths(string $tag, int $months): int
    {
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => $tag.' زبون', 'phone' => '0556'.random_int(100000, 999999), 'address1' => 'x',
            'client_type' => 'internet', 'subscription_id' => 1, 'price' => 15,
            'subscription_date' => '2026-01-01', 'start_date' => '2026-01-01',
            'is_active' => '1', 'created_at' => now(), 'updated_at' => now(),
        ]);

        for ($i = $months; $i >= 1; $i--) {
            $due = \Carbon\Carbon::today()->startOfMonth()->subMonthsNoOverflow($i)->day(5)->toDateString();
            $invoiceId = DB::table('tbl_invoices')->insertGetId([
                'invoice_number' => 'SRT-'.strtoupper(uniqid()), 'client_id' => $clientId,
                'subscription_id' => 1, 'amount' => '15.00', 'paid_amount' => '15.00',
                'remaining_amount' => 0, 'enshaa_date' => $due, 'due_date' => $due,
                'status' => 'paid', 'invoice_type' => 'subscription', 'paid_date' => $due.' 10:00:00',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('tbl_revenues')->insert([
                'client_id' => $clientId, 'invoice_id' => $invoiceId, 'amount' => '15.00',
                'collected_by' => 1, 'created_at' => $due.' 10:00:00', 'updated_at' => now(),
            ]);
        }

        return $clientId;
    }

    private function clientWithMonths(int $months, string $type = 'internet'): int
    {
        $clientId = DB::table('tbl_clients')->insertGetId([
            'name' => 'زبون بونص', 'phone' => '0555'.random_int(100000, 999999), 'address1' => 'x',
            'client_type' => $type, 'subscription_id' => 1, 'price' => 15,
            'subscription_date' => '2026-01-01', 'start_date' => '2026-01-01',
            'is_active' => '1', 'created_at' => now(), 'updated_at' => now(),
        ]);

        if ($months === 0) {
            return $clientId;
        }

        $dates = [];
        for ($i = $months; $i >= 1; $i--) {
            $dates[] = \Carbon\Carbon::today()->startOfMonth()->subMonthsNoOverflow($i)->day(5)->toDateString();
        }
        AppConfig::query()->updateOrCreate(['key' => 'bonus_count_start_date'], ['value' => $dates[0]]);
        BonusSettings::flush();

        foreach ($dates as $due) {
            $invoiceId = DB::table('tbl_invoices')->insertGetId([
                'invoice_number' => 'API-'.strtoupper(uniqid()), 'client_id' => $clientId,
                'subscription_id' => 1, 'amount' => '15.00', 'paid_amount' => '15.00',
                'remaining_amount' => 0, 'enshaa_date' => $due, 'due_date' => $due,
                'status' => 'paid', 'invoice_type' => 'subscription', 'paid_date' => $due.' 10:00:00',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('tbl_revenues')->insert([
                'client_id' => $clientId, 'invoice_id' => $invoiceId, 'amount' => '15.00',
                'collected_by' => 1, 'created_at' => $due.' 10:00:00', 'updated_at' => now(),
            ]);
        }

        return $clientId;
    }
}
