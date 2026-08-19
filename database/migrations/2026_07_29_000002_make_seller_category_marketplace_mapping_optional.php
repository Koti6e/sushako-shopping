<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seller_storefront_categories') || ! Schema::hasColumn('seller_storefront_categories', 'category_id')) {
            return;
        }

        Schema::table('seller_storefront_categories', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('seller_storefront_categories') || ! Schema::hasColumn('seller_storefront_categories', 'category_id')) {
            return;
        }

        Schema::table('seller_storefront_categories', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable(false)->change();
        });
    }
};
