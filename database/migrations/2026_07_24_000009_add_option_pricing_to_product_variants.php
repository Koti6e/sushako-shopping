<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_variants', 'option_name')) {
                $table->string('option_name')->nullable()->after('size');
            }

            if (! Schema::hasColumn('product_variants', 'option_value')) {
                $table->string('option_value')->nullable()->after('option_name');
            }

            if (! Schema::hasColumn('product_variants', 'price')) {
                $table->unsignedInteger('price')->nullable()->after('option_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            if (Schema::hasColumn('product_variants', 'price')) {
                $table->dropColumn('price');
            }

            if (Schema::hasColumn('product_variants', 'option_value')) {
                $table->dropColumn('option_value');
            }

            if (Schema::hasColumn('product_variants', 'option_name')) {
                $table->dropColumn('option_name');
            }
        });
    }
};
