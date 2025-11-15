<?php

namespace App\Modules\Catalog\Attribute\Domain\Filters;

use App\Modules\Core\Domain\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class AttributeFilter extends QueryFilter
{
    public function name(Builder $query, string $value): void
    {
        $this->applyLike($query, 'name', $value);
    }
}


