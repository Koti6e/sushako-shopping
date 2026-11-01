<?php

namespace App\Services;

use App\Models\FeaturedCampaign;
use App\Models\FeaturedCampaignEvent;
use App\Models\FeaturedCampaignPayment;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FeaturedCampaignService
{
    public function createPayment(Vendor $vendor, Product $product): FeaturedCampaign
    {
        if (! Schema::hasTable('featured_campaigns')) {
            throw ValidationException::withMessages(['campaign' => 'Featured promotion is pending the next database migration.']);
        }
        abort_unless($product->vendor_id === $vendor->id, 404);
        abort_unless($product->is_published && ! in_array($product->seller_status, [Product::SELLER_STATUS_INACTIVE, Product::SELLER_STATUS_REJECTED, Product::SELLER_STATUS_ARCHIVED], true), 422, 'Only an active product can be promoted.');

        return DB::transaction(function () use ($vendor, $product): FeaturedCampaign {
            if (blank(config('services.razorpay.key')) || blank(config('services.razorpay.secret')) || str_contains((string) config('services.razorpay.key'), 'YOUR_')) throw ValidationException::withMessages(['payment' => 'Razorpay is not configured for promotion payments.']);
            $recent = FeaturedCampaign::query()->where('vendor_id', $vendor->id)->where('created_at', '>=', now()->subDays(7))->whereIn('status', ['pending', 'active'])->lockForUpdate()->first();
            if ($recent) {
                throw ValidationException::withMessages(['campaign' => 'You can promote another product after '.$recent->created_at->addDays(7)->format('d M Y H:i').'.']);
            }
            $campaign = FeaturedCampaign::query()->create(['vendor_id' => $vendor->id, 'product_id' => $product->id, 'amount' => FeaturedCampaign::PRICE, 'currency' => 'INR']);
            $order = Http::withBasicAuth((string) config('services.razorpay.key'), (string) config('services.razorpay.secret'))->timeout(15)->post('https://api.razorpay.com/v1/orders', ['amount' => FeaturedCampaign::PRICE * 100, 'currency' => 'INR', 'receipt' => 'featured-'.$campaign->id, 'notes' => ['campaign_id' => (string) $campaign->id, 'vendor_id' => (string) $vendor->id]]);
            if ($order->failed() || ! filled($order->json('id'))) throw ValidationException::withMessages(['payment' => 'Razorpay could not create the promotion payment session.']);
            $orderId = (string) $order->json('id');
            $campaign->forceFill(['razorpay_order_id' => $orderId])->save();
            $campaign->payments()->create(['provider_order_id' => $orderId, 'amount' => FeaturedCampaign::PRICE, 'currency' => 'INR', 'status' => 'created']);
            return $campaign->fresh(['product']);
        });
    }

    public function verify(Vendor $vendor, FeaturedCampaign $campaign, array $data): FeaturedCampaign
    {
        abort_unless($campaign->vendor_id === $vendor->id, 404);
        $expected = hash_hmac('sha256', $campaign->razorpay_order_id.'|'.$data['razorpay_payment_id'], (string) config('services.razorpay.secret'));
        if (! hash_equals($expected, $data['razorpay_signature']) || $data['razorpay_order_id'] !== $campaign->razorpay_order_id) throw ValidationException::withMessages(['payment' => 'Promotion payment verification failed.']);
        $payment = Http::withBasicAuth((string) config('services.razorpay.key'), (string) config('services.razorpay.secret'))->timeout(15)->get('https://api.razorpay.com/v1/payments/'.rawurlencode($data['razorpay_payment_id']));
        if ($payment->failed() || (int) $payment->json('amount') !== FeaturedCampaign::PRICE * 100 || $payment->json('currency') !== 'INR' || ! in_array($payment->json('status'), ['authorized', 'captured'], true)) throw ValidationException::withMessages(['payment' => 'Razorpay did not confirm the exact promotion payment.']);
        return $this->activate($campaign, $data['razorpay_payment_id'], null);
    }

    public function activate(FeaturedCampaign $campaign, string $paymentId, ?string $eventId): FeaturedCampaign
    {
        return DB::transaction(function () use ($campaign, $paymentId, $eventId): FeaturedCampaign {
            $locked = FeaturedCampaign::query()->whereKey($campaign->id)->lockForUpdate()->firstOrFail();
            if ($locked->payment_status === 'paid') return $locked;
            $locked->forceFill(['payment_status' => 'paid', 'status' => 'active', 'razorpay_payment_id' => $paymentId, 'starts_at' => now(), 'ends_at' => now()->addDays(FeaturedCampaign::DURATION_DAYS)])->save();
            $locked->payments()->where('provider_order_id', $locked->razorpay_order_id)->update(['provider_payment_id' => $paymentId, 'status' => 'verified', 'event_id' => $eventId, 'verified_at' => now()]);
            return $locked->fresh();
        });
    }

    public function record(FeaturedCampaign $campaign, string $type, ?int $userId, string $fingerprint): void
    {
        if (! in_array($type, ['impression', 'click'], true)) return;
        $event = FeaturedCampaignEvent::query()->firstOrCreate(['featured_campaign_id' => $campaign->id, 'event_type' => $type, 'fingerprint' => Str::limit($fingerprint, 128, '')], ['user_id' => $userId]);
        if ($event->wasRecentlyCreated) $campaign->increment($type === 'impression' ? 'impressions' : 'clicks');
    }

    public function webhook(string $rawPayload, string $signature): array
    {
        $secret = (string) config('services.razorpay.webhook_secret');
        abort_unless(filled($secret) && filled($signature) && hash_equals(hash_hmac('sha256', $rawPayload, $secret), $signature), 403, 'Invalid promotion webhook signature.');
        $payload = json_decode($rawPayload, true);
        abort_unless(is_array($payload), 400, 'Invalid promotion webhook payload.');
        if (! Schema::hasTable('featured_campaigns')) {
            return ['received' => true, 'ignored' => true, 'reason' => 'featured_schema_pending'];
        }
        $entity = data_get($payload, 'payload.payment.entity', []);
        $orderId = (string) data_get($entity, 'order_id');
        $campaign = FeaturedCampaign::query()->where('razorpay_order_id', $orderId)->first();
        if (! $campaign) return ['received' => true, 'ignored' => true];
        if (in_array($payload['event'] ?? '', ['payment.captured', 'order.paid'], true)) {
            abort_unless((int) data_get($entity, 'amount') === FeaturedCampaign::PRICE * 100 && data_get($entity, 'currency') === 'INR' && in_array(data_get($entity, 'status'), ['authorized', 'captured'], true), 422, 'Promotion webhook payment does not match the campaign.');
            $this->activate($campaign, (string) data_get($entity, 'id'), (string) ($payload['id'] ?? null));
        }
        return ['received' => true, 'processed' => true];
    }
}
