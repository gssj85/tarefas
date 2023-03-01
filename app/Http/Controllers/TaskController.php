<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TaskAssignedEvent;
use App\Events\TaskDoneEvent;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskRepositoryInterface $taskRepository) {}

    public function index(Request $request, Authenticatable $user)
    {
        $data = $request->all();
        $data['user_id'] = $user->id;

        try {
            return TaskResource::collection(
                $this->taskRepository->findByAssignmentAndStatus($data)
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    public function store(StoreTaskRequest $storeTaskRequest, Authenticatable $user): JsonResponse
    {
        $permissions = auth()->payload()->get('permissions');
        if (!in_array('tasks:store', $permissions, true)) {
            return response()->json(
                ['message' => "O usuário $user->name não possui permissão para criar tarefas."],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $data = $storeTaskRequest->validated();
        $data['user_id'] = $user->id;
        $taskModel = $this->taskRepository->store($data);

        [$message, $subject, $to] = $this->getTaskAssignedEmailData($data, $user);
        TaskAssignedEvent::dispatch($message, $subject, $to);

        $taskResource = new TaskResource($taskModel);
        return response()->json($taskResource, Response::HTTP_CREATED);
    }

    public function show(int $taskId)
    {
        $userId = auth()->id();
        $taskModel = $this->taskRepository->findByIdWithUserAndAssigned($taskId);

        if ($taskModel === null) {
            return response()->json(['message' => 'Tarefa não encontrada!'], Response::HTTP_NOT_FOUND);
        }

        if ($taskModel->user_id !== $userId && $taskModel->user_id_assigned_to !== $userId) {
            $message = 'Permissão negada. Apenas tarefas criadas ou atribuídas a este usuário podem ser acessadas.';
            return response()->json(['message' => $message], Response::HTTP_UNAUTHORIZED);
        }

        return new TaskResource($taskModel);

    }

    public function update(int $taskId, UpdateTaskRequest $updateTaskRequest, Authenticatable $user): JsonResponse
    {
        $permissions = auth()->payload()->get('permissions');
        if (!in_array('tasks:update', $permissions, true)) {
            return response()->json(
                ['message' => "O usuário $user->name não possui permissão para editar tarefas."],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $oldData = $this->taskRepository->find($taskId);

        if ($oldData === null) {
            return response()->json(
                ['message' => "Tarefa de ID $taskId não encontrada!"],
                Response::HTTP_NOT_FOUND
            );
        }
        $oldData->makeHidden(['status', 'user_id_assigned_to']);

        if ($oldData->user_id !== $user->id) {
            $message = 'Permissão negada. Apenas tarefas criadas por esse usuário podem ser modificadas.';
            return response()->json(['message' => $message], Response::HTTP_UNAUTHORIZED);
        }

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

        return response()->json(['message' => "Tarefa com o ID: $taskId atualizada com sucesso!"]);
    }

    public function destroy(int $taskId, Authenticatable $user): \Illuminate\Http\Response|JsonResponse
    {
        $permissions = auth()->payload()->get('permissions');
        if (!in_array('tasks:delete', $permissions, true)) {
            return response()->json(
                ['message' => "O usuário $user->name não possui permissão para apagar tarefas."],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $userId = $user->id;
        $taskModel = $this->taskRepository->find($taskId);

        if ($taskModel === null) {
            return response()->json(['message' => 'Tarefa não encontrada!'], Response::HTTP_NOT_FOUND);
        }

        if ($taskModel->user_id !== $userId) {
            $message = 'Permissão negada. Apenas tarefas criadas por este usuário podem ser apagadas.';
            return response()->json(['message' => $message], Response::HTTP_UNAUTHORIZED);
        }

        $this->taskRepository->destroy($taskId);

        return response()->noContent();
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
