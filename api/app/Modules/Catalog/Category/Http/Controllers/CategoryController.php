<?php

namespace App\Modules\Catalog\Category\Http\Controllers;

use App\Modules\Catalog\Category\Domain\CategoryService;
use App\Modules\Catalog\Category\Http\Requests\CategoryRequest;
use App\Modules\Catalog\Category\Http\Resources\CategoryCollection;
use App\Modules\Catalog\Category\Http\Resources\CategoryResource;
use App\Modules\Core\Http\Controllers\CoreController;

class CategoryController extends CoreController
{
    public function __construct(
        private readonly CategoryService $service,
    ) {
        parent::__construct(
            $service,
            CategoryResource::class,
            CategoryCollection::class,
            CategoryRequest::class
        );
    }
}

