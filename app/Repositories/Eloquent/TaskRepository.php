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

    public function findByIdWithUserAndAssigned(int $taskId): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->getSelectQueryBuilder()->where('tasks.id', $taskId)->first();
    }

    public function findByAssignmentAndStatus(array $data): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();
        $isSuperAdmin = $user->hasRole('super-admin');

        $assignedTo = $data['assigned-to'] ?? null;
        $status = $data['status'] ?? null;

        $query = $this->getSelectQueryBuilder();
        if ($assignedTo) {
            match ($assignedTo) {
                'me' => $query->where('user_id_assigned_to', $userId),
                'others' => $query->where('user_id', $userId)
                    ->whereNot('user_id_assigned_to', $userId),
            };
        } else if (!$isSuperAdmin) {
            $query->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('user_id_assigned_to', $userId);
            });
        }

        if ($status) {
            $query->where('status', TaskStatusEnum::from($status));
        }

        return $query->paginate(5);
    }

    private function getSelectQueryBuilder(): Builder
    {
        return Task::query()->select([
            'tasks.id',
            'tasks.title',
            'tasks.description',
            'tasks.expected_start_date',
            'tasks.expected_completion_date',
            'tasks.status',
            'user.id as user_id',
            'user.name as user_name',
            'userAssignedTo.id as user_id_assigned_to',
            'userAssignedTo.name as user_name_assigned_to'
        ])
            ->join(
                'users as user',
                'user.id',
                '=',
                'tasks.user_id'
            )
            ->join(
                'users as userAssignedTo',
                'userAssignedTo.id',
                '=',
                'tasks.user_id_assigned_to'
            )
            ->orderBy('tasks.id');
    }
}
