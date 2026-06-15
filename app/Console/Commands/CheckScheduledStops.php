<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckScheduledStops extends Command
{
    protected $signature = "radius:check-scheduled-stops";

    protected $description = "Disable RADIUS access for clients with scheduled stop date passed";

    public function handle(): int
    {
        $today = now()->format("Y-m-d");

        $clients = DB::connection("mysql")
            ->table("tbl_clients")
            ->whereNotNull("radius_stop_at")
            ->where("radius_stop_at", "<=", $today)
            ->where("is_active", "1")
            ->get();

        $count = 0;

        foreach ($clients as $client) {
            if (!$client->sas_username) {
                continue;
            }

            $exists = DB::connection("radius")
                ->table("radcheck")
                ->where("username", $client->sas_username)
                ->where("attribute", "Auth-Type")
                ->where("value", "Reject")
                ->exists();

            if (!$exists) {
                DB::connection("radius")->table("radcheck")->insert([
                    "username" => $client->sas_username,
                    "attribute" => "Auth-Type",
                    "op" => ":=",
                    "value" => "Reject",
                ]);
                $this->info("Disabled: {$client->name} ({$client->sas_username})");
                $count++;
            }

            DB::connection("mysql")
                ->table("tbl_clients")
                ->where("id", $client->id)
                ->update(["radius_stop_at" => null]);
        }

        $this->line("Scheduled stops processed: {$count} clients disabled");
        return Command::SUCCESS;
    }
}
