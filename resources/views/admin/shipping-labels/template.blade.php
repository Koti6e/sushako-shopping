@php
    $paymentBadge = $order->payment_method === 'cod' ? 'COD' : 'PREPAID';
    $collectAmount = $order->payment_method === 'cod' ? $order->total_amount : 0;
    $addressLines = collect([$order->address_line_1, $order->address_line_2, $order->landmark, $order->city, $order->pincode])->filter();
@endphp

<article class="shipping-label shipping-label--{{ $label->brand_mode }}">
    <header>
        @if ($label->brand_mode !== 'courier_neutral')
            @if ($logoData)
                <img src="{{ $logoData }}" alt="Sushako Shopping">
            @endif
            <div>
                <strong>{{ $label->brand_mode === 'seller' ? ($label->seller_name ?: 'Seller') : 'SUSHAKO SHOPPING' }}</strong>
                <span>{{ $label->brand_mode === 'seller' ? 'Powered By Sushako' : 'Thoughtfully Selected. Delivered with Care.' }}</span>
            </div>
        @else
            <div>
                <strong>SHIPMENT LABEL</strong>
                <span>Address · Barcode · QR</span>
            </div>
        @endif
        <b class="shipping-payment-badge shipping-payment-badge--{{ strtolower($paymentBadge) }}">{{ $paymentBadge }}</b>
    </header>

    <section class="shipping-label__package">
        <span>Package</span>
        <strong>{{ $label->package_index }} OF {{ $label->package_count }}</strong>
        <span>Order Number</span>
        <strong>{{ $order->order_number }}</strong>
    </section>

    <section class="shipping-label__delivery">
        <div>
            <span>DELIVER TO</span>
            <strong>{{ $order->customer_name }}</strong>
            <p>{{ $order->customer_phone }}</p>
            @foreach ($addressLines as $line)
                <p>{{ $line }}</p>
            @endforeach
        </div>
        <div class="shipping-label__maps">
            @if ($locationQr)
                <img src="{{ $locationQr }}" alt="Google Maps delivery QR">
                <span>SCAN FOR DELIVERY LOCATION</span>
            @else
                <strong>Location Not Available</strong>
            @endif
        </div>
    </section>

    <section class="shipping-label__payment">
        @if ($paymentBadge === 'COD')
            <strong>COD</strong>
            <span>Collect &#8377;{{ number_format($collectAmount) }}</span>
        @else
            <strong>PREPAID</strong>
            <span>Do Not Collect Cash</span>
        @endif
    </section>

    <section class="shipping-label__meta">
        <span>Weight <strong>{{ $label->weight_grams ? number_format($label->weight_grams / 1000, 2).' kg' : 'Not set' }}</strong></span>
        <span>Seller <strong>{{ $label->seller_name ?: 'Sushako Shopping' }}</strong></span>
        <span>Courier <strong>{{ $label->courier_name ?: 'Manual / Pending' }}</strong></span>
    </section>

    <section class="shipping-label__codes">
        <div>
            <img src="{{ $internalBarcode }}" alt="Internal order barcode">
            <span>{{ $order->order_number }}</span>
        </div>
        <div>
            <img src="{{ $internalQr }}" alt="Internal warehouse lookup QR">
            <span>WAREHOUSE LOOKUP</span>
        </div>
    </section>

    <footer>
        <span>Sold & Packed By</span>
        <strong>{{ $label->seller_name ?: 'Sushako Shopping' }}</strong>
        <span>Powered By Sushako · {{ config('services.whatsapp.support_number') }} · shop.sushako.in</span>
    </footer>
</article>
