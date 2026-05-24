<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running migrations...');
        $this->call('migrate');
        $this->info('Seeding AppConfigSeeder...');
        $this->call('db:seed', [
            '--class' => 'AppConfigSeeder',
        ]);

        $this->info('Migrations and seeder have been executed successfully.');

        return 0;
    }
}
