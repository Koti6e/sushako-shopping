<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingLabel;
use App\Services\ShippingLabelService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ShippingLabelController extends Controller
{
    private const A6_PAPER = [0, 0, 297.64, 419.53];

    public function __construct(private readonly ShippingLabelService $labels) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'search_order' => ['nullable', 'string', 'max:80'],
            'search_customer' => ['nullable', 'string', 'max:120'],
            'seller' => ['nullable', 'string', 'max:120'],
            'warehouse' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
            'payment' => ['nullable', Rule::in(['cod', 'prepaid'])],
            'print_status' => ['nullable', Rule::in(['printed', 'unprinted'])],
            'courier' => ['nullable', 'string', 'max:80'],
            'package_count' => ['nullable', 'integer', 'min:1', 'max:99'],
            'location' => ['nullable', Rule::in(['missing', 'confirmed'])],
            'date' => ['nullable', 'date'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $orders = Order::query()
            ->with(['items', 'latestShippingLabel.generatedBy'])
            ->where('status', '!=', 'payment_pending')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('shipping_provider', 'like', "%{$search}%")
                        ->orWhere('shipping_provider_other', 'like', "%{$search}%");
                });
            })
            ->when($filters['search_order'] ?? null, fn ($query, string $search) => $query->where('order_number', 'like', "%{$search}%"))
            ->when($filters['search_customer'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('pincode', 'like', "%{$search}%");
                });
            })
            ->when($filters['seller'] ?? null, fn ($query, string $seller) => $query->whereHas('latestShippingLabel', fn ($query) => $query->where('seller_name', 'like', "%{$seller}%")))
            ->when($filters['warehouse'] ?? null, fn ($query, string $warehouse) => $query->whereHas('latestShippingLabel', fn ($query) => $query->where('warehouse_name', 'like', "%{$warehouse}%")))
            ->when($filters['status'] ?? null, function ($query, string $status): void {
                if ($status === 'pending') {
                    $query->doesntHave('shippingLabels');

                    return;
                }

                $query->whereHas('latestShippingLabel', fn ($query) => $query->where('status', $status));
            })
            ->when($filters['payment'] ?? null, function ($query, string $payment): void {
                $payment === 'cod'
                    ? $query->where('payment_method', 'cod')
                    : $query->where('payment_method', '!=', 'cod');
            })
            ->when($filters['print_status'] ?? null, function ($query, string $status): void {
                $query->whereHas('latestShippingLabel', function ($query) use ($status): void {
                    $status === 'printed'
                        ? $query->whereNotNull('printed_at')
                        : $query->whereNull('printed_at');
                });
            })
            ->when($filters['courier'] ?? null, fn ($query, string $courier) => $query->where('shipping_provider', $courier))
            ->when($filters['package_count'] ?? null, fn ($query, int $count) => $query->whereHas('latestShippingLabel', fn ($query) => $query->where('package_count', $count)))
            ->when($filters['location'] ?? null, function ($query, string $location): void {
                $location === 'missing'
                    ? $query->whereNull('delivery_location_url')->whereNull('delivery_latitude')
                    : $query->where('location_confirmed', true);
            })
            ->when($filters['date'] ?? null, fn ($query, string $date) => $query->whereDate('placed_at', $date))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('placed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('placed_at', '<=', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.shipping-labels.index', [
            'orders' => $orders,
            'filters' => $filters,
            'cards' => $this->cards(),
            'summary' => $this->summary(),
            'shippingProviders' => $this->shippingProviders(),
        ]);
    }

    public function show(ShippingLabel $label): View
    {
        return view('admin.shipping-labels.show', [
            'label' => $label->load(['order.items', 'generatedBy', 'printedBy', 'events.user', 'previousLabel']),
            'mapsUrl' => $this->labels->mapsUrl($label->order),
            'locationQr' => $label->location_qr_url ? $this->labels->codeSvgDataUri($label->location_qr_url, 'qr') : null,
            'internalBarcode' => $this->labels->codeSvgDataUri($label->internal_lookup_code, 'barcode'),
            'internalQr' => $this->labels->codeSvgDataUri($label->internal_lookup_code, 'qr'),
        ]);
    }

    public function generate(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        abort_if($order->status === 'payment_pending', 404);

        $data = $this->labelData($request);
        $label = $this->labels->generate($order, $request->user(), $data);

        if ($request->expectsJson()) {
            return response()->json(['message' => "Label {$label->label_number} generated.", 'label_id' => $label->id]);
        }

        return redirect()->route('admin.shipping-labels.show', $label)->with('status', "Label {$label->label_number} generated.");
    }

    public function regenerate(Request $request, ShippingLabel $label): RedirectResponse|JsonResponse
    {
        $data = $this->labelData($request, allowPartial: true);
        $newLabel = $this->labels->generate($label->order, $request->user(), array_filter($data, fn ($value) => $value !== null), $label);

        if ($request->expectsJson()) {
            return response()->json(['message' => "Label {$newLabel->label_number} regenerated.", 'label_id' => $newLabel->id]);
        }

        return redirect()->route('admin.shipping-labels.show', $newLabel)->with('status', "Label {$newLabel->label_number} regenerated.");
    }

    public function print(Request $request, ShippingLabel $label): Response
    {
        $data = $request->validate([
            'copies' => ['nullable', 'integer', 'min:1', 'max:20'],
            'printer' => ['nullable', 'string', 'max:120'],
            'reason' => ['nullable', 'string', 'max:180'],
        ]);

        $label = $this->labels->markPrinted($label, $request->user(), (int) ($data['copies'] ?? 1), $data['printer'] ?? null, $data['reason'] ?? null);

        return response()->view('admin.shipping-labels.print', $this->labelViewData($label));
    }

    public function pdf(ShippingLabel $label): Response
    {
        $pdf = Pdf::loadView('admin.shipping-labels.pdf', $this->labelViewData($label->load('order.items')))
            ->setPaper(self::A6_PAPER);

        return $pdf->download("sushako-shipping-label-{$label->label_number}.pdf");
    }

    public function updateLocation(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:delivery_longitude'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:delivery_latitude'],
            'delivery_location_url' => ['nullable', 'url', 'max:500'],
            'location_confirmed' => ['nullable', 'boolean'],
            'location_capture_method' => ['nullable', Rule::in(['gps', 'google_maps_pin', 'manual_entry', 'admin_updated'])],
        ]);

        $this->labels->updateLocation($order, $request->user(), $data);

        return back()->with('status', 'Delivery location updated. Existing label QR payloads were refreshed.');
    }

    public function bulk(Request $request): JsonResponse|BinaryFileResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['generate', 'print', 'mark_printed', 'regenerate', 'download_zip', 'export_pdfs'])],
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer'],
        ]);

        $orders = Order::query()
            ->with('latestShippingLabel')
            ->whereIn('id', $data['order_ids'])
            ->where('status', '!=', 'payment_pending')
            ->get();

        $labels = collect();

        foreach ($orders as $order) {
            $latest = $order->latestShippingLabel;

            if ($data['action'] === 'generate' && ! $latest) {
                $labels->push($this->labels->generate($order, $request->user()));
            } elseif ($data['action'] === 'regenerate' && $latest) {
                $labels->push($this->labels->generate($order, $request->user(), [], $latest));
            } elseif (in_array($data['action'], ['print', 'mark_printed', 'download_zip', 'export_pdfs'], true) && $latest) {
                $labels->push($latest->load('order.items'));
            }
        }

        if (in_array($data['action'], ['print', 'mark_printed'], true)) {
            $labels = $labels->map(fn (ShippingLabel $label) => $this->labels->markPrinted($label, $request->user()));
        }

        if (in_array($data['action'], ['download_zip', 'export_pdfs'], true)) {
            return $this->downloadZip($labels);
        }

        return response()->json([
            'message' => ucfirst(str_replace('_', ' ', $data['action']))." completed for {$labels->count()} label(s).",
            'count' => $labels->count(),
        ]);
    }

    private function labelData(Request $request, bool $allowPartial = false): array
    {
        $rules = [
            'brand_mode' => [$allowPartial ? 'nullable' : 'required', Rule::in(['sushako', 'seller', 'courier_neutral'])],
            'fulfillment_type' => [$allowPartial ? 'nullable' : 'required', Rule::in(['fulfilled_by_sushako', 'seller_direct', 'warehouse'])],
            'print_format' => ['nullable', Rule::in(['a6_thermal', 'a5', 'a4_single', 'a4_double', 'a4_four'])],
            'package_count' => ['nullable', 'integer', 'min:1', 'max:99'],
            'package_index' => ['nullable', 'integer', 'min:1', 'max:99'],
            'weight_grams' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'courier_code' => ['nullable', 'string', 'max:80'],
            'courier_name' => ['nullable', 'string', 'max:120'],
            'warehouse_name' => ['nullable', 'string', 'max:120'],
            'warehouse_address' => ['nullable', 'string', 'max:500'],
            'seller_name' => ['nullable', 'string', 'max:120'],
            'seller_address' => ['nullable', 'string', 'max:500'],
            'seller_gst' => ['nullable', 'string', 'max:32'],
            'seller_contact' => ['nullable', 'string', 'max:40'],
            'seller_return_address' => ['nullable', 'string', 'max:500'],
        ];

        return $request->validate($rules);
    }

    private function labelViewData(ShippingLabel $label): array
    {
        $label = $label->loadMissing(['order.items', 'generatedBy', 'printedBy']);

        return [
            'label' => $label,
            'order' => $label->order,
            'mapsUrl' => $this->labels->mapsUrl($label->order),
            'locationQr' => $label->location_qr_url ? $this->labels->codeSvgDataUri($label->location_qr_url, 'qr') : null,
            'internalBarcode' => $this->labels->codeSvgDataUri($label->internal_lookup_code, 'barcode'),
            'internalQr' => $this->labels->codeSvgDataUri($label->internal_lookup_code, 'qr'),
            'logoData' => $this->logoData($label),
        ];
    }

    private function cards(): array
    {
        $today = today();

        return [
            ['label' => 'Pending Generation', 'description' => 'Orders waiting for labels', 'tone' => 'pending', 'value' => Order::query()->where('status', '!=', 'payment_pending')->doesntHave('shippingLabels')->count(), 'url' => route('admin.shipping-labels.index', ['status' => 'pending'])],
            ['label' => 'Ready To Print', 'description' => 'Generated labels not printed', 'tone' => 'ready', 'value' => ShippingLabel::query()->whereNull('printed_at')->whereIn('status', ['generated', 'regenerated'])->count(), 'url' => route('admin.shipping-labels.index', ['print_status' => 'unprinted'])],
            ['label' => 'Printed Today', 'description' => 'Labels printed since midnight', 'tone' => 'ready', 'value' => ShippingLabel::query()->whereDate('printed_at', $today)->count(), 'url' => route('admin.shipping-labels.index', ['print_status' => 'printed', 'date' => $today->toDateString()])],
            ['label' => "Today's Dispatch", 'description' => 'Packed or shipped today', 'tone' => 'info', 'value' => Order::query()->where(function ($query) use ($today): void {
                $query->whereDate('packed_at', $today)->orWhereDate('shipped_at', $today);
            })->count(), 'url' => route('admin.shipping-labels.index', ['date' => $today->toDateString()])],
            ['label' => 'Location Missing', 'description' => 'No map URL or coordinates', 'tone' => 'danger', 'value' => Order::query()->whereNull('delivery_location_url')->whereNull('delivery_latitude')->count(), 'url' => route('admin.shipping-labels.index', ['location' => 'missing'])],
            ['label' => 'Bulk Queue', 'description' => 'Placed or packing orders', 'tone' => 'generated', 'value' => Order::query()->whereIn('status', ['placed', 'packing'])->count(), 'url' => route('admin.shipping-labels.index', ['status' => 'pending'])],
        ];
    }

    private function summary(): array
    {
        $today = today();
        $generated = ShippingLabel::query()->whereDate('generated_at', $today)->count();
        $printed = ShippingLabel::query()->whereDate('printed_at', $today)->count();
        $pendingDispatch = Order::query()->whereIn('status', ['placed', 'packing'])->count();
        $printedLabels = ShippingLabel::query()
            ->whereDate('printed_at', $today)
            ->whereNotNull('generated_at')
            ->get(['generated_at', 'printed_at']);
        $averageMinutes = $printedLabels->count()
            ? (int) round($printedLabels->avg(fn (ShippingLabel $label) => $label->generated_at->diffInMinutes($label->printed_at)))
            : 0;
        $efficiency = $generated > 0 ? min(100, (int) round(($printed / $generated) * 100)) : 0;

        return [
            'labelsGenerated' => $generated,
            'labelsPrinted' => $printed,
            'pendingDispatch' => $pendingDispatch,
            'averageProcessingTime' => $averageMinutes,
            'warehouseEfficiency' => $efficiency,
            'recentActivity' => ShippingLabel::query()
                ->with(['order', 'generatedBy', 'printedBy'])
                ->latest('updated_at')
                ->limit(5)
                ->get(),
        ];
    }

    private function downloadZip(Collection $labels): BinaryFileResponse
    {
        abort_unless(class_exists(ZipArchive::class), 500, 'ZIP extension is not available on this server.');

        $path = storage_path('app/shipping-labels-'.now()->format('Ymd-His').'.zip');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($labels as $label) {
            $pdf = Pdf::loadView('admin.shipping-labels.pdf', $this->labelViewData($label))->setPaper(self::A6_PAPER)->output();
            $zip->addFromString("shipping-label-{$label->label_number}.pdf", $pdf);
        }

        $zip->close();

        return response()->download($path)->deleteFileAfterSend();
    }

    private function logoData(ShippingLabel $label): ?string
    {
        $path = public_path('assets/brand/sushako-shopping-official-email.png');

        if ($label->brand_mode === 'seller' && $label->seller_logo_path) {
            $path = storage_path('app/public/'.$label->seller_logo_path);
        }

        return file_exists($path) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path)) : null;
    }

    private function shippingProviders(): array
    {
        return [
            'rapido' => 'Rapido',
            'uber' => 'Uber',
            'india_post' => 'India Post',
            'dtdc' => 'DTDC',
            'professional' => 'Professional Courier',
            'others' => 'Others',
        ];
    }
}
