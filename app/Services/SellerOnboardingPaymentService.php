<?php

namespace App\Services;

use App\Models\SellerOnboardingPayment;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class SellerOnboardingPaymentService
{
    public function __construct(private readonly SellerAccountService $sellerAccounts) {}

    public function createOrder(Vendor $vendor, string $planKey): SellerOnboardingPayment
    {
        $plan = $this->sellerAccounts->plans()[$planKey] ?? null;
        abort_unless($plan && $planKey !== Vendor::PLAN_FREE, 422);

        $payment = SellerOnboardingPayment::query()
            ->where('vendor_id', $vendor->id)
            ->where('plan', $planKey)
            ->where('status', SellerOnboardingPayment::STATUS_PENDING)
            ->latest()
            ->first();

        if ($payment?->provider_order_id) {
            return $payment;
        }

        return DB::transaction(function () use ($vendor, $plan, $planKey): SellerOnboardingPayment {
            $payment = SellerOnboardingPayment::query()->create([
                'vendor_id' => $vendor->id,
                'seller_plan_id' => $plan['id'] ?? null,
                'plan' => $planKey,
                'plan_name_snapshot' => $plan['name'],
                'amount' => $plan['amount'],
                'currency' => $plan['currency'],
                'internal_order_reference' => 'seller-'.$vendor->id.'-'.now()->format('YmdHis').'-'.strtolower($planKey),
                'status' => SellerOnboardingPayment::STATUS_PENDING,
                'plan_price_snapshot' => $plan['amount'],
                'commission_type_snapshot' => $plan['commission_type'] ?? null,
                'commission_value_snapshot' => $plan['commission_value'] ?? null,
                'product_limit_snapshot' => is_numeric($plan['product_limit']) ? (int) $plan['product_limit'] : null,
                'billing_period_snapshot' => $plan['billing_period'] ?? null,
                'metadata' => [
                    'commission' => $plan['commission'],
                    'product_limit' => $plan['product_limit'],
                    'order_limit' => $plan['order_limit'],
                    'plan_snapshot' => $plan,
                ],
            ]);

            if ($this->razorpayConfigured()) {
                $response = Http::withBasicAuth((string) config('services.razorpay.key'), (string) config('services.razorpay.secret'))
                    ->asJson()
                    ->timeout(15)
                    ->retry(2, 300)
                    ->post('https://api.razorpay.com/v1/orders', [
                        'amount' => (int) $plan['amount'] * 100,
                        'currency' => $plan['currency'],
                        'receipt' => $payment->internal_order_reference,
                        'payment_capture' => 1,
                        'notes' => [
                            'purpose' => 'seller_onboarding',
                            'vendor_id' => (string) $vendor->id,
                            'plan' => $planKey,
                            'description' => "Sushako {$plan['name']} Seller Plan - 1 Month",
                        ],
                    ]);

                if (! $response->successful() || blank($response->json('id'))) {
                    $payment->forceFill([
                        'status' => SellerOnboardingPayment::STATUS_FAILED,
                        'failure_reason' => 'Razorpay order creation failed.',
                    ])->save();

                    throw new \RuntimeException('Razorpay seller onboarding order creation failed: '.$response->body());
                }

                $payment->forceFill(['provider_order_id' => $response->json('id')])->save();
            } else {
                $payment->forceFill(['provider_order_id' => 'order_seller_test_'.$payment->id])->save();
            }

            $vendor->forceFill([
                'selected_plan' => $planKey,
                'selected_plan_id' => $plan['id'] ?? null,
                'selected_plan_slug' => $planKey,
                'selected_plan_snapshot' => $plan,
                'plan_selected_at' => $vendor->plan_selected_at ?? now(),
                'payment_status' => Vendor::PAYMENT_PENDING,
                'razorpay_payment_status' => Vendor::PAYMENT_PENDING,
                'plan_status' => Vendor::PLAN_PENDING_PAYMENT,
            ])->save();

            $this->sellerAccounts->audit($vendor, 'seller_payment_order_created', 'Seller onboarding payment order created.', [
                'payment_id' => $payment->id,
                'provider_order_id' => $payment->provider_order_id,
            ]);

            return $payment->refresh();
        });
    }

    public function verify(SellerOnboardingPayment $payment, array $data): Vendor
    {
        if ($payment->status === SellerOnboardingPayment::STATUS_PAID) {
            return $this->sellerAccounts->activatePaidPlan($payment->vendor, $payment);
        }

        if ($data['razorpay_order_id'] !== $payment->provider_order_id) {
            $this->fail($payment, 'Razorpay order mismatch.');
        }

        if (! $this->validSignature($data)) {
            $this->fail($payment, 'Razorpay payment verification failed.');
        }

        $payment->forceFill([
            'provider_payment_id' => $data['razorpay_payment_id'],
            'provider_signature' => $data['razorpay_signature'],
            'status' => SellerOnboardingPayment::STATUS_PAID,
            'verified_at' => now(),
            'failure_reason' => null,
        ])->save();

        return $this->sellerAccounts->activatePaidPlan($payment->vendor, $payment->fresh());
    }

    public function fail(SellerOnboardingPayment $payment, string $reason): never
    {
        $payment->forceFill([
            'status' => SellerOnboardingPayment::STATUS_FAILED,
            'failure_reason' => $reason,
        ])->save();

        $payment->vendor->forceFill([
            'payment_status' => Vendor::PAYMENT_FAILED,
            'razorpay_payment_status' => Vendor::PAYMENT_FAILED,
            'plan_status' => Vendor::PLAN_PENDING_PAYMENT,
            'dashboard_access_enabled' => false,
        ])->save();

        throw ValidationException::withMessages(['razorpay_signature' => $reason]);
    }

    public function validSignature(array $data): bool
    {
        if (! $this->razorpayConfigured() && app()->environment('testing')) {
            return ($data['razorpay_signature'] ?? '') === hash_hmac('sha256', $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'], 'test_secret');
        }

        $expected = hash_hmac(
            'sha256',
            $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'],
            (string) config('services.razorpay.secret')
        );

        return hash_equals($expected, (string) $data['razorpay_signature']);
    }

    public function razorpayConfigured(): bool
    {
        return filled(config('services.razorpay.key'))
            && filled(config('services.razorpay.secret'))
            && ! in_array(config('services.razorpay.key'), ['YOUR_KEY_ID', 'YOUR_TEST_KEY_ID'], true);
    }
}
