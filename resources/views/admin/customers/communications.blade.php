<x-layouts.admin title="Communication Logs - Admin">
    <x-admin.shell eyebrow="Customer Management" title="Communication Logs" subtitle="Audit trail for WhatsApp intents prepared, opened and marked sent by admins.">
        <x-slot:actions>
            <x-admin.action :href="route('admin.customers.index')" tone="secondary" icon="fa-solid fa-users">Customers</x-admin.action>
            <x-admin.action :href="route('admin.customers.abandoned-carts.index')" tone="outline" icon="fa-solid fa-cart-shopping">Abandoned Carts</x-admin.action>
        </x-slot:actions>
        <section class="customer-admin">
            <section class="customer-table-card">
                <div class="customer-table-scroll">
                    <table>
                        <thead><tr><th>Customer</th><th>Category</th><th>Channel</th><th>Status</th><th>Opened</th><th>Marked Sent</th><th>Admin</th><th>Actions</th></tr></thead>
                        <tbody>
                            @forelse ($communications as $communication)
                                <tr>
                                    <td>{{ $communication->user?->name ?? 'Unknown' }}</td>
                                    <td>{{ str($communication->category)->replace('_', ' ')->title() }}</td>
                                    <td>{{ str($communication->channel)->title() }}</td>
                                    <td><span class="customer-badge customer-badge--neutral">{{ str($communication->status)->replace('_', ' ')->title() }}</span></td>
                                    <td>{{ $communication->opened_at?->format('d M Y, h:i A') ?? 'Not opened' }}</td>
                                    <td>{{ $communication->marked_sent_at?->format('d M Y, h:i A') ?? 'Not marked' }}</td>
                                    <td>{{ $communication->admin?->name ?? 'Admin' }}</td>
                                    <td>
                                        @if ($communication->user)
                                            <a class="customer-inline-action" href="{{ route('admin.customers.show', $communication->user) }}">Profile</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8">No communication logs yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $communications->links() }}
            </section>
        </section>
    </x-admin.shell>
</x-layouts.admin>
