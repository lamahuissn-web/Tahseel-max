<?php

namespace Database\Seeders;

use App\Models\Finance\Account;
use Illuminate\Database\Seeder;

class accountsSeeder extends Seeder
{
    public function run()
    {
        // Root categories
        $electronics = Account::create([
            'name' => ['en' => 'Electronics', 'ar' => 'إلكترونيات']
        ]);
        $fashion = Account::create([
            'name' => ['en' => 'Fashion', 'ar' => 'أزياء']
        ]);
        $home = Account::create([
            'name' => ['en' => 'Home', 'ar' => 'منزل']
        ]);

        // Subcategories for Electronics
        $phones = $electronics->children()->create([
            'name' => ['en' => 'Phones', 'ar' => 'هواتف']
        ]);
        $laptops = $electronics->children()->create([
            'name' => ['en' => 'Laptops', 'ar' => 'أجهزة الكمبيوتر المحمولة']
        ]);

        // Subcategories for Phones
        $phones->children()->create([
            'name' => ['en' => 'Smartphones', 'ar' => 'الهواتف الذكية']
        ]);
        $phones->children()->create([
            'name' => ['en' => 'Feature Phones', 'ar' => 'هواتف مميزة']
        ]);

        // Subcategories for Laptops
        $laptops->children()->create([
            'name' => ['en' => 'Ultrabooks', 'ar' => 'الكمبيوترات المحمولة الفائقة']
        ]);
        $laptops->children()->create([
            'name' => ['en' => 'Gaming Laptops', 'ar' => 'أجهزة الكمبيوتر المحمولة للألعاب']
        ]);

        // Subcategories for Fashion
        $men = $fashion->children()->create([
            'name' => ['en' => 'Men', 'ar' => 'رجال']
        ]);
        $women = $fashion->children()->create([
            'name' => ['en' => 'Women', 'ar' => 'نساء']
        ]);

        // Subcategories for Men
        $men->children()->create([
            'name' => ['en' => 'Shirts', 'ar' => 'قمصان']
        ]);
        $men->children()->create([
            'name' => ['en' => 'Pants', 'ar' => 'بنطلونات']
        ]);

        // Subcategories for Women
        $women->children()->create([
            'name' => ['en' => 'Dresses', 'ar' => 'فساتين']
        ]);
        $women->children()->create([
            'name' => ['en' => 'Skirts', 'ar' => 'تنورات']
        ]);

        // Subcategories for Home
        $furniture = $home->children()->create([
            'name' => ['en' => 'Furniture', 'ar' => 'أثاث']
        ]);
        $appliances = $home->children()->create([
            'name' => ['en' => 'Appliances', 'ar' => 'الأجهزة']
        ]);

        // Subcategories for Furniture
        $furniture->children()->create([
            'name' => ['en' => 'Living Room', 'ar' => 'غرفة المعيشة']
        ]);
        $furniture->children()->create([
            'name' => ['en' => 'Bedroom', 'ar' => 'غرفة النوم']
        ]);

        // Subcategories for Appliances
        $appliances->children()->create([
            'name' => ['en' => 'Kitchen', 'ar' => 'مطبخ']
        ]);
        $appliances->children()->create([
            'name' => ['en' => 'Laundry', 'ar' => 'غسيل']
        ]);
    }
}
