<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;

abstract class AbstractRepository
{
    protected mixed $model;

    public function __construct()
    {
        $this->model = $this->resolveModel();
    }

    protected function resolveModel()
    {
        return app($this->model);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->model->create($data);
        }, 5);
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function update(int $id, array $data): void
    {
        $this->model->where('id', $id)->first()->update($data);
    }

    public function destroy(int $id): void
    {
        $this->model->destroy($id);
    }
}
