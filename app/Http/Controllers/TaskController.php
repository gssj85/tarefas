<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TaskAssignedEvent;
use App\Events\TaskDoneEvent;
use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskRepositoryInterface $taskRepository) {}

    public function index(IndexTaskRequest $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $data = $request->validated();

        return TaskResource::collection($this->taskRepository->findByAssignmentAndStatus($data));
    }

    public function store(StoreTaskRequest $storeTaskRequest, Authenticatable $user): JsonResponse
    {
        $data = $storeTaskRequest->validated();
        $data['user_id'] = $user->id;
        $taskModel = $this->taskRepository->store($data);

        [$message, $subject, $to] = $this->getTaskAssignedEmailData($data, $user);
        TaskAssignedEvent::dispatch($message, $subject, $to);

        return response()->json([
            'message' => "Tarefa $taskModel->title criada com sucesso!"
        ], Response::HTTP_CREATED);
    }

    public function show(int $taskId): \Illuminate\Http\Response|TaskResource
    {
        $task = $this->taskRepository->findByIdWithUserAndAssigned($taskId);

        if ($task === null || Gate::inspect('show', $task)->denied()) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        return new TaskResource($task);
    }

    public function update(
        int $taskId,
        UpdateTaskRequest $updateTaskRequest,
        Authenticatable $user
    ): \Illuminate\Http\Response {
        $oldData = $this->taskRepository->find($taskId);

        if ($oldData === null || Gate::inspect('updateOrDelete', $oldData)->denied()) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        $oldData->makeHidden(['status', 'user_id_assigned_to']);

        $newData = $updateTaskRequest->validated();
        $this->taskRepository->update($taskId, $newData);

        // Se a tarefa foi atribuída a um novo usuário, dispara e-mail
        $isNewAssignment = isset($newData['user_id_assigned_to'])
            && $newData['user_id_assigned_to'] !== $oldData['user_id_assigned_to'];
        if ($isNewAssignment) {
            [$message, $subject, $to] = $this->getTaskAssignedEmailData($newData, $user);
            TaskAssignedEvent::dispatch($message, $subject, $to);
        }

        // Se o status mudou para DONE, dispara e-mail, caso já estivesse em DONE, não envia
        $isStatusChangedToDone = isset($newData['status'])
            && $newData['status'] === 'DONE'
            && $oldData['status'] !== 'DONE';
        if ($isStatusChangedToDone) {
            [$message, $subject, $to] = $this->getTaskDoneEmailData($newData, $user);
            TaskDoneEvent::dispatch($message, $subject, $to);
        }

        return response()->noContent(Response::HTTP_OK);
    }

    public function destroy(int $taskId): \Illuminate\Http\Response
    {
        $task = $this->taskRepository->find($taskId);

        if ($task === null || Gate::inspect('updateOrDelete', $task)->denied()) {
            return response()->noContent(Response::HTTP_NOT_FOUND);
        }

        $this->taskRepository->destroy($taskId);

        return response()->noContent(Response::HTTP_OK);
    }

    private function getTaskAssignedEmailData(array $data, Authenticatable $user): array
    {
        $subject = 'Tarefa Atribuída!';
        $message = sprintf("A tarefa %s foi atribuída a você!", $data['title']);
        $to = $user->email;
        if ($data['user_id_assigned_to'] !== $user->id) {
            $message = sprintf("O usuário %s atribuiu a tarefa %s a você!", $user->name, $data['title']);

            $userRepository = app()->make(UserRepositoryInterface::class);
            $to = $userRepository->find($data['user_id_assigned_to'])->email;
        }

        return [$message, $subject, $to];
    }

    private function getTaskDoneEmailData(array $data, Authenticatable $user): array
    {
        $message = "A Tarefa {$data['title']} atribuída a você foi concluída!";
        $subject = "Tarefa Concluída";
        $to = $data['user_id_assigned_to'] === $user->id
            ? $user->email
            : app()->make(UserRepositoryInterface::class)->find($data['user_id_assigned_to'])->email;
        return array($message, $subject, $to);
    }
}
