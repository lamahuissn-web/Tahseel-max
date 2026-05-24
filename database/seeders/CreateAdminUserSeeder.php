<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;*/

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $role = Role::firstOrCreate(
                ['name' => 'super_admin'],
                [
                    'guard_name' => 'admin',
                    'title' => ['ar' => 'مدير عام', 'en' => 'Super-Admin'],
                ]
            );

            $user = Admin::create([
                'name' => 'main_admin',
                'email' => 'main_admin@yahoo.com',
                'password' => Hash::make('main_admin010'),
                'real_password' => 'main_admin010',
                'remember_token' => Str::random(10),
                'group_name'=>$role->id,
            ]);

            $permissions = Permission::where('guard_name', 'admin')->get();

            $role->syncPermissions($permissions);

// Assign the role to the user
            $user->assignRole($role);

        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();

        }
//        Admin::factory()->count(10)->create();
    }
}
