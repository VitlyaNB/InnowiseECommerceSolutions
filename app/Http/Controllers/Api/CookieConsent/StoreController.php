<?php

namespace App\Http\Controllers\Api\CookieConsent;

use App\Http\Controllers\Controller;
use App\Http\Requests\CookieConsentStoreRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class StoreController extends Controller
{
    #[OA\Post(
        path: '/api/cookie-consent',
        summary: 'Store cookie consent',
        description: 'Stores the user cookie consent preference in a cookie.',
        tags: ['Cookie Consent'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['accepted'],
                properties: [
                    new OA\Property(property: 'accepted', type: 'boolean', example: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cookie consent saved',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'accepted', type: 'boolean', example: true),
                    ]
                )
            ),
        ]
    )]
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
