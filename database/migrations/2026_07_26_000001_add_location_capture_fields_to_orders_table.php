<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_location_url');
            }

            if (! Schema::hasColumn('orders', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            }

            if (! Schema::hasColumn('orders', 'location_confirmed')) {
                $table->boolean('location_confirmed')->default(false)->after('delivery_longitude');
            }

            if (! Schema::hasColumn('orders', 'location_captured_at')) {
                $table->timestamp('location_captured_at')->nullable()->after('location_confirmed');
            }

            if (! Schema::hasColumn('orders', 'location_updated_by')) {
                $table->foreignId('location_updated_by')->nullable()->after('location_captured_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'location_capture_method')) {
                $table->string('location_capture_method')->nullable()->after('location_updated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'location_updated_by')) {
                $table->dropConstrainedForeignId('location_updated_by');
            }

            foreach ([
                'location_capture_method',
                'location_captured_at',
                'location_confirmed',
                'delivery_longitude',
                'delivery_latitude',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
