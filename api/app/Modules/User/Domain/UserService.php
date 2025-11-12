<?php

namespace App\Modules\User\Domain;

use App\Models\User;
use App\Modules\Core\Domain\Serivce;
use App\Modules\User\Domain\Filters\UserFilter;

class UserService extends Serivce
{
    protected array $with = ['profile'];

    public function __construct(User $user, UserFilter $filter)
    {
        parent::__construct($user, $filter);
    }
}

