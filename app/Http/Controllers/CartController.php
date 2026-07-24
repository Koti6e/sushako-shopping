<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Services\OperationalSettingsService;
use App\Support\ProductCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly OperationalSettingsService $settings)
    {
    }

    private function cartSummary(array $cart): array
    {
        $summary = $this->settings->checkoutSummary($cart);

        return [
            'cart_count' => count($cart),
            'subtotal' => $summary['subtotal'],
            'summary' => $summary,
            'cart' => array_values($cart),
        ];
    }

    public function show()
    {
        $cart = session('cart', []);

        return view($cart ? 'cart.show' : 'cart.empty', [
            'cart' => $cart,
            'summary' => $this->settings->checkoutSummary($cart),
            'product' => ProductCatalog::featuredProduct(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => ['required', 'string'],
            'colour' => ['required', 'string'],
            'size' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $productModel = ProductCatalog::productModelBySlug($data['slug']);
        abort_unless($productModel, 404);

        $variant = ProductCatalog::variantFor($productModel, $data['colour'], $data['size']);
        abort_unless($variant, 404);

        if ($variant->stock < $data['quantity']) {
            return back()->withErrors([
                'quantity' => "Only {$variant->stock} unit(s) available for this variant.",
            ]);
        }

        $product = ProductCatalog::productArray($productModel);
        abort_unless($product, 404);

        $cart = session('cart', []);
        $cart[$product['slug'].'-'.$data['colour'].'-'.$data['size']] = [
            'product_id' => $productModel->id,
            'variant_id' => $variant->id,
            'product' => $product['name'],
            'slug' => $product['slug'],
            'colour' => $data['colour'],
            'size' => $data['size'],
            'quantity' => $data['quantity'],
            'price' => (int) ($variant->price ?: $product['selling_price']),
            'image' => $product['images'][0]['path'],
        ];

        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'message' => 'Added to Cart',
                'item' => $cart[$product['slug'].'-'.$data['colour'].'-'.$data['size']],
            ], $this->cartSummary($cart)));
        }

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request, string $key)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $cart = session('cart', []);
        abort_unless(isset($cart[$key]), 404);

        if ($variantId = $cart[$key]['variant_id'] ?? null) {
            $variant = ProductVariant::query()->find($variantId);

            if (! $variant || $variant->stock < $data['quantity']) {
                $message = $variant ? "Only {$variant->stock} unit(s) available for this variant." : 'This product variant is no longer available.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }

                return back()->withErrors(['quantity' => $message]);
            }
        }

        $cart[$key]['quantity'] = $data['quantity'];
        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'message' => 'Cart updated',
            ], $this->cartSummary($cart)));
        }

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, string $key)
    {
        $cart = session('cart', []);
        abort_unless(isset($cart[$key]), 404);

        unset($cart[$key]);
        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'message' => 'Removed from cart',
            ], $this->cartSummary($cart)));
        }

        return back()->with('status', 'Removed from cart.');
    }

    public function buyNow(Request $request)
    {
        $response = $this->store($request);

        if ($response instanceof RedirectResponse && $response->getSession()->has('errors')) {
            return $response;
        }

        return redirect()->route('checkout')->with('status', 'Ready for secure checkout.');
    }

    public function wishlist(Request $request)
    {
        $slug = $request->validate(['slug' => ['required', 'string']])['slug'];
        $wishlist = collect(session('wishlist', []));

        session(['wishlist' => $wishlist->contains($slug) ? $wishlist->reject(fn ($item) => $item === $slug)->values()->all() : $wishlist->push($slug)->values()->all()]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Wishlist updated',
                'wishlist_count' => count(session('wishlist', [])),
            ]);
        }

        return back()->with('status', 'Wishlist updated.');
    }
}
