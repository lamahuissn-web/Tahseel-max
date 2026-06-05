<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;

class SyncRadiusUser implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(object $event): void
    {
        Artisan::call("radius:sync");
    }
}
