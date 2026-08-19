<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            if (! Schema::hasColumn('vendors', 'pickup_address_line_1')) {
                $table->string('pickup_address_line_1')->nullable()->after('pickup_address');
            }
            if (! Schema::hasColumn('vendors', 'pickup_city')) {
                $table->string('pickup_city')->nullable()->after('pickup_address_line_1');
            }
            if (! Schema::hasColumn('vendors', 'pickup_state')) {
                $table->string('pickup_state')->nullable()->after('pickup_city');
            }
            if (! Schema::hasColumn('vendors', 'pickup_postal_code')) {
                $table->string('pickup_postal_code', 6)->nullable()->after('pickup_state');
            }
            if (! Schema::hasColumn('vendors', 'returns_accepted')) {
                $table->boolean('returns_accepted')->default(false)->after('free_shipping_threshold');
            }
            if (! Schema::hasColumn('vendors', 'return_window_days')) {
                $table->unsignedSmallInteger('return_window_days')->nullable()->after('returns_accepted');
            }
            if (! Schema::hasColumn('vendors', 'return_policy_summary')) {
                $table->string('return_policy_summary')->default('No Returns')->after('return_window_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            foreach (['return_policy_summary', 'return_window_days', 'returns_accepted', 'pickup_postal_code', 'pickup_state', 'pickup_city', 'pickup_address_line_1'] as $column) {
                if (Schema::hasColumn('vendors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
