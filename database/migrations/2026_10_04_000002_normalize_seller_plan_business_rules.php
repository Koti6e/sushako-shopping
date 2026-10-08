<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seller_plans')) {
            Schema::table('seller_plans', function (Blueprint $table): void {
                if (! Schema::hasColumn('seller_plans', 'business_code')) {
                    $table->string('business_code', 32)->nullable()->after('slug');
                }
                if (! Schema::hasColumn('seller_plans', 'plan_version')) {
                    $table->unsignedInteger('plan_version')->default(1)->after('business_code');
                }
                if (! Schema::hasColumn('seller_plans', 'branding_mode')) {
                    $table->string('branding_mode', 32)->default('sushako')->after('plan_version');
                }
                if (! Schema::hasColumn('seller_plans', 'labelling_mode')) {
                    $table->string('labelling_mode', 32)->default('sushako')->after('branding_mode');
                }
            });

            DB::table('seller_plans')->where('slug', 'free')->update([
                'business_code' => 'free', 'name' => 'Free', 'product_limit' => null,
                'unlimited_products' => true, 'commission_type' => 'flat', 'commission_value' => 1,
                'branding_mode' => 'sushako', 'labelling_mode' => 'sushako',
            ]);
            DB::table('seller_plans')->where('slug', 'growth')->update([
                'business_code' => 'starter', 'name' => 'Starter', 'product_limit' => 1000,
                'unlimited_products' => false, 'commission_type' => 'flat', 'commission_value' => 1,
                'branding_mode' => 'seller', 'labelling_mode' => 'seller',
            ]);
            DB::table('seller_plans')->where('slug', 'enterprise')->update([
                'business_code' => 'premium', 'name' => 'Premium', 'product_limit' => null,
                'unlimited_products' => true, 'commission_type' => 'none', 'commission_value' => 0,
                'branding_mode' => 'seller', 'labelling_mode' => 'seller',
            ]);
        }

        if (Schema::hasTable('vendors') && ! Schema::hasColumn('vendors', 'active_plan_code')) {
            Schema::table('vendors', function (Blueprint $table): void {
                $table->string('active_plan_code', 32)->nullable()->after('current_plan')->index();
            });
        }
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'active_plan_code')) {
            DB::table('vendors')->whereNull('active_plan_code')->update([
                'active_plan_code' => DB::raw("CASE COALESCE(current_plan, selected_plan, selected_plan_slug) WHEN 'free' THEN 'free' WHEN 'growth' THEN 'starter' WHEN 'enterprise' THEN 'premium' WHEN 'starter' THEN 'starter' WHEN 'premium' THEN 'premium' ELSE NULL END"),
            ]);
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table): void {
                if (! Schema::hasColumn('order_items', 'seller_plan_version_snapshot')) {
                    $table->unsignedInteger('seller_plan_version_snapshot')->nullable()->after('seller_plan_at_order');
                }
                if (! Schema::hasColumn('order_items', 'commission_calculation_version')) {
                    $table->string('commission_calculation_version', 32)->nullable()->after('commission_rule_source');
                }
            });
        }
        if (Schema::hasTable('seller_ledger_entries')) {
            Schema::table('seller_ledger_entries', function (Blueprint $table): void {
                if (! Schema::hasColumn('seller_ledger_entries', 'dedupe_key')) {
                    $table->string('dedupe_key', 160)->nullable()->unique()->after('order_item_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Production recovery is application rollback.
    }
};
