<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function seeAllTasks(User $user, ?Task $task): Response
    {
        $isUserSuperAdmin = $user->hasRole('super-admin');

        $taskBelongsOrIsAssignedToUser = $task->user_id === $user->id
            || $task->user_id_assigned_to === $user->id;

        return $taskBelongsOrIsAssignedToUser || $isUserSuperAdmin
            ? Response::allow()
            : Response::denyAsNotFound('Tarefa não encontrada');
    }
}
