<?php

namespace App\Services;

use App\DTO\CartItemDTO;
use App\Models\CartItem;
use App\Models\User;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $sessionId = $request->header('X-Session-Id') ?? $request->cookie(self::CART_SESSION_COOKIE);

        if (!is_string($sessionId) || empty($sessionId)) {
            $sessionId = Str::uuid()->toString();
            Cookie::queue(self::CART_SESSION_COOKIE, $sessionId, 60 * 24 * 30, '/', null, false, true, false, 'Lax');
        }

        return $sessionId;
    }

    /** @return array<string, mixed> */
    private function getIdentifier(): array
    {
        if (auth('sanctum')->check()) {
            return ['user_id' => auth('sanctum')->id()];
        }
        return ['session_id' => $this->resolveSessionId(app('request'))];
    }

    public function addToCart(CartItemDTO $dto): CartItem
    {
        $identifier = $this->getIdentifier();
        $existing = $this->cartRepository->findItem($identifier, $dto->product_id);

        if ($existing) {
            $this->cartRepository->updateQuantity($existing->id, $existing->quantity + $dto->quantity);
            /** @var CartItem $fresh */
            $fresh = $existing->fresh('product.images');
            return $fresh;
        }

        $newItem = $this->cartRepository->create(array_merge($identifier, [
            'product_id' => $dto->product_id,
            'quantity' => $dto->quantity,
        ]));

        return $newItem->load('product.images');
    }

    /** @return array{items: Collection<int, CartItem>, totals: array<string, float>} */
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
        /** @var CartItem|null $fresh */
        $fresh = $item->fresh('product.images');
        return $fresh;
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
            /** @var int $userId */
            $userId = auth('sanctum')->id();
            return $this->cartRepository->clearUserCart($userId);
        }
        $sessionId = request()->cookie(self::CART_SESSION_COOKIE);
        if (is_string($sessionId) && !empty($sessionId)) {
            return $this->cartRepository->clearSessionCart($sessionId);
        }
        return true;
    }

    /** @param array<string, mixed> $identifier */
    private function itemBelongsToIdentifier(CartItem $item, array $identifier): bool
    {
        if (isset($identifier['user_id'])) {
            $idValue = $identifier['user_id'];
            return (int) $item->user_id === (int) (is_numeric($idValue) ? $idValue : 0);
        }
        if (isset($identifier['session_id'])) {
            return $item->session_id === $identifier['session_id'];
        }
        return false;
    }

    /**
     * @param Collection<int, CartItem> $items
     * @return array<string, float>
     */
    public function calculateTotals(Collection $items): array
    {
        $subtotal = $items->sum(fn (CartItem $i) => $i->product->price * $i->quantity);
        $total = $subtotal;

        return [
            'subtotal' => round((float) $subtotal, 2),
            'tax' => 0.0,
            'total' => round((float) $total, 2),
        ];
    }
}
