<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('site_data')->insert([
            [
                'image' => 'site_logo.png',
                'name' => json_encode(['en' => 'My Laboratory', 'ar' => 'مختبري']),
                'email' => 'info@mylaboratory.com',
                'address' => json_encode(['en' => '123 Lab Street, Science City', 'ar' => '123 شارع المختبر، مدينة العلوم']),
                'fax' => '123-456-789',
                'phone' => '987-654-321',
                'description' => json_encode(['en' => 'We provide high-quality laboratory services.', 'ar' => 'نقدم خدمات مختبر عالية الجودة.']),
                'contract_terms' => json_encode(['en' => 'All services are subject to terms and conditions.', 'ar' => 'تخضع جميع الخدمات للشروط والأحكام.']),
                'discount_ratio' => 10,
                'tax_number' => 'TAX123456789',
                'commercial_registration_number' => 'CR987654321',
                'image_print' => 'print_logo.png',
                'video' => null,
                'maplocation' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
