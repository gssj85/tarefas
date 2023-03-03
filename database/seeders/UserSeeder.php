<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => Hash::make('password')
        ]);
        $superAdmin->assignRole('super-admin');
    }
}
