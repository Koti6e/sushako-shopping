@if ($showEssentialCookieBanner ?? false)
    <section class="cookie-consent" aria-labelledby="cookie-consent-title" role="region">
        <div class="cookie-consent__icon" aria-hidden="true">🍪</div>
        <div class="cookie-consent__content">
            <h2 id="cookie-consent-title">🍪 Essential Cookies</h2>
            <p>We use essential cookies to keep Sushako Shopping secure, maintain your shopping cart, manage your login session, and remember your cookie preference. These cookies are required for the website to function properly.</p>
        </div>
        <div class="cookie-consent__actions">
            <form method="POST" action="{{ route('cookie-consent.essential.store') }}" data-no-page-loader>
                @csrf
                <button class="button button--primary" type="submit">Accept Essential Cookies</button>
            </form>
            <a class="button button--ghost" href="{{ route('policies.cookie') }}">Learn More</a>
        </div>
    </section>
@endif
