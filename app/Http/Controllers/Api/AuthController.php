<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\DTO\LoginDTO;
use App\DTO\RegisterDTO;
use App\DTO\UpdateUserDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    #[OA\Post(
        path: '/api/login',
        summary: 'Authenticate a user and issue an API token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Invalid credentials'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $dto    = LoginDTO::fromRequest($request);
        $result = $this->authService->login($dto);
        
        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token']
        ]);
    }

    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user account',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'secret123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto    = RegisterDTO::fromRequest($request);
        $result = $this->authService->register($dto);
        
        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token']
        ], 201);
    }

    #[OA\Post(
        path: '/api/wallet/top-up',
        summary: 'Top up the authenticated user\'s wallet balance',
        tags: ['User Profile'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount'],
                properties: [
                    new OA\Property(property: 'amount', type: 'number', format: 'float', minimum: 1, example: 100.0),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Balance topped up successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Баланс пополнен'),
                        new OA\Property(property: 'user', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function topUp(Request $request): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        /** @var User $user */
        $user = $request->user();

        /** @var float $amount */
        $amount = (float) $request->input('amount');

        $updatedUser = $this->authService->topUp($user, $amount);

        return response()->json([
            'message' => 'Баланс пополнен',
            'user'    => new UserResource($updatedUser)
        ]);
    }

    #[OA\Get(
        path: '/api/users',
        summary: 'Get list of all users (admin only)',
        tags: ['Admin'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of users',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
        ]
    )]
    public function index(): JsonResponse
    {
        $users = $this->authService->getAllUsers();
        return UserResource::collection($users)->response();
    }

    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update a user\'s details (admin only)',
        tags: ['Admin'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated Name'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'updated@example.com'),
                    new OA\Property(property: 'role', type: 'string', enum: ['user', 'admin'], example: 'user'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $dto = UpdateUserDTO::fromRequest($request);
        $this->authService->updateUser($id, $dto);
        return response()->json(['message' => 'User updated successfully']);
    }

    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Delete a user (admin only)',
        description: 'Admins cannot delete their own account.',
        tags: ['Admin'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'User ID',
                schema: new OA\Schema(type: 'integer', example: 2)
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (cannot delete own account or non-admin)'),
            new OA\Response(response: 404, description: 'User not found'),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        if ($currentUser->id === $id) {
            return response()->json(['message' => 'Нельзя удалить самого себя'], 403);
        }
        $this->authService->deleteUser($id, $currentUser->id);
        return response()->json(['message' => 'User deleted successfully']);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Revoke the current user\'s API token',
        tags: ['Authentication'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Successfully logged out'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $this->authService->logout($currentUser);
        return response()->json(['message' => 'Successfully logged out']);
    }
}
