<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    //@TODO Implementar estratégia de invalidar token antigo (blacklist ou token versioning)
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // Simulando permissões no payload
        $defaultPermissions = ['tasks:store', 'tasks:update', 'tasks:delete'];
        if (!$token = auth()->claims(['permissions' => $defaultPermissions])->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuário ou senha inválida, acesso não autorizado.'
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
                'permissions' => auth()->payload()->get('permissions'),
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        ]);
    }

    public function register(
        StoreUserRequest $storeUserRequest,
        UserRepositoryInterface $userRepository
    ): JsonResponse {
        $data = $storeUserRequest->validated();
        $beforeHashPassword = $data['password'];
        $data['password'] = Hash::make($data['password']);
        $user = $userRepository->store($data);

        // Simulando permissões no payload
        $defaultPermissions = ['tasks:store', 'tasks:update', 'tasks:delete'];
        $token = auth()->claims(['permissions' => $defaultPermissions])->attempt([
            'email' => $data['email'],
            'password' => $beforeHashPassword
        ]);

        return response()->json(
            [
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'permissions' => auth()->payload()->get('permissions'),
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]
            ],
            Response::HTTP_CREATED
        );
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Usuário deslogado!']);
    }

    public function refresh()
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
