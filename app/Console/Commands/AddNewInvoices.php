<?php

namespace App\Console\Commands;

use App\Models\Admin\Invoice;
use App\Models\Clients;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AddNewInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-new-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for all clients on the 1st of every month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clients = Clients::whereNull('deleted_at')->get();

        foreach ($clients as $client) {
            Invoice::create([
                'client_id' => $client->id,
                'invoice_number' => getLastFieldValue(Invoice::class, 'invoice_number'),
                'amount' => $client->price,
                'remaining_amount' => $client->price,
                'subscription_id' => $client->subscription_id,
                'enshaa_date' => Carbon::now()->startOfMonth(),
                'due_date' => Carbon::now()->addMonth()->startOfMonth(),
                'status' => 'unpaid',
            ]);
        }

        $this->info('Invoices have been generated for all clients.');
    }


}
