<x-layouts.admin title="Customer Exports - Admin">
    <x-admin.shell eyebrow="Customer Management" title="Customer Exports" subtitle="Controlled CSV exports with audit logging and sensitive fields excluded.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.index')" tone="secondary" icon="fa-solid fa-users">Customers</x-admin.action>
            <x-admin.action :href="route('admin.customers.communications.index')" tone="outline" icon="fa-solid fa-clock-rotate-left">Logs</x-admin.action>
        </x-slot:actions>
        <section class="customer-admin">
            <div class="customer-segment-grid">
                @foreach ($exportTypes as $key => $label)
                    <a class="customer-segment-card" href="{{ route('admin.customers.export', ['export' => $key]) }}">
                        <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                        <strong>{{ $label }}</strong>
                        <span>Download CSV with allowed customer fields.</span>
                    </a>
                @endforeach
            </div>

            <section class="customer-table-card">
                <div class="customer-table-scroll">
                    <table>
                        <thead><tr><th>Export Type</th><th>Records</th><th>Admin</th><th>Date</th><th>Filters</th></tr></thead>
                        <tbody>
                            @forelse ($audits as $audit)
                                <tr>
                                    <td>{{ str($audit->export_type)->replace('_', ' ')->title() }}</td>
                                    <td>{{ number_format($audit->record_count) }}</td>
                                    <td>{{ $audit->admin?->name ?? 'Admin' }}</td>
                                    <td>{{ $audit->created_at->format('d M Y, h:i A') }}</td>
                                    <td>{{ collect($audit->filters ?? [])->map(fn ($value, $key) => $key.': '.$value)->implode(', ') ?: 'None' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No customer exports have been generated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $audits->links() }}
            </section>
        </section>
    </x-admin.shell>
</x-layouts.admin>
