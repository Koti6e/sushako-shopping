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
                'business_name_edit_count' => ['unsignedTinyInteger', 0],
                'tax_preference' => ['string', null],
                'approval_status' => ['string', 'registered'],
                'approval_rejection_reason' => ['text', null],
                'store_visibility' => ['string', 'draft'],
                'setup_completed_at' => ['timestamp', null],
                'published_by' => ['foreignId', null],
            ] as $column => [$type, $default]) {
                if (Schema::hasColumn('vendors', $column)) {
                    continue;
                }

                match ($type) {
                    'unsignedTinyInteger' => $table->unsignedTinyInteger($column)->default((int) $default),
                    'text' => $table->text($column)->nullable(),
                    'timestamp' => $table->timestamp($column)->nullable(),
                    'foreignId' => $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete(),
                    default => $table->string($column)->nullable()->default($default),
                };

                if (in_array($column, ['tax_preference', 'approval_status', 'store_visibility'], true)) {
                    $table->index($column);
                }
            }
        });

        if (! Schema::hasTable('seller_holidays')) {
            Schema::create('seller_holidays', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
                $table->date('holiday_date');
                $table->string('title');
                $table->text('note')->nullable();
                $table->timestamps();
                $table->unique(['vendor_id', 'holiday_date'], 'seller_holidays_vendor_date_unique');
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'seller_rejection_reason')) {
                $table->text('seller_rejection_reason')->nullable();
            }
            if (! Schema::hasColumn('products', 'continue_selling_when_out_of_stock')) {
                $table->boolean('continue_selling_when_out_of_stock')->default(false);
            }
            if (! Schema::hasColumn('products', 'seller_product_views')) {
                $table->unsignedBigInteger('seller_product_views')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_holidays');
    }
};
