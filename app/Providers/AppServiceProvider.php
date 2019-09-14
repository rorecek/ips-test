<?php

namespace App\Providers;

use App\Http\Helpers\CRMHelperInterface;
use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CRMHelperInterface::class, InfusionsoftHelper::class);
    }
}
