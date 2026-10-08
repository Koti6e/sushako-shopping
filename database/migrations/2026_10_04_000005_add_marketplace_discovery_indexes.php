<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasIndex('products', 'products_marketplace_listing_index')) {
                $table->index(['is_published', 'seller_status', 'vendor_id', 'category_id', 'created_at'], 'products_marketplace_listing_index');
            }
            if (! Schema::hasIndex('products', 'products_price_listing_index')) {
                $table->index(['selling_price', 'is_published', 'seller_status'], 'products_price_listing_index');
            }
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('products', function (Blueprint $table): void {
                $table->fullText(['name', 'brand', 'short_description', 'full_description'], 'products_marketplace_search_fulltext');
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Production recovery is application rollback.
    }
};
