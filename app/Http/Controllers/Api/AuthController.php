<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\DTO\LoginDTO; // Добавили импорт
use App\DTO\RegisterDTO; // Добавили импорт
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $dto = \App\DTO\LoginDTO::fromRequest($request);

        $result = $this->authService->login($dto);

        return response()->json($result);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = RegisterDTO::fromRequest($request);

        $result = $this->authService->register($dto);

        return response()->json($result, 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Successfully logged out']);
    }

    // Метод для пополнения баланса
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $user->balance += $request->amount;
        $user->save();

        return response()->json([
            'message' => 'Баланс пополнен',
            'user' => $user
        ]);
    }
}
