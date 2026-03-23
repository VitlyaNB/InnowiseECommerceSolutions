<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TopUpRequest;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class TopUpController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

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
    public function __invoke(TopUpRequest $request): JsonResponse
    {
        $user = $request->user();
        $updatedUserDto = $this->userRepository->topUp($user->id, $request->toDto()->amount);

        return response()->json([
            'message' => 'Баланс пополнен',
            'user' => new UserResource($updatedUserDto),
        ]);
    }
}
