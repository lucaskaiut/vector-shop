<?php

namespace App\Modules\User\Domain\Filters;

use App\Modules\Core\Domain\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends QueryFilter
{
    public function name(Builder $query, string $name): void
    {
        $this->applyLike($query, 'name', $name);
    }

    public function email(Builder $query, string $email): void
    {
        $this->applyLike($query, 'email', $email);
    }
}
