<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SapConnectionService;


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
        $this->app->singleton(SapConnectionService::class, function ($app) {
            return new SapConnectionService();
        });        

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
