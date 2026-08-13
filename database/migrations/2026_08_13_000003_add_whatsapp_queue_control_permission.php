<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permission = Permission::findOrCreate('control_whatsapp_queue', 'admin');
        DB::table('permissions')->where('id', $permission->id)->update([
            'title' => json_encode([
                'ar' => 'التحكم في طابور واتساب',
                'en' => 'Control WhatsApp queue',
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ]);

        Role::query()
            ->whereIn('name', ['Super-Admin', 'super_admin'])
            ->where('guard_name', 'admin')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        Permission::query()
            ->where('name', 'control_whatsapp_queue')
            ->where('guard_name', 'admin')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
