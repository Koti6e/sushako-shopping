<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerDomainImport;
use App\Models\SellerImportCandidate;
use App\Models\CatalogueDiscoverySession;
use App\Models\CatalogueProductMatch;
use App\Models\CatalogueExtractedProduct;
use App\Models\CatalogueEnrichmentSuggestion;
use App\Services\CatalogueAuthorization;
use App\Services\SellerDomainImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainImportController extends Controller
{
    private function guard(Request $request, string $permission): \App\Models\Vendor
    {
        $vendor = $request->attributes->get('vendor');
        app(CatalogueAuthorization::class)->enforce($request->user(), $vendor, $permission);
        return $vendor;
    }

    public function index(Request $request): View
    {
        $vendor = $this->guard($request, 'catalogue.sources.view');

        return view('seller.imports.index', [
            'vendor' => $vendor,
            'imports' => $vendor->domainImports()->latest()->get(),
            'discoverySessions' => CatalogueDiscoverySession::query()->where('vendor_id', $vendor->id)->latest()->limit(10)->get(),
            'matchCounts' => CatalogueProductMatch::query()->where('vendor_id', $vendor->id)->selectRaw("outcome, count(*) as aggregate")->groupBy('outcome')->pluck('aggregate', 'outcome'),
            'matches' => CatalogueProductMatch::query()->where('vendor_id', $vendor->id)->with(['extractedProduct.session.source', 'product'])->latest()->limit(30)->get(),
        ]);
    }

    public function categories(Request $request, CatalogueDiscoverySession $session): View
    {
        $vendor = $this->guard($request, 'catalogue.discovery.view');
        abort_unless($session->vendor_id === $vendor->id, 404);
        return view('seller.imports.categories', ['vendor' => $vendor, 'session' => $session->load('source', 'categories.parent')]);
    }

    public function selectCategories(Request $request, CatalogueDiscoverySession $session, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.products.select');
        $data = $request->validate(['selected' => ['array'], 'selected.*' => ['integer'], 'excluded' => ['array'], 'excluded.*' => ['integer']]);
        $imports->selectCategories($vendor, $session, $data['selected'] ?? [], $data['excluded'] ?? []);
        return back()->with('status', 'Category selection saved.');
    }

    public function analyse(Request $request, SellerDomainImport $import, SellerDomainImportService $imports): RedirectResponse
    {
        $this->guard($request, 'catalogue.analysis.run');
        $imports->analyse($request->attributes->get('vendor'), $import);

        return to_route('seller.imports.index')->with('status', 'Website intelligence report refreshed. Review it before starting discovery.');
    }

    public function store(Request $request, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.sources.create');
        $data = $request->validate(['domain' => ['required', 'string', 'max:255']]);
        $imports->register($vendor, $data['domain']);

        return to_route('seller.imports.index')->with('status', 'Domain saved. Add the verification file before starting an import.');
    }

    public function verify(Request $request, SellerDomainImport $import, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.sources.verify');
        abort_unless($import->vendor_id === $vendor->id, 404);
        $imports->verifyDnsTxt($import);

        return to_route('seller.imports.index')->with('status', 'Domain verified.');
    }

    public function start(Request $request, SellerDomainImport $import, SellerDomainImportService $imports): RedirectResponse
    {
        $this->guard($request, 'catalogue.discovery.start');
        $job = $imports->start($request->attributes->get('vendor'), $import);
        return to_route('seller.imports.index')->with('status', 'Import queued. Job #'.$job->id.' will process safely in the background.');
    }

    public function approve(Request $request, SellerImportCandidate $candidate, SellerDomainImportService $imports): RedirectResponse
    {
        $this->guard($request, 'catalogue.import.approve');
        $request->validate(['category_id' => ['nullable', 'integer'], 'review_notes' => ['nullable', 'string', 'max:2000']]);
        $imports->approve($request->attributes->get('vendor'), $candidate, $request->only('category_id', 'review_notes'));
        return back()->with('status', 'Candidate approved for publication.');
    }

    public function publish(Request $request, SellerImportCandidate $candidate, SellerDomainImportService $imports): RedirectResponse
    {
        $this->guard($request, 'catalogue.import.execute');
        $product = $imports->publish($request->attributes->get('vendor'), $candidate);
        return back()->with('status', 'Imported product #'.$product->id.' is saved as a draft for final seller publishing.');
    }

    public function decide(Request $request, CatalogueProductMatch $match, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.matches.review');
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected,held,pending'], 'review_notes' => ['nullable', 'string', 'max:2000']]);
        $imports->decideMatch($vendor, $match, $data['decision'], $data['review_notes'] ?? null);
        return back()->with('status', 'Match review decision saved.');
    }

    public function importMatch(Request $request, CatalogueProductMatch $match, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.import.execute');
        $data = $request->validate(['action' => ['required', 'in:import_new,update_price'], 'category_id' => ['nullable', 'integer']]);
        $product = $imports->importMatch($vendor, $match, $data['action'], $data['category_id'] ?? null);
        return back()->with('status', 'Catalogue action completed. Product #'.$product->id.' remains subject to the existing seller publication workflow.');
    }

    public function enrich(Request $request, CatalogueExtractedProduct $product, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.enrichment.run');
        $imports->suggestEnrichment($vendor, $product);
        return back()->with('status', 'Optional enrichment suggestions generated for review.');
    }

    public function approveEnrichment(Request $request, CatalogueEnrichmentSuggestion $suggestion, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.enrichment.approve');
        $imports->approveEnrichment($vendor, $suggestion);
        return back()->with('status', 'Enrichment suggestion approved for seller review.');
    }

    public function processImage(Request $request, CatalogueExtractedProduct $product, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.images.process');
        $data = $request->validate(['index' => ['nullable', 'integer', 'min:0'], 'max_dimension' => ['nullable', 'integer', 'min:400', 'max:2400'], 'quality' => ['nullable', 'integer', 'min:40', 'max:95']]);
        $imports->processImage($vendor, $product, (int) ($data['index'] ?? 0), $data);
        return back()->with('status', 'Image processed and stored separately. Seller approval is required before replacement.');
    }

    public function monitoring(Request $request, SellerDomainImportService $imports): RedirectResponse
    {
        $vendor = $this->guard($request, 'catalogue.monitoring.manage');
        $data = $request->validate(['source_id' => ['required', 'integer'], 'frequency' => ['required', 'in:disabled,daily,weekly,monthly'], 'new_products' => ['boolean'], 'price_changes' => ['boolean'], 'image_changes' => ['boolean'], 'availability_changes' => ['boolean']]);
        $source = SellerDomainImport::query()->where('vendor_id', $vendor->id)->findOrFail($data['source_id']);
        $imports->saveMonitoring($vendor, $source, $data);
        return back()->with('status', 'Monitoring settings saved. Automatic publishing remains disabled.');
    }
}
