# Demo Image Sources

Real royalty-free demo images are stored locally for the MVP demo phase.

Runtime source:

- Database-backed catalog images rendered through `App\Support\ProductCatalog`

Local assets live in:

- `public/images/brand/`
- `public/assets/banners/`
- `public/assets/categories/`
- `public/assets/products/`
- `public/assets/reviews/`
- `public/assets/payments/`

This avoids hotlinked runtime imagery. Admin-uploaded media now uses the public storage disk.

Original photos were downloaded from Unsplash image CDN URLs with `auto=format`, `fit=crop`, width, height and quality parameters. UPI and RuPay SVGs were downloaded from Wikimedia Commons; Visa, Mastercard and Razorpay SVGs were downloaded from Simple Icons.
