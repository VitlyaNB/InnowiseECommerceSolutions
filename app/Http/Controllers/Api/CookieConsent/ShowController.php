<?php

namespace App\Http\Controllers\Api\CookieConsent;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'accepted' => $request->cookie('cookie_consent') === 'accepted',
        ]);
    }
}
