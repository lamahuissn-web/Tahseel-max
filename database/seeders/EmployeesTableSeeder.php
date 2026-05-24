<?php

namespace Database\Seeders;

use App\Models\hr\employee\Employee;
use App\Models\hr\setting\MainSetting;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EmployeesTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $socialStatusIds = MainSetting::where('type_code', 101)->pluck('id')->toArray();
        $jop_titleIds = MainSetting::where('type_code', 106)->pluck('id')->toArray();
        $specializationIds = MainSetting::where('type_code', 104)->pluck('id')->toArray();

        for ($i = 0; $i < 100; $i++) {
            Employee::create([
                'emp_code' => Employee::max('emp_code') + 1, // Ensure unique emp_code
                'name' => $faker->name,
                'national_id' => $faker->ean13,
                'birthday' => $faker->date,
                'address' => $faker->address,
                'social_status' => $faker->randomElement($socialStatusIds),
                'specialization' => $faker->randomElement($specializationIds),
                'email' => $faker->email,
                'phone' => $faker->boolean,
                'experience_year' => $faker->numberBetween(0, 20),
                'date_hired' => $faker->date,
                'job_title' => $faker->randomElement($jop_titleIds), // Assuming job_title IDs exist in your MainSetting model
                'work_hour_day' => $faker->numberBetween(6, 10),
                'work_month_day' => $faker->numberBetween(20, 30),
                'holiday_emergency' => $faker->boolean,
                'holiday_year' => $faker->boolean,
                'main_salary' => $faker->numberBetween(2000, 5000),
                'image' => $faker->imageUrl(),
            ]);
        }
    }
}
