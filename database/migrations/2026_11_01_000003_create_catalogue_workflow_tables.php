<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_catalogue_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission', 80);
            $table->timestamps();
            $table->unique(['vendor_id', 'user_id', 'permission']);
            $table->index(['vendor_id', 'user_id']);
        });

        Schema::create('catalogue_image_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalogue_extracted_product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('original_path', 500);
            $table->string('processed_path', 500)->nullable();
            $table->string('original_hash', 64)->index();
            $table->string('processed_hash', 64)->nullable();
            $table->json('settings')->nullable();
            $table->string('status', 30)->default('original')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('catalogue_enrichment_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('catalogue_extracted_product_id')->constrained()->cascadeOnDelete();
            $table->json('fields');
            $table->string('generated_by', 30)->default('rules');
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('catalogue_monitoring_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_domain_import_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('queued')->index();
            $table->unsignedInteger('new_products')->default(0);
            $table->unsignedInteger('price_changes')->default(0);
            $table->unsignedInteger('image_changes')->default(0);
            $table->unsignedInteger('availability_changes')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'seller_domain_import_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogue_monitoring_runs');
        Schema::dropIfExists('catalogue_enrichment_suggestions');
        Schema::dropIfExists('catalogue_image_assets');
        Schema::dropIfExists('seller_catalogue_permissions');
    }
};
