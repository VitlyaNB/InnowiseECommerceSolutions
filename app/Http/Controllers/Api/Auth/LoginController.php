<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\CartSessionResolver;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly CartSessionResolver $sessionResolver
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
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $sessionId = $this->sessionResolver->resolveSessionId($request);
        $result = $this->authService->login($request->toDto(), $sessionId);

        return response()->json([
            'user' => new UserResource($result->user),
            'token' => $result->token,
        ]);
    }
}
