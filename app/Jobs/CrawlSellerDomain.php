<?php

namespace App\Jobs;

use App\Models\SellerImportJob;
use App\Services\SellerDomainImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlSellerDomain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(public int $importJobId) {}

    public function handle(SellerDomainImportService $imports): void
    {
        $job = SellerImportJob::query()->findOrFail($this->importJobId);
        $imports->crawl($job);
    }
}
