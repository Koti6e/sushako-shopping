<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table): void {
                if (! Schema::hasColumn('orders', 'payment_verified_at')) {
                    $table->timestamp('payment_verified_at')->nullable()->after('payment_status');
                }
                if (! Schema::hasColumn('orders', 'payment_webhook_event_id')) {
                    $table->string('payment_webhook_event_id', 160)->nullable()->unique()->after('payment_verified_at');
                }
            });
        }

        if (! Schema::hasTable('payment_webhook_events')) {
            Schema::create('payment_webhook_events', function (Blueprint $table): void {
                $table->id();
                $table->string('provider', 32)->default('razorpay')->index();
                $table->string('event_id', 160)->unique();
                $table->string('event', 120)->nullable()->index();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->string('provider_order_id', 120)->nullable()->index();
                $table->string('provider_payment_id', 120)->nullable()->index();
                $table->string('payload_hash', 64);
                $table->string('status', 32)->default('received')->index();
                $table->text('failure_reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Production recovery is application rollback.
    }
};
