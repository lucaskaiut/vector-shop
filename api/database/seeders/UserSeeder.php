<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Company\Domain\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrFail();

        User::updateOrCreate(
            ['email' => 'lucas.kaiut@gmail.com'],
            [
                'company_id' => $company->id,
                'name' => 'Lucas Kaiut de Souza',
                'password' => Hash::make('123456'),
                'remember_token' => Str::random(10),
            ]
        );
    }
}

