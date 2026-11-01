<x-layouts.seller title="Import From Your Website">
    <x-seller.header title="Import From Your Website" subtitle="Bring your own catalog to Sushako for review before publishing." :vendor="$vendor" />
    <section class="seller-content">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif
        <section class="seller-card-grid seller-card-grid--metrics" aria-label="Catalogue intelligence overview">
            @foreach ([
                ['label' => 'Registered sources', 'value' => $imports->count(), 'icon' => 'fa-globe'],
                ['label' => 'Analysed sessions', 'value' => $discoverySessions->count(), 'icon' => 'fa-magnifying-glass-chart'],
                ['label' => 'New products', 'value' => $matchCounts->get('new_product', 0), 'icon' => 'fa-sparkles'],
                ['label' => 'Possible matches', 'value' => $matchCounts->get('possible_match', 0), 'icon' => 'fa-code-compare'],
            ] as $metric)
                <article class="seller-metric-card">
                    <span class="seller-metric-card__icon"><i class="fa-solid {{ $metric['icon'] }}" aria-hidden="true"></i></span>
                    <span>{{ $metric['label'] }}</span>
                    <strong>{{ number_format($metric['value']) }}</strong>
                </article>
            @endforeach
        </section>
        <section class="seller-panel seller-panel--wide">
            <p class="eyebrow">Catalogue Intelligence V2.0</p>
            <h2>Understand your catalogue before you import it.</h2>
            <p>Connect a public HTTPS store, review the intelligence report, then choose what enters Sushako. Discovery is bounded, seller-controlled and never auto-publishes products.</p>
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
                        <p><strong>Verified.</strong> Run website intelligence first. It identifies the public platform, sitemaps, categories and extraction capabilities.</p>
                        <div class="seller-form-actions">
                            <form method="POST" action="{{ route('seller.imports.analyse', $import) }}">@csrf<button type="submit">Analyse Website</button></form>
                            @if ($import->intelligenceProfile?->status === 'ready')
                                <form method="POST" action="{{ route('seller.imports.start', $import) }}">@csrf<button type="submit">Start Discovery</button></form>
                            @endif
                        </div>
                        @if ($import->intelligenceProfile)
                            @php($profile = $import->intelligenceProfile)
                            <section class="seller-panel seller-panel--nested" aria-label="Website intelligence report">
                                <div class="seller-panel__heading"><div><p class="eyebrow">Intelligence report</p><h4>{{ str($profile->platform)->title() }} · {{ str($profile->status)->title() }}</h4></div><span class="status-badge">{{ $profile->analysed_at?->diffForHumans() }}</span></div>
                                <div class="seller-fact-grid">
                                    <span><small>Categories</small><strong>{{ number_format($profile->category_count) }}</strong></span>
                                    <span><small>Product URLs</small><strong>{{ number_format($profile->product_url_count) }}</strong></span>
                                    <span><small>Currency</small><strong>{{ $profile->currency ?: 'Not detected' }}</strong></span>
                                    <span><small>Access</small><strong>{{ $profile->is_accessible ? 'Accessible' : 'Unavailable' }}</strong></span>
                                </div>
                                @if ($profile->limitations)<p class="status-banner status-banner--warning">{{ implode(' ', $profile->limitations) }}</p>@endif
                                <p><strong>Capabilities:</strong> {{ collect($profile->capabilities ?: [])->filter()->keys()->map(fn ($key) => str($key)->replace('_', ' ')->title())->implode(' · ') ?: 'No capabilities confirmed yet.' }}</p>
                            </section>
                        @endif
                        <form class="seller-form-card" method="POST" action="{{ route('seller.imports.monitoring') }}">
                            @csrf
                            <input type="hidden" name="source_id" value="{{ $import->id }}">
                            <label>Monitoring frequency
                                <select name="frequency"><option value="disabled">Disabled</option><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select>
                            </label>
                            <label><input type="checkbox" name="new_products" value="1" checked> New products</label>
                            <label><input type="checkbox" name="price_changes" value="1" checked> Price changes</label>
                            <label><input type="checkbox" name="image_changes" value="1"> Image changes</label>
                            <label><input type="checkbox" name="availability_changes" value="1" checked> Availability changes</label>
                            <button type="submit">Save Monitoring</button>
                        </form>
                        @foreach ($import->jobs()->with('candidates')->latest()->limit(3)->get() as $job)
                            <div class="seller-import-job"><strong>Job #{{ $job->id }} · {{ str($job->status)->title() }}</strong><span>{{ $job->products_found }} found · {{ $job->products_needing_review }} need review</span>
                            @foreach ($job->candidates as $candidate)
                                <details><summary>{{ data_get($candidate->payload, 'name', 'Unnamed product') }} · {{ str($candidate->status)->title() }}</summary>
                                    <p>{{ data_get($candidate->payload, 'description') }}</p>
                                    @if (! in_array($candidate->status, ['imported_draft'], true))
                                        <form method="POST" action="{{ route('seller.imports.candidates.approve', $candidate) }}">@csrf<input type="number" name="category_id" value="{{ data_get($candidate->category_suggestion, 'category_id') }}" placeholder="Master category ID"><button type="submit">Approve Candidate</button></form>
                                    @endif
                                    @if ($candidate->status === 'approved')<form method="POST" action="{{ route('seller.imports.candidates.publish', $candidate) }}">@csrf<button type="submit">Import as Draft</button></form>@endif
                                    @if ($candidate->status === 'imported_draft')<p class="status-banner">Imported as a seller draft. Final product publishing remains under seller control.</p>@endif
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
        @if ($discoverySessions->isNotEmpty())
            <section class="seller-panel seller-panel--wide">
                <div class="seller-panel__heading"><div><p class="eyebrow">Category Explorer</p><h2>Discovery sessions</h2></div></div>
                <div class="seller-card-grid">
                    @foreach ($discoverySessions as $session)
                        <article class="seller-panel seller-panel--nested"><p class="eyebrow">{{ str($session->status)->title() }}</p><h3>Session #{{ $session->id }}</h3><p>{{ number_format($session->categories_discovered) }} categories · {{ number_format($session->unique_urls) }} unique URLs</p><a class="button button--secondary" href="{{ route('seller.imports.categories', $session) }}">Review categories</a></article>
                    @endforeach
                </div>
            </section>
        @endif
        @if ($matches->isNotEmpty())
            <section class="seller-panel seller-panel--wide">
                <div class="seller-panel__heading"><div><p class="eyebrow">Import Centre</p><h2>Review catalogue matches</h2></div><span class="status-badge">Seller approval required</span></div>
                <div class="seller-import-job">
                    @foreach ($matches as $match)
                        <details><summary>{{ $match->extractedProduct->name ?: 'Unnamed product' }} · {{ str($match->outcome)->replace('_', ' ')->title() }} · {{ number_format((float) $match->confidence * 100, 0) }}%</summary>
                            <p>{{ $match->extractedProduct->sourceUrl?->canonical_url }}</p>
                            @if ($match->changes)<p><strong>Changes:</strong> {{ collect($match->changes)->keys()->map(fn ($key) => str($key)->replace('_', ' ')->title())->implode(', ') }}</p>@endif
                            <div class="seller-form-actions">
                                @if ($match->decision === 'pending')
                                    <form method="POST" action="{{ route('seller.imports.matches.decide', $match) }}">@csrf<input type="hidden" name="decision" value="approved"><button type="submit">Approve Review</button></form>
                                    <form method="POST" action="{{ route('seller.imports.matches.decide', $match) }}">@csrf<input type="hidden" name="decision" value="rejected"><button type="submit" class="button button--secondary">Reject</button></form>
                                @elseif ($match->decision === 'approved')
                                    @if ($match->outcome === 'new_product')<form method="POST" action="{{ route('seller.imports.matches.import', $match) }}">@csrf<input type="hidden" name="action" value="import_new"><input type="number" name="category_id" placeholder="Master category ID" required><button type="submit">Import as Draft</button></form>@elseif ($match->changes && isset($match->changes['price']))<form method="POST" action="{{ route('seller.imports.matches.import', $match) }}">@csrf<input type="hidden" name="action" value="update_price"><button type="submit">Approve Price Update</button></form>@endif
                                @endif
                                <form method="POST" action="{{ route('seller.imports.enrich', $match->extractedProduct) }}">@csrf<button type="submit" class="button button--secondary">Suggest Enrichment</button></form>
                                @if (($match->extractedProduct->images ?? []) !== [])<form method="POST" action="{{ route('seller.imports.images.process', $match->extractedProduct) }}">@csrf<button type="submit" class="button button--secondary">Prepare Images</button></form>@endif
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif
    </section>
</x-layouts.seller>
