<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function taskBelongsOrIsAssignedToUser(User $user, Task $task): Response
    {
        $taskBelongsOrIsAssignedToUser = $task->user_id === $user->id
            || $task->user_id_assigned_to === $user->id;

        return $taskBelongsOrIsAssignedToUser
            ? Response::allow()
            : Response::denyAsNotFound('Tarefa não encontrada');
    }
}
