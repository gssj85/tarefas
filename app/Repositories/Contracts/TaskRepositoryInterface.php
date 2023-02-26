<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface TaskRepositoryInterface extends AbstractRepositoryInterface
{
    public function findByAssignmentAndStatus(array $data);
    public function findByIdWithUserAndAssigned(int $taskId);
}
