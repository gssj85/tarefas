<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Task;
use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        return [
            'title' => $faker->text(80),
            'description' => $faker->text(120),
            'expected_start_date' => now()->addHour()->format('Y-m-d H:i:s'),
            'expected_completion_date' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'status' => $faker->randomElement(['IN_PROGRESS', 'DONE', 'CANCELED']),
        ];
    }
}
