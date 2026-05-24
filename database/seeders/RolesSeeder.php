<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'guard_name' => 'admin',
                'title' => json_encode(['ar' => 'مدير عام', 'en' => 'Super-Admin'], JSON_UNESCAPED_UNICODE),
                'name' => 'super_admin',
            ],
            [
                'guard_name' => 'admin',
                'title' => json_encode(['ar' => 'مدير', 'en' => 'Admin'], JSON_UNESCAPED_UNICODE),
                'name' => 'admin',
            ],
            [
                'guard_name' => 'admin',
                'title' => json_encode(['ar' => 'محصل', 'en' => 'Collector'], JSON_UNESCAPED_UNICODE),
                'name' => 'collector',
            ],
            [
                'guard_name' => 'admin',
                'title' => json_encode(['ar' => 'محاسب', 'en' => 'Accountant'], JSON_UNESCAPED_UNICODE),
                'name' => 'accountant',
            ],
        ];


        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                [
                    'guard_name' => $role['guard_name'],
                    'title' => $role['title'],
                ]
            );
        }
    }
}
