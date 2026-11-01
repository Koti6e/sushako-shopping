<?php

namespace App\Jobs;

use App\Models\CatalogueMonitoringRun;
use App\Services\SellerDomainImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MonitorCatalogueSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(public int $runId) {}

    public function handle(SellerDomainImportService $imports): void
    {
        $run = CatalogueMonitoringRun::query()->with(['source', 'vendor'])->findOrFail($this->runId);
        $run->forceFill(['status' => 'running', 'started_at' => now()])->save();
        try {
            $imports->analyse($run->vendor, $run->source);
            $run->forceFill(['status' => 'completed', 'finished_at' => now()])->save();
            $run->source->monitoring()->update(['last_run_at' => now()]);
        } catch (\Throwable $exception) {
            $run->forceFill(['status' => 'failed', 'error_message' => substr($exception->getMessage(), 0, 1000), 'finished_at' => now()])->save();
            throw $exception;
        }
    }
}
