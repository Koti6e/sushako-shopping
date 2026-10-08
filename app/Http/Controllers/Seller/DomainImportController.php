<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerDomainImport;
use App\Models\SellerImportCandidate;
use App\Services\SellerDomainImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainImportController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->attributes->get('vendor');

        return view('seller.imports.index', [
            'vendor' => $vendor,
            'imports' => $vendor->domainImports()->latest()->get(),
        ]);
    }

    public function store(Request $request, SellerDomainImportService $imports): RedirectResponse
    {
        $data = $request->validate(['domain' => ['required', 'string', 'max:255']]);
        $imports->register($request->attributes->get('vendor'), $data['domain']);

        return to_route('seller.imports.index')->with('status', 'Domain saved. Add the verification file before starting an import.');
    }

    public function verify(Request $request, SellerDomainImport $import, SellerDomainImportService $imports): RedirectResponse
    {
        abort_unless($import->vendor_id === $request->attributes->get('vendor')?->id, 404);
        $imports->verifyDnsTxt($import);

        return to_route('seller.imports.index')->with('status', 'Domain verified.');
    }

    public function start(Request $request, SellerDomainImport $import, SellerDomainImportService $imports): RedirectResponse
    {
        $job = $imports->start($request->attributes->get('vendor'), $import);
        return to_route('seller.imports.index')->with('status', 'Import queued. Job #'.$job->id.' will process safely in the background.');
    }

    public function approve(Request $request, SellerImportCandidate $candidate, SellerDomainImportService $imports): RedirectResponse
    {
        $request->validate(['category_id' => ['nullable', 'integer'], 'review_notes' => ['nullable', 'string', 'max:2000']]);
        $imports->approve($request->attributes->get('vendor'), $candidate, $request->only('category_id', 'review_notes'));
        return back()->with('status', 'Candidate approved for publication.');
    }

    public function publish(Request $request, SellerImportCandidate $candidate, SellerDomainImportService $imports): RedirectResponse
    {
        $product = $imports->publish($request->attributes->get('vendor'), $candidate);
        return back()->with('status', 'Imported product #'.$product->id.' is saved as a draft for final seller publishing.');
    }
}
