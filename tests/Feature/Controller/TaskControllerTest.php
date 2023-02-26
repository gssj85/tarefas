<?php

declare(strict_types=1);

namespace Tests\Feature\Controller;

use App\Models\Task;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Request;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsDataInValidFormat(): void
    {
        $user = User::factory()->create();

        Task::factory()->create(function () use ($user) {
            return [
                'user_id' => $user->id,
                'user_id_assigned_to' => $user->id
            ];
        });

        $this->actingAs($user)->json(Request::METHOD_GET, route('tasks.index'))
            ->assertOk()
            ->assertJsonStructure([
                'current_page',
                'data',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ])
            ->assertJsonCount(1, 'data');
    }

    public function testStoreReturnDataInValidFormat(): void
    {
        $faker = Faker::create('pt_BR');

        $user = User::factory()->create();
        $token = JWTAuth::claims(['permissions' => ['tasks:store']])->fromUser($user);

        $payload = [
            'title' => $faker->sentence(),
            'description' => $faker->paragraph(),
            'expected_start_date' => now()->addHour()->format('Y-m-d H:i:s'),
            'expected_completion_date' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'status' => $faker->randomElement(['IN_PROGRESS', 'DONE', 'CANCELED']),
            'user_id_assigned_to' => $user->id
        ];

        $headers = ['Authorization' => 'Bearer ' . $token];

        $this->actingAs($user)->json(Request::METHOD_POST, route('tasks.store'), $payload, $headers)
            ->assertCreated()
            ->assertJsonStructure([
                'title',
                'description',
                "expected_start_date",
                "expected_completion_date",
                "status",
                "user_id_assigned_to",
                "user_id",
                "updated_at",
                "created_at",
                "id"
            ]);

        $this->assertDatabaseCount('tasks', 1);
        $this->assertDatabaseHas('tasks', $payload);
    }

    public function testShowReturnDataInValidFormat(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'user_id_assigned_to' => $user->id,
        ]);

        $this->actingAs($user)->json(Request::METHOD_GET, route('tasks.show', ['task' => $task->id]))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'expected_start_date',
                'expected_completion_date',
                'status',
                'user_id',
                'user_id_assigned_to',
                'created_at',
                'updated_at'
            ]);
    }

    public function testUpdateReturnDataInValidFormat(): void
    {
        $faker = Faker::create('pt_BR');

        $user = User::factory()->create();
        $token = JWTAuth::claims(['permissions' => ['tasks:update']])->fromUser($user);
        $headers = ['Authorization' => 'Bearer ' . $token];

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'user_id_assigned_to' => $user->id,
        ]);

        $payload = [
            'title' => $faker->sentence
        ];

        $this->actingAs($user)
            ->json(
                Request::METHOD_PUT,
                route('tasks.update', ['task' => $task->id]),
                $payload,
                $headers
            )
            ->assertOk()
            ->assertJson(['message' => "Tarefa com o ID: $task->id atualizada com sucesso!"]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $payload['title']
        ]);
    }

    public function testDestroyReturnDataInValidFormat(): void
    {
        $user = User::factory()->create();
        $token = JWTAuth::claims(['permissions' => ['tasks:delete']])->fromUser($user);
        $headers = ['Authorization' => 'Bearer ' . $token];

        $task = Task::factory()->create(function () use ($user) {
            return [
                'user_id' => $user->id,
                'user_id_assigned_to' => $user->id
            ];
        });

        $this->assertModelExists($task);

        $this->actingAs($user)
            ->json(
                method: Request::METHOD_DELETE,
                uri: route('tasks.destroy', ['task' => $task->id]),
                headers: $headers
            )
            ->assertNoContent();

        $this->assertModelMissing($task);
    }
}
