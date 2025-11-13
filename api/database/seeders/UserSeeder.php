<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Acl\Domain\Models\Profile;
use App\Modules\Acl\Domain\Permission;
use App\Modules\Company\Domain\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrFail();

        $allPermissions = Permission::values();

        $profile = Profile::firstOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Administrador',
            ]
        );

        $profile->permissions = $allPermissions;
        $profile->save();

        User::updateOrCreate(
            ['email' => 'lucas.kaiut@gmail.com'],
            [
                'company_id' => $company->id,
                'profile_id' => $profile->id,
                'name' => 'Lucas Kaiut de Souza',
                'password' => Hash::make('123456'),
                'remember_token' => Str::random(10),
            ]
        );
    }
}

