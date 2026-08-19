<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            foreach ([
                'store_display_name' => 'string',
                'store_description' => 'text',
                'business_logo_path' => 'string',
                'pan_number' => 'string',
                'gstin' => 'string',
                'website' => 'string',
                'area' => 'string',
                'landmark' => 'string',
                'pickup_address' => 'text',
                'return_address' => 'text',
                'working_days' => 'json',
                'opens_at' => 'time',
                'closes_at' => 'time',
                'order_cutoff_at' => 'time',
                'support_hours' => 'string',
                'delivery_radius' => 'unsignedInteger',
                'radius_unit' => 'string',
                'store_timezone' => 'string',
                'shipping_commitment' => 'string',
                'flat_shipping_charge' => 'decimal',
                'free_shipping_threshold' => 'decimal',
                'local_delivery_preference' => 'boolean',
                'preferred_courier_name' => 'string',
                'manual_tracking_enabled' => 'boolean',
                'store_status' => 'string',
                'current_plan' => 'string',
                'plan_status' => 'string',
                'plan_activated_at' => 'timestamp',
                'plan_expires_at' => 'timestamp',
                'grace_starts_at' => 'timestamp',
                'grace_ends_at' => 'timestamp',
                'onboarding_step' => 'unsignedTinyInteger',
                'onboarding_completed_at' => 'timestamp',
                'published_at' => 'timestamp',
                'vacation_starts_at' => 'timestamp',
                'vacation_ends_at' => 'timestamp',
                'vacation_message' => 'text',
                'vacation_auto_reactivate' => 'boolean',
                'bank_account_holder_name' => 'string',
                'bank_name' => 'string',
                'bank_account_number' => 'text',
                'bank_ifsc' => 'text',
                'bank_branch_name' => 'string',
                'bank_account_type' => 'string',
                'bank_upi_id' => 'text',
                'bank_document_path' => 'string',
                'bank_verification_status' => 'string',
            ] as $column => $type) {
                if (! Schema::hasColumn('vendors', $column)) {
                    $definition = match ($type) {
                        'text' => $table->text($column)->nullable(),
                        'json' => $table->json($column)->nullable(),
                        'time' => $table->time($column)->nullable(),
                        'timestamp' => $table->timestamp($column)->nullable(),
                        'unsignedInteger' => $table->unsignedInteger($column)->nullable(),
                        'unsignedTinyInteger' => $table->unsignedTinyInteger($column)->default(1),
                        'decimal' => $table->decimal($column, 12, 2)->nullable(),
                        'boolean' => $table->boolean($column)->default(false),
                        default => $table->string($column)->nullable(),
                    };

                    if (in_array($column, ['store_status', 'current_plan', 'plan_status', 'bank_verification_status'], true)) {
                        $definition->index();
                    }
                }
            }
        });

        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'available_to_sellers')) {
                $table->boolean('available_to_sellers')->default(true)->index();
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('id')->constrained('vendors')->nullOnDelete();
            }

            foreach ([
                'seller_status' => 'string',
                'product_condition' => 'string',
                'refurbishment_type' => 'string',
                'refurbishment_grade' => 'string',
                'condition_description' => 'text',
                'cosmetic_condition' => 'string',
                'testing_details' => 'text',
                'warranty_period' => 'string',
                'package_contents' => 'text',
                'weight_grams' => 'unsignedInteger',
                'length_cm' => 'decimal',
                'width_cm' => 'decimal',
                'height_cm' => 'decimal',
                'low_stock_threshold' => 'unsignedInteger',
            ] as $column => $type) {
                if (! Schema::hasColumn('products', $column)) {
                    $definition = match ($type) {
                        'text' => $table->text($column)->nullable(),
                        'unsignedInteger' => $table->unsignedInteger($column)->nullable(),
                        'decimal' => $table->decimal($column, 10, 2)->nullable(),
                        default => $table->string($column)->nullable(),
                    };

                    if ($column === 'seller_status') {
                        $definition->default('draft')->index();
                    }
                }
            }

            if (! Schema::hasIndex('products', 'products_vendor_seller_status_idx')) {
                $table->index(['vendor_id', 'seller_status'], 'products_vendor_seller_status_idx');
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'seller_order_status')) {
                $table->string('seller_order_status')->default('new')->index()->after('status');
            }

            if (! Schema::hasColumn('orders', 'shipment_deadline_at')) {
                $table->timestamp('shipment_deadline_at')->nullable()->index()->after('delivered_at');
            }

            if (! Schema::hasColumn('orders', 'seller_overdue_at')) {
                $table->timestamp('seller_overdue_at')->nullable()->after('shipment_deadline_at');
            }

            if (! Schema::hasColumn('orders', 'tracking_url')) {
                $table->string('tracking_url')->nullable()->after('tracking_number');
            }
        });

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->after('order_id')->constrained('vendors')->nullOnDelete();
            }

            foreach ([
                'seller_plan_at_order' => 'string',
                'gross_line_amount' => 'decimal',
                'commission_base' => 'decimal',
                'commission_type' => 'string',
                'commission_rate' => 'decimal',
                'commission_amount' => 'decimal',
                'seller_earning' => 'decimal',
                'commission_rule_source' => 'string',
                'commission_calculated_at' => 'timestamp',
                'settlement_status' => 'string',
            ] as $column => $type) {
                if (! Schema::hasColumn('order_items', $column)) {
                    $definition = match ($type) {
                        'decimal' => $table->decimal($column, 12, 2)->default(0),
                        'timestamp' => $table->timestamp($column)->nullable(),
                        default => $table->string($column)->nullable(),
                    };

                    if ($column === 'settlement_status') {
                        $definition->default('upcoming')->index();
                    }
                }
            }

            if (! Schema::hasIndex('order_items', 'order_items_vendor_settlement_idx')) {
                $table->index(['vendor_id', 'settlement_status'], 'order_items_vendor_settlement_idx');
            }
        });

        if (! Schema::hasTable('seller_categories')) {
            Schema::create('seller_categories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['vendor_id', 'category_id'], 'seller_categories_vendor_category_unique');
            });
        }

        if (! Schema::hasTable('seller_policy_acceptances')) {
            Schema::create('seller_policy_acceptances', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('policy_key');
                $table->string('policy_version')->default('2026-07');
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('accepted_at');
                $table->timestamps();
                $table->unique(['vendor_id', 'policy_key', 'policy_version'], 'seller_policy_vendor_key_version_unique');
            });
        } elseif (! Schema::hasIndex('seller_policy_acceptances', 'seller_policy_vendor_key_version_unique')) {
            Schema::table('seller_policy_acceptances', function (Blueprint $table): void {
                $table->unique(['vendor_id', 'policy_key', 'policy_version'], 'seller_policy_vendor_key_version_unique');
            });
        }

        if (! Schema::hasTable('seller_plan_payments')) {
            Schema::create('seller_plan_payments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('plan');
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('payment_reference')->nullable();
                $table->string('status')->default('pending_payment')->index();
                $table->timestamp('activated_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('grace_starts_at')->nullable();
                $table->timestamp('grace_ends_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('commission_rules')) {
            Schema::create('commission_rules', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('plan')->nullable()->index();
                $table->string('type')->default('percentage');
                $table->decimal('rate', 8, 2)->default(10);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seller_ledger_entries')) {
            Schema::create('seller_ledger_entries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
                $table->string('entry_type')->index();
                $table->decimal('gross_amount', 12, 2)->default(0);
                $table->decimal('commission_amount', 12, 2)->default(0);
                $table->decimal('net_amount', 12, 2)->default(0);
                $table->string('status')->default('posted')->index();
                $table->text('reason')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seller_settlements')) {
            Schema::create('seller_settlements', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('settlement_number')->unique();
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->decimal('gross_product_value', 12, 2)->default(0);
                $table->decimal('total_commission', 12, 2)->default(0);
                $table->decimal('refund_deductions', 12, 2)->default(0);
                $table->decimal('other_adjustments', 12, 2)->default(0);
                $table->decimal('net_payable', 12, 2)->default(0);
                $table->date('expected_settlement_date')->nullable();
                $table->date('paid_at')->nullable();
                $table->string('payment_reference')->nullable();
                $table->string('status')->default('upcoming')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seller_settlement_items')) {
            Schema::create('seller_settlement_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('seller_settlement_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
                $table->decimal('gross_amount', 12, 2)->default(0);
                $table->decimal('commission_amount', 12, 2)->default(0);
                $table->decimal('seller_earning', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seller_inventory_adjustments')) {
            Schema::create('seller_inventory_adjustments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity_delta');
                $table->unsignedInteger('stock_after');
                $table->string('reason');
                $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seller_notifications')) {
            Schema::create('seller_notifications', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('type')->index();
                $table->string('title');
                $table->text('body')->nullable();
                $table->string('action_url')->nullable();
                $table->string('dedupe_key')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->unique(['vendor_id', 'dedupe_key'], 'seller_notifications_vendor_dedupe_unique');
            });
        }

        if (! Schema::hasTable('seller_audit_logs')) {
            Schema::create('seller_audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->nullable()->constrained('vendors')->cascadeOnDelete();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action')->index();
                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seller_api_integrations')) {
            Schema::create('seller_api_integrations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('provider');
                $table->text('api_key')->nullable();
                $table->text('api_secret')->nullable();
                $table->string('webhook_url')->nullable();
                $table->string('status')->default('not_connected')->index();
                $table->timestamp('last_successful_sync_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('storefront_promotional_banners')) {
            Schema::create('storefront_promotional_banners', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->string('desktop_image_path');
                $table->string('mobile_image_path');
                $table->string('cta_label');
                $table->string('cta_url');
                $table->unsignedInteger('display_order')->default(0)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_promotional_banners');
        Schema::dropIfExists('seller_api_integrations');
        Schema::dropIfExists('seller_audit_logs');
        Schema::dropIfExists('seller_notifications');
        Schema::dropIfExists('seller_inventory_adjustments');
        Schema::dropIfExists('seller_settlement_items');
        Schema::dropIfExists('seller_settlements');
        Schema::dropIfExists('seller_ledger_entries');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('seller_plan_payments');
        Schema::dropIfExists('seller_policy_acceptances');
        Schema::dropIfExists('seller_categories');
    }
};
