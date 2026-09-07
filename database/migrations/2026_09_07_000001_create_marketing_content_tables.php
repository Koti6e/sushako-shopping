<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_collections')) {
            Schema::create('product_collections', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('display_order')->default(0)->index();
                $table->timestamp('starts_at')->nullable()->index();
                $table->timestamp('ends_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('product_collection_product')) {
            Schema::create('product_collection_product', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_collection_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();

                // MySQL limits identifiers to 64 characters.
                $table->unique(['product_collection_id', 'product_id'], 'collection_product_unique');
            });
        } elseif (! Schema::hasIndex('product_collection_product', 'collection_product_unique')) {
            // A failed pre-release migration may have created this table before
            // MySQL rejected Laravel's generated (overlong) index name.
            Schema::table('product_collection_product', function (Blueprint $table): void {
                $table->unique(['product_collection_id', 'product_id'], 'collection_product_unique');
            });
        }

        if (! Schema::hasTable('marketing_contents')) {
            Schema::create('marketing_contents', function (Blueprint $table): void {
                $table->id();
                $table->string('type', 40)->index();
                $table->string('placement', 40)->default('homepage')->index();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('subtitle')->nullable();
                $table->text('description')->nullable();
                $table->string('cta_label', 80)->nullable();
                $table->string('destination_type', 20)->default('none')->index();
                $table->string('destination_url', 500)->nullable();
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_collection_id')->nullable()->constrained()->nullOnDelete();
                $table->string('image_path')->nullable();
                $table->string('mobile_image_path')->nullable();
                $table->unsignedInteger('display_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('starts_at')->nullable()->index();
                $table->timestamp('ends_at')->nullable()->index();
                $table->timestamps();

                $table->index(['placement', 'is_active', 'display_order']);
            });
        }

        Schema::table('seller_category_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('seller_category_requests', 'suggested_parent_id')) {
                $table->foreignId('suggested_parent_id')->nullable()->after('suggested_parent_category')->constrained('categories')->nullOnDelete();
            }

            if (! Schema::hasColumn('seller_category_requests', 'resolved_category_id')) {
                $table->foreignId('resolved_category_id')->nullable()->after('admin_comment')->constrained('categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('seller_category_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('seller_category_requests', 'resolved_category_id')) {
                $table->dropConstrainedForeignId('resolved_category_id');
            }

            if (Schema::hasColumn('seller_category_requests', 'suggested_parent_id')) {
                $table->dropConstrainedForeignId('suggested_parent_id');
            }
        });

        Schema::dropIfExists('marketing_contents');
        Schema::dropIfExists('product_collection_product');
        Schema::dropIfExists('product_collections');
    }
};
