<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class LogoutController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

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
    public function __invoke(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->userRepository->deleteTokens($user->id);

        return response()->json(['message' => 'Successfully logged out']);
    }
}
