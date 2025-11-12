<?php

namespace App\Modules\Acl\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProfileCollection extends ResourceCollection
{
    public $collects = ProfileResource::class;
}
