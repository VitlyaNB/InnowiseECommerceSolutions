<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class DeleteController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

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
    public function __invoke(Request $request, int $id): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        $this->authService->deleteUser($id, $currentUser->id);

        return response()->json(['message' => 'User deleted successfully']);
    }
}
