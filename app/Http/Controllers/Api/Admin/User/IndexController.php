<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class IndexController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

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
    public function __invoke(): JsonResponse
    {
        $users = $this->authService->getAllUsers();

        return UserResource::collection($users)->response();
    }
}
