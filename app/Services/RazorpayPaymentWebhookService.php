<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class RazorpayPaymentWebhookService
{
    public function __construct(private readonly OrderPaymentService $payments) {}

    public function handle(string $rawPayload, string $signature, ?string $eventId = null): array
    {
        $secret = (string) config('services.razorpay.webhook_secret');
        abort_unless(filled($secret), 503, 'Payment webhook secret is not configured.');
        $expected = hash_hmac('sha256', $rawPayload, $secret);
        abort_unless(filled($signature) && hash_equals($expected, $signature), 403, 'Invalid payment webhook signature.');

        $payload = json_decode($rawPayload, true);
        abort_unless(is_array($payload), 400, 'Invalid payment webhook payload.');
        $event = (string) ($payload['event'] ?? '');
        $eventId = $eventId ?: (string) ($payload['id'] ?? 'razorpay:'.hash('sha256', $rawPayload));
        $entity = data_get($payload, 'payload.payment.entity', data_get($payload, 'payload.order.entity', []));
        $providerOrderId = (string) (data_get($entity, 'order_id') ?: data_get($entity, 'id'));
        $providerPaymentId = data_get($entity, 'id');
        $order = $providerOrderId ? Order::query()->where('razorpay_order_id', $providerOrderId)->first() : null;

        try {
            $webhook = PaymentWebhookEvent::query()->firstOrCreate(['event_id' => $eventId], [
                'provider' => 'razorpay', 'event' => $event, 'order_id' => $order?->id,
                'provider_order_id' => $providerOrderId ?: null, 'provider_payment_id' => $providerPaymentId,
                'payload_hash' => hash('sha256', $rawPayload), 'status' => 'received', 'metadata' => ['event' => $event],
            ]);
        } catch (QueryException $exception) {
            // A concurrent webhook may win the unique event insert. Re-read it
            // and let the locked order/payment path make the second delivery a
            // harmless no-op.
            $webhook = PaymentWebhookEvent::query()->where('event_id', $eventId)->firstOrFail();
        }

        if ($webhook->status === 'processed') {
            return ['received' => true, 'duplicate' => true];
        }

        if (! $order) {
            $webhook->forceFill(['status' => 'ignored', 'failure_reason' => 'No matching payment-pending order.', 'processed_at' => now()])->save();
            return ['received' => true, 'ignored' => true];
        }

        try {
            if (in_array($event, ['payment.captured', 'order.paid'], true)) {
                $amount = (int) data_get($entity, 'amount', 0);
                $currency = (string) data_get($entity, 'currency', config('services.razorpay.currency', 'INR'));
                abort_if($amount !== ((int) $order->total_amount * 100), 422, 'Payment amount does not match order total.');
                abort_if($currency !== config('services.razorpay.currency', 'INR'), 422, 'Payment currency does not match order currency.');
                abort_unless(filled($providerPaymentId), 422, 'Payment identifier is missing.');
                $this->payments->markRazorpayPaid($order, (string) $providerPaymentId, $providerOrderId, $eventId);
                $webhook->forceFill(['status' => 'processed', 'order_id' => $order->id, 'processed_at' => now()])->save();
            } elseif (str_contains($event, 'failed')) {
                $webhook->forceFill(['status' => 'processed', 'order_id' => $order->id, 'processed_at' => now()])->save();
            } else {
                $webhook->forceFill(['status' => 'ignored', 'order_id' => $order->id, 'processed_at' => now()])->save();
            }
        } catch (\Throwable $exception) {
            $webhook->forceFill(['status' => 'failed', 'failure_reason' => $exception->getMessage()])->save();
            throw $exception;
        }

        return ['received' => true, 'processed' => true];
    }
}
