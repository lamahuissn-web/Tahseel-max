<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\UpdateWhatsAppSafetySettingsRequest;
use App\Models\Admin;
use App\Models\Role;
use App\Services\WhatsApp\WhatsAppSafetySettings;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WhatsAppSafetySettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
        ]);
        DB::purge();
        Cache::setDefaultDriver('array');

        $this->createApplicationTables();
        $this->createPermissionTables();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_presets_match_the_approved_safe_timings(): void
    {
        $presets = app(WhatsAppSafetySettings::class)->presets();

        $this->assertSame([
            'base_delay' => 10,
            'jitter_percent' => 40,
            'hourly_limit' => 60,
            'daily_limit' => 300,
            'batch_pause_every' => 25,
            'batch_pause_min_seconds' => 180,
            'batch_pause_max_seconds' => 420,
        ], $presets['balanced']);
        $this->assertSame([
            'base_delay' => 15,
            'jitter_percent' => 40,
            'hourly_limit' => 40,
            'daily_limit' => 200,
            'batch_pause_every' => 20,
            'batch_pause_min_seconds' => 240,
            'batch_pause_max_seconds' => 480,
        ], $presets['very_safe']);
    }

    public function test_missing_settings_use_balanced_defaults(): void
    {
        $settings = app(WhatsAppSafetySettings::class)->settings();

        $this->assertSame('balanced', $settings['preset']);
        $this->assertSame(10, $settings['base_delay']);
        $this->assertSame(40, $settings['jitter_percent']);
        $this->assertSame(60, $settings['hourly_limit']);
        $this->assertSame(300, $settings['daily_limit']);
        $this->assertSame(25, $settings['batch_pause_every']);
        $this->assertSame(180, $settings['batch_pause_min_seconds']);
        $this->assertSame(420, $settings['batch_pause_max_seconds']);
        $this->assertTrue($settings['enabled']);
    }

    public function test_invalid_preset_falls_back_to_balanced_defaults(): void
    {
        DB::table('app_config')->insert([
            ['key' => 'whatsapp_rate_preset', 'value' => 'unsafe_mode'],
            ['key' => 'whatsapp_rate_base_delay', 'value' => '120'],
            ['key' => 'whatsapp_rate_hourly_limit', 'value' => '10'],
        ]);

        $settings = app(WhatsAppSafetySettings::class)->settings();

        $this->assertSame('balanced', $settings['preset']);
        $this->assertSame(10, $settings['base_delay']);
        $this->assertSame(60, $settings['hourly_limit']);
    }

    public function test_legacy_default_delay_without_a_preset_is_classified_as_balanced(): void
    {
        DB::table('app_config')->insert([
            'key' => 'whatsapp_auto_delay',
            'value' => '10',
        ]);

        $settings = app(WhatsAppSafetySettings::class)->settings();

        $this->assertSame('balanced', $settings['preset']);
        $this->assertSame(10, $settings['base_delay']);
    }

    public function test_legacy_delay_without_a_preset_is_preserved_as_custom(): void
    {
        DB::table('app_config')->insert([
            'key' => 'whatsapp_auto_delay',
            'value' => '20',
        ]);

        $settings = app(WhatsAppSafetySettings::class)->settings();

        $this->assertSame('custom', $settings['preset']);
        $this->assertSame(20, $settings['base_delay']);
        $this->assertSame(60, $settings['hourly_limit']);
        $this->assertSame(300, $settings['daily_limit']);
    }

    public function test_legacy_rate_values_without_a_preset_are_preserved_and_normalized(): void
    {
        DB::table('app_config')->insert([
            ['key' => 'whatsapp_rate_hourly_limit', 'value' => '30'],
            ['key' => 'whatsapp_rate_daily_limit', 'value' => '150'],
            ['key' => 'whatsapp_rate_batch_pause_every', 'value' => 'invalid'],
        ]);

        $settings = app(WhatsAppSafetySettings::class)->settings();

        $this->assertSame('custom', $settings['preset']);
        $this->assertSame(30, $settings['hourly_limit']);
        $this->assertSame(150, $settings['daily_limit']);
        $this->assertSame(25, $settings['batch_pause_every']);
    }

    public function test_corrupt_partial_custom_rows_use_independent_safe_defaults(): void
    {
        DB::table('app_config')->insert([
            ['key' => 'whatsapp_rate_preset', 'value' => 'custom'],
            ['key' => 'whatsapp_rate_base_delay', 'value' => 'not-a-number'],
            ['key' => 'whatsapp_rate_jitter_percent', 'value' => null],
            ['key' => 'whatsapp_rate_hourly_limit', 'value' => '61'],
            ['key' => 'whatsapp_rate_batch_pause_every', 'value' => ''],
            ['key' => 'whatsapp_rate_batch_pause_min_seconds', 'value' => '600'],
            ['key' => 'whatsapp_rate_batch_pause_max_seconds', 'value' => '180'],
        ]);

        $settings = app(WhatsAppSafetySettings::class)->settings();

        $this->assertSame('custom', $settings['preset']);
        $this->assertSame(10, $settings['base_delay']);
        $this->assertSame(40, $settings['jitter_percent']);
        $this->assertSame(60, $settings['hourly_limit']);
        $this->assertSame(300, $settings['daily_limit']);
        $this->assertSame(25, $settings['batch_pause_every']);
        $this->assertSame(180, $settings['batch_pause_min_seconds']);
        $this->assertSame(420, $settings['batch_pause_max_seconds']);
        $this->assertGreaterThanOrEqual(4, $this->effectiveMinimumDelay($settings));
    }

    #[DataProvider('effectiveDelayCases')]
    public function test_effective_delay_rounding_boundary(int $baseDelay, int $jitter, bool $shouldFail): void
    {
        $input = $this->validCustomSettings([
            'base_delay' => $baseDelay,
            'jitter_percent' => $jitter,
        ]);

        $validator = $this->validatorFor($input);

        $this->assertSame($shouldFail, $validator->fails());
        if ($shouldFail) {
            $this->assertArrayHasKey('base_delay', $validator->errors()->toArray());
        }
    }

    public static function effectiveDelayCases(): array
    {
        return [
            'three seconds is rejected after PHP rounding' => [7, 50, true],
            'four seconds is accepted after PHP rounding' => [8, 50, false],
        ];
    }

    #[DataProvider('invalidCustomSettings')]
    public function test_custom_validation_enforces_every_hard_rule(array $overrides, string $errorKey): void
    {
        $input = $this->validCustomSettings($overrides);
        $validator = $this->validatorFor($input);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($errorKey, $validator->errors()->toArray());
    }

    public static function invalidCustomSettings(): array
    {
        return [
            'invalid preset' => [['preset' => 'unrestricted'], 'preset'],
            'required field' => [['base_delay' => null], 'base_delay'],
            'integer field' => [['base_delay' => '8.5'], 'base_delay'],
            'base below range' => [['base_delay' => 5], 'base_delay'],
            'base above range' => [['base_delay' => 121], 'base_delay'],
            'jitter below range' => [['jitter_percent' => 9], 'jitter_percent'],
            'jitter above range' => [['jitter_percent' => 51], 'jitter_percent'],
            'hourly below range' => [['hourly_limit' => 9], 'hourly_limit'],
            'hourly above range' => [['hourly_limit' => 61], 'hourly_limit'],
            'daily below range' => [['daily_limit' => 19], 'daily_limit'],
            'daily above range' => [['daily_limit' => 301], 'daily_limit'],
            'batch below range' => [['batch_pause_every' => 9], 'batch_pause_every'],
            'batch above range' => [['batch_pause_every' => 51], 'batch_pause_every'],
            'pause minimum below range' => [['batch_pause_min_seconds' => 179], 'batch_pause_min_seconds'],
            'pause maximum above range' => [['batch_pause_max_seconds' => 3601], 'batch_pause_max_seconds'],
            'inverted pause range' => [[
                'batch_pause_min_seconds' => 420,
                'batch_pause_max_seconds' => 180,
            ], 'batch_pause_max_seconds'],
        ];
    }

    public function test_preset_save_persists_every_key_and_complete_audit_context(): void
    {
        $saved = app(WhatsAppSafetySettings::class)->save(['preset' => 'very_safe'], [
            'admin_id' => 7,
            'ip_address' => '192.0.2.10',
            'user_agent' => str_repeat('A', 300),
        ]);

        $expected = ['enabled' => true, 'preset' => 'very_safe'] + app(WhatsAppSafetySettings::class)->presets()['very_safe'];
        $this->assertSame($expected, $saved);

        foreach ($expected as $name => $value) {
            if ($name !== 'enabled') {
                $row = DB::table('app_config')->where('key', 'whatsapp_rate_'.$name)->sole();
                $this->assertSame((string) $value, $row->value);
                $this->assertSame(7, $row->updated_by);
            }
        }
        $this->assertSame('1', DB::table('app_config')->where('key', 'whatsapp_rate_limiter_enabled')->value('value'));
        $this->assertSame('15', DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value'));

        $audit = DB::table('logs')->where('action', 'whatsapp_safety_settings_updated')->sole();
        $this->assertSame(7, $audit->user_id);
        $this->assertSame('192.0.2.10', $audit->ip_address);
        $this->assertSame(255, strlen($audit->user_agent));
        $this->assertSame('balanced', json_decode($audit->old_data, true, 512, JSON_THROW_ON_ERROR)['preset']);
        $this->assertSame($expected, json_decode($audit->new_data, true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_custom_save_persists_validated_custom_values(): void
    {
        $custom = $this->validCustomSettings([
            'base_delay' => 20,
            'jitter_percent' => 25,
            'hourly_limit' => 30,
            'daily_limit' => 150,
            'batch_pause_every' => 15,
            'batch_pause_min_seconds' => 300,
            'batch_pause_max_seconds' => 600,
        ]);

        $saved = app(WhatsAppSafetySettings::class)->save($custom, [
            'admin_id' => 4,
            'ip_address' => '2001:db8::1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertSame($custom, array_intersect_key($saved, $custom));
        $this->assertSame('20', DB::table('app_config')->where('key', 'whatsapp_auto_delay')->value('value'));
        $this->assertSame(1, DB::table('logs')->where('user_id', 4)->count());
    }

    public function test_unauthenticated_admin_cannot_update_safety_settings(): void
    {
        $this->post(route('admin.whatsapp.safety.update'), ['preset' => 'balanced'])
            ->assertRedirect(route('admin.login'));

        $this->assertSame(0, DB::table('logs')->count());
    }

    public function test_admin_without_permission_receives_forbidden(): void
    {
        $admin = Admin::create([
            'name' => 'Restricted',
            'email' => 'restricted@example.test',
            'password' => 'test-password',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.whatsapp.safety.update'), ['preset' => 'balanced'])
            ->assertForbidden();

        $this->assertSame(0, DB::table('logs')->count());
    }

    public function test_authorized_admin_can_update_through_http_endpoint(): void
    {
        $admin = $this->authorizedAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.whatsapp.safety.update'), ['preset' => 'balanced'])
            ->assertRedirect(route('admin.whatsapp.safety'));

        $this->assertSame('balanced', DB::table('app_config')->where('key', 'whatsapp_rate_preset')->value('value'));
        $this->assertSame($admin->id, DB::table('logs')->sole()->user_id);
    }

    public function test_authorized_admin_can_persist_custom_settings_through_http_endpoint(): void
    {
        $admin = $this->authorizedAdmin();
        $custom = $this->validCustomSettings([
            'base_delay' => 20,
            'hourly_limit' => 30,
            'daily_limit' => 150,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.whatsapp.safety.update'), $custom)
            ->assertRedirect(route('admin.whatsapp.safety'));

        $this->assertSame('custom', DB::table('app_config')->where('key', 'whatsapp_rate_preset')->value('value'));
        $this->assertSame('30', DB::table('app_config')->where('key', 'whatsapp_rate_hourly_limit')->value('value'));
        $this->assertSame($admin->id, DB::table('logs')->sole()->user_id);
    }

    public function test_authorized_invalid_custom_request_does_not_persist_or_audit(): void
    {
        $admin = $this->authorizedAdmin();
        $invalid = $this->validCustomSettings(['hourly_limit' => 61]);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.whatsapp.safety'))
            ->post(route('admin.whatsapp.safety.update'), $invalid)
            ->assertRedirect(route('admin.whatsapp.safety'))
            ->assertSessionHasErrors('hourly_limit');

        $this->assertSame(0, DB::table('app_config')->where('key', 'like', 'whatsapp_rate_%')->count());
        $this->assertSame(0, DB::table('logs')->count());
    }

    public function test_safety_migration_adds_unique_keys_and_grants_super_admin_permission(): void
    {
        $role = Role::create(['name' => 'Super-Admin', 'guard_name' => 'admin']);
        $migration = require database_path('migrations/2026_07_30_160000_add_whatsapp_safety_controls.php');

        $migration->up();
        $migration->up();

        $permission = Permission::findByName(UpdateWhatsAppSafetySettingsRequest::PERMISSION, 'admin');
        $this->assertTrue($role->fresh()->hasPermissionTo($permission));

        DB::table('app_config')->insert(['key' => 'unique-key', 'value' => 'first']);
        $this->expectException(QueryException::class);
        DB::table('app_config')->insert(['key' => 'unique-key', 'value' => 'second']);
    }

    public function test_safety_migration_fails_safely_when_duplicate_config_keys_exist(): void
    {
        DB::table('app_config')->insert([
            ['key' => 'duplicate-key', 'value' => 'first'],
            ['key' => 'duplicate-key', 'value' => 'second'],
        ]);
        $migration = require database_path('migrations/2026_07_30_160000_add_whatsapp_safety_controls.php');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('duplicate rows exist');
        $migration->up();
    }

    private function validCustomSettings(array $overrides = []): array
    {
        return array_replace([
            'preset' => 'custom',
            'base_delay' => 10,
            'jitter_percent' => 40,
            'hourly_limit' => 60,
            'daily_limit' => 300,
            'batch_pause_every' => 25,
            'batch_pause_min_seconds' => 180,
            'batch_pause_max_seconds' => 420,
        ], $overrides);
    }

    private function authorizedAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Safety Admin',
            'email' => uniqid('safety-', true).'@example.test',
            'password' => 'test-password',
        ]);
        $permission = Permission::firstOrCreate([
            'name' => UpdateWhatsAppSafetySettingsRequest::PERMISSION,
            'guard_name' => 'admin',
        ]);
        $admin->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $admin;
    }

    private function validatorFor(array $input): \Illuminate\Validation\Validator
    {
        $request = UpdateWhatsAppSafetySettingsRequest::create('/', 'POST', $input);

        return Validator::make($input, $request->rules(), $request->messages());
    }

    private function effectiveMinimumDelay(array $settings): int
    {
        return $settings['base_delay'] - (int) round($settings['base_delay'] * ($settings['jitter_percent'] / 100));
    }

    private function createApplicationTables(): void
    {
        Schema::create('app_config', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
        DB::table('app_config')->insert([
            'key' => 'whatsapp_safety_settings_lock',
            'value' => '1',
        ]);
        Schema::create('logs', function (Blueprint $table): void {
            $table->id();
            $table->string('action');
            $table->text('description');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    private function createPermissionTables(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->json('title')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->json('title')->nullable();
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });
        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });
    }
}
