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

//        $this->call([CreateAdminUserSeeder::class,HrTypeSettingSeeder::class,EmployeesTableSeeder::class]);
/*$this->call(CreateAdminUserSeeder::class);
$this->call(HrTypeSettingSeeder::class);
$this->call(EmployeesTableSeeder::class);
$this->call(PermissionsSeeder::class);*/
// $this->call(accountsSeeder::class);
        // ini_set('memory_limit', '-1');

        $this->call(CreateAdminUserSeeder::class);
        $this->call(BranchesSeeder::class);
        $this->call(AreaSettingsSeeder::class);
        $this->call(SiteDataSeeder::class);

    }
}
