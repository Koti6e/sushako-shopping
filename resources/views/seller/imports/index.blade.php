<x-layouts.seller title="Import From Your Website">
    <x-seller.header title="Import From Your Website" subtitle="Bring your own catalog to Sushako for review before publishing." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
        <section class="seller-panel seller-panel--wide">
            <p class="eyebrow">Seller-controlled catalog</p>
            <h2>Your Product. Your Price. Your Customers. Your Branding. Your Shipping.</h2>
            <p>Verify a public HTTPS domain first. Products are never published automatically; every imported item will require review and approval.</p>
            <form class="seller-form-card" method="POST" action="{{ route('seller.imports.store') }}">
                @csrf
                <label>Website domain
                    <input name="domain" type="url" placeholder="https://your-store.example" value="{{ old('domain') }}" required>
                </label>
                <button type="submit">Verify Domain</button>
            </form>
        </section>
        <section class="seller-card-grid">
            @forelse ($imports as $import)
                <article class="seller-panel">
                    <p class="eyebrow">{{ strtoupper($import->status) }}</p>
                    <h3>{{ $import->domain }}</h3>
                    <p>Add this token as a DNS TXT record at <code>_sushako-verification.{{ $import->domain }}</code>:</p>
                    <code>{{ $import->verification_token }}</code>
                    @if ($import->status !== 'verified')
                        <form method="POST" action="{{ route('seller.imports.verify', $import) }}">
                            @csrf
                            <button type="submit">Verify Ownership</button>
                        </form>
                    @else
                        <p><strong>Verified.</strong> Imports use bounded asynchronous crawling and remain unpublished until you approve them.</p>
                        <form method="POST" action="{{ route('seller.imports.start', $import) }}">@csrf<button type="submit">Start Safe Import</button></form>
                        @foreach ($import->jobs()->with('candidates')->latest()->limit(3)->get() as $job)
                            <div class="seller-import-job"><strong>Job #{{ $job->id }} · {{ str($job->status)->title() }}</strong><span>{{ $job->products_found }} found · {{ $job->products_needing_review }} need review</span>
                            @foreach ($job->candidates as $candidate)
                                <details><summary>{{ data_get($candidate->payload, 'name', 'Unnamed product') }} · {{ str($candidate->status)->title() }}</summary>
                                    <p>{{ data_get($candidate->payload, 'description') }}</p>
                                    @if ($candidate->status !== 'published')
                                        <form method="POST" action="{{ route('seller.imports.candidates.approve', $candidate) }}">@csrf<input type="number" name="category_id" value="{{ data_get($candidate->category_suggestion, 'category_id') }}" placeholder="Master category ID"><button type="submit">Approve Candidate</button></form>
                                    @endif
                                    @if ($candidate->status === 'approved')<form method="POST" action="{{ route('seller.imports.candidates.publish', $candidate) }}">@csrf<button type="submit">Import as Draft</button></form>@endif
                                </details>
                            @endforeach
                            </div>
                        @endforeach
                    @endif
                </article>
            @empty
                <article class="seller-panel"><h3>No verified domains yet</h3><p>Add your store domain to begin.</p></article>
            @endforelse
        </section>
    </section>
</x-layouts.seller>
