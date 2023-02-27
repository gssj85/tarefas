<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository extends AbstractRepository implements TaskRepositoryInterface
{
    protected mixed $model = Task::class;

    public function findByIdWithUserAndAssigned(int $taskId)
    {
        return Task::with('user', 'userAssignedTo')->find($taskId);
    }

    public function findByAssignmentAndStatus(array $data): Builder
    {
        $userId = $data['user_id'];
        $assignedTo = $data['assigned-to'] ?? null;
        $status = $data['status'] ?? null;

        $query = Task::query()->with('user', 'userAssignedTo');

        if ($assignedTo) {
            try {
                match ($assignedTo) {
                    'me' => $query->where('user_id_assigned_to', $userId),
                    'others' => $query->where('user_id', $userId)
                        ->whereNot('user_id_assigned_to', $userId),
                };
            } catch (\UnhandledMatchError $e) {
                $message = 'O parâmetro passado no filtro de atribuição é inválido,'
                    . ' parâmetros válidos são: \'me\' e \'others\'';

                throw new \DomainException($message);
            }
        } else {
            $query->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('user_id_assigned_to', $userId);
            });
        }

        if ($status) {
            $statusEnum = TaskStatusEnum::tryFrom(strtoupper($status));

            if ($statusEnum === null) {
                $message = 'O parâmetro passado no filtro de status é inválido,'
                    . ' parâmetros válidos são: \'in_progress\', \'done\' e \'canceled\'';

                throw new \DomainException($message);
            }

            $query->where('status', $statusEnum);
        }

        return $query;
    }
}
