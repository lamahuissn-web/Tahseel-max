<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Services\Bonus\BonusSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\BonusDatabase;
use Tests\TestCase;

/**
 * Feature 022 — the three screens.
 *
 * The list is where the owner decides, so it must show names, the running total and
 * never offer a customer who still owes money. The settings screen must show what a
 * rule change costs BEFORE it is persisted.
 */
class SimpleBonusScreensTest extends TestCase
{
    use BonusDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapBonusDatabase();
        $this->seedRoles();
        BonusSettings::flush();
        $this->seedSettings();

        // the shared phpunit.xml key is not a valid AES length for the web stack
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        $this->app->forgetInstance('encrypter');
    }

    protected function tearDown(): void
    {
        $this->teardownBonusDatabase();
        BonusSettings::flush();
        parent::tearDown();
    }

    public function test_the_followup_page_lists_every_customer_not_only_the_eligible_ones(): void
    {
        $this->setRequired(3);

        $dates = $this->monthlyDues(4);
        $clean = $this->client('زبون منتظم');
        $dirty = $this->client('زبون عليه دين');

        foreach ([$clean, $dirty] as $id) {
            $this->pay($this->invoice($id, $dates[0]), $id, $this->settleDate($dates[0]));
            $this->pay($this->invoice($id, $dates[1]), $id, $this->settleDate($dates[1]));
            $this->pay($this->invoice($id, $dates[2]), $id, $this->settleDate($dates[2]));
        }

        // the debt sits on a SERVICE invoice: it still disqualifies from earning
        $this->invoice($dirty, $dates[0], ['invoice_type' => 'service', 'amount' => '40.00']);

        $response = $this->actingAs($this->adminWithRole('Super-Admin'), 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk();

        // the promise of this screen: the owner can follow EVERY customer
        $response->assertSee('متابعة الزبائن', false);
        $response->assertSee('زبون منتظم', false);
        $response->assertSee('زبون عليه دين', false);
        $response->assertSee('إجمالي الزبائن', false);
    }

    public function test_the_eligible_tab_keeps_only_the_customer_who_earned_the_month(): void
    {
        $this->setRequired(3);

        $dates = $this->monthlyDues(4);
        $clean = $this->client('زبون منتظم');
        $dirty = $this->client('زبون عليه دين');

        foreach ([$clean, $dirty] as $id) {
            foreach ([$dates[0], $dates[1], $dates[2]] as $d) {
                $this->pay($this->invoice($id, $d), $id, $this->settleDate($d));
            }
        }

        $this->invoice($dirty, $dates[0], ['invoice_type' => 'service', 'amount' => '40.00']);

        $this->actingAs($this->adminWithRole('Super-Admin'), 'admin')
            ->get(route('admin.bonus.index', ['tab' => 'eligible']))
            ->assertOk()
            ->assertSee('زبون منتظم', false)
            ->assertDontSee('زبون عليه دين', false);
    }

    public function test_the_search_filters_by_name_and_by_phone(): void
    {
        $this->setRequired(3);
        $dates = $this->monthlyDues(4);

        $a = $this->client('سامر الخطيب');
        $b = $this->client('نبيل حداد');
        foreach ([$a, $b] as $id) {
            $this->pay($this->invoice($id, $dates[0]), $id, $this->settleDate($dates[0]));
        }

        $admin = $this->adminWithRole('Super-Admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index', ['q' => 'سامر']))
            ->assertOk()
            ->assertSee('سامر الخطيب', false)
            ->assertDontSee('نبيل حداد', false);

        $phone = (string) DB::table('tbl_clients')->where('id', $b)->value('phone');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.index', ['q' => $phone]))
            ->assertOk()
            ->assertSee('نبيل حداد', false)
            ->assertDontSee('سامر الخطيب', false);
    }

    public function test_the_list_is_paged_fifty_at_a_time(): void
    {
        $this->setRequired(3);
        $admin = $this->adminWithRole('Super-Admin');

        for ($i = 1; $i <= 51; $i++) {
            $this->client('زبون رقم '.$i);
        }

        $first = $this->actingAs($admin, 'admin')->get(route('admin.bonus.index'))->assertOk();
        $this->assertSame(50, substr_count($first->getContent(), '>تفاصيل</a>'));
        $first->assertSee('صفحة 1 من 2', false);

        $second = $this->actingAs($admin, 'admin')->get(route('admin.bonus.index', ['page' => 2]))->assertOk();
        $this->assertSame(1, substr_count($second->getContent(), '>تفاصيل</a>'));
        $second->assertSee('صفحة 2 من 2', false);
    }

    public function test_the_payment_habit_is_described_from_real_payment_dates(): void
    {
        $this->setRequired(3);
        $dates = $this->monthlyDues(4);

        $early = $this->client('زبون مبكر');
        $late  = $this->client('زبون متأخر');
        $never = $this->client('زبون بلا دفع');

        foreach ([$dates[0], $dates[1], $dates[2]] as $d) {
            $this->pay($this->invoice($early, $d), $early, Carbon::parse($d)->subDays(5)->toDateString());
            $this->pay($this->invoice($late, $d), $late, Carbon::parse($d)->addDays(4)->toDateString());
        }
        $this->invoice($never, $dates[0]);

        $response = $this->actingAs($this->adminWithRole('Super-Admin'), 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk();

        $response->assertSee('يدفع قبل الاستحقاق بـ 5', false);
        $response->assertSee('يتأخر عادةً 4', false);
        $response->assertSee('لا سجل دفع بعد', false);

        // a habit is a sentence, never a score or a band
        $response->assertDontSee('الدرجة', false);
        $response->assertDontSee('دوامة', false);
        $response->assertDontSee('متعثر', false);
    }

    public function test_the_list_states_the_cost_of_issuing_to_everyone(): void
    {
        $this->setRequired(4);

        $dates = $this->monthlyDues(4);
        $a = $this->client('أ', 'internet', true, 15);
        $b = $this->client('ب', 'internet', true, 25);

        foreach ([$a, $b] as $id) {
            foreach ($dates as $d) {
                $this->pay($this->invoice($id, $d), $id, $this->settleDate($d));
            }
        }

        $this->actingAs($this->adminWithRole('Super-Admin'), 'admin')
            ->get(route('admin.bonus.index'))
            ->assertOk()
            ->assertSee('كلفة إصدار للجميع', false)
            ->assertSee('40.00', false);
    }

    public function test_a_lower_threshold_shows_its_cost_before_anything_is_saved(): void
    {
        $dates = $this->monthlyDues(4);
        $id = $this->client('زبون');

        foreach ($dates as $d) {
            $this->pay($this->invoice($id, $d), $id, $this->settleDate($d));
        }

        $before = BonusSettings::monthsRequired();

        $response = $this->actingAs($this->adminWithRole('Super-Admin'), 'admin')
            ->post(route('admin.bonus.settings.save'), [
                'bonus_months_required'  => 3,
                'bonus_grace_days'       => 5,
                'bonus_count_start_date' => $this->monthlyDues(8)[0],
                'bonus_enabled'          => '1',
            ]);

        $response->assertOk();                       // stayed on the page, not a redirect
        $response->assertSee('هذا التغيير يفتح استحقاقاً فورياً', false);

        BonusSettings::flush();
        $this->assertSame($before, BonusSettings::monthsRequired(), 'Nothing may be saved before the owner confirms.');
    }

    public function test_the_rule_is_saved_once_the_owner_confirms_the_cost(): void
    {
        $dates = $this->monthlyDues(4);
        $id = $this->client('زبون');

        foreach ($dates as $d) {
            $this->pay($this->invoice($id, $d), $id, $this->settleDate($d));
        }

        $this->actingAs($this->adminWithRole('Super-Admin'), 'admin')
            ->post(route('admin.bonus.settings.save'), [
                'confirmed'              => '1',
                'bonus_months_required'  => 3,
                'bonus_grace_days'       => 4,
                'bonus_count_start_date' => $this->monthlyDues(8)[0],
                'bonus_enabled'          => '1',
            ])
            ->assertRedirect(route('admin.bonus.settings'));

        BonusSettings::flush();
        $this->assertSame(3, BonusSettings::monthsRequired());
        $this->assertSame(4, BonusSettings::graceDays());
    }

    // ------------------------------------------------------------------ helpers

    private function setRequired(int $months): void
    {
        BonusSettings::set('bonus_months_required', $months);
        BonusSettings::flush();
    }

    private function seedSettings(): void
    {
        foreach ([
            'bonus_enabled'          => '1',
            'bonus_months_required'  => '12',
            'bonus_grace_days'       => '5',
            'bonus_count_start_date' => $this->monthlyDues(8)[0],
        ] as $key => $value) {
            BonusSettings::set($key, $value);
        }

        BonusSettings::flush();
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
        $roleId = (int) DB::table('roles')->where('name', $roleName)->value('id');

        $admin = Admin::create([
            'name'     => 'مشرف '.$roleName,
            'email'    => 'admin'.$roleId.'@example.test',
            'password' => bcrypt('secret'),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id'    => $roleId,
            'model_type' => Admin::class,
            'model_id'   => $admin->id,
        ]);

        return $admin;
    }

    private function monthlyDues(int $n): array
    {
        $dues = [];

        for ($i = $n; $i >= 1; $i--) {
            $dues[] = Carbon::now()->startOfMonth()->subMonthsNoOverflow($i)->toDateString();
        }

        return $dues;
    }

    private function settleDate(string $dueDate): string
    {
        return Carbon::parse($dueDate)->addDays(1)->toDateString();
    }

    private function client(string $name, string $type = 'internet', bool $active = true, float $price = 15.0): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name'              => $name,
            'phone'             => '0555'.random_int(100000, 999999),
            'address1'          => 'عنوان',
            'client_type'       => $type,
            'subscription_id'   => 1,
            'price'             => $price,
            'subscription_date' => '2026-01-01',
            'start_date'        => '2026-01-01',
            'is_active'         => $active ? '1' : '0',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    private function invoice(int $clientId, string $dueDate, array $overrides = []): int
    {
        return DB::table('tbl_invoices')->insertGetId(array_merge([
            'invoice_number'   => 'BINV-'.strtoupper(uniqid()),
            'client_id'        => $clientId,
            'subscription_id'  => 1,
            'amount'           => '15.00',
            'paid_amount'      => '0.00',
            'remaining_amount' => '15.00',
            'enshaa_date'      => $dueDate,
            'due_date'         => $dueDate,
            'status'           => 'unpaid',
            'invoice_type'     => 'subscription',
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $overrides));
    }

    private function pay(int $invoiceId, int $clientId, string $at): void
    {
        $amount = (float) DB::table('tbl_invoices')->where('id', $invoiceId)->value('amount');

        DB::table('tbl_invoices')->where('id', $invoiceId)->update([
            'paid_amount'      => $amount,
            'remaining_amount' => 0,
            'status'           => 'paid',
            'paid_date'        => $at.' 10:00:00',
            'updated_at'       => now(),
        ]);

        DB::table('tbl_revenues')->insert([
            'amount'           => $amount,
            'invoice_id'       => $invoiceId,
            'client_id'        => $clientId,
            'collected_by'     => 1,
            'received_at'      => $at.' 10:00:00',
            'status'           => 'paid',
            'remaining_amount' => 0,
            'created_at'       => $at.' 10:00:00',
            'updated_at'       => $at.' 10:00:00',
        ]);
    }
}
