<?php

namespace App\Http\Controllers\Api\CookieConsent;

use App\Http\Controllers\Controller;
use App\Http\Requests\CookieConsentStoreRequest;
use Illuminate\Http\JsonResponse;

final class StoreController extends Controller
{
    public function __invoke(CookieConsentStoreRequest $request): JsonResponse
    {
        $dto = $request->toDto();
        $cookie = cookie(
            'cookie_consent',
            $dto->accepted ? 'accepted' : 'declined',
            365 * 24 * 60
        );

        return response()
            ->json(['accepted' => $dto->accepted])
            ->cookie($cookie);
    }
}
