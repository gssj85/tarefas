<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Cache;

class TaskObserver
{
    public function created(Task $task): void
    {
        Cache::tags('tasks')->flush();
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        Cache::tags('tasks')->flush();
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        Cache::tags('tasks')->flush();
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        Cache::tags('tasks')->flush();
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        Cache::tags('tasks')->flush();
    }
}
