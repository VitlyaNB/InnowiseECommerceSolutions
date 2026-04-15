<?php

namespace App\Http\Controllers\Api\CookieConsent;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

final class ShowController extends Controller
{
    #[OA\Get(
        path: '/api/cookie-consent',
        summary: 'Get cookie consent status',
        description: 'Retrieves the current cookie consent status from cookies.',
        tags: ['Cookie Consent'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cookie consent status',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'accepted', type: 'boolean', example: false),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'accepted' => $request->cookie('cookie_consent') === 'accepted',
        ]);
    }
}
