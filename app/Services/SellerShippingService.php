<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderShippingSnapshot;
use App\Models\ProductShippingOverride;
use App\Models\SellerShippingProfile;
use App\Models\SellerShippingZone;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class SellerShippingService
{
    public function profile(Vendor $vendor): SellerShippingProfile
    {
        $profile = SellerShippingProfile::query()->firstOrCreate(
            ['vendor_id' => $vendor->id],
            ['default_method' => $vendor->preferred_courier_name ?: 'Seller-managed delivery', 'free_local_pickup' => false]
        );

        if (! $profile->zones()->exists()) {
            foreach ([
                ['name' => 'Local Delivery', 'coverage_type' => 'local', 'base_charge' => 40, 'priority' => 10],
                ['name' => 'Within District', 'coverage_type' => 'district', 'base_charge' => 60, 'priority' => 20],
                ['name' => 'Within State', 'coverage_type' => 'state', 'base_charge' => 80, 'priority' => 30],
                ['name' => 'Other States', 'coverage_type' => 'nationwide', 'base_charge' => 120, 'priority' => 40],
            ] as $zone) {
                $profile->zones()->create($zone + ['charge_type' => 'flat', 'is_active' => true]);
            }
        }

        return $profile->load('zones');
    }

    public function save(Vendor $vendor, array $data): SellerShippingProfile
    {
        $profile = SellerShippingProfile::query()->updateOrCreate(
            ['vendor_id' => $vendor->id],
            ['default_method' => $data['default_method'] ?? null, 'free_shipping_threshold' => $data['free_shipping_threshold'] ?? null, 'free_local_pickup' => (bool) ($data['free_local_pickup'] ?? false), 'is_active' => true]
        );

        foreach ($data['zones'] ?? [] as $zoneId => $zoneData) {
            $zone = $profile->zones()->whereKey($zoneId)->firstOrFail();
            $coverage = $zoneData['coverage'] ?? [];
            if (is_string($coverage['pincodes'] ?? null)) $coverage['pincodes'] = array_values(array_filter(array_map('trim', explode(',', $coverage['pincodes']))));
            $zone->update([
                'name' => $zoneData['name'], 'coverage_type' => $zoneData['coverage_type'],
                'coverage' => $coverage, 'charge_type' => $zoneData['charge_type'],
                'base_charge' => $zoneData['base_charge'], 'per_kg_charge' => $zoneData['per_kg_charge'] ?? 0,
                'free_above_value' => $zoneData['free_above_value'] ?? null, 'is_active' => (bool) ($zoneData['is_active'] ?? false),
            ]);
        }

        return $profile->fresh('zones');
    }

    public function addZone(Vendor $vendor, array $data): SellerShippingZone
    {
        return DB::transaction(function () use ($vendor, $data): SellerShippingZone {
            $profile = $this->profile($vendor);
            return $profile->zones()->create($data + ['coverage' => [], 'is_active' => true]);
        });
    }

    public function calculate(array $cart, string $pincode, array $address = []): array
    {
        $groups = collect($cart)->groupBy(fn (array $item) => (int) ($item['vendor_id'] ?? 0));
        $shipments = [];

        foreach ($groups as $vendorId => $items) {
            $vendor = Vendor::query()->find($vendorId);
            if (! $vendor) {
                throw ValidationException::withMessages(['shipping' => 'A product seller could not be identified.']);
            }
            if (! Schema::hasTable('seller_shipping_profiles')) {
                $shipments[] = ['vendor_id' => $vendor->id, 'vendor' => $vendor, 'amount' => 0, 'zone' => null, 'method' => null, 'rule' => ['source' => 'legacy_global_shipping', 'reason' => 'shipping_schema_pending']];
                continue;
            }
            $profile = SellerShippingProfile::query()->where('vendor_id', $vendor->id)->where('is_active', true)->with('zones')->first();
            if (! $profile) {
                $shipments[] = ['vendor_id' => $vendor->id, 'vendor' => $vendor, 'amount' => 0, 'zone' => null, 'method' => null, 'rule' => ['source' => 'legacy_global_shipping']];
                continue;
            }
            $override = ProductShippingOverride::query()->whereIn('product_id', $items->pluck('product_id'))->where('vendor_id', $vendor->id)->where('is_active', true)->first();
            $zone = $profile->zones->filter(fn (SellerShippingZone $candidate) => $this->matches($candidate, $pincode, $address, $vendor))->sortBy('priority')->first();
            if (! $zone && ! $override) {
                throw ValidationException::withMessages(['shipping' => "{$vendor->store_display_name} does not deliver to {$pincode}."]);
            }
            $subtotal = (int) $items->sum(fn (array $item) => $item['price'] * $item['quantity']);
            $amount = $override ? $this->overrideCharge($override, $items) : $this->zoneCharge($zone, $items, $subtotal, $profile);
            $shipments[] = ['vendor_id' => $vendor->id, 'vendor' => $vendor, 'amount' => $amount, 'zone' => $zone?->name, 'method' => $override?->method ?: $profile->default_method, 'rule' => ['source' => $override ? 'product_override' : 'shipping_zone', 'zone_id' => $zone?->id, 'override_id' => $override?->id, 'charge_type' => $override?->mode ?: $zone?->charge_type]];
        }

        $amount = (int) collect($shipments)->sum('amount');
        return ['amount' => $amount, 'shipments' => $shipments, 'status' => $amount === 0 ? 'free' : 'available'];
    }

    public function snapshot(Order $order, array $shipping, array $address): void
    {
        if (! Schema::hasTable('order_shipping_snapshots')) {
            return;
        }

        foreach ($shipping['shipments'] as $shipment) {
            OrderShippingSnapshot::query()->create([
                'order_id' => $order->id, 'vendor_id' => $shipment['vendor_id'], 'zone_name' => $shipment['zone'],
                'shipping_method' => $shipment['method'], 'shipping_charge' => $shipment['amount'], 'pincode' => $order->pincode,
                'delivery_address' => $address, 'rule_details' => $shipment['rule'],
            ]);
        }
    }

    private function matches(SellerShippingZone $zone, string $pincode, array $address, Vendor $vendor): bool
    {
        $coverage = $zone->coverage ?: [];
        if ($zone->coverage_type === 'restricted') return false;
        if ($zone->coverage_type === 'pincodes') return in_array($pincode, array_map('strval', $coverage['pincodes'] ?? []), true);
        if ($zone->coverage_type === 'local') {
            $prefix = substr((string) ($vendor->postal_code ?: $vendor->pickup_postal_code), 0, 3);
            return filled($prefix) && str_starts_with($pincode, $prefix);
        }
        if ($zone->coverage_type === 'district') return filled($address['district'] ?? null) && in_array(strtolower($address['district']), array_map('strtolower', $coverage['districts'] ?? []), true);
        if ($zone->coverage_type === 'state') return filled($address['state'] ?? null) && in_array(strtolower($address['state']), array_map('strtolower', $coverage['states'] ?? [$vendor->state]), true);
        return true;
    }

    private function overrideCharge(ProductShippingOverride $override, $items): int
    {
        return match ($override->mode) {
            'free', 'digital' => 0,
            'weight' => (int) round($override->charge * max(1, $items->sum(fn (array $item) => (int) ($item['weight_grams'] ?? 0)) / 1000)),
            default => (int) $override->charge,
        };
    }

    private function zoneCharge(SellerShippingZone $zone, $items, int $subtotal, SellerShippingProfile $profile): int
    {
        if ($profile->free_shipping_threshold !== null && $subtotal >= $profile->free_shipping_threshold) return 0;
        if ($zone->free_above_value !== null && $subtotal >= $zone->free_above_value) return 0;
        return match ($zone->charge_type) {
            'weight' => (int) $zone->base_charge + (int) ceil(max(0, $items->sum(fn (array $item) => (int) ($item['weight_grams'] ?? 0))) / 1000) * (int) $zone->per_kg_charge,
            'order_value' => $subtotal >= (int) ($zone->free_above_value ?? PHP_INT_MAX) ? 0 : (int) $zone->base_charge,
            default => (int) $zone->base_charge,
        };
    }
}
