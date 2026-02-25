<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AuthService;
use App\DTO\RegisterDTO;
use App\DTO\LoginDTO;
use App\DTO\UpdateUserDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register(RegisterDTO::fromRequest($request));

        return response()->json([
            'access_token' => $result['token'],
            'token_type'   => 'Bearer',
            'user'         => $result['user'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(LoginDTO::fromRequest($request));

        return response()->json([
            'access_token' => $result['token'],
            'token_type'   => 'Bearer',
            'user'         => $result['user'],
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json($this->authService->getAllUsers());
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $this->authService->updateUser($id, UpdateUserDTO::fromRequest($request));

        return response()->json(['message' => 'Пользователь обновлен']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authService->deleteUser($id, $request->user()->id);

        return response()->json(['message' => 'Пользователь удален']);
    }
}
