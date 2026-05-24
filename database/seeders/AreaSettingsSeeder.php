<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tbl_area_settings')->insert([
            ['title' => 'المنوفيه', 'parent_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'شبين الكوم', 'parent_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'الشهداء', 'parent_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'القليوبيه', 'parent_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'طوخ', 'parent_id' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['title' => 'بنها', 'parent_id' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
