<?php

namespace App\Modules\Company\Domain;

use App\Modules\Company\Domain\Models\Company;
use RuntimeException;

class CompanyRegistry
{
    private ?Company $company = null;

    public function registerCompany(Company $company): void
    {
        $this->company = $company;
    }

    public function getCompany(): Company
    {
        if ($this->company === null) {
            throw new RuntimeException('Nenhuma empresa registrada.');
        }

        return $this->company;
    }
}

