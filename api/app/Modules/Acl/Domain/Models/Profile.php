<?php

namespace App\Modules\Acl\Domain\Models;

use App\Models\User;
use App\Modules\Company\Domain\Models\Company;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }

    protected function permissions(): Attribute
    {
        return Attribute::make(
            get: static fn (?array $value) => $value ?? [],
            set: static function ($value) {
                if (!is_array($value)) {
                    $value = [];
                }

                $filtered = [];

                foreach ($value as $permission) {
                    if (!is_string($permission)) {
                        continue;
                    }

                    if (!in_array($permission, \App\Modules\Acl\Domain\Permission::values(), true)) {
                        continue;
                    }

                    if (!in_array($permission, $filtered, true)) {
                        $filtered[] = $permission;
                    }
                }

                return ['permissions' => $filtered];
            }
        );
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
