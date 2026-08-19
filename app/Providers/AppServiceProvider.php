<?php

namespace App\Providers;

use App\Services\SellerAccountService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(SellerAccountService $sellerAccounts): void
    {
        if ($this->app->runningUnitTests() || $this->app->runningInConsole()) {
            return;
        }

        $sellerAccounts->ensureSeedData();
    }
}
