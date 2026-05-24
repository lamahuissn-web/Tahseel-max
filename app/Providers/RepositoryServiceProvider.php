<?php

namespace App\Providers;

use App\Interfaces\BasicRepositoryInterface;
use App\Repositories\BasicRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BasicRepositoryInterface::class, BasicRepository::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
