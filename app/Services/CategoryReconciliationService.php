<?php

namespace App\Services;

use App\Models\CategoryMigrationMapping;
use App\Models\CategoryReconciliationItem;
use App\Models\CategoryReconciliationRun;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CategoryReconciliationService
{
    public function dryRun(?int $actorId = null): CategoryReconciliationRun
    {
        return DB::transaction(function () use ($actorId): CategoryReconciliationRun {
            $run = CategoryReconciliationRun::query()->create(['initiated_by' => $actorId, 'status' => 'dry_run']);
            $counts = ['total_count' => 0, 'unchanged_count' => 0, 'mapped_count' => 0, 'uncertain_count' => 0, 'missing_count' => 0, 'conflict_count' => 0];

            Product::query()->with('category')->orderBy('id')->chunkById(200, function ($products) use ($run, &$counts): void {
                foreach ($products as $product) {
                    $counts['total_count']++;
                    $mapping = CategoryMigrationMapping::query()->where('source_category_id', $product->category_id)->whereIn('status', ['approved', 'mapped'])->first();
                    $target = $mapping?->target_category_id ? \App\Models\Category::query()->find($mapping->target_category_id) : null;
                    $status = ! $product->category_id ? 'missing' : ($target && $target->is_active ? ($target->id === $product->category_id ? 'unchanged' : 'mapped') : 'uncertain');
                    $counts[$status.'_count']++;
                    CategoryReconciliationItem::query()->create([
                        'run_id' => $run->id, 'product_id' => $product->id, 'current_category_id' => $product->category_id,
                        'proposed_category_id' => $target?->id, 'status' => $status, 'confidence' => $target ? 100 : null,
                        'reason' => $mapping?->reason ?: ($product->category_id ? 'No approved category mapping.' : 'Product has no category.'),
                        'old_slug' => $product->slug, 'new_slug' => $product->slug,
                    ]);
                }
            });

            $run->forceFill($counts)->save();
            return $run->fresh();
        });
    }

    public function approve(CategoryReconciliationRun $run, int $actorId): CategoryReconciliationRun
    {
        abort_unless($run->status === 'dry_run', 422, 'Only a dry-run can be approved.');
        $run->forceFill(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $actorId])->save();
        return $run->fresh();
    }

    public function apply(CategoryReconciliationRun $run, int $actorId): CategoryReconciliationRun
    {
        abort_unless($run->status === 'approved', 422, 'Approve the dry-run before applying it.');
        DB::transaction(function () use ($run, $actorId): void {
            $run->items()->where('status', 'mapped')->orderBy('id')->chunkById(100, function ($items): void {
                foreach ($items as $item) {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);
                    if (! $product || $product->category_id !== $item->current_category_id || ! $item->proposed_category_id) {
                        $item->forceFill(['status' => 'conflict'])->save();
                        continue;
                    }
                    $product->forceFill(['category_id' => $item->proposed_category_id, 'needs_category_review' => false])->save();
                    $item->forceFill(['status' => 'applied', 'reviewed_by' => $actorId, 'reviewed_at' => now()])->save();
                }
            });
            $run->forceFill(['status' => 'applied'])->save();
        });
        return $run->fresh();
    }
}
