<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            foreach ([
                'eligible_quantity' => ['unsignedInteger', 0],
                'platform_fee_per_unit' => ['decimal', 0],
                'platform_fee_total' => ['decimal', 0],
                'refund_amount' => ['decimal', 0],
                'return_amount' => ['decimal', 0],
                'adjustment_amount' => ['decimal', 0],
                'retained_quantity' => ['unsignedInteger', null],
                'settled_quantity' => ['unsignedInteger', 0],
            ] as $column => [$type, $default]) {
                if (Schema::hasColumn('order_items', $column)) {
                    continue;
                }

                match ($type) {
                    'unsignedInteger' => is_null($default)
                        ? $table->unsignedInteger($column)->nullable()
                        : $table->unsignedInteger($column)->default((int) $default),
                    default => $table->decimal($column, 12, 2)->default((float) $default),
                };
            }

            if (! Schema::hasIndex('order_items', 'order_items_vendor_platform_fee_idx')) {
                $table->index(['vendor_id', 'platform_fee_per_unit'], 'order_items_vendor_platform_fee_idx');
            }
        });

        Schema::table('seller_settlements', function (Blueprint $table): void {
            foreach ([
                'gross_amount' => ['decimal', 0],
                'platform_fee_amount' => ['decimal', 0],
                'refund_amount' => ['decimal', 0],
                'return_amount' => ['decimal', 0],
                'adjustment_amount' => ['decimal', 0],
                'final_settlement_amount' => ['decimal', 0],
                'eligible_quantity' => ['unsignedInteger', 0],
                'platform_fee_per_unit' => ['decimal', 0],
                'settlement_period_start' => ['date', null],
                'settlement_period_end' => ['date', null],
                'payment_mode' => ['string', null],
                'hold_reason' => ['text', null],
                'failure_reason' => ['text', null],
                'admin_note' => ['text', null],
                'approved_at' => ['timestamp', null],
                'approved_by' => ['foreignId', null],
                'payment_marked_by' => ['foreignId', null],
            ] as $column => [$type, $default]) {
                if (Schema::hasColumn('seller_settlements', $column)) {
                    continue;
                }

                match ($type) {
                    'unsignedInteger' => $table->unsignedInteger($column)->default((int) $default),
                    'date' => $table->date($column)->nullable(),
                    'text' => $table->text($column)->nullable(),
                    'timestamp' => $table->timestamp($column)->nullable(),
                    'foreignId' => $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete(),
                    'string' => $table->string($column)->nullable(),
                    default => $table->decimal($column, 12, 2)->default((float) $default),
                };
            }
        });

        Schema::table('seller_settlement_items', function (Blueprint $table): void {
            foreach ([
                'eligible_quantity' => ['unsignedInteger', 0],
                'platform_fee_per_unit' => ['decimal', 0],
                'platform_fee_total' => ['decimal', 0],
                'refund_amount' => ['decimal', 0],
                'return_amount' => ['decimal', 0],
                'adjustment_amount' => ['decimal', 0],
                'final_amount' => ['decimal', 0],
            ] as $column => [$type, $default]) {
                if (Schema::hasColumn('seller_settlement_items', $column)) {
                    continue;
                }

                $type === 'unsignedInteger'
                    ? $table->unsignedInteger($column)->default((int) $default)
                    : $table->decimal($column, 12, 2)->default((float) $default);
            }

            if (! Schema::hasIndex('seller_settlement_items', 'settlement_items_order_item_unique')) {
                $table->unique('order_item_id', 'settlement_items_order_item_unique');
            }
        });

        if (! Schema::hasTable('seller_storefront_categories')) {
            Schema::create('seller_storefront_categories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->unsignedInteger('display_order')->default(0)->index();
                $table->string('status')->default('draft')->index();
                $table->timestamps();
                $table->unique(['vendor_id', 'name'], 'seller_storefront_categories_vendor_name_unique');
                $table->unique(['vendor_id', 'slug'], 'seller_storefront_categories_vendor_slug_unique');
            });
        }

        if (! Schema::hasColumn('products', 'seller_storefront_category_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreignId('seller_storefront_category_id')->nullable()->after('category_id')->constrained('seller_storefront_categories')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('seller_category_requests')) {
            Schema::create('seller_category_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('requested_category_name');
                $table->text('description')->nullable();
                $table->string('suggested_parent_category')->nullable();
                $table->text('example_products')->nullable();
                $table->string('status')->default('pending_approval')->index();
                $table->text('admin_comment')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('customer_status_logs')) {
            Schema::create('customer_status_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->text('reason');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('settlement_adjustments')) {
            Schema::create('settlement_adjustments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('seller_settlement_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('adjustment_type');
                $table->decimal('amount', 12, 2);
                $table->text('reason');
                $table->foreignId('admin_user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('financial_audit_logs')) {
            Schema::create('financial_audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
                $table->foreignId('seller_settlement_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action')->index();
                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_audit_logs');
        Schema::dropIfExists('settlement_adjustments');
        Schema::dropIfExists('customer_status_logs');
        Schema::dropIfExists('seller_category_requests');
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'seller_storefront_category_id')) {
                $table->dropConstrainedForeignId('seller_storefront_category_id');
            }
        });
        Schema::dropIfExists('seller_storefront_categories');
    }
};
