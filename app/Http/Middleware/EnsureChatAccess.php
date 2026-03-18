<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ChatService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChatAccess
{
    public function __construct(
        private readonly ChatService $chatService
    ) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $chatId = (int) $request->route('chat');
        if (!$this->chatService->exists($chatId)) {
            abort(404);
        }

        $isAdmin = $user->role === 'admin';
        $hasAccess = $this->chatService->hasAccess($chatId, $user->id, $isAdmin);
        if (!$hasAccess) {
            abort(403);
        }

        return $next($request);
    }
}
