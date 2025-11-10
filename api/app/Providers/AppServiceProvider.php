<?php

namespace App\Providers;

use App\Modules\Company\Domain\CompanyRegistry;
use App\Observers\CompanyObserver;
use App\Scopes\CompanyScope;
use Illuminate\Contracts\Events\Dispatcher;
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
    public function boot(Dispatcher $events): void
    {
        $events->listen('eloquent.booted: *', function (string $event, array $payload): void {
            /** @var Model $model */
            $model = $payload[0] ?? null;

            if (!$model instanceof Model) {
                return;
            }

            $table = $model->getTable();

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'company_id')) {
                return;
            }

            if ($model->hasGlobalScope(CompanyScope::class)) {
                return;
            }

            $model->addGlobalScope(new CompanyScope());

            static $observed = [];
            $class = $model::class;

            if (!isset($observed[$class])) {
                $class::observe(CompanyObserver::class);
                $observed[$class] = true;
            }
        });
    }
}
