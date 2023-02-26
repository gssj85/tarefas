<?php

namespace App\Providers;

use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Eloquent\TaskRepository;
use Illuminate\Support\ServiceProvider;

class TaskRepositoryProvider extends ServiceProvider
{
    public array $bindings = [
        TaskRepositoryInterface::class => TaskRepository::class
    ];
}
