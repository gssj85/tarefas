<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        Task::factory()->count(100)->create(function () use ($faker) {
            $userId = $faker->numberBetween(1, 5);
            $userAssigned = $faker->numberBetween(1, 5);

            return [
                'user_id' => $userId,
                'user_id_assigned_to' => $userAssigned
            ];
        });
    }
}
