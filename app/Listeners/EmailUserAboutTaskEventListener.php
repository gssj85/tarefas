<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskAssignedEvent;
use App\Events\TaskDoneEvent;
use App\Mail\TaskEventsMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class EmailUserAboutTaskEventListener implements ShouldQueue
{
    public $connection = 'rabbitmq';
    public $queue = 'emails';

    public function handle(TaskAssignedEvent|TaskDoneEvent $taskAssignedEvent): void
    {
        Mail::to($taskAssignedEvent->to)->send(
            new TaskEventsMail($taskAssignedEvent->message, $taskAssignedEvent->subject)
        );
    }
}
