<?php

namespace App\Domain\Contracts\Repositories;

use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function find(string|int $id): ?object;

    public function all(): Collection;

    public function delete(string|int $id): bool;

    public function update(array $attributes, string|int $id): bool;

    public function findBy(array $filters): Collection;

    public function findOneBy(array $filters): ?object;

    public function save(array $attributes): object;
}
