<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ExternalCategorySyncService;
use App\Jobs\SyncExternalCategoriesJob;

class ExternalCategorySyncController extends Controller
{
    public function __construct(
        private readonly ExternalCategorySyncService $service
    ) {}

    public function sync(Request $request): JsonResponse
    {
        if ($request->boolean('async')) {
            SyncExternalCategoriesJob::dispatch();
            return response()->json([
                'message' => 'Sync queued',
                'synced' => 0,
            ], 202);
        }

        $result = $this->service->sync();

        return response()->json([
            'message' => $result['message'],
            'synced' => $result['synced'],
        ], $result['status']);
    }
}
