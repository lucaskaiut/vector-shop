<?php

namespace App\Modules\Core\Domain;

use App\Modules\Core\Domain\Filters\QueryFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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

    /**
     * Sincroniza registros relacionados a partir de comandos explícitos
     * (criar, atualizar ou remover) enviados pelo frontend.
     *
     * @param array<array<string, mixed>> $commands
     * @param array<int, string> $allowedFields
     * @param callable|null $preparePayload fn(array $command, bool $isUpdate): array
     */
    protected function syncRelationCommands(
        Model $parent,
        string $relationName,
        array $commands,
        array $allowedFields = [],
        ?callable $preparePayload = null
    ): void {
        if ($commands === []) {
            return;
        }

        if (!method_exists($parent, $relationName)) {
            throw new InvalidArgumentException(sprintf(
                'A relação %s não existe no modelo %s.',
                $relationName,
                $parent::class
            ));
        }

        $relation = $parent->{$relationName}();

        if (!$relation instanceof Relation) {
            throw new InvalidArgumentException(sprintf(
                'O relacionamento %s em %s não é suportado.',
                $relationName,
                $parent::class
            ));
        }

        $related = $relation->getRelated();
        $keyName = $related->getKeyName();
        $existing = $relation->get()->keyBy($keyName);

        foreach ($commands as $command) {
            $recordId = $command[$keyName] ?? $command['id'] ?? null;
            $shouldDelete = (bool) ($command['delete'] ?? false);
            $isUpdate = $recordId !== null;

            $payload = $preparePayload
                ? $preparePayload($command, $isUpdate)
                : $this->filterRelationPayload($command, $allowedFields);

            if ($isUpdate) {
                $model = $existing->get($recordId);

                if ($model === null) {
                    continue;
                }

                if ($shouldDelete) {
                    $model->delete();
                    continue;
                }

                if ($payload === []) {
                    continue;
                }

                $model->fill($payload);
                $model->save();
                continue;
            }

            if ($shouldDelete) {
                continue;
            }

            if ($payload === []) {
                continue;
            }

            $relation->create($payload);
        }

        $parent->load($relationName);
    }

    protected function filterRelationPayload(array $payload, array $allowedFields): array
    {
        if ($allowedFields === []) {
            return Arr::except($payload, ['id', 'delete']);
        }

        return Arr::only($payload, $allowedFields);
    }
}

