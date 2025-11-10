<?php

namespace App\Providers;

use App\Modules\Company\Domain\CompanyRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CompanyRegistry::class, function (): CompanyRegistry {
            return new CompanyRegistry();
        });

        $this->app->alias(CompanyRegistry::class, 'company');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
