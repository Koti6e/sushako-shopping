<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\AbandonedCartService;
use App\Services\OperationalSettingsService;
use App\Services\SellerCommissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OperationalSettingsService $settings,
        private readonly AbandonedCartService $abandonedCart,
        private readonly SellerCommissionService $sellerCommission,
    ) {}

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
            'addresses' => $user?->role === User::ROLE_CUSTOMER
                ? $user->addresses()->latest('is_default')->latest()->get()
                : collect(),
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
            'customer_phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'address_line_1' => ['required', 'string', 'max:180'],
            'address_line_2' => ['nullable', 'string', 'max:180'],
            'city' => ['nullable', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^\d{6}$/'],
            'landmark' => ['nullable', 'string', 'max:180'],
            'delivery_location_url' => ['nullable', 'url', 'max:500'],
            'terms' => ['accepted'],
        ]);
        $data['customer_phone'] = $this->normalizeIndianMobile($data['customer_phone']);
        $data['pincode'] = preg_replace('/\D+/', '', $data['pincode']);
        $data['city'] = filled($data['city'] ?? null) ? trim($data['city']) : 'Not provided';

        $summary = $this->summary($cart);
        $customer = $request->user()?->role === User::ROLE_CUSTOMER
            ? $request->user()
            : $this->customerForGuestCheckout($data);

        $order = DB::transaction(function () use ($cart, $data, $customer, $summary): Order {
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
                'user_id' => $customer?->id,
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

                $orderItem = $order->items()->create([
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

                $variant->loadMissing('product.vendor');
                if ($variant->product?->vendor) {
                    $this->sellerCommission->capture($orderItem, $variant->product->vendor);
                }
            }

            return $order;
        });

        $request->session()->forget('cart');
        $request->session()->put('checkout_orders.'.$order->order_number, [
            'phone' => $order->customer_phone,
            'email' => $order->customer_email,
        ]);
        $this->abandonedCart->markConverted($customer, $order);

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

        $razorpayError = null;
        $razorpaySetting = $this->paymentSetting('razorpay');

        if ((bool) $razorpaySetting?->enabled && $this->razorpayConfigured()) {
            try {
                $order = $this->ensureRazorpayOrder($order);
            } catch (Throwable $exception) {
                report($exception);
                $razorpayError = 'Unable to create Razorpay payment session. Please try again or choose Cash on Delivery.';
            }
        }

        return view('orders.payment', [
            'order' => $order->load('items'),
            'codSetting' => $this->paymentSetting('cod'),
            'razorpaySetting' => $razorpaySetting,
            'razorpayError' => $razorpayError,
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
                'seller_order_status' => 'new',
                'placed_at' => $order->placed_at ?? now(),
                'seller_acceptance_due_at' => $order->seller_acceptance_due_at ?? now()->addHours(24),
            ])->save();

            $order->statusEvents()->create([
                'actor' => 'customer',
                'from_status' => 'payment_pending',
                'to_status' => 'new',
                'event' => 'order_placed_cod',
                'note' => 'Customer placed order using Cash on Delivery.',
                'user_id' => request()->user()?->id,
            ]);
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
            : public_path('assets/brand/sushako-shopping-official-email.png');
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
        $localTestPayment = $this->allowsLocalRazorpayTest($request);
        abort_unless($this->razorpayConfigured() || $localTestPayment, 403);

        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:120'],
            'razorpay_order_id' => ['required', 'string', 'max:120'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);

        if (! $localTestPayment && (! $order->razorpay_order_id || $data['razorpay_order_id'] !== $order->razorpay_order_id)) {
            throw ValidationException::withMessages([
                'razorpay_order_id' => 'Razorpay order mismatch. Please retry payment from the order page.',
            ]);
        }

        if (! $this->validRazorpaySignature($data)) {
            throw ValidationException::withMessages([
                'razorpay_signature' => 'Razorpay payment verification failed. Please contact support if money was debited.',
            ]);
        }

        DB::transaction(function () use ($order, $data): void {
            $order->refresh();

            if ($order->status === 'payment_pending') {
                $this->reduceStockFor($order);
            }

            $order->forceFill([
                'payment_method' => 'razorpay',
                'payment_status' => 'paid',
                'status' => 'placed',
                'seller_order_status' => 'new',
                'placed_at' => $order->placed_at ?? now(),
                'seller_acceptance_due_at' => $order->seller_acceptance_due_at ?? now()->addHours(24),
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_order_id' => $data['razorpay_order_id'],
            ])->save();

            $order->statusEvents()->create([
                'actor' => 'customer',
                'from_status' => 'payment_pending',
                'to_status' => 'new',
                'event' => 'order_paid',
                'note' => 'Customer paid online and order entered seller queue.',
                'user_id' => request()->user()?->id,
            ]);
        });

        return response()->json([
            'status' => 'paid',
            'message' => "Payment captured for {$order->order_number}.",
            'redirect_url' => route('order.success', $order->order_number),
        ]);
    }

    public function track(Request $request): View
    {
        $data = $request->validate([
            'query' => ['nullable', 'string', 'max:32'],
            'contact' => ['nullable', 'string', 'max:255'],
        ]);

        return view('orders.track', [
            'orders' => $this->trackableOrders($request, $data['query'] ?? null, $data['contact'] ?? null),
            'query' => $data,
        ]);
    }

    public function lookup(Request $request): View
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:32'],
            'contact' => ['nullable', 'string', 'max:255'],
        ]);

        $orders = $this->trackableOrders($request, $data['query'], $data['contact'] ?? null);
        foreach ($orders as $order) {
            $request->session()->put('checkout_orders.'.$order->order_number, [
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
            ]);
        }

        return view('orders.track', [
            'orders' => $orders,
            'query' => $data,
        ]);
    }

    public function waitForSeller(Order $order): RedirectResponse
    {
        $this->authorizeCustomerOrder($order);
        abort_unless($this->canChooseOverdueAction($order), 422, 'This order is not eligible for overdue seller action yet.');

        $order->forceFill([
            'seller_overdue_at' => $order->seller_overdue_at ?? now(),
            'customer_overdue_choice' => 'wait',
            'customer_overdue_choice_at' => now(),
        ])->save();

        $order->statusEvents()->create([
            'actor' => 'customer',
            'user_id' => request()->user()?->id,
            'from_status' => $order->seller_order_status,
            'to_status' => $order->seller_order_status,
            'event' => 'customer_wait_selected',
            'note' => 'Customer chose to wait for seller acceptance after 24 hours.',
        ]);

        return back()->with('status', 'We have recorded that you want to wait for the seller.');
    }

    public function requestRefund(Order $order): RedirectResponse
    {
        $this->authorizeCustomerOrder($order);
        abort_unless($this->canChooseOverdueAction($order), 422, 'This order is not eligible for refund request yet.');

        $order->forceFill([
            'seller_overdue_at' => $order->seller_overdue_at ?? now(),
            'customer_overdue_choice' => 'refund',
            'customer_overdue_choice_at' => now(),
            'status' => 'refund_initiated',
        ])->save();

        $order->statusEvents()->create([
            'actor' => 'customer',
            'user_id' => request()->user()?->id,
            'from_status' => $order->seller_order_status,
            'to_status' => 'refund_initiated',
            'event' => 'customer_refund_requested',
            'note' => 'Customer requested refund after seller missed the 24-hour acceptance window.',
            'metadata' => ['gateway_deduction_disclosed' => true],
        ]);

        return back()->with('status', 'Refund request received. Payment gateway charges, if any, may be deducted as disclosed before confirmation.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorizeCustomerOrder($order);
        abort_if(in_array($order->seller_order_status, ['shipped', 'delivered'], true), 422, 'Cancellation is blocked after shipment.');
        $from = $order->seller_order_status ?: $order->status;

        $order->forceFill([
            'seller_order_status' => 'cancelled',
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Customer requested cancellation before shipment.',
        ])->save();

        $order->statusEvents()->create([
            'actor' => 'customer',
            'user_id' => request()->user()?->id,
            'from_status' => $from,
            'to_status' => 'cancelled',
            'event' => 'customer_cancelled',
            'note' => 'Customer cancelled before shipment.',
        ]);

        return back()->with('status', 'Order cancelled.');
    }

    private function trackableOrders(Request $request, ?string $orderNumber = null, ?string $contact = null)
    {
        $user = $request->user();
        $orderNumber = $orderNumber ? strtoupper(trim($orderNumber)) : null;
        $contact = trim((string) $contact);

        $query = Order::query()
            ->with(['items.vendor'])
            ->whereIn('status', ['payment_pending', 'placed', 'confirmed', 'packing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'rejected', 'refund_initiated', 'refunded'])
            ->when($orderNumber, fn ($builder) => $builder->where('order_number', $orderNumber))
            ->latest();

        if ($user && $user->role === User::ROLE_CUSTOMER) {
            return $query->where('user_id', $user->id)->get();
        }

        if (! $orderNumber || blank($contact)) {
            return collect();
        }

        $normalizedContact = preg_replace('/\D+/', '', $contact) ?: $contact;

        return $query
            ->where(function ($builder) use ($contact, $normalizedContact): void {
                $builder
                    ->where('customer_email', $contact)
                    ->orWhere('customer_phone', $contact)
                    ->orWhere('customer_phone', $normalizedContact);
            })
            ->get();
    }

    private function authorizeCustomerOrder(Order $order): void
    {
        $user = request()->user();

        if ($user?->role === User::ROLE_SUPER_ADMIN) {
            return;
        }

        if ($user?->role === User::ROLE_CUSTOMER && $order->user_id === $user->id) {
            return;
        }

        $sessionOrder = request()->session()->get('checkout_orders.'.$order->order_number);
        if (
            is_array($sessionOrder)
            && ($sessionOrder['phone'] ?? null) === $order->customer_phone
            && (($sessionOrder['email'] ?? null) === $order->customer_email || blank($order->customer_email))
        ) {
            return;
        }

        abort($user ? 403 : 404);
    }

    private function canChooseOverdueAction(Order $order): bool
    {
        return in_array($order->seller_order_status, ['new'], true)
            && $order->seller_acceptance_due_at
            && now()->greaterThan($order->seller_acceptance_due_at)
            && $order->status === 'placed';
    }

    private function customerForGuestCheckout(array $data): User
    {
        $phone = $data['customer_phone'];
        $email = $data['customer_email'] ?: "guest-{$phone}@shop.sushako.in";

        $customer = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->where('phone', $phone)
            ->first();

        if ($customer) {
            $updates = [
                'name' => $data['customer_name'],
                'last_activity_at' => now(),
            ];

            if (blank($customer->email) && filled($data['customer_email'] ?? null)) {
                $updates['email'] = $data['customer_email'];
            }

            $customer->forceFill($updates)->save();

            return $customer;
        }

        return User::query()->create([
            'name' => $data['customer_name'],
            'email' => $this->uniqueCustomerEmail($email),
            'phone' => $phone,
            'password' => Hash::make(Str::random(40)),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'last_activity_at' => now(),
        ]);
    }

    private function uniqueCustomerEmail(string $email): string
    {
        if (! User::query()->where('email', $email)->exists()) {
            return $email;
        }

        [$local, $domain] = str($email)->contains('@')
            ? explode('@', $email, 2)
            : [$email, 'shop.sushako.in'];

        return $local.'+'.Str::lower(Str::random(8)).'@'.$domain;
    }

    private function normalizeIndianMobile(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        $digits = str_starts_with($digits, '91') && strlen($digits) === 12 ? substr($digits, 2) : $digits;

        if (! preg_match('/^[6-9]\d{9}$/', $digits)) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Enter a valid 10 digit Indian mobile number.',
            ]);
        }

        return $digits;
    }

    private function summary(array $cart): array
    {
        return $this->settings->checkoutSummary($cart);
    }

    private function paymentSetting(string $provider): ?PaymentSetting
    {
        return $this->settings->paymentProvider($provider);
    }

    private function razorpayConfigured(): bool
    {
        return filled(config('services.razorpay.key'))
            && filled(config('services.razorpay.secret'))
            && ! in_array(config('services.razorpay.key'), ['YOUR_KEY_ID', 'YOUR_TEST_KEY_ID'], true);
    }

    private function allowsLocalRazorpayTest(Request $request): bool
    {
        return $request->routeIs('order.payment.razorpay-test')
            && app()->environment(['local', 'testing'])
            && (bool) config('services.razorpay.test_mode');
    }

    private function ensureRazorpayOrder(Order $order): Order
    {
        if ($order->razorpay_order_id) {
            return $order;
        }

        $response = Http::withBasicAuth(
            (string) config('services.razorpay.key'),
            (string) config('services.razorpay.secret')
        )
            ->asJson()
            ->timeout(15)
            ->retry(2, 300)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) $order->total_amount * 100,
                'currency' => config('services.razorpay.currency', 'INR'),
                'receipt' => $order->order_number,
                'payment_capture' => 1,
                'notes' => [
                    'order_number' => $order->order_number,
                    'customer_email' => $order->customer_email,
                    'customer_phone' => $order->customer_phone,
                ],
            ]);

        if (! $response->successful() || blank($response->json('id'))) {
            throw new \RuntimeException('Razorpay order creation failed: '.$response->body());
        }

        $order->forceFill([
            'razorpay_order_id' => $response->json('id'),
        ])->save();

        return $order->fresh();
    }

    private function validRazorpaySignature(array $data): bool
    {
        if (app()->environment(['local', 'testing']) && (bool) config('services.razorpay.test_mode')) {
            return ($data['razorpay_signature'] ?? '') === 'test_signature';
        }

        $expected = hash_hmac(
            'sha256',
            $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'],
            (string) config('services.razorpay.secret')
        );

        return hash_equals($expected, $data['razorpay_signature']);
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
