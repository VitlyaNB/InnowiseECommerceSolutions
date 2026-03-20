<?php

namespace App\Http\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

final readonly class CartSessionResolver
{
    private const CART_SESSION_COOKIE = 'cart_session';

    public function resolveSessionId(Request $request): string
    {
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie(self::CART_SESSION_COOKIE);

        if (! is_string($sessionId) || $sessionId === '') {
            $sessionId = Str::uuid()->toString();
            Cookie::queue(self::CART_SESSION_COOKIE, $sessionId, 60 * 24 * 30, '/', null, false, true, false, 'Lax');
        }

        return $sessionId;
    }
}
