<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'category_level')) {
            Schema::table('categories', function (Blueprint $table): void {
                $table->string('category_level', 30)->default('category')->after('parent_id');
                $table->boolean('is_master')->default(true)->after('category_level')->index();
                $table->boolean('needs_review')->default(false)->after('is_master')->index();
            });
        }

        if (! Schema::hasColumn('vendors', 'cod_enabled')) {
            Schema::table('vendors', function (Blueprint $table): void {
                $table->boolean('cod_enabled')->nullable()->after('manual_tracking_enabled');
                $table->string('fulfilment_mode', 40)->default('seller_fulfilled')->after('cod_enabled');
            });
        }

        if (! Schema::hasColumn('products', 'cod_enabled')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->boolean('cod_enabled')->nullable()->after('is_published');
                $table->string('fulfilment_mode', 40)->default('seller_fulfilled')->after('cod_enabled');
                $table->boolean('needs_category_review')->default(false)->after('fulfilment_mode')->index();
            });
        }

        if (! Schema::hasColumn('orders', 'cod_eligibility_status')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->string('cod_eligibility_status', 30)->nullable()->after('payment_status');
                $table->string('cod_eligibility_reason', 255)->nullable()->after('cod_eligibility_status');
                $table->string('fulfilment_mode', 40)->default('seller_fulfilled')->after('cod_eligibility_reason');
            });
        }

        if (! Schema::hasColumn('order_items', 'commission_rate_snapshot')) {
            Schema::table('order_items', function (Blueprint $table): void {
                $table->decimal('commission_rate_snapshot', 8, 3)->nullable()->after('commission_rate');
            });
        }

        if (! Schema::hasTable('category_migration_mappings')) {
        Schema::create('category_migration_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('target_category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('source_name');
            $table->string('target_name')->nullable();
            $table->string('status', 30)->default('needs_review')->index();
            $table->text('reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['source_category_id', 'target_category_id'], 'cat_map_source_target_idx');
        });
        }

        Schema::create('slug_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('from_path', 500)->unique();
            $table->string('to_path', 500);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('seller_domain_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('domain', 255);
            $table->string('verification_token', 80)->nullable();
            $table->string('verification_method', 30)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->unsignedInteger('page_limit')->default(100);
            $table->unsignedInteger('crawl_depth')->default(2);
            $table->timestamps();
            $table->unique(['vendor_id', 'domain']);
        });

        Schema::create('seller_import_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_domain_import_id')->constrained()->cascadeOnDelete();
            $table->string('idempotency_key', 100)->unique();
            $table->string('status', 30)->default('queued')->index();
            $table->unsignedInteger('pages_found')->default(0);
            $table->unsignedInteger('products_found')->default(0);
            $table->unsignedInteger('products_ready')->default(0);
            $table->unsignedInteger('products_needing_review')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_import_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_import_job_id')->constrained()->cascadeOnDelete();
            $table->string('source_url', 1000);
            $table->string('source_hash', 64)->index();
            $table->json('payload');
            $table->json('category_suggestion')->nullable();
            $table->foreignId('duplicate_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('status', 30)->default('needs_review')->index();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->unique(['seller_import_job_id', 'source_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_import_candidates');
        Schema::dropIfExists('seller_import_jobs');
        Schema::dropIfExists('seller_domain_imports');
        Schema::dropIfExists('slug_redirects');
        Schema::dropIfExists('category_migration_mappings');
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('commission_rate_snapshot'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['cod_eligibility_status', 'cod_eligibility_reason', 'fulfilment_mode']));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['cod_enabled', 'fulfilment_mode', 'needs_category_review']));
        Schema::table('vendors', fn (Blueprint $table) => $table->dropColumn(['cod_enabled', 'fulfilment_mode']));
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn(['category_level', 'is_master', 'needs_review']));
    }
};
