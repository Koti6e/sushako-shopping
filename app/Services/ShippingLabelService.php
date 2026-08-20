<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ShippingLabel;
use App\Models\ShippingLabelEvent;
use App\Models\User;
use App\Models\Vendor;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ShippingLabelService
{
    public function generate(Order $order, User $user, array $options = [], ?ShippingLabel $previous = null): ShippingLabel
    {
        return DB::transaction(function () use ($order, $user, $options, $previous): ShippingLabel {
            $order->refresh();

            if ($previous) {
                $previous->forceFill([
                    'status' => ShippingLabel::STATUS_REGENERATED,
                    'regenerated_by_id' => $user->id,
                    'regenerated_at' => now(),
                ])->save();
            }

            $version = ((int) $order->shippingLabels()->max('version')) + 1;
            $branding = $this->labelBrandingForOrder($order, $options);
            $label = ShippingLabel::query()->create([
                'order_id' => $order->id,
                'label_number' => $this->nextLabelNumber($order, $version),
                'version' => $version,
                'status' => ShippingLabel::STATUS_GENERATED,
                'fulfillment_type' => $options['fulfillment_type'] ?? ShippingLabel::FULFILLMENT_SUSHAKO,
                'brand_mode' => $branding['brand_mode'],
                'print_format' => $options['print_format'] ?? 'a6_thermal',
                'courier_code' => $options['courier_code'] ?? $order->shipping_provider,
                'courier_name' => $options['courier_name'] ?? $this->courierName($order),
                'warehouse_name' => $options['warehouse_name'] ?? 'Sushako Dispatch',
                'warehouse_address' => $options['warehouse_address'] ?? 'Sushako Shopping Fulfilment Desk',
                'seller_name' => $branding['seller_name'],
                'seller_logo_path' => $branding['seller_logo_path'],
                'seller_address' => $branding['seller_address'],
                'seller_gst' => $branding['seller_gst'],
                'seller_contact' => $branding['seller_contact'],
                'seller_return_address' => $branding['seller_return_address'],
                'package_count' => max(1, (int) ($options['package_count'] ?? 1)),
                'package_index' => max(1, (int) ($options['package_index'] ?? 1)),
                'weight_grams' => $options['weight_grams'] ?? null,
                'generated_by_id' => $user->id,
                'generated_at' => now(),
                'previous_label_id' => $previous?->id,
                'internal_lookup_code' => $this->internalLookupCode($order),
                'location_qr_url' => $this->mapsUrl($order),
                'metadata' => [
                    'future_courier_payload' => [],
                    'source' => $previous ? 'regeneration' : 'manual_admin_generation',
                    'seller_plan' => $branding['plan'],
                    'branding_rule' => $branding['label'],
                    'requested_brand_mode' => $options['brand_mode'] ?? null,
                ],
            ]);

            $this->log($label, $user, $previous ? 'regenerated' : 'generated', [
                'previous_label_id' => $previous?->id,
                'brand_mode' => $label->brand_mode,
                'fulfillment_type' => $label->fulfillment_type,
            ]);

            return $label->load(['order.items', 'generatedBy']);
        });
    }

    public function markPrinted(ShippingLabel $label, User $user, int $copies = 1, ?string $printer = null, ?string $reason = null): ShippingLabel
    {
        $label->forceFill([
            'status' => ShippingLabel::STATUS_PRINTED,
            'printed_at' => now(),
            'printed_by_id' => $user->id,
            'print_count' => $label->print_count + max(1, $copies),
            'last_printed_printer' => $printer,
        ])->save();

        $this->log($label, $user, 'printed', [
            'copies' => max(1, $copies),
            'printer' => $printer,
            'reason' => $reason,
        ]);

        return $label->fresh(['order.items', 'printedBy']);
    }

    public function updateLocation(Order $order, User $user, array $data): void
    {
        $order->forceFill([
            'delivery_latitude' => $data['delivery_latitude'] ?? null,
            'delivery_longitude' => $data['delivery_longitude'] ?? null,
            'delivery_location_url' => $data['delivery_location_url'] ?? $order->delivery_location_url,
            'location_confirmed' => (bool) ($data['location_confirmed'] ?? false),
            'location_captured_at' => now(),
            'location_updated_by' => $user->id,
            'location_capture_method' => $data['location_capture_method'] ?? 'admin_updated',
        ])->save();

        foreach ($order->shippingLabels()->latest()->get() as $label) {
            $label->forceFill([
                'location_qr_url' => $this->mapsUrl($order->fresh()),
            ])->save();

            $this->log($label, $user, 'location_updated', [
                'method' => $order->location_capture_method,
            ]);
        }
    }

    public function mapsUrl(Order $order): ?string
    {
        if (filled($order->delivery_latitude) && filled($order->delivery_longitude)) {
            return 'https://www.google.com/maps/search/?api=1&query='.$order->delivery_latitude.','.$order->delivery_longitude;
        }

        return filled($order->delivery_location_url) ? $order->delivery_location_url : null;
    }

    public function labelBrandingForVendor(Vendor $vendor, array $options = []): array
    {
        $plan = $vendor->current_plan ?: Vendor::PLAN_FREE;
        $sellerName = $vendor->store_display_name ?: $vendor->business_name ?: $vendor->user?->name ?: 'Seller';

        if ($plan === Vendor::PLAN_ENTERPRISE) {
            return [
                'plan' => Vendor::PLAN_ENTERPRISE,
                'label' => 'Own Branding Label',
                'brand_mode' => ShippingLabel::BRAND_SELLER,
                'seller_name' => $options['seller_name'] ?? $sellerName,
                'seller_logo_path' => $options['seller_logo_path'] ?? $vendor->business_logo_path,
                'seller_address' => $options['seller_address'] ?? ($vendor->pickup_address ?: $vendor->address_line_1 ?: $sellerName),
                'seller_gst' => $options['seller_gst'] ?? $vendor->gstin,
                'seller_contact' => $options['seller_contact'] ?? ($vendor->phone ?: config('services.whatsapp.support_number')),
                'seller_return_address' => $options['seller_return_address'] ?? ($vendor->return_address ?: $vendor->pickup_address ?: 'Return to '.$sellerName),
            ];
        }

        return [
            'plan' => $plan === Vendor::PLAN_GROWTH ? Vendor::PLAN_GROWTH : Vendor::PLAN_FREE,
            'label' => $plan === Vendor::PLAN_GROWTH ? 'Sushako Labelling' : 'Sushako Branding',
            'brand_mode' => ShippingLabel::BRAND_SUSHAKO,
            'seller_name' => $sellerName,
            'seller_logo_path' => null,
            'seller_address' => $vendor->pickup_address ?: $vendor->address_line_1 ?: 'Sushako Shopping',
            'seller_gst' => $vendor->gstin,
            'seller_contact' => $vendor->phone ?: config('services.whatsapp.support_number'),
            'seller_return_address' => $vendor->return_address ?: 'Return to Sushako Shopping Fulfilment Desk',
        ];
    }

    public function codeSvgDataUri(string $payload, string $type = 'qr'): string
    {
        if ($type === 'barcode') {
            $barcode = (new BarcodeGeneratorSVG)->getBarcode(
                $payload,
                BarcodeGenerator::TYPE_CODE_128,
                1.6,
                58,
                '#111111'
            );

            return 'data:image/svg+xml;base64,'.base64_encode($barcode);
        }

        return (new Builder)->build(
            writer: new PngWriter,
            data: $payload,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 180,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        )->getDataUri();
    }

    public function log(?ShippingLabel $label, User $user, string $event, array $metadata = []): void
    {
        ShippingLabelEvent::query()->create([
            'shipping_label_id' => $label?->id,
            'order_id' => $label?->order_id ?? (int) ($metadata['order_id'] ?? 0),
            'user_id' => $user->id,
            'event' => $event,
            'metadata' => $metadata,
        ]);
    }

    private function nextLabelNumber(Order $order, int $version): string
    {
        return $order->order_number.'-LBL-'.str_pad((string) $version, 3, '0', STR_PAD_LEFT);
    }

    private function internalLookupCode(Order $order): string
    {
        return 'SL-'.Str::upper(Str::random(10)).'-'.substr(hash('sha256', $order->order_number.microtime(true)), 0, 8);
    }

    private function courierName(Order $order): ?string
    {
        return match ($order->shipping_provider) {
            'rapido' => 'Rapido',
            'uber' => 'Uber',
            'india_post' => 'India Post',
            'dtdc' => 'DTDC',
            'professional' => 'Professional Courier',
            'others' => $order->shipping_provider_other,
            default => null,
        };
    }

    private function labelBrandingForOrder(Order $order, array $options): array
    {
        $vendor = $order->items()->with('vendor.user')->whereNotNull('vendor_id')->first()?->vendor;

        if ($vendor) {
            return $this->labelBrandingForVendor($vendor, $options);
        }

        return [
            'plan' => null,
            'label' => 'Sushako Branding',
            'brand_mode' => $options['brand_mode'] ?? ShippingLabel::BRAND_SUSHAKO,
            'seller_name' => $options['seller_name'] ?? 'Sushako Shopping',
            'seller_logo_path' => $options['seller_logo_path'] ?? null,
            'seller_address' => $options['seller_address'] ?? 'Sushako Shopping',
            'seller_gst' => $options['seller_gst'] ?? null,
            'seller_contact' => $options['seller_contact'] ?? config('services.whatsapp.support_number'),
            'seller_return_address' => $options['seller_return_address'] ?? 'Return to Sushako Shopping Fulfilment Desk',
        ];
    }
}
