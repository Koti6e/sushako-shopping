<x-layouts.admin title="Category reconciliation">
    <section class="admin-content">
        <h1>Category reconciliation dry-run</h1>
        <p>This report does not mutate products or categories. Apply is available only after explicit approval.</p>
        <dl class="admin-stats-grid">
            <div><dt>Total</dt><dd>{{ $run->total_count }}</dd></div>
            <div><dt>Unchanged</dt><dd>{{ $run->unchanged_count }}</dd></div>
            <div><dt>Mapped</dt><dd>{{ $run->mapped_count }}</dd></div>
            <div><dt>Uncertain</dt><dd>{{ $run->uncertain_count }}</dd></div>
            <div><dt>Missing</dt><dd>{{ $run->missing_count }}</dd></div>
            <div><dt>Conflicts</dt><dd>{{ $run->conflict_count }}</dd></div>
        </dl>
        @if ($run->status === 'dry_run')
            <form method="POST" action="{{ route('admin.categories.reconciliation.approve', $run) }}">@csrf<button class="button button--primary">Approve dry-run</button></form>
        @elseif ($run->status === 'approved')
            <form method="POST" action="{{ route('admin.categories.reconciliation.apply', $run) }}">@csrf<button class="button button--primary">Apply approved mappings</button></form>
        @endif
    </section>
</x-layouts.admin>
