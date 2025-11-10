<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class CompanyObserver
{
    public function creating(Model $model): void
    {
        $this->assignCompany($model);
    }

    public function saving(Model $model): void
    {
        $this->assignCompany($model);
    }

    protected function assignCompany(Model $model): void
    {
        if ($model->getAttribute('company_id')) {
            return;
        }

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

        if ($company === null) {
            return;
        }

        $model->setAttribute('company_id', $company->getKey());
    }
}

