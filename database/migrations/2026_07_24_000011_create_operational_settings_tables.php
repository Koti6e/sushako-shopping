<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('company_name')->default('Sushako Shopping');
            $table->string('legal_business_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('pan', 20)->nullable();
            $table->string('cin', 30)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 12)->nullable();
            $table->string('country')->default('India');
            $table->string('support_email')->nullable();
            $table->string('support_phone', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('currency', 3)->default('INR');
            $table->string('timezone')->default('Asia/Kolkata');
            $table->string('business_hours')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_prefix')->default('INV');
            $table->unsignedInteger('next_invoice_number')->default(1);
            $table->text('invoice_footer')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->string('authorized_signatory_name')->nullable();
            $table->string('authorized_signatory_designation')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_slabs', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['name', 'rate']);
        });

        Schema::create('shipping_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('free_shipping_enabled')->default(true);
            $table->unsignedInteger('free_shipping_threshold')->default(1000);
            $table->text('shipping_policy_text')->nullable();
            $table->boolean('weight_based_shipping_enabled')->default(false);
            $table->boolean('courier_integration_enabled')->default(false);
            $table->boolean('zone_based_shipping_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('payment_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->unique();
            $table->string('label');
            $table->boolean('enabled')->default(false);
            $table->string('environment')->default('sandbox');
            $table->string('key_placeholder')->nullable();
            $table->string('webhook_url_placeholder')->nullable();
            $table->boolean('is_future_provider')->default(false);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'tax_slab_id')) {
                $table->foreignId('tax_slab_id')->nullable()->after('accent')->constrained('tax_slabs')->nullOnDelete();
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'tax_slab_id')) {
                $table->foreignId('tax_slab_id')->nullable()->after('category_id')->constrained('tax_slabs')->nullOnDelete();
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->unique()->after('order_number');
            }

            if (! Schema::hasColumn('orders', 'invoice_sequence')) {
                $table->unsignedInteger('invoice_sequence')->nullable()->after('invoice_number');
            }

            if (! Schema::hasColumn('orders', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('invoice_sequence');
            }

            if (! Schema::hasColumn('orders', 'cgst_amount')) {
                $table->unsignedInteger('cgst_amount')->default(0)->after('tax_amount');
            }

            if (! Schema::hasColumn('orders', 'sgst_amount')) {
                $table->unsignedInteger('sgst_amount')->default(0)->after('cgst_amount');
            }

            if (! Schema::hasColumn('orders', 'igst_amount')) {
                $table->unsignedInteger('igst_amount')->default(0)->after('sgst_amount');
            }

            if (! Schema::hasColumn('orders', 'shipping_status')) {
                $table->string('shipping_status')->default('free')->after('shipping_amount');
            }

            $table->unsignedInteger('shipping_amount')->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'gst_rate')) {
                $table->decimal('gst_rate', 5, 2)->default(0)->after('line_total');
            }

            if (! Schema::hasColumn('order_items', 'tax_amount')) {
                $table->unsignedInteger('tax_amount')->default(0)->after('gst_rate');
            }

            if (! Schema::hasColumn('order_items', 'cgst_amount')) {
                $table->unsignedInteger('cgst_amount')->default(0)->after('tax_amount');
            }

            if (! Schema::hasColumn('order_items', 'sgst_amount')) {
                $table->unsignedInteger('sgst_amount')->default(0)->after('cgst_amount');
            }

            if (! Schema::hasColumn('order_items', 'igst_amount')) {
                $table->unsignedInteger('igst_amount')->default(0)->after('sgst_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            foreach (['igst_amount', 'sgst_amount', 'cgst_amount', 'tax_amount', 'gst_rate'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            foreach (['shipping_status', 'igst_amount', 'sgst_amount', 'cgst_amount', 'invoiced_at', 'invoice_sequence', 'invoice_number'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'tax_slab_id')) {
                $table->dropConstrainedForeignId('tax_slab_id');
            }
        });

        Schema::table('categories', function (Blueprint $table): void {
            if (Schema::hasColumn('categories', 'tax_slab_id')) {
                $table->dropConstrainedForeignId('tax_slab_id');
            }
        });

        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('shipping_settings');
        Schema::dropIfExists('tax_slabs');
        Schema::dropIfExists('invoice_settings');
        Schema::dropIfExists('company_settings');
    }
};
