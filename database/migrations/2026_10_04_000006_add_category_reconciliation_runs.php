<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('category_reconciliation_runs')) {
            Schema::create('category_reconciliation_runs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 32)->default('dry_run')->index();
                $table->unsignedInteger('total_count')->default(0);
                $table->unsignedInteger('unchanged_count')->default(0);
                $table->unsignedInteger('mapped_count')->default(0);
                $table->unsignedInteger('uncertain_count')->default(0);
                $table->unsignedInteger('missing_count')->default(0);
                $table->unsignedInteger('conflict_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('category_reconciliation_items')) {
            Schema::create('category_reconciliation_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('run_id')->constrained('category_reconciliation_runs')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->restrictOnDelete();
                $table->foreignId('current_category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('proposed_category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->string('status', 32)->index();
                $table->decimal('confidence', 5, 2)->unsigned()->nullable();
                $table->string('reason', 500)->nullable();
                $table->string('old_slug', 180)->nullable();
                $table->string('new_slug', 180)->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
                $table->unique(['run_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Production recovery is application rollback.
    }
};
