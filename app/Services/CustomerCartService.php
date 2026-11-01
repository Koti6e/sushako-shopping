<?php

namespace App\Services;

use App\Support\ProductCatalog;

class CustomerCartService
{
    public function refresh(array $cart): array
    {
        if ($cart === []) {
            return ['cart' => [], 'removed' => [], 'price_changed' => false, 'stock_issues' => []];
        }

        $slugs = collect($cart)->pluck('slug')->filter()->unique()->values();
        $products = ProductCatalog::marketplaceQuery()
            ->whereIn('products.slug', $slugs)
            ->with(['variants', 'images', 'vendor'])
            ->get()
            ->keyBy('slug');
        $updatedCart = [];
        $removed = [];
        $stockIssues = [];
        $priceChanged = false;

        foreach ($cart as $key => $item) {
            $product = $products->get((string) ($item['slug'] ?? ''));
            $variant = $product?->variants->first(fn ($candidate): bool =>
                (int) $candidate->id === (int) ($item['variant_id'] ?? 0)
                && $candidate->colour === ($item['colour'] ?? null)
                && $candidate->size === ($item['size'] ?? null)
            );

            if (! $product || ! $variant) {
                $removed[] = $item['product'] ?? 'An unavailable product';

                continue;
            }

            $currentPrice = (int) ($variant->price ?: $product->selling_price);
            $priceChanged = $priceChanged || $currentPrice !== (int) ($item['price'] ?? 0);
            $item['product_id'] = $product->id;
            $item['vendor_id'] = $product->vendor_id;
            $item['variant_id'] = $variant->id;
            $item['product'] = $product->name;
            $item['price'] = $currentPrice;
            $item['seller_name'] = $product->vendor?->store_display_name ?: $product->vendor?->business_name;
            $item['seller_slug'] = $product->vendor?->slug;
            $item['image'] = $product->images->first()?->url() ?? $item['image'] ?? null;
            $item['weight_grams'] = (int) ($product->weight_grams ?? $item['weight_grams'] ?? 0);

            if ((int) $variant->stock < (int) ($item['quantity'] ?? 0)) {
                $item['availability_issue'] = 'Only '.(int) $variant->stock.' available. Update quantity to continue.';
                $stockIssues[] = $product->name;
            } else {
                unset($item['availability_issue']);
            }

            $updatedCart[$key] = $item;
        }

        return [
            'cart' => $updatedCart,
            'removed' => $removed,
            'price_changed' => $priceChanged,
            'stock_issues' => $stockIssues,
        ];
    }
}
