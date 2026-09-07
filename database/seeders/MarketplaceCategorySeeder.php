<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceCategorySeeder extends Seeder
{
    /**
     * The initial taxonomy is deliberately small enough to remain usable and
     * can be extended by a superadmin from the category manager.
     */
    private const CATALOG = [
        'Grocery & Food' => ['Groceries', 'Staples', 'Snacks', 'Beverages', 'Packaged Food', 'Cooking Essentials', 'Household Supplies'],
        'Mobiles & Accessories' => ['Smartphones', 'Feature Phones', 'Mobile Cases', 'Chargers & Cables', 'Power Banks', 'Screen Protectors', 'Mobile Accessories'],
        'Electronics' => ['Headphones', 'Earphones', 'Speakers', 'Cameras', 'Smart Watches', 'Gaming', 'Electronic Accessories'],
        'Computers & Laptops' => ['Laptops', 'Desktops', 'Monitors', 'Keyboards', 'Mouse', 'Storage', 'Computer Accessories', 'Networking'],
        'Home & Kitchen' => ['Kitchen', 'Cookware', 'Home Decor', 'Storage', 'Cleaning', 'Dining', 'Furniture', 'Home Improvement'],
        'Fashion' => ["Men's Clothing", "Women's Clothing", 'Kids Clothing', 'Footwear', 'Bags', 'Watches', 'Fashion Accessories'],
        'Beauty & Personal Care' => ['Skincare', 'Hair Care', 'Makeup', 'Fragrances', 'Grooming', 'Personal Care'],
        'Appliances' => ['Kitchen Appliances', 'Home Appliances', 'Fans & Coolers', 'Small Appliances', 'Large Appliances'],
        'Baby & Kids' => ['Baby Care', 'Baby Clothing', 'Kids Clothing', 'Toys', 'Learning', 'School Supplies'],
        'Sports & Fitness' => ['Fitness Equipment', 'Sports Equipment', 'Outdoor', 'Fitness Accessories'],
        'Books & Stationery' => ['Books', 'School Supplies', 'Office Supplies', 'Art & Craft'],
        'Automotive' => ['Car Accessories', 'Bike Accessories', 'Vehicle Care', 'Tools'],
        'Jewellery & Accessories' => ['Jewellery', 'Fashion Jewellery', 'Accessories', 'Watches'],
        'Travel & Luggage' => ['Luggage', 'Backpacks', 'Travel Accessories'],
        'Pet Supplies' => ['Pet Food', 'Pet Accessories', 'Pet Care'],
        'Tools & Hardware' => ['Hand Tools', 'Hardware', 'Electrical', 'Home Repair'],
        'Office Supplies' => ['Office Stationery', 'Office Equipment', 'Desk Accessories'],
        'Other' => ['Other Products'],
    ];

    public function run(): void
    {
        $rootPosition = 0;

        foreach (self::CATALOG as $name => $children) {
            $root = $this->firstOrCreateCategory($name, null, $rootPosition * 10);

            foreach ($children as $childPosition => $childName) {
                $this->firstOrCreateCategory($childName, $root->id, ($childPosition + 1) * 10, $root->slug);
            }

            $rootPosition++;
        }
    }

    private function firstOrCreateCategory(string $name, ?int $parentId, int $sortOrder, ?string $parentSlug = null): Category
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $parentSlug ? $parentSlug.'-'.$baseSlug : $baseSlug;

        // A pre-existing category is marketplace data, not seed-owned data.
        // Leave its custom copy, status, and position intact on future runs.
        return Category::query()->firstOrCreate(['slug' => $slug], [
            'parent_id' => $parentId,
            'name' => $name,
            'tagline' => 'Marketplace category',
            'headline' => $name,
            'description' => 'Products listed in this marketplace category.',
            'accent' => '#2563eb',
            'is_active' => true,
            'available_to_sellers' => true,
            'sort_order' => $sortOrder,
        ]);
    }
}
