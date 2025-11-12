<?php

namespace App\Modules\Core\Domain\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

abstract class QueryFilter
{
    public function apply(Builder $query, array $filters = []): Builder
    {
        foreach ($filters as $name => $value) {
            if ($this->shouldIgnore($value)) {
                continue;
            }

            $method = $this->resolveMethod($name);

            if (method_exists($this, $method)) {
                $this->{$method}($query, $value);
                continue;
            }

            $this->applyDefault($query, $name, $value);
        }

        return $query;
    }

    protected function resolveMethod(string $name): string
    {
        return Str::camel($name);
    }

    protected function shouldIgnore(mixed $value): bool
    {
        if (is_array($value)) {
            return empty(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        }

        return $value === null || $value === '';
    }

    protected function applyDefault(Builder $query, string $column, mixed $value): void
    {
        if (is_array($value)) {
            $query->whereIn($column, $value);
            return;
        }

        $query->where($column, $value);
    }

    protected function applyLike(Builder $query, string $column, string $value): void
    {
        $query->where($column, 'like', '%' . $value . '%');
    }
}
