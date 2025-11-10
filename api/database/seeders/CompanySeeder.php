<?php

namespace Database\Seeders;

use App\Modules\Company\Domain\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['domain' => 'vector-shop.com'],
            [
                'name' => 'Vector Shop',
                'phone' => '5541987654321',
                'logo' => 'https://example.com/logos/vector-shop.png',
            ]
        );
    }
}

