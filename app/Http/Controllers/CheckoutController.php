<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\ProductVariant;
use App\Services\OperationalSettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly OperationalSettingsService $settings)
    {
    }

    public function show(): View|RedirectResponse
    {
        $cart = session('cart', []);
        $user = request()->user();

        if (! $cart) {
            return redirect()->route('cart.empty')->with('status', 'Add products to your cart before checkout.');
        }

        return view('checkout.show', [
            'cart' => $cart,
            'summary' => $this->summary($cart),
            'user' => $user,
            'addresses' => $user->addresses()->latest('is_default')->latest()->get(),
        ]);
    }

    public function place(Request $request): RedirectResponse
    {
        $cart = session('cart', []);

        if (! $cart) {
            return redirect()->route('cart.empty')->with('status', 'Your cart is empty.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:180'],
            'address_line_2' => ['nullable', 'string', 'max:180'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:12'],
            'landmark' => ['nullable', 'string', 'max:180'],
            'delivery_location_url' => ['nullable', 'url', 'max:500'],
            'terms' => ['accepted'],
        ]);

        $summary = $this->summary($cart);

        $order = DB::transaction(function () use ($cart, $data, $request, $summary): Order {
            foreach ($cart as $item) {
                $variantId = $item['variant_id'] ?? null;
                abort_unless($variantId, 422, 'Cart item is missing variant details.');

                $variant = ProductVariant::query()
                    ->whereKey($variantId)
                    ->lockForUpdate()
                    ->first();

                abort_unless($variant, 422, 'One of the selected product variants is no longer available.');
                abort_if($variant->stock < $item['quantity'], 422, "Only {$variant->stock} unit(s) available for {$item['product']}.");
            }

            $order = Order::query()->create(array_merge($data, [
                'order_number' => $this->nextOrderNumber(),
                'user_id' => $request->user()->id,
                'subtotal' => $summary['subtotal'],
                'shipping_amount' => $summary['shipping'],
                'shipping_status' => $summary['shipping_status'],
                'tax_amount' => $summary['tax'],
                'cgst_amount' => $summary['cgst'],
                'sgst_amount' => $summary['sgst'],
                'igst_amount' => $summary['igst'],
                'discount_amount' => $summary['discount'],
                'total_amount' => $summary['total'],
                'status' => 'payment_pending',
                'payment_method' => 'unselected',
                'payment_status' => 'pending',
            ]));

            foreach ($cart as $key => $item) {
                $taxLine = $summary['tax_lines'][$key] ?? [
                    'gst_rate' => 0,
                    'tax_amount' => 0,
                    'cgst_amount' => 0,
                    'sgst_amount' => 0,
                    'igst_amount' => 0,
                ];

                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['product'],
                    'product_slug' => $item['slug'],
                    'colour' => $item['colour'],
                    'size' => $item['size'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['price'] * $item['quantity'],
                    'gst_rate' => $taxLine['gst_rate'],
                    'tax_amount' => $taxLine['tax_amount'],
                    'cgst_amount' => $taxLine['cgst_amount'],
                    'sgst_amount' => $taxLine['sgst_amount'],
                    'igst_amount' => $taxLine['igst_amount'],
                    'image' => $item['image'],
                ]);
            }

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('order.payment', $order->order_number);
    }

    public function payment(Order $order): View
    {
        $this->authorizeCustomerOrder($order);

        if ($order->status !== 'payment_pending') {
            abort_unless($order->status === 'placed', 404);

            return view('orders.success', [
                'order' => $order->load('items'),
            ]);
        }

        return view('orders.payment', [
            'order' => $order->load('items'),
            'codSetting' => $this->paymentSetting('cod'),
            'razorpaySetting' => $this->paymentSetting('razorpay'),
        ]);
    }

    public function chooseCod(Order $order): RedirectResponse
    {
        $this->authorizeCustomerOrder($order);
        abort_unless((bool) $this->paymentSetting('cod')?->enabled, 403);

        DB::transaction(function () use ($order): void {
            $order->refresh();

            if ($order->status === 'payment_pending') {
                $this->reduceStockFor($order);
            }

            $order->forceFill([
                'payment_method' => 'cod',
                'payment_status' => 'pending',
                'status' => 'placed',
                'placed_at' => $order->placed_at ?? now(),
            ])->save();
        });

        return redirect()->route('order.success', $order->order_number)
            ->with('status', 'Cash on Delivery selected. Please keep the exact amount ready at delivery.');
    }

    public function success(Order $order): View
    {
        $this->authorizeCustomerOrder($order);
        abort_if($order->status === 'payment_pending', 404);

        return view('orders.success', [
            'order' => $order->load('items'),
        ]);
    }

    public function invoice(Order $order): Response
    {
        $this->authorizeCustomerOrder($order);
        abort_if($order->status === 'payment_pending', 404);

        return $this->downloadInvoice($order);
    }

    public function adminInvoice(Order $order): Response
    {
        abort_if($order->status === 'payment_pending', 404);

        return $this->downloadInvoice($order);
    }

    private function downloadInvoice(Order $order): Response
    {
        $order = $this->settings->assignInvoiceNumber($order);
        $company = $this->settings->company();
        $invoiceSettings = $this->settings->invoice();
        $logoPath = $company->logo_path
            ? storage_path('app/public/'.$company->logo_path)
            : public_path('images/brand/sushako-shopping-logo-optimized.png');
        $logoData = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath)) : null;

        $pdf = Pdf::loadView('orders.invoice', [
            'order' => $order->load('items'),
            'company' => $company,
            'invoiceSettings' => $invoiceSettings,
            'logoData' => $logoData,
        ])->setPaper('a4');

        return $pdf->download("sushako-invoice-{$order->invoice_number}.pdf");
    }

    public function confirmRazorpayPayment(Request $request, Order $order): JsonResponse
    {
        $this->authorizeCustomerOrder($order);
        abort_unless((bool) $this->paymentSetting('razorpay')?->enabled, 403);
        abort_unless(config('services.razorpay.test_mode'), 403);

        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:120'],
            'razorpay_order_id' => ['nullable', 'string', 'max:120'],
            'razorpay_signature' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $data): void {
            $order->refresh();

            if ($order->status === 'payment_pending') {
                $this->reduceStockFor($order);
            }

            $order->forceFill([
                'payment_method' => 'razorpay',
                'payment_status' => 'paid',
                'status' => 'placed',
                'placed_at' => $order->placed_at ?? now(),
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_order_id' => $data['razorpay_order_id'] ?? $order->razorpay_order_id,
            ])->save();
        });

        return response()->json([
            'status' => 'paid',
            'message' => "Payment captured for {$order->order_number}.",
            'redirect_url' => route('order.success', $order->order_number),
        ]);
    }

    public function track(): View
    {
        return view('orders.track', [
            'orders' => $this->customerOrders(),
            'query' => null,
        ]);
    }

    public function lookup(Request $request): View
    {
        $data = $request->validate([
            'query' => ['nullable', 'string', 'max:32'],
        ]);

        $orders = $this->customerOrders($data['query'] ?? null);

        return view('orders.track', [
            'orders' => $orders,
            'query' => $data,
        ]);
    }

    private function customerOrders(?string $orderNumber = null)
    {
        return Order::query()
            ->with('items')
            ->where('user_id', request()->user()->id)
            ->whereIn('status', ['placed', 'packing', 'shipped', 'delivered'])
            ->when($orderNumber, fn ($query) => $query->where('order_number', strtoupper($orderNumber)))
            ->latest()
            ->get();
    }

    private function authorizeCustomerOrder(Order $order): void
    {
        abort_if(! $order->user_id, 404);
        abort_unless($order->user_id === request()->user()->id, 403);
    }

    private function summary(array $cart): array
    {
        return $this->settings->checkoutSummary($cart);
    }

    private function paymentSetting(string $provider): ?PaymentSetting
    {
        return $this->settings->paymentProvider($provider);
    }

    private function nextOrderNumber(): string
    {
        $prefix = 'SS'.now()->format('Ym');
        $last = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }

    private function reduceStockFor(Order $order): void
    {
        foreach ($order->items()->with('variant')->get() as $item) {
            if (! $item->product_variant_id) {
                continue;
            }

            $variant = ProductVariant::query()
                ->whereKey($item->product_variant_id)
                ->lockForUpdate()
                ->first();

            abort_unless($variant, 422, "{$item->product_name} is no longer available.");
            abort_if($variant->stock < $item->quantity, 422, "Only {$variant->stock} unit(s) available for {$item->product_name}.");

            $variant->decrement('stock', $item->quantity);
        }
    }
}
