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
                'owner_name' => ['string', null],
                'business_category' => ['string', null],
                'years_in_business' => ['unsignedSmallInteger', null],
                'store_tagline' => ['string', null],
                'store_banner_path' => ['string', null],
                'store_support_number' => ['string', null],
                'store_support_email' => ['string', null],
                'gst_status' => ['string', null],
                'legal_compliance_confirmed_at' => ['timestamp', null],
                'gst_certificate_path' => ['string', null],
                'pan_document_path' => ['string', null],
                'district' => ['string', null],
                'address_latitude' => ['decimal', [10, 7]],
                'address_longitude' => ['decimal', [10, 7]],
                'billing_address' => ['json', null],
                'use_pickup_as_return' => ['boolean', false],
                'use_pickup_as_billing' => ['boolean', false],
                'delivery_settings' => ['json', null],
                'settlement_cycle' => ['string', null],
                'minimum_settlement_amount' => ['decimal', [12, 2]],
                'pending_settlement_notice' => ['text', null],
                'commission_deductions_note' => ['text', null],
                'razorpay_payment_status' => ['string', null],
                'selected_plan' => ['string', null],
                'selected_plan_snapshot' => ['json', null],
                'payment_status' => ['string', null],
                'dashboard_access_enabled' => ['boolean', false],
            ] as $column => [$type, $args]) {
                if (Schema::hasColumn('vendors', $column)) {
                    continue;
                }

                $definition = match ($type) {
                    'unsignedSmallInteger' => $table->unsignedSmallInteger($column)->nullable(),
                    'timestamp' => $table->timestamp($column)->nullable(),
                    'decimal' => $table->decimal($column, $args[0], $args[1])->nullable(),
                    'json' => $table->json($column)->nullable(),
                    'boolean' => $table->boolean($column)->default((bool) $args),
                    'text' => $table->text($column)->nullable(),
                    default => $table->string($column)->nullable(),
                };

                if (in_array($column, ['gst_status', 'selected_plan', 'payment_status'], true)) {
                    $definition->index();
                }
            }
        });

        if (! Schema::hasTable('seller_onboarding_payments')) {
            Schema::create('seller_onboarding_payments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->string('plan');
                $table->string('plan_name_snapshot');
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('currency', 3)->default('INR');
                $table->string('provider')->default('razorpay');
                $table->string('provider_order_id')->nullable()->unique();
                $table->string('provider_payment_id')->nullable()->unique();
                $table->string('provider_signature')->nullable();
                $table->string('status')->default('pending')->index();
                $table->string('payment_purpose')->default('seller_onboarding');
                $table->text('failure_reason')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['vendor_id', 'plan', 'status'], 'seller_onboarding_payments_vendor_plan_status_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_onboarding_payments');
    }
};
