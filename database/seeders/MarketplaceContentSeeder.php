<?php

namespace Database\Seeders;

use App\Models\MarketingContent;
use App\Models\Product;
use App\Models\ProductCollection;
use Illuminate\Database\Seeder;

class MarketplaceContentSeeder extends Seeder
{
    public function run(): void
    {
        $collection = ProductCollection::query()->firstOrCreate(['slug' => 'new-arrivals'], [
            'name' => 'New Arrivals',
            'description' => 'Recently added products that are currently available in the marketplace.',
            'is_active' => true,
            'display_order' => 10,
        ]);

        $productIds = Product::query()
            ->where('is_new', true)
            ->orderByDesc('created_at')
            ->limit(12)
            ->pluck('id');

        foreach ($productIds as $position => $productId) {
            $collection->products()->syncWithoutDetaching([
                $productId => ['display_order' => $position + 1],
            ]);
        }

        MarketingContent::query()->firstOrCreate(['slug' => 'homepage-discover-whats-new'], [
            'type' => MarketingContent::TYPE_HERO,
            'placement' => 'homepage_hero',
            'title' => "Discover What's New",
            'subtitle' => 'Browse active products and marketplace categories as they become available.',
            'cta_label' => 'Browse Products',
            'destination_type' => MarketingContent::DESTINATION_URL,
            'destination_url' => '/shop',
            'display_order' => 10,
            'is_active' => true,
        ]);

        MarketingContent::query()->firstOrCreate(['slug' => 'homepage-new-arrivals'], [
            'type' => MarketingContent::TYPE_COLLECTION,
            'placement' => 'homepage_collection',
            'title' => 'New Arrivals',
            'subtitle' => 'A collection built from products already listed in Sushako.',
            'cta_label' => 'View Collection',
            'destination_type' => MarketingContent::DESTINATION_COLLECTION,
            'product_collection_id' => $collection->id,
            'display_order' => 20,
            'is_active' => true,
        ]);

        MarketingContent::query()->firstOrCreate(['slug' => 'homepage-seller-onboarding'], [
            'type' => MarketingContent::TYPE_PROMOTION,
            'placement' => 'homepage_promotion',
            'title' => 'Start Selling with Zero Upfront Cost',
            'subtitle' => 'The Sushako Free Plan is available for sellers who are ready to set up a marketplace store.',
            'cta_label' => 'Become a Seller',
            'destination_type' => MarketingContent::DESTINATION_URL,
            'destination_url' => '/seller/login',
            'display_order' => 30,
            'is_active' => true,
        ]);
    }
}
