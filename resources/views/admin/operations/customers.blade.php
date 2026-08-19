<x-layouts.admin title="Customer Management - Admin">
    <x-admin.shell title="Customers">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.export', request()->query())" tone="secondary" icon="fa-solid fa-file-export">Export</x-admin.action>
            <x-admin.action :href="route('admin.customers.whatsapp.index')" tone="outline" icon="fa-brands fa-whatsapp">WhatsApp Marketing</x-admin.action>
            <x-admin.action :href="route('admin.customers.index')" tone="primary" icon="fa-solid fa-arrows-rotate">Refresh</x-admin.action>
        </x-slot:actions>

        @include('admin.customers.index')
    </x-admin.shell>
</x-layouts.admin>
