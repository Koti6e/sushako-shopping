<x-layouts.admin title="Approval Center - Sushako Admin">
    <x-admin.shell eyebrow="Marketplace" title="Approval Center" subtitle="One inbox for seller, category, product, store verification and settlement approvals." badge="Control Queue">
        <x-slot:actions>
            <x-admin.action :href="route('admin.sellers.index')" tone="secondary" icon="fa-solid fa-store">Sellers</x-admin.action>
            <x-admin.action :href="route('admin.settlements.index')" tone="primary" icon="fa-solid fa-indian-rupee-sign">Settlements</x-admin.action>
        </x-slot:actions>

        <section class="admin-control-page admin-approval-center">
            <div class="admin-approval-grid">
                @foreach ($queues as $queue)
                    <article class="admin-approval-card">
                        <header>
                            <div>
                                <p class="eyebrow">{{ $queue['label'] }}</p>
                                <h2>{{ $queue['count'] }} waiting</h2>
                                <p>{{ $queue['description'] }}</p>
                            </div>
                            <a href="{{ $queue['url'] }}">Open</a>
                        </header>

                        <div class="admin-approval-card__items">
                            @forelse ($queue['items'] as $item)
                                <a href="{{ $item['url'] }}">
                                    <strong>{{ $item['title'] }}</strong>
                                    <span>{{ $item['meta'] }}</span>
                                </a>
                            @empty
                                <p>No pending items in this queue.</p>
                            @endforelse
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
