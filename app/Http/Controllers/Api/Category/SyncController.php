<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategorySyncRequest;
use App\Jobs\SyncExternalCategoriesJob;
use App\Services\ExternalCategorySyncService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class SyncController extends Controller
{
    public function __construct(
        private ExternalCategorySyncService $externalCategorySyncService
    ) {}

    #[OA\Post(
        path: '/api/categories/sync',
        summary: 'Sync categories from external service',
        description: 'Synchronizes categories from external API. Can run synchronously or queue for async processing.',
        tags: ['Categories'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['async'],
                properties: [
                    new OA\Property(property: 'async', type: 'boolean', example: false, description: 'Run sync asynchronously via queue'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sync completed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Categories synced'),
                        new OA\Property(property: 'synced', type: 'integer', example: 15),
                    ]
                )
            ),
            new OA\Response(
                response: 202,
                description: 'Sync queued',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Sync queued'),
                        new OA\Property(property: 'synced', type: 'integer', example: 0),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function __invoke(CategorySyncRequest $request): JsonResponse
    {
        if ($request->toDto()->async) {
            SyncExternalCategoriesJob::dispatch();

            return response()->json([
                'message' => 'Sync queued',
                'synced' => 0,
            ], 202);
        }

        $result = $this->externalCategorySyncService->sync();

        return response()->json([
            'status' => $result->status,
            'message' => $result->message,
            'synced' => $result->synced,
        ], $result->httpStatus);
    }
}
