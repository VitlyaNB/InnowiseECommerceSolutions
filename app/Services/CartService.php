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
    private const COOKIE_CONSENT_COOKIE = 'cookie_consent';
    private const CART_COOKIE_DAYS = 30;

    public function __construct(
        private readonly CartItemRepositoryInterface $cartRepository
    ) {}

    public function resolveSessionId(Request $request): string
    {
        $sessionId = $request->cookie(self::CART_SESSION_COOKIE);

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            $minutes = $this->cookieConsentAccepted($request)
                ? self::CART_COOKIE_DAYS * 24 * 60
                : 0;

            Cookie::queue(
                self::CART_SESSION_COOKIE,
                $sessionId,
                $minutes,
                '/',
                null,
                true,
                true,
                false,
                'Lax'
            );
        }

        return $sessionId;
    }

    public function cookieConsentAccepted(Request $request): bool
    {
        return $request->cookie(self::COOKIE_CONSENT_COOKIE) === 'accepted';
    }

    public function addToCart(CartItemDTO $dto): CartItem
    {
        $request = request();
        $identifier = auth()->check()
            ? ['user_id' => auth()->id()]
            : ['session_id' => $this->resolveSessionId($request)];

        $existing = $this->cartRepository->findItem($identifier, $dto->product_id);

        if ($existing) {
            $this->cartRepository->updateQuantity(
                $existing->id,
                $existing->quantity + $dto->quantity
            );

            return $existing->fresh('product.images');
        }

        return $this->cartRepository->create(array_merge($identifier, [
            'product_id' => $dto->product_id,
            'quantity' => $dto->quantity,
        ]));
    }

    public function getCart(): array
    {
        $request = request();
        $identifier = auth()->check()
            ? ['user_id' => auth()->id()]
            : ['session_id' => $this->resolveSessionId($request)];

        $items = $this->cartRepository->getCartItems($identifier);
        $totals = $this->calculateTotals($items);

        return [
            'items' => $items,
            'totals' => $totals,
        ];
    }

    public function updateQuantity(int $id, int $quantity): ?CartItem
    {
        $request = request();
        $identifier = auth()->check()
            ? ['user_id' => auth()->id()]
            : ['session_id' => $this->resolveSessionId($request)];

        $item = $this->cartRepository->findById($id);

        if (!$item || !$this->itemBelongsToIdentifier($item, $identifier)) {
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
        $request = request();
        $identifier = auth()->check()
            ? ['user_id' => auth()->id()]
            : ['session_id' => $this->resolveSessionId($request)];

        $item = $this->cartRepository->findById($id);

        if (!$item || !$this->itemBelongsToIdentifier($item, $identifier)) {
            return false;
        }

        return $this->cartRepository->delete($id);
    }

    public function clearCart(): bool
    {
        $request = request();

        if (auth()->check()) {
            return $this->cartRepository->clearUserCart(auth()->id());
        }

        $sessionId = $request->cookie(self::CART_SESSION_COOKIE);

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

    private function calculateTotals($items, float $taxRate = 0.20, float $discountPercent = 0): array
    {
        $subtotal = $items->sum(fn ($i) => $i->product->price * $i->quantity);
        $discount = $subtotal * ($discountPercent / 100);
        $taxable = $subtotal - $discount;
        $tax = $taxable * $taxRate;
        $total = $taxable + $tax;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
        ];
    }
}
