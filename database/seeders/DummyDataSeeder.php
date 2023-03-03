<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        User::factory(5)->create()->each(fn($user) => $user->assignRole('user'));
        Task::factory(100)->create(fn() => [
            'user_id' => $faker->numberBetween(1, 5),
            'user_id_assigned_to' => $faker->numberBetween(1, 5)
        ]);
    }
}
