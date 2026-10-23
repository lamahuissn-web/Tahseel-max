<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppConfig;
use App\Models\ClientBonus;
use App\Services\Bonus\BonusLedger;
use App\Services\Bonus\BonusProgressService;
use App\Services\Bonus\BonusSettings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

/**
 * Feature 021 — Phase 2: the writes.
 *
 * Contract:
 *  - an earned bonus is a proposal until an admin approves it;
 *  - issuing the free month creates exactly ONE 0-amount invoice for the next
 *    cycle month, with that cycle's normal due date, settled, and NO revenue row;
 *  - never while the customer still owes money;
 *  - never twice;
 *  - cancel / reset never touch existing invoices or payments;
 *  - satellite customers are outside the program.
 */
class BonusActionsTest extends TestCase
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
        $this->seedSettings();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        $this->app->forgetInstance('encrypter');
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        BonusSettings::flush();
        parent::tearDown();
    }

    // ------------------------------------------------------------ granting

    public function test_manual_grant_creates_one_open_bonus(): void
    {
        $client = $this->client();

        $bonus = $this->ledger()->grant($client, 1, 'استثناء إداري');

        $this->assertSame(ClientBonus::STATUS_PENDING, $bonus->status);
        $this->assertSame('manual', $bonus->source);
        $this->assertSame(1, ClientBonus::where('client_id', $client)->open()->count());
    }

    public function test_a_second_open_bonus_is_refused(): void
    {
        $client = $this->client();
        $this->ledger()->grant($client, 1);

        $this->expectException(RuntimeException::class);
        $this->ledger()->grant($client, 1);
    }

    public function test_inactive_customers_cannot_be_granted_a_bonus(): void
    {
        $client = $this->client('internet', false);

        $this->expectException(RuntimeException::class);
        $this->ledger()->grant($client, 1);
    }

    public function test_satellite_customers_cannot_be_granted_a_bonus(): void
    {
        $client = $this->client('satellite');

        $this->expectException(RuntimeException::class);
        $this->ledger()->grant($client, 1);
    }

    // ----------------------------------------------------------- approvals

    public function test_approve_records_who_and_when(): void
    {
        $client = $this->client();
        $bonus = $this->ledger()->grant($client, 1);

        $approved = $this->ledger()->approve($bonus->id, 9);

        // auto-apply is ON and the customer owes nothing, so it is issued at once
        $this->assertContains($approved->status, [ClientBonus::STATUS_APPROVED, ClientBonus::STATUS_REDEEMED]);
        $this->assertSame(9, (int) $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
    }

    public function test_cancel_closes_the_bonus_without_touching_payments(): void
    {
        $client = $this->client();
        $inv = $this->invoice($client, '2026-01-16');
        $this->pay($inv, $client, '2026-01-16');

        $before = DB::table('tbl_invoices')->where('client_id', $client)->count();
        $bonus = $this->ledger()->grant($client, 1);
        $this->ledger()->cancel($bonus->id, 1, 'طلب الزبون');

        $this->assertSame(ClientBonus::STATUS_CANCELLED, $bonus->fresh()->status);
        $this->assertSame($before, DB::table('tbl_invoices')->where('client_id', $client)->count());
        $this->assertSame(1, DB::table('tbl_revenues')->where('client_id', $client)->count());
    }

    // -------------------------------------------------------------- issuing

    public function test_apply_now_creates_exactly_one_zero_invoice(): void
    {
        $client = $this->client();
        foreach (['2026-08-16', '2026-09-16'] as $due) {
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        $bonus = $this->ledger()->grant($client, 1, null, true);
        $result = $this->ledger()->applyNow($bonus->id, 1);

        $invoice = DB::table('tbl_invoices')->find($result['invoice_id']);

        $this->assertSame('0.00', number_format((float) $invoice->amount, 2));
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('subscription', $invoice->invoice_type);
        $this->assertSame('2026-10-16', $invoice->due_date, 'The free month is the next cycle month with the normal day.');
        $this->assertSame(1, DB::table('tbl_invoices')->where('client_id', $client)->where('amount', 0)->count());
        $this->assertSame(0, DB::table('tbl_revenues')->where('invoice_id', $invoice->id)->count(), 'No revenue row for a 0 invoice.');

        $fresh = $bonus->fresh();
        $this->assertSame(ClientBonus::STATUS_REDEEMED, $fresh->status);
        $this->assertSame((int) $invoice->id, (int) $fresh->redeemed_invoice_id);
        $this->assertNotNull($fresh->redeemed_at);
    }

    public function test_apply_now_is_blocked_while_the_customer_owes(): void
    {
        $client = $this->client();
        $this->invoice($client, '2026-09-16');           // unpaid → debt
        $bonus = $this->ledger()->grant($client, 1, null, true);

        $this->expectException(RuntimeException::class);
        $this->ledger()->applyNow($bonus->id, 1);
    }

    public function test_apply_now_cannot_run_twice(): void
    {
        $client = $this->client();
        $inv = $this->invoice($client, '2026-09-16');
        $this->pay($inv, $client, '2026-09-16');

        $bonus = $this->ledger()->grant($client, 1, null, true);
        $this->ledger()->applyNow($bonus->id, 1);

        $this->expectException(RuntimeException::class);
        $this->ledger()->applyNow($bonus->id, 1);
    }

    public function test_apply_now_requires_approval_first(): void
    {
        $client = $this->client();
        $bonus = $this->ledger()->grant($client, 1);      // pending

        $this->expectException(RuntimeException::class);
        $this->ledger()->applyNow($bonus->id, 1);
    }

    // ---------------------------------------------------- points and resets

    public function test_manual_points_raise_the_progress(): void
    {
        $client = $this->client();
        $inv = $this->invoice($client, '2026-01-16');
        $this->pay($inv, $client, '2026-01-16');

        $this->assertSame(1, $this->progress()->forClient($client)->progress);

        $this->ledger()->addPoints($client, 3, 1, 'ظرف خاص');
        BonusSettings::flush();

        $this->assertSame(4, $this->progress()->forClient($client)->progress);
    }

    public function test_reset_also_wipes_points_granted_before_it(): void
    {
        $client = $this->client();
        $this->ledger()->addPoints($client, 5, 1, 'قبل التصفير');
        $this->assertSame(5, $this->progress()->forClient($client)->progress);

        $this->ledger()->resetCycle($client, 1, 'تصفير');

        $this->assertSame(0, $this->progress()->forClient($client)->progress);
    }

    public function test_points_still_count_when_the_cycle_has_no_invoices_yet(): void
    {
        $client = $this->client();
        $this->ledger()->addPoints($client, 2, 1, 'بعد الاستبدال');

        $this->assertSame(2, $this->progress()->forClient($client)->progress);
    }

    public function test_reset_moves_the_cycle_origin_to_zero(): void
    {
        $client = $this->client();
        foreach (['2026-01-16', '2026-02-16', '2026-03-16'] as $due) {
            $inv = $this->invoice($client, $due);
            $this->pay($inv, $client, $due);
        }

        $this->assertSame(3, $this->progress()->forClient($client)->progress);

        $this->ledger()->resetCycle($client, 1, 'قرار إداري');

        $fresh = $this->progress()->forClient($client);
        $this->assertSame(0, $fresh->progress, 'Everything earned before the reset is dropped.');
    }

    // ------------------------------------------------------------ interface

    public function test_collector_cannot_fire_bonus_actions(): void
    {
        $this->seedRoles();
        $roleId = DB::table('roles')->where('name', 'collector')->value('id');

        $admin = Admin::create(['name' => 'جابي', 'email' => 'c'.uniqid().'@x.test', 'password' => bcrypt('x')]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => Admin::class, 'model_id' => $admin->id]);

        $client = $this->client();

        $this->actingAs($admin->fresh(), 'admin')
            ->post(route('admin.bonus.grant', $client))
            ->assertForbidden();
    }

    public function test_admin_can_grant_and_approve_through_the_ui(): void
    {
        $this->seedRoles();
        $roleId = DB::table('roles')->where('name', 'Super-Admin')->value('id');

        $admin = Admin::create(['name' => 'أدمن', 'email' => 'a'.uniqid().'@x.test', 'password' => bcrypt('x')]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => Admin::class, 'model_id' => $admin->id]);

        $client = $this->client();

        $this->actingAs($admin->fresh(), 'admin')
            ->post(route('admin.bonus.grant', $client), ['notes' => 'اختبار'])
            ->assertRedirect();

        $bonus = ClientBonus::where('client_id', $client)->first();
        $this->assertNotNull($bonus);

        $this->actingAs($admin->fresh(), 'admin')
            ->post(route('admin.bonus.approve', $bonus->id))
            ->assertRedirect();

        $this->assertContains($bonus->fresh()->status, [ClientBonus::STATUS_APPROVED, ClientBonus::STATUS_REDEEMED]);
    }

    public function test_customer_panel_shows_the_payment_time_and_catchup_months(): void
    {
        $this->seedRoles();
        $roleId = DB::table('roles')->where('name', 'Super-Admin')->value('id');
        $admin = Admin::create(['name' => 'أدمن', 'email' => 'p'.uniqid().'@x.test', 'password' => bcrypt('x')]);
        DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => Admin::class, 'model_id' => $admin->id]);

        $client = $this->client();
        $jul = $this->invoice($client, '2026-07-16');
        $aug = $this->invoice($client, '2026-08-16');
        $sep = $this->invoice($client, '2026-09-16');

        $this->pay($jul, $client, '2026-07-16');
        $this->pay($aug, $client, '2026-09-20');   // broken month, settled late
        $this->pay($sep, $client, '2026-09-20');

        $this->actingAs($admin->fresh(), 'admin')
            ->get(route('admin.bonus.show', $client))
            ->assertOk()
            ->assertSee('استدراك')
            ->assertSee('2026-09-20 10:00')
            ->assertSee('تاريخ ووقت الدفع')
            ->assertSee('ما تبقّى قبل تغيّر التصنيف')
            ->assertSee('التاريخ الحاسم');
    }

    // ---------------------------------------------------------------- helpers

    private function ledger(): BonusLedger
    {
        return app(BonusLedger::class);
    }

    private function progress(): BonusProgressService
    {
        return app(BonusProgressService::class);
    }

    // ------------------------------------------------------------ admin panel UX

    public function test_the_panel_states_the_bonus_position_in_arabic(): void
    {
        $admin  = $this->bonusAdmin();
        $client = $this->client();
        $this->invoice($client, '2026-03-05');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.bonus.show', $client))
            ->assertOk();

        $response->assertSee('حالة البونص الآن', false);
        $response->assertSee('التقدّم', false);
        $response->assertSee('لا يوجد بونص مفتوح', false);
        $response->assertSee('إجراءات الإدارة', false);
        $response->assertSee('تعديل النقاط يدوياً', false);
        // the old stale sentence must never come back
        $response->assertDontSee('سيُضاف في المرحلة القادمة', false);
    }

    public function test_issuing_the_invoice_is_disabled_while_the_customer_owes_money(): void
    {
        $admin  = $this->bonusAdmin();
        $client = $this->client();

        // approved bonus + one open invoice
        $bonus = app(BonusLedger::class)->grant($client, $admin->id, 'test', true);
        DB::table('tbl_invoices')->insert([
            'client_id' => $client, 'subscription_id' => 1, 'amount' => 25, 'remaining_amount' => 25,
            'invoice_number' => 'INV-'.uniqid(),
            'paid_amount' => 0, 'enshaa_date' => '2026-09-05', 'due_date' => '2026-09-05',
            'status' => 'unpaid', 'invoice_type' => 'subscription', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $html = $this->actingAs($admin, 'admin')->get(route('admin.bonus.show', $client))->getContent();

        $this->assertStringContainsString('يجب تصفيته قبل إصدار الفاتورة', $html);
        $this->assertMatchesRegularExpression('/disabled/', $html, 'The apply button must be disabled with a balance.');
    }

    public function test_dangerous_actions_ask_for_a_simple_confirmation_without_typing(): void
    {
        $admin  = $this->bonusAdmin();
        $client = $this->client();
        $this->invoice($client, '2026-03-05');

        $response = $this->actingAs($admin, 'admin')->get(route('admin.bonus.show', $client));

        $response->assertSee('js-confirm', false);
        $response->assertSee('Swal.fire', false);
        // typed confirmations were dropped: a plain yes/no dialog is enough
        $response->assertDontSee('data-typed', false);
    }

    public function test_a_pending_bonus_granted_by_mistake_can_be_deleted(): void
    {
        $admin  = $this->bonusAdmin();
        $client = $this->client();

        $bonus = app(BonusLedger::class)->grant($client, $admin->id, 'ضغط خاطئ');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.bonus.delete', $bonus->id), ['reason' => 'ضغطت الزر بالخطأ'])
            ->assertRedirect(route('admin.bonus.show', $client));

        $this->assertSame(0, ClientBonus::where('client_id', $client)->count());
        $this->assertSame(0, ClientBonus::where('client_id', $client)->open()->count());
    }

    public function test_an_approved_bonus_cannot_be_deleted(): void
    {
        $admin  = $this->bonusAdmin();
        $client = $this->client();

        $bonus = app(BonusLedger::class)->grant($client, $admin->id, 'استحقاق');
        app(BonusLedger::class)->approve($bonus->id, $admin->id);

        $this->from(route('admin.bonus.show', $client))
            ->actingAs($admin, 'admin')
            ->post(route('admin.bonus.delete', $bonus->id))
            ->assertRedirect(route('admin.bonus.show', $client));

        // the row survives, and the ledger refuses with a clear Arabic message
        $this->assertSame(1, ClientBonus::where('client_id', $client)->count());

        $this->expectExceptionMessage('يمكن حذف البونص المعلّق فقط');
        app(BonusLedger::class)->deletePending($bonus->id, $admin->id);
    }

    public function test_deleting_a_pending_bonus_does_not_touch_invoices_or_balance(): void
    {
        $admin  = $this->bonusAdmin();
        $client = $this->client();
        $this->invoice($client, '2026-04-05');

        $before = DB::table('tbl_invoices')->where('client_id', $client)->count();
        $bonus  = app(BonusLedger::class)->grant($client, $admin->id, 'ضغط خاطئ');

        $this->actingAs($admin, 'admin')->post(route('admin.bonus.delete', $bonus->id));

        $this->assertSame($before, DB::table('tbl_invoices')->where('client_id', $client)->count());
        $this->assertSame(0, DB::table('tbl_revenues')->where('client_id', $client)->count());
    }

    /** An admin that passes the bonus-access middleware (Super-Admin). */
    private function bonusAdmin(): Admin
    {
        DB::table('roles')->updateOrInsert(
            ['id' => 1],
            ['name' => 'Super-Admin', 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now()]
        );

        $admin = Admin::create([
            'name' => 'أدمن بونص', 'email' => 'ba'.uniqid().'@x.test', 'password' => bcrypt('x'),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => 1, 'model_type' => Admin::class, 'model_id' => $admin->id,
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
            'bonus_auto_apply_on_approve'   => '1',
            'bonus_invoice_notes'           => 'شهر مجاني - برنامج البونص',
        ];

        foreach ($defaults as $key => $value) {
            AppConfig::query()->updateOrCreate(['key' => $key], ['value' => $value]);
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

    private function client(string $type = 'internet', bool $active = true): int
    {
        return DB::table('tbl_clients')->insertGetId([
            'name' => 'زبون بونص', 'phone' => '0555'.random_int(100000, 999999), 'address1' => 'x',
            'client_type' => $type, 'subscription_id' => 1, 'price' => 15,
            'subscription_date' => '2026-01-01', 'start_date' => '2026-01-01',
            'is_active' => $active ? '1' : '0', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function invoice(int $clientId, string $dueDate): int
    {
        return DB::table('tbl_invoices')->insertGetId([
            'invoice_number' => 'AINV-'.strtoupper(uniqid()), 'client_id' => $clientId, 'subscription_id' => 1,
            'amount' => '15.00', 'paid_amount' => '0.00', 'remaining_amount' => '15.00',
            'enshaa_date' => $dueDate, 'due_date' => $dueDate, 'status' => 'unpaid',
            'invoice_type' => 'subscription', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function pay(int $invoiceId, int $clientId, string $at): void
    {
        DB::table('tbl_invoices')->where('id', $invoiceId)->update([
            'paid_amount' => 15, 'remaining_amount' => 0, 'status' => 'paid',
            'paid_date' => $at.' 10:00:00', 'updated_at' => now(),
        ]);

        DB::table('tbl_revenues')->insert([
            'amount' => 15, 'invoice_id' => $invoiceId, 'client_id' => $clientId,
            'collected_by' => 1, 'received_at' => $at.' 10:00:00', 'status' => 'paid',
            'remaining_amount' => 0, 'created_at' => $at.' 10:00:00', 'updated_at' => $at.' 10:00:00',
        ]);
    }
}
