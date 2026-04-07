<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategorySyncRequest;
use App\Jobs\SyncExternalCategoriesJob;
use App\Services\ExternalCategorySyncService;
use Illuminate\Http\JsonResponse;

final class SyncController extends Controller
{
    public function __construct(
        private ExternalCategorySyncService $externalCategorySyncService
    ) {}

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
