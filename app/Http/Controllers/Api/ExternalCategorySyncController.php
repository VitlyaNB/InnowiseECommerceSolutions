<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncExternalCategoriesJob;
use App\Services\ExternalCategorySyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ExternalCategorySyncController extends Controller
{
    public function __construct(
        private readonly ExternalCategorySyncService $service
    ) {}

    #[OA\Post(
        path: '/api/categories/sync',
        summary: 'Sync categories from an external API',
        description: 'Fetches categories from the configured external project API and imports any new ones. Pass `async=true` to queue the job instead of running it synchronously.',
        tags: ['Admin'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'async',
                        type: 'boolean',
                        default: false,
                        example: false,
                        description: 'If true, the sync will be queued as a background job'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sync completed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Synced 5 new categories from external project'),
                        new OA\Property(property: 'synced', type: 'integer', example: 5),
                    ]
                )
            ),
            new OA\Response(
                response: 202,
                description: 'Sync queued (async mode)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Sync queued'),
                        new OA\Property(property: 'synced', type: 'integer', example: 0),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'External API URL not configured'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden (admin only)'),
            new OA\Response(response: 422, description: 'Invalid response format from external API'),
            new OA\Response(response: 500, description: 'Sync failed'),
            new OA\Response(response: 502, description: 'Failed to fetch from external API'),
        ]
    )]
    public function sync(Request $request): JsonResponse
    {
        if ($request->boolean('async')) {
            SyncExternalCategoriesJob::dispatch();
            return response()->json([
                'message' => 'Sync queued',
                'synced'  => 0,
            ], 202);
        }

        $result = $this->service->sync();

        return response()->json([
            'message' => $result['message'],
            'synced'  => $result['synced'],
        ], $result['status']);
    }
}
