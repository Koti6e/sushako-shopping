<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_labels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('label_number')->unique();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('generated')->index();
            $table->string('fulfillment_type')->default('fulfilled_by_sushako')->index();
            $table->string('brand_mode')->default('sushako')->index();
            $table->string('print_format')->default('a6_thermal');
            $table->string('courier_code')->nullable()->index();
            $table->string('courier_name')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->text('warehouse_address')->nullable();
            $table->string('seller_name')->nullable();
            $table->string('seller_logo_path')->nullable();
            $table->text('seller_address')->nullable();
            $table->string('seller_gst')->nullable();
            $table->string('seller_contact')->nullable();
            $table->text('seller_return_address')->nullable();
            $table->unsignedInteger('package_count')->default(1);
            $table->unsignedInteger('package_index')->default(1);
            $table->unsignedInteger('weight_grams')->nullable();
            $table->foreignId('generated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('regenerated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('regenerated_at')->nullable();
            $table->foreignId('previous_label_id')->nullable()->constrained('shipping_labels')->nullOnDelete();
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('printed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('print_count')->default(0);
            $table->string('last_printed_printer')->nullable();
            $table->string('internal_lookup_code')->unique();
            $table->string('location_qr_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'printed_at']);
            $table->index(['generated_at', 'printed_at']);
        });

        Schema::create('shipping_label_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shipping_label_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_label_events');
        Schema::dropIfExists('shipping_labels');
    }
};
