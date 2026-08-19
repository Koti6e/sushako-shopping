<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'buying_price')) {
                $table->unsignedInteger('buying_price')->default(0)->after('selling_price');
            }
            if (! Schema::hasColumn('products', 'scheduled_go_live_at')) {
                $table->timestamp('scheduled_go_live_at')->nullable()->index()->after('seller_status');
            }
            if (! Schema::hasColumn('products', 'published_mode')) {
                $table->string('published_mode')->default('publish_now')->after('scheduled_go_live_at');
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'seller_acceptance_due_at')) {
                $table->timestamp('seller_acceptance_due_at')->nullable()->index()->after('seller_order_status');
            }
            if (! Schema::hasColumn('orders', 'seller_accepted_at')) {
                $table->timestamp('seller_accepted_at')->nullable()->after('seller_acceptance_due_at');
            }
            if (! Schema::hasColumn('orders', 'customer_overdue_choice')) {
                $table->string('customer_overdue_choice')->nullable()->after('seller_overdue_at');
            }
            if (! Schema::hasColumn('orders', 'customer_overdue_choice_at')) {
                $table->timestamp('customer_overdue_choice_at')->nullable()->after('customer_overdue_choice');
            }
            if (! Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
            }
            if (! Schema::hasColumn('orders', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            }
        });

        if (! Schema::hasTable('order_status_events')) {
            Schema::create('order_status_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('actor')->index();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->string('event')->index();
                $table->text('note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_events');

        Schema::table('orders', function (Blueprint $table): void {
            foreach (['cancellation_reason', 'cancelled_at', 'customer_overdue_choice_at', 'customer_overdue_choice', 'seller_accepted_at', 'seller_acceptance_due_at'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            foreach (['published_mode', 'scheduled_go_live_at', 'buying_price'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
