<?php

namespace App\Modules\User\Domain;

use App\Models\User;
use App\Modules\Core\Domain\Serivce;

class UserService extends Serivce
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }
}

