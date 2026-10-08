<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_vendor_fulfilments')) {
            Schema::create('order_vendor_fulfilments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
                $table->string('status', 40)->default('new')->index();
                $table->string('shipping_method', 120)->nullable();
                $table->unsignedInteger('shipping_charge')->default(0);
                $table->date('expected_delivery_date')->nullable();
                $table->string('tracking_number', 120)->nullable();
                $table->string('tracking_url', 500)->nullable();
                $table->timestamp('customer_contacted_at')->nullable();
                $table->timestamp('shipping_confirmed_at')->nullable();
                $table->text('fulfilment_notes')->nullable();
                $table->string('policy_version', 40)->default('seller-fulfilment-v1');
                $table->timestamps();
                $table->unique(['order_id', 'vendor_id']);
            });
        }

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'fulfilment_policy_version')) {
                $table->string('fulfilment_policy_version', 40)->nullable()->after('fulfilment_mode');
            }
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive. Production recovery is application rollback.
    }
};
