<?php

namespace App\Modules\Catalog\Category\Domain\Filters;

use App\Modules\Core\Domain\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class CategoryFilter extends QueryFilter
{
    public function name(Builder $query, string $value): void
    {
        $this->applyLike($query, 'name', $value);
    }

    public function parentId(Builder $query, int $value): void
    {
        $query->where('parent_id', $value);
    }

    public function url(Builder $query, string $value): void
    {
        $this->applyLike($query, 'url', $value);
    }
}

