<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_config')->updateOrInsert(
            ['key' => 'whatsapp_safety_settings_lock'],
            ['value' => '1', 'updated_at' => now(), 'created_at' => now()]
        );

        $permission = Permission::findOrCreate('update_whatsapp_safety_settings', 'admin');
        DB::table('permissions')->where('id', $permission->id)->update([
            'title' => json_encode([
                'ar' => 'تحديث إعدادات أمان WhatsApp',
                'en' => 'Update WhatsApp safety settings',
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);

        Role::query()
            ->whereIn('name', ['Super-Admin', 'super_admin'])
            ->where('guard_name', 'admin')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (! Schema::hasIndex('app_config', 'app_config_key_unique')) {
            $duplicateKey = DB::table('app_config')
                ->select('key')
                ->whereNotNull('key')
                ->groupBy('key')
                ->havingRaw('COUNT(*) > 1')
                ->value('key');
            if ($duplicateKey !== null) {
                throw new RuntimeException('Cannot enforce unique app_config keys while duplicate rows exist.');
            }

            Schema::table('app_config', function (Blueprint $table): void {
                $table->unique('key', 'app_config_key_unique');
            });
        }
    }

    public function down(): void
    {
        // Intentionally irreversible: retaining this safety permission and
        // uniqueness guarantee is safer than weakening a rolled-back deploy.
    }
};
