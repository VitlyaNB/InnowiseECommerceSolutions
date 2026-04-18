<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChatAccess
{
    public function __construct(
        private readonly ChatRepositoryInterface $chatRepository
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $routeChat = $request->route('chat');
        $chatId = is_numeric($routeChat) ? (int) $routeChat : 0;
        if ($chatId === 0) {
            abort(404);
        }

        if (! $this->chatRepository->existsById($chatId)) {
            abort(404);
        }

        $isAdmin = $user->role === 'admin';
        $hasAccess = $this->chatRepository->hasAccess($chatId, $user->id, $isAdmin);
        if (! $hasAccess) {
            abort(403);
        }

        return $next($request);
    }
}
