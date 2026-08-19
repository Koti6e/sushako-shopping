<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'whatsapp_order_updates')) {
                $table->boolean('whatsapp_order_updates')->default(false)->after('phone_is_whatsapp');
            }

            if (! Schema::hasColumn('users', 'whatsapp_marketing_consent')) {
                $table->boolean('whatsapp_marketing_consent')->default(false)->index()->after('whatsapp_order_updates');
            }

            if (! Schema::hasColumn('users', 'whatsapp_marketing_consent_at')) {
                $table->timestamp('whatsapp_marketing_consent_at')->nullable()->after('whatsapp_marketing_consent');
            }

            if (! Schema::hasColumn('users', 'whatsapp_marketing_consent_source')) {
                $table->string('whatsapp_marketing_consent_source')->nullable()->after('whatsapp_marketing_consent_at');
            }

            if (! Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->index()->after('last_login_at');
            }
        });

        Schema::create('customer_management_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        Schema::create('customer_carts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('customer_type')->default('registered')->index();
            $table->string('status')->default('active')->index();
            $table->unsignedInteger('original_value')->default(0);
            $table->string('recovery_token', 80)->nullable()->unique();
            $table->timestamp('recovery_token_expires_at')->nullable();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('abandoned_at')->nullable()->index();
            $table->timestamp('reminder_opened_at')->nullable();
            $table->timestamp('reminder_marked_sent_at')->nullable();
            $table->timestamp('recovered_at')->nullable();
            $table->foreignId('recovered_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('coupon_used')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('customer_cart_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('cart_key');
            $table->string('product_name');
            $table->string('product_slug')->nullable();
            $table->string('colour')->nullable();
            $table->string('size')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price');
            $table->unsignedInteger('line_total');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->unique(['customer_cart_id', 'cart_key']);
        });

        Schema::create('customer_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone')->nullable()->index();
            $table->string('type')->index();
            $table->string('title');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->string('source')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();
        });

        Schema::create('customer_communications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_cart_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel')->default('whatsapp')->index();
            $table->string('category')->index();
            $table->string('template_key')->nullable();
            $table->string('status')->default('prepared')->index();
            $table->text('message_content');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('marked_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_export_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('export_type')->index();
            $table->json('filters')->nullable();
            $table->unsignedInteger('record_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_export_audits');
        Schema::dropIfExists('customer_communications');
        Schema::dropIfExists('customer_activities');
        Schema::dropIfExists('customer_cart_items');
        Schema::dropIfExists('customer_carts');
        Schema::dropIfExists('customer_management_settings');

        Schema::table('users', function (Blueprint $table): void {
            foreach ([
                'last_activity_at',
                'whatsapp_marketing_consent_source',
                'whatsapp_marketing_consent_at',
                'whatsapp_marketing_consent',
                'whatsapp_order_updates',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
