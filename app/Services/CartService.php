<?php

namespace App\Services;

use App\DTO\CartItemDTO;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    private const CART_SESSION_COOKIE = 'cart_session';

    public function __construct(
        private readonly CartItemRepositoryInterface $cartRepository
    ) {}

    public function resolveSessionId(Request $request): string
    {
        $sessionId = $request->cookie(self::CART_SESSION_COOKIE);

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            Cookie::queue(self::CART_SESSION_COOKIE, $sessionId, 60 * 24 * 30, '/', null, false, true, false, 'Lax');
        }

        return $sessionId;
    }

    private function getIdentifier(): array
    {
        if (auth('sanctum')->check()) {
            return ['user_id' => auth('sanctum')->id()];
        }
        return ['session_id' => $this->resolveSessionId(request())];
    }

    public function addToCart(CartItemDTO $dto): CartItem
    {
        $identifier = $this->getIdentifier();
        $existing = $this->cartRepository->findItem($identifier, $dto->product_id);

        if ($existing) {
            $this->cartRepository->updateQuantity($existing->id, $existing->quantity + $dto->quantity);
            return $existing->fresh('product.images');
        }

        // --- ИСПРАВЛЕНИЕ: Загружаем связи для нового элемента ---
        $newItem = $this->cartRepository->create(array_merge($identifier, [
            'product_id' => $dto->product_id,
            'quantity' => $dto->quantity,
        ]));

        return $newItem->load('product.images');
    }

    public function getCart(): array
    {
        $items = $this->cartRepository->getCartItems($this->getIdentifier());
        return [
            'items' => $items,
            'totals' => $this->calculateTotals($items),
        ];
    }

    public function updateQuantity(int $id, int $quantity): ?CartItem
    {
        $item = $this->cartRepository->findById($id);

        if (!$item || !$this->itemBelongsToIdentifier($item, $this->getIdentifier())) {
            return null;
        }

        if ($quantity < 1) {
            $this->cartRepository->delete($id);
            return null;
        }

        $this->cartRepository->updateQuantity($id, $quantity);
        return $item->fresh('product.images');
    }

    public function removeItem(int $id): bool
    {
        $item = $this->cartRepository->findById($id);
        if (!$item || !$this->itemBelongsToIdentifier($item, $this->getIdentifier())) {
            return false;
        }
        return $this->cartRepository->delete($id);
    }

    public function clearCart(): bool
    {
        if (auth('sanctum')->check()) {
            return $this->cartRepository->clearUserCart(auth('sanctum')->id());
        }
        $sessionId = request()->cookie(self::CART_SESSION_COOKIE);
        if ($sessionId) {
            return $this->cartRepository->clearSessionCart($sessionId);
        }
        return true;
    }

    private function itemBelongsToIdentifier(CartItem $item, array $identifier): bool
    {
        if (isset($identifier['user_id'])) {
            return (int) $item->user_id === (int) $identifier['user_id'];
        }
        if (isset($identifier['session_id'])) {
            return $item->session_id === $identifier['session_id'];
        }
        return false;
    }

    private function calculateTotals($items): array
    {
        $subtotal = $items->sum(fn ($i) => $i->product->price * $i->quantity);
        $total = $subtotal;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => 0,
            'total' => round($total, 2),
        ];
    }
}
