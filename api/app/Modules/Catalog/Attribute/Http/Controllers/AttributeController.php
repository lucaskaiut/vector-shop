<?php

namespace App\Modules\Catalog\Attribute\Http\Controllers;

use App\Modules\Catalog\Attribute\Domain\AttributeService;
use App\Modules\Catalog\Attribute\Http\Requests\AttributeRequest;
use App\Modules\Catalog\Attribute\Http\Resources\AttributeCollection;
use App\Modules\Catalog\Attribute\Http\Resources\AttributeResource;
use App\Modules\Core\Http\Controllers\CoreController;

class AttributeController extends CoreController
{
    public function __construct(AttributeService $service)
    {
        parent::__construct(
            $service,
            AttributeResource::class,
            AttributeCollection::class,
            AttributeRequest::class
        );
    }
}


