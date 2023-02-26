<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface AbstractRepositoryInterface
{
    public function store(array $data);

    public function find(int $id);

    public function update(int $id, array $data): void;

    public function destroy(int $id): void;
}
