<x-layouts.admin title="Customer Segments - Admin">
    <x-admin.shell eyebrow="Customer Management" title="Customer Segments" subtitle="Calculated customer groups powered by live order, payment and activity data.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.index')" tone="secondary" icon="fa-solid fa-users">All Customers</x-admin.action>
            <x-admin.action :href="route('admin.customers.export', ['export' => 'customer_list'])" tone="primary" icon="fa-solid fa-file-export">Export</x-admin.action>
        </x-slot:actions>
        <section class="customer-admin">
            <div class="customer-segment-grid">
                @foreach ($segments as $segment)
                    <a class="customer-segment-card" href="{{ $segment['url'] }}">
                        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        <strong>{{ $segment['label'] }}</strong>
                        <span>{{ $segment['description'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
