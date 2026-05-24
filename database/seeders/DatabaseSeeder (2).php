<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([CreateAdminUserSeeder::class]);

        ini_set('memory_limit', '-1');

        /*\DB::unprepared(file_get_contents(__dir__ . '/source/main_settings.sql'));
        \DB::unprepared(file_get_contents(__dir__ . '/source/city.sql'));*/


    }
}
