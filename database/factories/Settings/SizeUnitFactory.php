<?php

namespace Database\Factories\Settings;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\settings\SizeUnit>
 */
class SizeUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $table_data= [

                ['name' => json_encode(['ar' => 'المتر المربع', 'en' => 'Square Meter']), 'sb' => 'm²'],
                ['name' => json_encode(['ar' => 'الكيلومتر المربع', 'en' => 'Square Kilometer']), 'sb' => 'km²'],
                ['name' => json_encode(['ar' => 'الهكتار ', 'en' => 'Hectare']), 'sb' => 'ha'],
                ['name' => json_encode(['ar' => 'الفدان ', 'en' => 'Acre']), 'sb' => 'ac'],
                ['name' => json_encode(['ar' => 'الفترة المربعة', 'en' => 'Square Furlong']), 'sb' => 'fur²'],
                ['name' => json_encode(['ar' => 'الميل المربع', 'en' => 'Square Mile']), 'sb' => 'mi²'],
                ['name' => json_encode(['ar' => 'الياردة المربعة', 'en' => 'Square Yard']), 'sb' => 'yd²'],
                ['name' => json_encode(['ar' => 'القدم المربع', 'en' => 'Square Foot']), 'sb' => 'ft²']

        ];
        /*return [
            'name'=>fake()->unique()->randomElement($table_data['name']),
        ];*/
return $table_data;
    }
}
