<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());
        return response()->json($result, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());
        return response()->json($result);
    }

    /**
     * Получить всех пользователей (для админки)
     */
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json(['data' => $users]);
    }

    /**
     * Обновить данные пользователя (имя, email, роль)
     */
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Обновляем поля, если они переданы в запросе
        $user->update($request->validated());

        return response()->json([
            'message' => 'Данные пользователя успешно обновлены',
            'user' => $user
        ]);
    }

    /**
     * Полное удаление пользователя из БД
     */
    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Защита: нельзя удалить самого себя (опционально)
        if (auth()->id() == $id) {
            return response()->json(['message' => 'Вы не можете удалить свою собственную учетную запись'], 400);
        }

        $user->delete();

        return response()->json(['message' => 'Пользователь успешно удален']);
    }
    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1|max:10000',
        ]);

        $user = $request->user();
        $user->balance += $request->amount;
        $user->save();

        return response()->json([
            'message' => 'Баланс успешно пополнен!',
            'user' => $user
        ]);
    }
}
