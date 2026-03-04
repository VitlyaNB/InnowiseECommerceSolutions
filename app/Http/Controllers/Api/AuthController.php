<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AuthService;
use App\DTO\LoginDTO;
use App\DTO\RegisterDTO;
use App\DTO\UpdateUserDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    // ПОЛУЧЕНИЕ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ (для админки)
    public function index(): JsonResponse
    {
        $users = $this->authService->getAllUsers();
        return response()->json(['data' => $users]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginDTO::fromRequest($request);
        $result = $this->authService->login($dto);
        return response()->json($result);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = RegisterDTO::fromRequest($request);
        $result = $this->authService->register($dto);
        return response()->json($result, 201);
    }

    // ОБНОВЛЕНИЕ (для админки)
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $dto = UpdateUserDTO::fromRequest($request);
        $this->authService->updateUser($id, $dto);
        return response()->json(['message' => 'User updated successfully']);
    }

    // УДАЛЕНИЕ (для админки)
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($request->user()->id === $id) {
            return response()->json(['message' => 'Нельзя удалить самого себя'], 403);
        }
        $this->authService->deleteUser($id, $request->user()->id);
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function topUp(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $user = $request->user();
        $user->balance += $request->amount;
        $user->save();

        return response()->json([
            'message' => 'Баланс пополнен',
            'user' => $user
        ]);
    }
}
