<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceMasterTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Grocery & Food', 'Fresh Food', 'Fashion', 'Footwear',
            'Bags, Luggage & Accessories', 'Beauty & Personal Care',
            'Health & Wellness', 'Electronics', 'Home Appliances',
            'Kitchen & Dining', 'Home & Living', 'Furniture', 'Automotive',
            'EV & Mobility', 'Sports & Fitness', 'Toys, Baby & Kids',
            'Books, Stationery & Education', 'Computers & IT',
            'Jewellery & Watches', 'Garden & Agriculture',
            'Hardware, Electrical & Tools', 'Pet Supplies', 'Gifts & Occasions',
            'Handmade, Art & Crafts', 'Local & Regional Products',
            'Refurbished & Pre-Owned', 'Business & B2B', 'Travel & Outdoor',
            'Musical Instruments', 'Services & Digital Products',
        ];

        foreach ($categories as $position => $name) {
            $category = Category::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                    'available_to_sellers' => true,
                    'sort_order' => ($position + 1) * 10,
                ],
            );
            $category->forceFill([
                'category_level' => 'category',
                'is_master' => true,
                'needs_review' => false,
            ])->save();
        }
    }
}
