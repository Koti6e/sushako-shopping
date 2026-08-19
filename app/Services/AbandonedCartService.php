<?php

namespace App\Services;

use App\Models\CustomerActivity;
use App\Models\CustomerCart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AbandonedCartService
{
    public function syncFromSession(?User $user, ?string $sessionId, array $cart): ?CustomerCart
    {
        if (! $user || $user->role !== User::ROLE_CUSTOMER || ! $sessionId) {
            return null;
        }

        if ($cart === []) {
            $this->activeCart($user, $sessionId)?->delete();

            return null;
        }

        return DB::transaction(function () use ($user, $sessionId, $cart): CustomerCart {
            $snapshot = CustomerCart::query()->firstOrCreate([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'status' => CustomerCart::STATUS_ACTIVE,
            ], [
                'customer_type' => 'registered',
                'recovery_token' => Str::random(48),
                'recovery_token_expires_at' => now()->addDays(14),
            ]);

            $snapshot->forceFill([
                'original_value' => collect($cart)->sum(fn (array $item): int => (int) $item['price'] * (int) $item['quantity']),
                'last_activity_at' => now(),
                'abandoned_at' => null,
                'expired_at' => null,
            ])->save();

            $snapshot->items()->delete();

            foreach ($cart as $key => $item) {
                $snapshot->items()->create([
                    'cart_key' => (string) $key,
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['product'],
                    'product_slug' => $item['slug'] ?? null,
                    'colour' => $item['colour'] ?? null,
                    'size' => $item['size'] ?? null,
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (int) $item['price'],
                    'line_total' => (int) $item['price'] * (int) $item['quantity'],
                    'image' => $item['image'] ?? null,
                ]);
            }

            $this->activity($user, 'cart_updated', 'Cart updated', ['items' => count($cart)]);

            return $snapshot;
        });
    }

    public function markConverted(?User $user, Order $order): void
    {
        if (! $user) {
            return;
        }

        $cart = CustomerCart::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [CustomerCart::STATUS_ACTIVE, CustomerCart::STATUS_ABANDONED, CustomerCart::STATUS_WHATSAPP_OPENED, CustomerCart::STATUS_REMINDER_MARKED_SENT])
            ->latest('last_activity_at')
            ->first();

        if (! $cart) {
            return;
        }

        $cart->forceFill([
            'status' => $cart->reminder_opened_at ? CustomerCart::STATUS_RECOVERED : CustomerCart::STATUS_CONVERTED_WITHOUT_REMINDER,
            'recovered_at' => now(),
            'recovered_order_id' => $order->id,
        ])->save();
    }

    public function markAbandonedCarts(): int
    {
        $cutoff = now()->subHours($this->abandonmentHours());

        return CustomerCart::query()
            ->where('status', CustomerCart::STATUS_ACTIVE)
            ->where('last_activity_at', '<=', $cutoff)
            ->whereHas('items')
            ->update([
                'status' => CustomerCart::STATUS_ABANDONED,
                'abandoned_at' => now(),
            ]);
    }

    public function restore(CustomerCart $cart, ?User $user): array
    {
        abort_unless($cart->recovery_token && $cart->recovery_token_expires_at?->isFuture(), 403);
        abort_if($cart->user_id && $user && $cart->user_id !== $user->id, 403);

        $restored = [];
        $unavailable = [];

        $cart->loadMissing('items.variant.product.images');

        foreach ($cart->items as $item) {
            $variant = $item->variant;
            $product = $item->product;

            if (! $variant || ! $product || ! $product->is_published || $variant->stock < 1) {
                $unavailable[] = $item->product_name;

                continue;
            }

            $quantity = min($item->quantity, $variant->stock);
            $restored[$item->cart_key] = [
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'product' => $product->name,
                'slug' => $product->slug,
                'colour' => $variant->colour,
                'size' => $variant->size,
                'quantity' => $quantity,
                'price' => (int) ($variant->price ?: $product->selling_price),
                'image' => $product->images->first()?->path ?? $item->image,
            ];
        }

        if ($restored !== []) {
            $cart->forceFill([
                'status' => CustomerCart::STATUS_WHATSAPP_OPENED,
                'reminder_opened_at' => $cart->reminder_opened_at ?? now(),
            ])->save();
        }

        return [$restored, $unavailable];
    }

    public function abandonmentHours(): int
    {
        $value = DB::table('customer_management_settings')->where('key', 'abandoned_cart_hours')->value('value');

        if (! $value) {
            DB::table('customer_management_settings')->insertOrIgnore([
                'key' => 'abandoned_cart_hours',
                'value' => '2',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return 2;
        }

        return max(1, min(168, (int) $value));
    }

    private function activeCart(User $user, string $sessionId): ?CustomerCart
    {
        return CustomerCart::query()
            ->where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('status', CustomerCart::STATUS_ACTIVE)
            ->first();
    }

    private function activity(User $user, string $type, string $title, array $metadata = []): void
    {
        CustomerActivity::query()->create([
            'user_id' => $user->id,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'type' => $type,
            'title' => $title,
            'metadata' => $metadata,
            'source' => 'storefront',
            'occurred_at' => now(),
        ]);

        $user->forceFill(['last_activity_at' => now()])->save();
    }
}
