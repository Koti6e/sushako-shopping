<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('seller_plans')) {
            Schema::create('seller_plans', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->decimal('price', 12, 2)->default(0);
                $table->string('billing_period')->default('none');
                $table->unsignedInteger('product_limit')->nullable();
                $table->boolean('unlimited_products')->default(false);
                $table->string('order_limit')->nullable();
                $table->string('commission_type')->default('percentage');
                $table->decimal('commission_value', 8, 2)->default(0);
                $table->boolean('is_paid')->default(false);
                $table->unsignedInteger('grace_period_days')->default(0);
                $table->string('status')->default('active')->index();
                $table->json('features')->nullable();
                $table->text('supporting_text')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('vendors', function (Blueprint $table): void {
            if (! Schema::hasColumn('vendors', 'selected_plan_id')) {
                $table->foreignId('selected_plan_id')->nullable()->after('selected_plan')->constrained('seller_plans')->nullOnDelete();
            }

            if (! Schema::hasColumn('vendors', 'selected_plan_slug')) {
                $table->string('selected_plan_slug')->nullable()->after('selected_plan_id')->index();
            }

            if (! Schema::hasColumn('vendors', 'plan_selected_at')) {
                $table->timestamp('plan_selected_at')->nullable()->after('selected_plan_snapshot');
            }
        });

        Schema::table('seller_onboarding_payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('seller_onboarding_payments', 'seller_plan_id')) {
                $table->foreignId('seller_plan_id')->nullable()->after('vendor_id')->constrained('seller_plans')->nullOnDelete();
            }

            if (! Schema::hasColumn('seller_onboarding_payments', 'internal_order_reference')) {
                $table->string('internal_order_reference')->nullable()->unique()->after('provider');
            }

            foreach ([
                'plan_price_snapshot' => ['decimal', [12, 2]],
                'commission_type_snapshot' => ['string', null],
                'commission_value_snapshot' => ['decimal', [8, 2]],
                'product_limit_snapshot' => ['unsignedInteger', null],
                'billing_period_snapshot' => ['string', null],
            ] as $column => [$type, $args]) {
                if (Schema::hasColumn('seller_onboarding_payments', $column)) {
                    continue;
                }

                match ($type) {
                    'decimal' => $table->decimal($column, $args[0], $args[1])->nullable(),
                    'unsignedInteger' => $table->unsignedInteger($column)->nullable(),
                    default => $table->string($column)->nullable(),
                };
            }
        });
    }

    public function down(): void
    {
        Schema::table('seller_onboarding_payments', function (Blueprint $table): void {
            foreach ([
                'seller_plan_id',
                'internal_order_reference',
                'plan_price_snapshot',
                'commission_type_snapshot',
                'commission_value_snapshot',
                'product_limit_snapshot',
                'billing_period_snapshot',
            ] as $column) {
                if (! Schema::hasColumn('seller_onboarding_payments', $column)) {
                    continue;
                }

                if ($column === 'seller_plan_id') {
                    $table->dropConstrainedForeignId($column);
                } else {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('vendors', function (Blueprint $table): void {
            foreach (['selected_plan_id', 'selected_plan_slug', 'plan_selected_at'] as $column) {
                if (! Schema::hasColumn('vendors', $column)) {
                    continue;
                }

                if ($column === 'selected_plan_id') {
                    $table->dropConstrainedForeignId($column);
                } else {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('seller_plans');
    }
};
