<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogue_discovered_categories', function (Blueprint $table): void {
            $table->boolean('selected')->default(true)->after('product_count');
            $table->boolean('excluded')->default(false)->after('selected');
            $table->timestamp('processed_at')->nullable()->after('status');
            $table->index(['catalogue_discovery_session_id', 'selected', 'excluded']);
        });

        Schema::table('catalogue_discovery_sessions', function (Blueprint $table): void {
            $table->json('progress')->nullable()->after('options');
            $table->unsignedInteger('eligible_products')->default(0)->after('failed_records');
            $table->unsignedInteger('selected_products')->default(0)->after('eligible_products');
            $table->unsignedInteger('imported_products')->default(0)->after('selected_products');
        });

        Schema::table('seller_domain_imports', function (Blueprint $table): void {
            $table->json('settings')->nullable()->after('crawl_depth');
        });
    }

    public function down(): void
    {
        Schema::table('seller_domain_imports', function (Blueprint $table): void {
            $table->dropColumn('settings');
        });
        Schema::table('catalogue_discovery_sessions', function (Blueprint $table): void {
            $table->dropColumn(['progress', 'eligible_products', 'selected_products', 'imported_products']);
        });
        Schema::table('catalogue_discovered_categories', function (Blueprint $table): void {
            $table->dropIndex(['catalogue_discovery_session_id', 'selected', 'excluded']);
            $table->dropColumn(['selected', 'excluded', 'processed_at']);
        });
    }
};
