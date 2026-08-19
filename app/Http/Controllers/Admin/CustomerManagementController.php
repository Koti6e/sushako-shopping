<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerFilterRequest;
use App\Http\Requests\Admin\PrepareWhatsappMessageRequest;
use App\Models\CustomerActivity;
use App\Models\CustomerCart;
use App\Models\CustomerCommunication;
use App\Models\CustomerExportAudit;
use App\Models\CustomerStatusLog;
use App\Models\User;
use App\Services\CustomerMetricsService;
use App\Services\WhatsAppIntentService;
use App\Support\CustomerContactIntents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerManagementController extends Controller
{
    public function __construct(
        private readonly CustomerMetricsService $metrics,
        private readonly WhatsAppIntentService $whatsApp,
    ) {}

    public function index(CustomerFilterRequest $request): View
    {
        $filters = $request->filters();

        return view('admin.operations.customers', [
            'customers' => $this->metrics->paginate($filters),
            'filters' => $filters,
            'summaryCards' => $this->metrics->summaries($filters),
            'viewMode' => $request->validated('view') ?: 'cards',
        ]);
    }

    public function show(User $customer): View
    {
        $this->authorizeCustomer($customer);
        $profile = $this->metrics->profile($customer);
        $latestOrder = $profile['latestOrder'];

        return view('admin.customers.show', array_merge([
            'customer' => $customer->loadCount(['orders', 'addresses']),
            'contactActions' => [
                'tel' => CustomerContactIntents::tel($customer->phone),
                'email' => CustomerContactIntents::email($customer->email),
                'whatsapp' => CustomerContactIntents::whatsApp($customer->phone, (bool) ($customer->whatsapp_order_updates || $customer->whatsapp_marketing_consent)),
                'maps' => CustomerContactIntents::maps($latestOrder?->delivery_location_url),
            ],
            'latestAddress' => $latestOrder ? CustomerContactIntents::addressFromOrder($latestOrder) : null,
        ], $profile));
    }

    public function updateStatus(Request $request, User $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer);

        $data = $request->validate([
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_BLOCKED, User::STATUS_SUSPENDED])],
            'reason' => ['required', 'string', 'min:5', 'max:240'],
        ]);

        DB::transaction(function () use ($customer, $data, $request): void {
            $oldStatus = $customer->status;
            $customer->forceFill(['status' => $data['status']])->save();

            CustomerActivity::query()->create([
                'user_id' => $customer->id,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'type' => 'status_changed',
                'title' => 'Customer status changed',
                'metadata' => [
                    'from' => $oldStatus,
                    'to' => $data['status'],
                    'reason' => $data['reason'],
                    'admin_user_id' => $request->user()->id,
                ],
                'source' => 'admin',
                'occurred_at' => now(),
            ]);

            CustomerStatusLog::query()->create([
                'user_id' => $customer->id,
                'admin_user_id' => $request->user()->id,
                'from_status' => $oldStatus,
                'to_status' => $data['status'],
                'reason' => $data['reason'],
            ]);
        });

        return back()->with('status', 'Customer status updated with an audit entry.');
    }

    public function updateConsent(Request $request, User $customer): RedirectResponse
    {
        $this->authorizeCustomer($customer);

        $data = $request->validate([
            'whatsapp_order_updates' => ['nullable', 'boolean'],
            'whatsapp_marketing_consent' => ['nullable', 'boolean'],
            'source' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        DB::transaction(function () use ($customer, $data, $request): void {
            $marketing = (bool) ($data['whatsapp_marketing_consent'] ?? false);
            $customer->forceFill([
                'whatsapp_order_updates' => (bool) ($data['whatsapp_order_updates'] ?? false),
                'whatsapp_marketing_consent' => $marketing,
                'whatsapp_marketing_consent_at' => $marketing ? now() : null,
                'whatsapp_marketing_consent_source' => $data['source'],
            ])->save();

            CustomerActivity::query()->create([
                'user_id' => $customer->id,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'type' => 'marketing_consent_changed',
                'title' => 'WhatsApp consent updated',
                'metadata' => [
                    'order_updates' => $customer->whatsapp_order_updates,
                    'marketing' => $customer->whatsapp_marketing_consent,
                    'source' => $data['source'],
                    'admin_user_id' => $request->user()->id,
                ],
                'source' => 'admin',
                'occurred_at' => now(),
            ]);
        });

        return back()->with('status', 'Communication preferences updated.');
    }

    public function segments(): View
    {
        return view('admin.customers.segments', [
            'segments' => $this->segmentCards(),
        ]);
    }

    public function communications(): View
    {
        return view('admin.customers.communications', [
            'communications' => CustomerCommunication::query()->with(['user', 'admin', 'order', 'cart'])->latest()->paginate(20),
        ]);
    }

    public function exports(): View
    {
        return view('admin.customers.exports', [
            'audits' => CustomerExportAudit::query()->with('admin')->latest()->paginate(20),
            'exportTypes' => [
                'customer_list' => 'Customer List',
                'order_summary' => 'Customer Order Summary',
                'cod_report' => 'COD Customer Report',
                'online_paid_report' => 'Online-Paid Customer Report',
                'abandoned_cart_report' => 'Abandoned Cart Report',
                'marketing_consent_report' => 'Marketing Consent Report',
            ],
        ]);
    }

    public function whatsapp(Request $request, User $customer): View
    {
        $this->authorizeCustomer($customer);
        $order = $customer->orders()->latest()->first();
        $cart = $customer->customerCarts()->whereIn('status', [CustomerCart::STATUS_ABANDONED, CustomerCart::STATUS_WHATSAPP_OPENED, CustomerCart::STATUS_REMINDER_MARKED_SENT])->latest('abandoned_at')->first();

        return view('admin.customers.whatsapp', [
            'customer' => $customer,
            'purposes' => WhatsAppIntentService::MARKETING_PURPOSES,
            'defaultMessage' => $this->whatsApp->defaultMessage($customer, $request->query('purpose', 'thank_you'), $order, $cart),
            'latestOrder' => $order,
            'latestCart' => $cart,
            'normalPhone' => $this->whatsApp->normalizeIndianPhone($customer->phone),
            'prepared' => null,
            'intentUrl' => null,
        ]);
    }

    public function prepareWhatsapp(PrepareWhatsappMessageRequest $request, User $customer): View
    {
        $this->authorizeCustomer($customer);
        $data = $request->validated();
        $this->whatsApp->assertMarketingAllowed($customer, $data['purpose']);

        $order = isset($data['order_id']) ? $customer->orders()->find($data['order_id']) : null;
        $cart = isset($data['customer_cart_id']) ? $customer->customerCarts()->find($data['customer_cart_id']) : null;

        $communication = CustomerCommunication::query()->create([
            'user_id' => $customer->id,
            'order_id' => $order?->id,
            'customer_cart_id' => $cart?->id,
            'admin_user_id' => $request->user()->id,
            'channel' => 'whatsapp',
            'category' => $data['purpose'],
            'template_key' => $data['purpose'],
            'status' => 'prepared',
            'message_content' => $data['message'],
        ]);

        return view('admin.customers.whatsapp', [
            'customer' => $customer,
            'purposes' => WhatsAppIntentService::MARKETING_PURPOSES,
            'defaultMessage' => $data['message'],
            'latestOrder' => $order ?: $customer->orders()->latest()->first(),
            'latestCart' => $cart ?: $customer->customerCarts()->latest('abandoned_at')->first(),
            'normalPhone' => $this->whatsApp->normalizeIndianPhone($customer->phone),
            'prepared' => $communication,
            'intentUrl' => $this->whatsApp->intentUrl($customer, $data['message']),
        ]);
    }

    public function openWhatsapp(CustomerCommunication $communication): RedirectResponse
    {
        $communication->loadMissing('user');
        abort_unless($communication->user?->role === User::ROLE_CUSTOMER, 404);

        $communication->forceFill([
            'status' => 'opened',
            'opened_at' => $communication->opened_at ?? now(),
        ])->save();

        if ($communication->cart) {
            $communication->cart->forceFill([
                'status' => CustomerCart::STATUS_WHATSAPP_OPENED,
                'reminder_opened_at' => $communication->cart->reminder_opened_at ?? now(),
            ])->save();
        }

        return redirect()->away($this->whatsApp->intentUrl($communication->user, $communication->message_content));
    }

    public function markCommunicationSent(CustomerCommunication $communication): RedirectResponse
    {
        $communication->forceFill([
            'status' => 'marked_sent',
            'marked_sent_at' => now(),
        ])->save();

        if ($communication->cart) {
            $communication->cart->forceFill([
                'status' => CustomerCart::STATUS_REMINDER_MARKED_SENT,
                'reminder_marked_sent_at' => now(),
            ])->save();
        }

        return back()->with('status', 'Communication marked sent by admin.');
    }

    public function export(CustomerFilterRequest $request): Response
    {
        $type = $request->validated('export') ?: 'customer_list';
        $customers = $this->metrics->customerQuery($request->filters())->limit(1000)->get();

        CustomerExportAudit::query()->create([
            'admin_user_id' => $request->user()->id,
            'export_type' => $type,
            'filters' => $request->filters(),
            'record_count' => $customers->count(),
        ]);

        $headers = ['Customer ID', 'Name', 'Phone', 'Email', 'Registration Date', 'Customer Status', 'Total Orders', 'COD Orders', 'Online Paid Orders', 'Cancelled Orders', 'Total Spent', 'Last Order Date', 'Last Activity Date', 'WhatsApp Marketing Consent'];
        $lines = [$this->csvRow($headers)];

        foreach ($customers as $customer) {
            $lines[] = $this->csvRow([
                'CUS-'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT),
                $customer->name,
                $customer->phone,
                $customer->email,
                $customer->created_at?->toDateTimeString(),
                $customer->status,
                $customer->total_orders_count,
                $customer->cod_orders_count,
                $customer->online_paid_orders_count,
                $customer->cancelled_orders_count,
                (int) $customer->total_spent,
                $customer->last_order_at,
                $customer->last_activity_at?->toDateTimeString(),
                $customer->whatsapp_marketing_consent ? 'yes' : 'no',
            ]);
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sushako-'.$type.'-'.now()->format('Ymd-His').'.csv"',
        ]);
    }

    private function authorizeCustomer(User $customer): void
    {
        abort_unless($customer->role === User::ROLE_CUSTOMER, 404);
    }

    private function segmentCards(): array
    {
        return [
            ['label' => 'New Customers', 'description' => 'Joined this month', 'url' => route('admin.customers.index', ['activity' => 'new'])],
            ['label' => 'Repeat Customers', 'description' => 'More than one order', 'url' => route('admin.customers.index', ['type' => 'repeat'])],
            ['label' => 'High-Value Customers', 'description' => 'Sort by spend', 'url' => route('admin.customers.index', ['sort' => 'spent'])],
            ['label' => 'No-Order Customers', 'description' => 'Registered without orders', 'url' => route('admin.customers.index', ['type' => 'registered'])],
            ['label' => 'Inactive 30 Days', 'description' => 'No recent activity', 'url' => route('admin.customers.index', ['activity' => 'inactive_30'])],
            ['label' => 'Inactive 60 Days', 'description' => 'Longer dormancy', 'url' => route('admin.customers.index', ['activity' => 'inactive_60'])],
            ['label' => 'COD Customers', 'description' => 'Cash on delivery users', 'url' => route('admin.customers.index', ['payment' => 'cod'])],
            ['label' => 'Online-Paid Customers', 'description' => 'Razorpay paid orders', 'url' => route('admin.customers.index', ['payment' => 'online'])],
            ['label' => 'Abandoned Carts', 'description' => 'Recovery queue', 'url' => route('admin.customers.abandoned-carts.index')],
            ['label' => 'Marketing Consent', 'description' => 'WhatsApp enabled', 'url' => route('admin.customers.index')],
        ];
    }

    private function csvRow(array $values): string
    {
        return collect($values)->map(fn ($value): string => '"'.str_replace('"', '""', (string) $value).'"')->implode(',');
    }
}
