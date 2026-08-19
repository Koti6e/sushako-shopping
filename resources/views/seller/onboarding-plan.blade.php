@php
    $selectedPlan = old('plan', $vendor->selected_plan_slug ?: $vendor->selected_plan);
@endphp
<x-layouts.seller title="Choose Seller Plan">
    <x-seller.header title="Choose the plan built for your business" subtitle="Start free or unlock zero-commission selling with a monthly plan." :vendor="$vendor" />
    <section class="seller-content seller-onboarding seller-onboarding--elite">
        @if (session('status'))<div class="status-banner">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="status-banner status-banner--error">{{ $errors->first() }}</div>@endif

        <div class="seller-stepper seller-stepper--elite">
            @foreach ($steps as $number => $label)
                @php
                    $href = $number === 7 ? route('seller.onboarding.plan') : ($number === 8 ? route('seller.onboarding.review') : route('seller.onboarding', ['step' => $number]));
                @endphp
                <a href="{{ $href }}" @class(['is-active' => $number === 7, 'is-complete' => $number < 7 || ($number === 7 && $selectedPlan)])>
                    <span>{{ $number }}</span><strong>{{ $label }}</strong>
                </a>
            @endforeach
        </div>

        <form class="seller-form-card seller-form-card--elite seller-plan-selection-page" method="POST" action="{{ route('seller.onboarding.plan.select') }}">
            @csrf
            <h2>Choose the plan built for your business</h2>
            <p>Start free or unlock zero-commission selling with a monthly plan.</p>

            <div class="seller-plan-grid seller-plan-grid--premium seller-plan-grid--select">
                @foreach ($plans as $key => $plan)
                    @php
                        $price = match ($key) {
                            'free' => '₹0',
                            'growth' => '₹999/month',
                            'enterprise' => '₹4,999/month',
                            default => '₹'.number_format((int) $plan['amount']),
                        };
                        $button = match ($key) {
                            'free' => 'Start with Free',
                            'growth' => 'Choose Growth',
                            'enterprise' => 'Activate Enterprise',
                            default => 'Choose Plan',
                        };
                    @endphp
                    <label class="seller-plan-card seller-plan-card--action @if($key === 'growth') is-recommended @endif @if($selectedPlan === $key) is-selected @endif">
                        @if ($key === 'growth')<b>Most Popular</b>@endif
                        @if ($key === 'enterprise')<b>Best for Scale</b>@endif
                        @if ($selectedPlan === $key)<mark>Selected Plan</mark>@endif
                        <input type="radio" name="plan" value="{{ $key }}" @checked($selectedPlan === $key) required>
                        <strong>{{ $plan['name'] }} Plan</strong>
                        <span>{{ $price }}</span>
                        <small>{{ $key === 'free' ? 'No monthly payment' : str($plan['billing_period'])->title() }}</small>
                        <p>{{ $plan['supporting_text'] }}</p>
                        <dl>
                            <div><dt>Commission</dt><dd>{{ $plan['commission'] }}</dd></div>
                            <div><dt>Product limit</dt><dd>{{ $plan['product_limit'] }}</dd></div>
                            <div><dt>Order access</dt><dd>{{ $plan['order_limit'] }}</dd></div>
                            <div><dt>Validity</dt><dd>{{ $plan['validity_days'] ? 'One month' : 'No monthly payment' }}</dd></div>
                        </dl>
                        <ul>@foreach ($plan['features'] as $feature)<li>{{ $feature }}</li>@endforeach</ul>
                        <em>{{ $button }}</em>
                    </label>
                @endforeach
            </div>

            <section class="seller-plan-comparison" aria-label="Seller plan comparison">
                <h3>Plan comparison</h3>
                <div>
                    <table>
                        <thead><tr><th>Feature</th><th>Free</th><th>Growth</th><th>Enterprise</th></tr></thead>
                        <tbody>
                            @foreach ($comparisonRows as $label => $values)
                                <tr><th>{{ $label }}</th><td>{{ $values['free'] }}</td><td>{{ $values['growth'] }}</td><td>{{ $values['enterprise'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="seller-form-actions">
                <a href="{{ route('seller.onboarding', ['step' => 6]) }}">Previous</a>
                <button type="submit">Continue to Review</button>
            </div>
        </form>
    </section>
</x-layouts.seller>
