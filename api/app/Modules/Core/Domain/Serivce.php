<?php

namespace App\Modules\Core\Domain;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Serivce
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);
        return $query->paginate($perPage);
    }

    public function list(array $filters = []): Collection
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);
        return $query->get();
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->model->newQuery()->find($id, $columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->model->newQuery()->findOrFail($id, $columns);
    }

    public function findOne(array $filters, array $columns = ['*']): ?Model
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);
        return $query->first($columns);
    }

    public function findOneOrFail(array $filters, array $columns = ['*']): Model
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);
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

        return $model;
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
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

