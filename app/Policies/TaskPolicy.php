<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function show(User $user, Task $task): Response
    {
        return $task->user_id === $user->id || $task->user_id_assigned_to === $user->id
            ? Response::allow()
            : Response::deny();
    }

    public function updateOrDelete(User $user, Task $task): Response
    {
        return $user->id === $task->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}



