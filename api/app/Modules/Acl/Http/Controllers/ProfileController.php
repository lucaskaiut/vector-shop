<?php

namespace App\Modules\Acl\Http\Controllers;

use App\Modules\Acl\Domain\ProfileService;
use App\Modules\Core\Http\Controllers\CoreController;

class ProfileController extends CoreController
{
    public function __construct(ProfileService $service)
    {
        parent::__construct(
            $service,
            \App\Modules\Acl\Http\Resources\ProfileResource::class,
            \App\Modules\Acl\Http\Resources\ProfileCollection::class,
            \App\Modules\Acl\Http\Requests\ProfileRequest::class
        );
    }
}
