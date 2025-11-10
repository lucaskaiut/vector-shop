<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RuntimeException;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $app = app();

        if (!$app->bound('company')) {
            return;
        }

        $companyRegistry = $app->make('company');

        try {
            $company = $companyRegistry->getCompany();
        } catch (RuntimeException) {
            return;
        }

        $builder->where(
            $model->qualifyColumn('company_id'),
            $company->getKey()
        );
    }
}


