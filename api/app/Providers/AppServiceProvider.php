<?php

namespace App\Providers;

use App\Modules\Company\Domain\CompanyRegistry;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
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
        $this->app->booted(function (): void {
            Model::booted(function (Model $model): void {
                $table = $model->getTable();

                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                    return;
                }

                if ($model->hasGlobalScope(CompanyScope::class)) {
                    return;
                }

                $model->addGlobalScope(new CompanyScope());
            });
        });
    }
}
