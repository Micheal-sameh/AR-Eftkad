<?php

namespace App\Providers;

use App\Auth\AvarewaseUserProvisioner;
use Avarewase\SsoClient\Contracts\ProvisionsAvarewaseUsers;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProvisionsAvarewaseUsers::class, AvarewaseUserProvisioner::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
