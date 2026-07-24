<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'shipping_provider')) {
                $table->string('shipping_provider')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('orders', 'shipping_provider_other')) {
                $table->string('shipping_provider_other')->nullable()->after('shipping_provider');
            }

            if (! Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('shipping_provider_other');
            }

            if (! Schema::hasColumn('orders', 'packed_at')) {
                $table->timestamp('packed_at')->nullable()->after('tracking_number');
            }

            if (! Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('packed_at');
            }

            if (! Schema::hasColumn('orders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach (['delivered_at', 'shipped_at', 'packed_at', 'tracking_number', 'shipping_provider_other', 'shipping_provider'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
