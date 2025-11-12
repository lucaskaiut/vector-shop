<?php

namespace App\Modules\Acl\Domain;

use App\Modules\Acl\Domain\Models\Profile;
use App\Modules\Core\Domain\Serivce;

class ProfileService extends Serivce
{
    public function __construct(Profile $profile)
    {
        parent::__construct($profile);
    }
}
