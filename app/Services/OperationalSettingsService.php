<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\InvoiceSetting;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ShippingSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OperationalSettingsService
{
    public function company(): CompanySetting
    {
        return CompanySetting::query()->firstOrCreate([], $this->defaultCompany());
    }

    public function invoice(): InvoiceSetting
    {
        return InvoiceSetting::query()->firstOrCreate([], $this->defaultInvoice());
    }

    public function shipping(): ShippingSetting
    {
        return ShippingSetting::query()->firstOrCreate([], $this->defaultShipping());
    }

    public function payments(): Collection
    {
        $this->ensurePaymentDefaults();

        return PaymentSetting::query()->orderBy('id')->get();
    }

    public function paymentProvider(string $provider): ?PaymentSetting
    {
        return $this->payments()->firstWhere('provider', $provider);
    }

    public function checkoutSummary(array $cart): array
    {
        $subtotal = collect($cart)->sum(fn (array $item): int => $item['price'] * $item['quantity']);
        $tax = $this->taxSummary($cart);
        $shipping = $this->shippingSummary($subtotal);

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping['amount'],
            'shipping_status' => $shipping['status'],
            'shipping_label' => $shipping['label'],
            'shipping_policy_text' => $shipping['policy_text'],
            'tax' => $tax['tax_amount'],
            'cgst' => $tax['cgst_amount'],
            'sgst' => $tax['sgst_amount'],
            'igst' => $tax['igst_amount'],
            'discount' => 0,
            'total' => $subtotal + (int) ($shipping['amount'] ?? 0),
            'tax_lines' => $tax['lines'],
        ];
    }

    public function taxForProduct(Product $product, int $inclusiveLineTotal): array
    {
        $product->loadMissing(['taxSlab', 'category.taxSlab']);
        $rate = (float) ($product->taxSlab?->rate ?? $product->category?->taxSlab?->rate ?? 0);
        $taxAmount = $rate > 0 ? (int) round($inclusiveLineTotal * $rate / (100 + $rate)) : 0;
        $cgst = (int) floor($taxAmount / 2);
        $sgst = $taxAmount - $cgst;

        return [
            'gst_rate' => $rate,
            'tax_amount' => $taxAmount,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => 0,
        ];
    }

    public function assignInvoiceNumber(Order $order): Order
    {
        if ($order->invoice_number) {
            return $order;
        }

        return DB::transaction(function () use ($order): Order {
            $settings = InvoiceSetting::query()->lockForUpdate()->firstOrCreate([], $this->defaultInvoice());
            $sequence = $settings->next_invoice_number;
            $invoiceNumber = $settings->invoice_prefix.'-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);

            $order->forceFill([
                'invoice_number' => $invoiceNumber,
                'invoice_sequence' => $sequence,
                'invoiced_at' => now(),
            ])->save();

            $settings->forceFill([
                'next_invoice_number' => $sequence + 1,
            ])->save();

            return $order->refresh();
        });
    }

    public function defaultCompany(): array
    {
        return [
            'company_name' => 'Sushako Shopping',
            'legal_business_name' => 'Sushako Shopping',
            'address_line_1' => 'Chennai',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600001',
            'country' => 'India',
            'support_email' => 'support@sushako.test',
            'support_phone' => config('services.whatsapp.support_number'),
            'website' => config('app.url'),
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'business_hours' => 'Monday to Saturday, 10 AM to 7 PM',
        ];
    }

    public function defaultInvoice(): array
    {
        return [
            'invoice_prefix' => 'INV',
            'next_invoice_number' => 1,
            'invoice_footer' => 'Thank you for shopping with Sushako.',
            'terms_conditions' => 'Goods once delivered are governed by the Sushako return and support policies.',
            'authorized_signatory_name' => 'Sushako Store Admin',
            'authorized_signatory_designation' => 'Authorized Signatory',
        ];
    }

    public function defaultShipping(): array
    {
        return [
            'free_shipping_enabled' => true,
            'free_shipping_threshold' => 1000,
            'shipping_policy_text' => 'Delivery charges applicable for eligible orders below the free shipping threshold. Our team will contact the customer after order confirmation regarding delivery charges and logistics.',
            'weight_based_shipping_enabled' => false,
            'courier_integration_enabled' => false,
            'zone_based_shipping_enabled' => false,
        ];
    }

    private function taxSummary(array $cart): array
    {
        $lines = [];
        $taxAmount = 0;
        $cgst = 0;
        $sgst = 0;

        foreach ($cart as $key => $item) {
            $product = Product::query()->with(['taxSlab', 'category.taxSlab'])->find($item['product_id'] ?? null);
            $lineTotal = $item['price'] * $item['quantity'];
            $line = $product ? $this->taxForProduct($product, $lineTotal) : [
                'gst_rate' => 0,
                'tax_amount' => 0,
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => 0,
            ];
            $lines[$key] = $line;
            $taxAmount += $line['tax_amount'];
            $cgst += $line['cgst_amount'];
            $sgst += $line['sgst_amount'];
        }

        return [
            'tax_amount' => $taxAmount,
            'cgst_amount' => $cgst,
            'sgst_amount' => $sgst,
            'igst_amount' => 0,
            'lines' => $lines,
        ];
    }

    private function shippingSummary(int $subtotal): array
    {
        $settings = $this->shipping();

        if ($settings->free_shipping_enabled && $subtotal >= $settings->free_shipping_threshold) {
            return [
                'amount' => 0,
                'status' => 'free',
                'label' => 'Free',
                'policy_text' => $settings->shipping_policy_text,
            ];
        }

        return [
            'amount' => null,
            'status' => 'delivery_charges_applicable',
            'label' => 'Delivery charges applicable',
            'policy_text' => $settings->shipping_policy_text,
        ];
    }

    private function ensurePaymentDefaults(): void
    {
        foreach ([
            ['provider' => 'cod', 'label' => 'Cash on Delivery', 'enabled' => true, 'environment' => 'sandbox', 'key_placeholder' => null, 'webhook_url_placeholder' => null, 'is_future_provider' => false],
            ['provider' => 'razorpay', 'label' => 'Razorpay', 'enabled' => false, 'environment' => 'sandbox', 'key_placeholder' => 'RAZORPAY_KEY_ID', 'webhook_url_placeholder' => 'RAZORPAY_WEBHOOK_URL', 'is_future_provider' => false],
            ['provider' => 'future_provider', 'label' => 'Future Provider', 'enabled' => false, 'environment' => 'sandbox', 'key_placeholder' => 'PROVIDER_KEY', 'webhook_url_placeholder' => 'PROVIDER_WEBHOOK_URL', 'is_future_provider' => true],
        ] as $provider) {
            PaymentSetting::query()->firstOrCreate(['provider' => $provider['provider']], $provider);
        }
    }
}
