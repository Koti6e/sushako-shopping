<?php

namespace App\Console\Commands;

use App\Jobs\MonitorCatalogueSource;
use App\Models\CatalogueMonitoringRun;
use App\Models\CatalogueMonitoringSetting;
use Illuminate\Console\Command;

class MonitorCatalogueSources extends Command
{
    protected $signature = 'catalogue:monitor';
    protected $description = 'Queue enabled seller catalogue monitoring runs';

    public function handle(): int
    {
        $now = now();
        $queued = 0;
        CatalogueMonitoringSetting::query()->with('source')->whereIn('frequency', ['daily', 'weekly', 'monthly'])->chunkById(100, function ($settings) use ($now, &$queued): void {
            foreach ($settings as $setting) {
                $last = $setting->last_run_at;
                $due = ! $last || match ($setting->frequency) {
                    'daily' => $last->lte($now->copy()->subDay()),
                    'weekly' => $last->lte($now->copy()->subWeek()),
                    'monthly' => $last->lte($now->copy()->subMonth()),
                    default => false,
                };
                if (! $due || CatalogueMonitoringRun::query()->where('seller_domain_import_id', $setting->seller_domain_import_id)->whereIn('status', ['queued', 'running'])->exists()) continue;
                $run = CatalogueMonitoringRun::query()->create(['vendor_id' => $setting->vendor_id, 'seller_domain_import_id' => $setting->seller_domain_import_id, 'status' => 'queued']);
                MonitorCatalogueSource::dispatch($run->id);
                $setting->forceFill(['last_run_at' => $now])->save();
                $queued++;
            }
        });
        $this->info("Queued {$queued} catalogue monitoring run(s).");
        return self::SUCCESS;
    }
}
