<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('accent', 20)->default('#2f6b4f');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('collection')->default('Sushako Signature');
            $table->string('subcategory')->nullable();
            $table->string('brand')->default('Sushako');
            $table->string('badge')->default('Featured');
            $table->string('short_description', 500)->nullable();
            $table->text('full_description')->nullable();
            $table->string('fabric')->nullable();
            $table->string('fit')->nullable();
            $table->string('sleeve')->nullable();
            $table->string('pattern')->nullable();
            $table->string('occasion')->nullable();
            $table->string('country_of_origin')->default('India');
            $table->string('return_policy')->default('Easy return support');
            $table->unsignedInteger('mrp');
            $table->unsignedInteger('selling_price');
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->unsignedInteger('reviews')->default(0);
            $table->boolean('is_published')->default(true)->index();
            $table->boolean('is_new')->default(true)->index();
            $table->boolean('is_best_seller')->default(false)->index();
            $table->boolean('local_delivery')->default(true);
            $table->string('fulfillment_scope')->default('Sushako Fulfillment');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->index(['category_id', 'is_published']);
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('label')->default('Product Image');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('colour')->default('Standard');
            $table->string('colour_hex', 20)->default('#d8ccb9');
            $table->string('size')->default('Standard');
            $table->unsignedInteger('stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'colour', 'size']);
            $table->index(['product_id', 'stock']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
