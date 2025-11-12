<?php

namespace App\Modules\Core\Domain;

use App\Modules\Core\Domain\Filters\QueryFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Serivce
{
    protected Model $model;

    protected ?QueryFilter $filter;

    protected array $with = [];

    public function __construct(Model $model, ?QueryFilter $filter = null)
    {
        $this->model = $model;
        $this->filter = $filter;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->newQuery(), $filters);
        return $query->paginate($perPage);
    }

    public function list(array $filters = []): Collection
    {
        $query = $this->applyFilters($this->newQuery(), $filters);
        return $query->get();
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->newQuery()->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->newQuery()->findOrFail($id, $columns);
    }

    public function findOne(array $filters, array $columns = ['*']): ?Model
    {
        $query = $this->applyFilters($this->newQuery(), $filters);
        return $query->first($columns);
    }

    public function findOneOrFail(array $filters, array $columns = ['*']): Model
    {
        $query = $this->applyFilters($this->newQuery(), $filters);
        return $query->firstOrFail($columns);
    }

    public function create(array $data): Model
    {
        $model = $this->model->newInstance();
        return $this->save($model, $data);
    }

    public function update(Model $model, array $data): Model
    {
        return $this->save($model, $data);
    }

    public function save(Model $model, array $data): Model
    {
        $model->fill($data);
        $model->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    protected function newQuery(): Builder
    {
        $query = $this->model->newQuery();

        if ($this->with !== []) {
            $query->with($this->with);
        }

        return $query;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if ($this->filter instanceof QueryFilter) {
            return $this->filter->apply($query, $filters);
        }

        foreach ($filters as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query;
    }
}

