<?php

namespace App\Providers;

use App\Models\Task;
use App\Observers\TaskObserver;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Task::observe(TaskObserver::class);

        Queue::failing(function (JobFailed $event) {
            $job = $event->job;
            Queue::pushRaw(
                payload: json_encode($job->payload(), JSON_THROW_ON_ERROR),
                queue: $job->getQueue() . '_failed'
            );
        });
    }
}
