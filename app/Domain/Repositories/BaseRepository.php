<?php

namespace App\Domain\Repositories;

use App\Domain\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(
        protected Model $model
    ) {
    }

    public function find(int|string $id): ?object
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model->get();
    }

    public function delete(int|string $id): bool
    {
        $model = $this->model->find($id);
        return is_null($model) ? false : $model->delete();
    }

    public function update(array $attributes, int|string $id): bool
    {
        $model = $this->model->find($id);
        return is_null($model) ? false : $model->update($attributes);
    }

    public function findBy(array $filters): Collection
    {
        $builder = $this->appendStandardFilters($this->model->query(), $filters);

        return $builder->get();
    }

    public function findOneBy(array $filters): ?object
    {
        $builder = $this->appendStandardFilters($this->model->query(), $filters);

        return $builder->first();
    }

    public function save(array $attributes): object
    {
        $this->model->fill($attributes)->save();
        return $this->model;
    }

    private function appendStandardFilters(Builder $builder, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                $builder->whereIn($key, $value);
                continue;
            }

            if (is_null($value)) {
                $builder->whereNull($key);
                continue;
            }

            $builder->where($key, $value);
        }
        return $builder;
    }
}
