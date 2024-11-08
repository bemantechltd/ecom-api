<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Dotenv\Dotenv;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        // if (file_exists($localEnvPath = base_path('.env.local'))) {
        //     $dotenv = Dotenv::createImmutable(base_path(), '.env.local');
        //     $dotenv->load();
        // }
    }
}
