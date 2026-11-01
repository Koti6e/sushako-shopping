<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogue_intelligence_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_domain_import_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('platform', 40)->default('generic');
            $table->string('status', 30)->default('pending')->index();
            $table->boolean('is_accessible')->nullable();
            $table->string('currency', 12)->nullable();
            $table->unsignedInteger('category_count')->default(0);
            $table->unsignedInteger('product_url_count')->default(0);
            $table->json('capabilities')->nullable();
            $table->json('strategy')->nullable();
            $table->json('limitations')->nullable();
            $table->timestamp('analysed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('profile_version', 20)->default('2.0');
            $table->timestamps();
        });

        Schema::create('catalogue_discovery_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_domain_import_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('queued')->index();
            $table->unsignedInteger('pages_scanned')->default(0);
            $table->unsignedInteger('urls_discovered')->default(0);
            $table->unsignedInteger('unique_urls')->default(0);
            $table->unsignedInteger('duplicate_urls')->default(0);
            $table->unsignedInteger('categories_discovered')->default(0);
            $table->unsignedInteger('failed_records')->default(0);
            $table->json('options')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });

        Schema::create('catalogue_discovered_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalogue_discovery_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('catalogue_discovered_categories')->nullOnDelete();
            $table->string('name', 180);
            $table->string('canonical_url', 1000);
            $table->unsignedInteger('product_count')->default(0);
            $table->string('status', 30)->default('discovered')->index();
            $table->timestamps();
            $table->unique(['catalogue_discovery_session_id', 'canonical_url']);
        });

        Schema::create('catalogue_discovered_urls', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalogue_discovery_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalogue_discovered_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('canonical_url', 1000);
            $table->string('source_hash', 64);
            $table->string('identifier', 180)->nullable()->index();
            $table->string('status', 30)->default('discovered')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['catalogue_discovery_session_id', 'source_hash']);
            $table->index(['catalogue_discovery_session_id', 'canonical_url']);
        });

        Schema::create('catalogue_extracted_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalogue_discovery_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalogue_discovered_url_id')->nullable()->constrained()->nullOnDelete();
            $table->string('canonical_url', 1000);
            $table->string('identity_hash', 64)->index();
            $table->string('name', 500)->nullable();
            $table->string('sku', 180)->nullable()->index();
            $table->string('gtin', 180)->nullable()->index();
            $table->string('mpn', 180)->nullable()->index();
            $table->string('brand', 180)->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->string('currency', 12)->nullable();
            $table->string('availability', 80)->nullable();
            $table->json('payload')->nullable();
            $table->json('images')->nullable();
            $table->json('variants')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->string('status', 30)->default('extracted')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['catalogue_discovery_session_id', 'identity_hash']);
        });

        Schema::create('catalogue_product_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalogue_extracted_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('outcome', 30)->default('insufficient_information')->index();
            $table->decimal('confidence', 5, 4)->default(0);
            $table->json('evidence')->nullable();
            $table->json('changes')->nullable();
            $table->string('decision', 30)->default('pending')->index();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'catalogue_extracted_product_id']);
        });

        Schema::create('catalogue_import_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalogue_discovery_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('review')->index();
            $table->unsignedInteger('selected_count')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamps();
            $table->index(['vendor_id', 'status']);
        });

        Schema::create('catalogue_import_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalogue_import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalogue_product_match_id')->constrained()->cascadeOnDelete();
            $table->string('action', 30)->default('hold');
            $table->string('status', 30)->default('pending')->index();
            $table->json('approved_fields')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->unique(['catalogue_import_batch_id', 'catalogue_product_match_id']);
        });

        Schema::create('catalogue_monitoring_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_domain_import_id')->constrained()->cascadeOnDelete();
            $table->string('frequency', 20)->default('disabled');
            $table->boolean('new_products')->default(true);
            $table->boolean('price_changes')->default(true);
            $table->boolean('image_changes')->default(false);
            $table->boolean('availability_changes')->default(true);
            $table->boolean('auto_publish')->default(false);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'seller_domain_import_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogue_monitoring_settings');
        Schema::dropIfExists('catalogue_import_items');
        Schema::dropIfExists('catalogue_import_batches');
        Schema::dropIfExists('catalogue_product_matches');
        Schema::dropIfExists('catalogue_extracted_products');
        Schema::dropIfExists('catalogue_discovered_urls');
        Schema::dropIfExists('catalogue_discovered_categories');
        Schema::dropIfExists('catalogue_discovery_sessions');
        Schema::dropIfExists('catalogue_intelligence_profiles');
    }
};
