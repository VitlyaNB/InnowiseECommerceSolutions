<?php

namespace App\Http\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

final class ViewSessionResolver
{
    private const VIEW_SESSION_COOKIE = 'view_session';

    public function resolveSessionId(Request $request): string
    {
        $sessionId = $request->cookie(self::VIEW_SESSION_COOKIE);

        if (! is_string($sessionId) || $sessionId === '') {
            $sessionId = Str::uuid()->toString();
            Cookie::queue(self::VIEW_SESSION_COOKIE, $sessionId, 60 * 24 * 30, '/', null, false, true, false, 'Lax');
        }

        return $sessionId;
    }
}
