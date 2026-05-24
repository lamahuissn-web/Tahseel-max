<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HrTypeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('hr_type_setting')->insert([
            [
                'title' => json_encode(['ar' => 'الحاله الاجتماعية', 'en' => 'الحاله الاجتماعية']),
                'code' => '101',
            ],
            [
                'title' => json_encode(['ar' => 'التخصص', 'en' => 'التخصص']),
                'code' => '104',
            ],
            [
                'title' => json_encode(['ar' => 'نوع العقود', 'en' => 'نوع العقود']),
                'code' => '105',
            ],
            [
                'title' => json_encode(['ar' => 'المسمى الوظيفي', 'en' => 'المسمى الوظيفي']),
                'code' => '106',
            ],
            [
                'title' => json_encode(['ar' => 'الاستحقاقات', 'en' => 'الاستحقاقات']),
                'code' => '107',
            ],
            [
                'title' => json_encode(['ar' => 'الاستقطاعات', 'en' => 'الاستقطاعات']),
                'code' => '108',
            ],
            [
                'title' => json_encode(['ar' => 'الملفات', 'en' => 'الملفات']),
                'code' => '109',
            ],
            [
                'title' => json_encode(['ar' => 'اسباب الاجازات', 'en' => 'اسباب الاجازات']),
                'code' => '110',
            ],
            [
                'title' => json_encode(['ar' => 'اسباب الاذونات', 'en' => 'اسباب الاذونات']),
                'code' => '111',
            ],
            [
                'title' => json_encode(['ar' => 'فئة الموظف', 'en' => 'فئة الموظف']),
                'code' => '102',
            ],
        ]);
    }
}

