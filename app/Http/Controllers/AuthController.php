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
    //@TODO Implementar estratégia de invalidar token antigo (blacklist ou token versioning)
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|max:255'
        ]);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'message' => 'Usuário ou senha inválida, acesso não autorizado.'
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $user = Auth::user();

        return response()->json([
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
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

        $token = auth()->attempt([
            'email' => $data['email'],
            'password' => $beforeHashPassword
        ]);

        return response()->json(
            [
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]
            ],
            Response::HTTP_CREATED
        );
    }

    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Usuário deslogado!']);
    }

    public function refresh(): JsonResponse
    {
        return response()->json([
            'access_token' => auth()->refresh(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
