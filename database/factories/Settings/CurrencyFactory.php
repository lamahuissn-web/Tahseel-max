<?php

namespace Database\Factories\Settings;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\settings\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $table_data= [

            ['name' => json_encode(['ar' => 'الدولار', 'en' => 'Dollar']), 'sb' => json_encode(['en'=>'$','ar'=>'$'])]

        ];
        return [
            'name'=>fake()->unique()->randomElement($table_data['name']),
        ];
    }
}
