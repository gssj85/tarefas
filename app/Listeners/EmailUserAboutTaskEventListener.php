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
    public function __construct() {}

    public function handle(TaskAssignedEvent|TaskDoneEvent $taskAssignedEvent): void
    {
        $email = new TaskEventsMail($taskAssignedEvent->message, $taskAssignedEvent->subject);
        // Adiciona uma gordurinha para tentar evitar falhas com o MailTrap
        $when = now()->addSeconds(3);
        Mail::to($taskAssignedEvent->to)->later($when, $email);
    }
}
