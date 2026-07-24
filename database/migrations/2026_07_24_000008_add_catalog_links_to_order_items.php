<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->dropConstrainedForeignId('product_variant_id');
            }

            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }
        });
    }
};
