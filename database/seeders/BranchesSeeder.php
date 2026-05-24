<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tbl_branches')->insert([
            // [
            //     'name' => 'toukh Branch',
            //     'area_setting_id' => 5,
            //     'address' => '123 Main Street, toukh',
            //     'phone' => '123-456-789',
            //     'color' => '#FF5733',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'name' => 'benha Branch',
            //     'area_setting_id' => 6,
            //     'address' => '456 Market Avenue, benha',
            //     'phone' => '987-654-321',
            //     'color' => '#33FF57',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            [
                'name' => 'sheben Branch',
                'area_setting_id' => 2,
                'address' => '789 Country Road, sheben',
                'phone' => '456-789-123',
                'color' => '#3357FF',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
