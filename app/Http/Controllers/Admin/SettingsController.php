<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaxSlabRequest;
use App\Http\Requests\UpdateCompanySettingsRequest;
use App\Http\Requests\UpdateInvoiceSettingsRequest;
use App\Http\Requests\UpdatePaymentSettingsRequest;
use App\Http\Requests\UpdateShippingSettingsRequest;
use App\Http\Requests\UpdateTaxAssignmentsRequest;
use App\Models\Category;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\TaxSlab;
use App\Services\OperationalSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly OperationalSettingsService $settings)
    {
    }

    public function company(): View
    {
        return view('admin.settings.company', [
            'settings' => $this->settings->company(),
        ]);
    }

    public function updateCompany(UpdateCompanySettingsRequest $request): RedirectResponse
    {
        $settings = $this->settings->company();
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('company', 'public');
        }

        unset($data['logo']);
        $settings->fill($data)->save();

        return back()->with('status', 'Company settings updated.');
    }

    public function invoice(): View
    {
        return view('admin.settings.invoice', [
            'settings' => $this->settings->invoice(),
        ]);
    }

    public function updateInvoice(UpdateInvoiceSettingsRequest $request): RedirectResponse
    {
        $this->settings->invoice()->fill($request->validated())->save();

        return back()->with('status', 'Invoice settings updated.');
    }

    public function tax(): View
    {
        return view('admin.settings.tax', [
            'taxSlabs' => TaxSlab::query()->orderBy('rate')->get(),
            'categories' => Category::query()->with('taxSlab')->orderBy('sort_order')->orderBy('name')->get(),
            'products' => Product::query()->with(['category', 'taxSlab'])->orderBy('name')->get(),
        ]);
    }

    public function storeTaxSlab(StoreTaxSlabRequest $request): RedirectResponse
    {
        TaxSlab::query()->create(array_merge($request->validated(), [
            'is_active' => $request->boolean('is_active'),
        ]));

        return back()->with('status', 'GST slab created.');
    }

    public function updateTaxAssignments(UpdateTaxAssignmentsRequest $request): RedirectResponse
    {
        foreach ($request->validated('categories', []) as $categoryId => $data) {
            Category::query()->whereKey($categoryId)->update(['tax_slab_id' => $data['tax_slab_id'] ?? null]);
        }

        foreach ($request->validated('products', []) as $productId => $data) {
            Product::query()->whereKey($productId)->update(['tax_slab_id' => $data['tax_slab_id'] ?? null]);
        }

        return back()->with('status', 'Tax assignments updated.');
    }

    public function shipping(): View
    {
        return view('admin.settings.shipping', [
            'settings' => $this->settings->shipping(),
        ]);
    }

    public function updateShipping(UpdateShippingSettingsRequest $request): RedirectResponse
    {
        $this->settings->shipping()->fill(array_merge($request->validated(), [
            'free_shipping_enabled' => $request->boolean('free_shipping_enabled'),
            'weight_based_shipping_enabled' => $request->boolean('weight_based_shipping_enabled'),
            'courier_integration_enabled' => $request->boolean('courier_integration_enabled'),
            'zone_based_shipping_enabled' => $request->boolean('zone_based_shipping_enabled'),
        ]))->save();

        return back()->with('status', 'Shipping settings updated.');
    }

    public function payments(): View
    {
        return view('admin.settings.payments', [
            'providers' => $this->settings->payments(),
        ]);
    }

    public function updatePayments(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        foreach ($request->validated('providers', []) as $provider => $data) {
            PaymentSetting::query()->where('provider', $provider)->update([
                'enabled' => (bool) ($data['enabled'] ?? false),
                'environment' => $data['environment'],
                'key_placeholder' => $data['key_placeholder'] ?? null,
                'webhook_url_placeholder' => $data['webhook_url_placeholder'] ?? null,
            ]);
        }

        return back()->with('status', 'Payment settings updated.');
    }

    public function placeholder(string $section): View
    {
        abort_unless(in_array($section, ['policies', 'notifications'], true), 404);

        return view('admin.settings.placeholder', [
            'section' => $section,
        ]);
    }
}
