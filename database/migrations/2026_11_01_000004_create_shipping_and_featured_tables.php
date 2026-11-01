<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_shipping_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('default_method', 120)->nullable();
            $table->unsignedInteger('free_shipping_threshold')->nullable();
            $table->boolean('free_local_pickup')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('seller_shipping_zones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seller_shipping_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('coverage_type', 30)->default('nationwide');
            $table->json('coverage')->nullable();
            $table->string('charge_type', 30)->default('flat');
            $table->unsignedInteger('base_charge')->default(0);
            $table->unsignedInteger('per_kg_charge')->default(0);
            $table->unsignedInteger('free_above_value')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['seller_shipping_profile_id', 'is_active', 'priority']);
        });

        Schema::create('product_shipping_overrides', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 30)->default('flat');
            $table->unsignedInteger('charge')->default(0);
            $table->string('method', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('order_shipping_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->string('zone_name', 120)->nullable();
            $table->string('shipping_method', 120)->nullable();
            $table->unsignedInteger('shipping_charge')->default(0);
            $table->string('pincode', 6);
            $table->json('delivery_address');
            $table->json('rule_details')->nullable();
            $table->timestamps();
            $table->unique(['order_id', 'vendor_id']);
        });

        Schema::create('featured_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('status', 24)->default('pending')->index();
            $table->unsignedInteger('amount')->default(99);
            $table->string('currency', 3)->default('INR');
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->unique();
            $table->string('payment_status', 24)->default('pending')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->timestamps();
            $table->index(['vendor_id', 'created_at']);
        });

        Schema::create('featured_campaign_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('featured_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 20);
            $table->string('fingerprint', 128)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['featured_campaign_id', 'event_type', 'created_at']);
            $table->unique(['featured_campaign_id', 'event_type', 'fingerprint']);
        });

        Schema::create('featured_campaign_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('featured_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 30)->default('razorpay');
            $table->string('provider_order_id')->nullable();
            $table->string('provider_payment_id')->nullable();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('INR');
            $table->string('status', 24)->default('created')->index();
            $table->string('event_id')->nullable()->unique();
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['provider_order_id', 'provider_payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_campaign_payments');
        Schema::dropIfExists('featured_campaign_events');
        Schema::dropIfExists('featured_campaigns');
        Schema::dropIfExists('order_shipping_snapshots');
        Schema::dropIfExists('product_shipping_overrides');
        Schema::dropIfExists('seller_shipping_zones');
        Schema::dropIfExists('seller_shipping_profiles');
    }
};
