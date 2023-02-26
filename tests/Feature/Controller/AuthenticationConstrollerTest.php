<?php

declare(strict_types=1);

namespace Tests\Feature\Controller;

use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Request;
use Tests\TestCase;

class AuthenticationConstrollerTest extends TestCase
{
    use RefreshDatabase;

    public function testUsersCanLoginWithRightPasswordAndTokenIsReturningWithoutIssues(): void
    {
        $user = User::factory()->create();

        $response = $this->json(Request::METHOD_POST, '/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertOk()
            ->assertJson([
                'status' => $response['status'],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'authorisation' => [
                    'token' => $response['authorisation']['token'],
                    'type' => $response['authorisation']['type'],
                    'permissions' => $response['authorisation']['permissions'],
                    'expires_in' => $response['authorisation']['expires_in']
                ]
            ]);

        $this->assertAuthenticated();
    }

    public function testUsersCanNotAuthenticateWithInvalidPassword(): void
    {
        $user = User::factory()->create();

        $this->json(Request::METHOD_POST, '/auth/login', [
            'email' => $user,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function testNewUsersCanBeRegistered(): void
    {
        $faker = Faker::create('pt_BR');

        $password = $faker->password(8);
        $userData = [
            'name' => "$faker->firstName $faker->lastName",
            'email' => $faker->email,
            'password' => $password,
            'password_confirmation' => $password
        ];

        $response = $this->json(Request::METHOD_POST, '/auth/register', $userData);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'user' => [
                    'id',
                    'name',
                ],
                'authorisation' => [
                    'token',
                    'type',
                    'permissions',
                    'expires_in'
                ]
            ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check($password, $user->password));
    }
}
