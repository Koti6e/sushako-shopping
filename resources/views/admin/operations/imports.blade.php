<x-layouts.admin title="Website Imports - Admin">
    <x-admin.shell eyebrow="Catalogue operations" title="Website import monitor" subtitle="Review bounded seller imports and their draft boundaries.">
        <section class="admin-control-page admin-import-monitor">
            <div class="admin-enterprise-card">
                <p class="eyebrow">Phase A operations</p>
                <h2>Seller website imports</h2>
                <p>Imported products remain seller drafts. Master-category mapping and duplicate signals require review before any customer visibility.</p>
            </div>

            <div class="admin-control-metrics" aria-label="Catalogue intelligence metrics">
                @foreach ([
                    ['label' => 'Registered sources', 'value' => $catalogueStats['sources']],
                    ['label' => 'Discovery sessions', 'value' => $catalogueStats['sessions']],
                    ['label' => 'Extracted products', 'value' => $catalogueStats['extracted']],
                    ['label' => 'Product matches', 'value' => $catalogueStats['matches']],
                    ['label' => 'Failed sessions', 'value' => $catalogueStats['failed']],
                ] as $metric)
                    <article><span>{{ $metric['label'] }}</span><strong>{{ number_format($metric['value']) }}</strong></article>
                @endforeach
            </div>

            <div class="admin-enterprise-card admin-import-monitor__table">
                <div class="admin-import-monitor__table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Seller / website</th>
                                <th>Status</th>
                                <th>Pages</th>
                                <th>Discovered</th>
                                <th>Review</th>
                                <th>Duplicates</th>
                                <th>Drafts</th>
                                <th>Finished</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jobs as $job)
                                <tr>
                                    <td>
                                        <strong>{{ $job->vendor?->business_name ?: 'Seller' }}</strong>
                                        <small>{{ $job->domainImport?->domain }}</small>
                                    </td>
                                    <td><span class="status-badge status-{{ $job->status }}">{{ str($job->status)->replace('_', ' ')->title() }}</span></td>
                                    <td>{{ $job->pages_found }}</td>
                                    <td>{{ $job->candidates_count }}</td>
                                    <td>{{ $job->ready_count + $job->review_count }}</td>
                                    <td>{{ $job->duplicate_count }}</td>
                                    <td>{{ $job->draft_count }}</td>
                                    <td>{{ $job->finished_at?->format('d M Y, H:i') ?: '—' }}</td>
                                </tr>
                                @if ($job->error_message)
                                    <tr class="admin-import-monitor__error"><td colspan="8">{{ $job->error_message }}</td></tr>
                                @endif
                            @empty
                                <tr><td colspan="8">No website imports have been started.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $jobs->links() }}
            </div>
        </section>
    </x-admin.shell>
</x-layouts.admin>
