@php
    $isPaidOnline = $order->payment_method === 'razorpay' && $order->payment_status === 'paid';
    $isCod = $order->payment_method === 'cod';
    $paymentTitle = $isPaidOnline ? 'Paid Online' : ($isCod ? 'Cash on Delivery' : 'Payment Pending');
    $paymentSeal = $isPaidOnline ? 'PAID' : ($isCod ? 'AMOUNT DUE' : 'PENDING');
    $amountLabel = $isPaidOnline ? 'Amount Paid' : 'Amount To Pay';
    $invoiceNumber = $order->invoice_number ?? $order->order_number;
    $address = collect([$order->address_line_1, $order->address_line_2, $order->city.' - '.$order->pincode])->filter()->join(', ');
    $companyAddress = collect([$company->address_line_1, $company->address_line_2, $company->city, $company->state, $company->pincode, $company->country])->filter()->join(', ');
    $shippingLabel = $order->shipping_status === 'delivery_charges_applicable'
        ? 'Delivery charges applicable'
        : ($order->shipping_amount ? 'INR '.number_format($order->shipping_amount) : 'Free');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoiceNumber }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #14213d;
            background: #fffdf8;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.55;
        }
        .invoice-page { padding: 28px; }
        .header {
            display: table;
            width: 100%;
            padding-bottom: 18px;
            border-bottom: 2px solid #e8e1d6;
        }
        .brand, .invoice-meta { display: table-cell; vertical-align: top; width: 50%; }
        .brand img { width: 150px; height: auto; margin-bottom: 12px; }
        .brand h1 { margin: 0 0 6px; font-family: Georgia, serif; font-size: 24px; }
        .brand p, .invoice-meta p, .box p { margin: 0; color: #5f6878; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { margin: 0 0 8px; font-family: Georgia, serif; font-size: 28px; }
        .seal {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 14px;
            border: 2px solid {{ $isPaidOnline ? '#2f6b4f' : '#b4751f' }};
            color: {{ $isPaidOnline ? '#2f6b4f' : '#9a5c15' }};
            font-weight: 800;
            letter-spacing: 1px;
        }
        .grid { display: table; width: 100%; margin-top: 22px; }
        .box {
            display: table-cell;
            width: 50%;
            padding: 16px;
            border: 1px solid #e8e1d6;
            background: #ffffff;
            vertical-align: top;
        }
        .box + .box { border-left: 0; }
        .box h3, .section h3 { margin: 0 0 10px; font-family: Georgia, serif; font-size: 17px; }
        .section { margin-top: 22px; }
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 11px 10px;
            border: 1px solid #e8e1d6;
            background: #f8f3ea;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 12px 10px;
            border: 1px solid #e8e1d6;
            vertical-align: top;
        }
        .right { text-align: right; }
        .totals {
            width: 42%;
            margin-left: auto;
            margin-top: 16px;
            border: 1px solid #e8e1d6;
            background: #ffffff;
        }
        .totals div {
            display: table;
            width: 100%;
            padding: 10px 12px;
            border-bottom: 1px solid #e8e1d6;
        }
        .totals div:last-child { border-bottom: 0; background: #f8f3ea; font-weight: 800; }
        .totals span, .totals strong { display: table-cell; }
        .totals strong { text-align: right; }
        .note {
            margin-top: 24px;
            padding: 14px;
            border: 1px solid #e8e1d6;
            background: #ffffff;
            color: #5f6878;
        }
        .footer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px solid #e8e1d6;
            color: #5f6878;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <main class="invoice-page">
        <section class="header">
            <div class="brand">
                @if ($logoData)
                    <img src="{{ $logoData }}" alt="Sushako Shopping">
                @endif
                <h1>{{ $company->company_name }}</h1>
                <p>{{ $company->legal_business_name ?: $company->company_name }}</p>
                @if ($companyAddress)
                    <p>{{ $companyAddress }}</p>
                @endif
                @if ($company->gstin)
                    <p>GSTIN: {{ $company->gstin }}</p>
                @endif
                @if ($company->pan)
                    <p>PAN: {{ $company->pan }}</p>
                @endif
                <p>Support: {{ $company->support_phone }} @if ($company->support_email) · {{ $company->support_email }} @endif</p>
            </div>
            <div class="invoice-meta">
                <h2>Invoice</h2>
                <p><strong>Invoice No:</strong> {{ $invoiceNumber }}</p>
                <p><strong>Order ID:</strong> {{ $order->order_number }}</p>
                <p><strong>Date:</strong> {{ ($order->invoiced_at ?? $order->placed_at)?->format('d M Y, h:i A') }}</p>
                <div class="seal">{{ $paymentSeal }}</div>
            </div>
        </section>

        <section class="grid">
            <div class="box">
                <h3>Bill To</h3>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->customer_phone }}</p>
                @if ($order->customer_email)
                    <p>{{ $order->customer_email }}</p>
                @endif
                <p>{{ $address }}</p>
                @if ($order->landmark)
                    <p>Landmark: {{ $order->landmark }}</p>
                @endif
            </div>
            <div class="box">
                <h3>Payment Details</h3>
                <p><strong>{{ $paymentTitle }}</strong></p>
                <p>Status: {{ ucfirst($order->payment_status) }}</p>
                <p>{{ $amountLabel }}: INR {{ number_format($order->total_amount) }}</p>
                @if ($order->razorpay_payment_id)
                    <p>Razorpay Payment ID: {{ $order->razorpay_payment_id }}</p>
                @endif
                @if ($isCod)
                    <p>Please collect INR {{ number_format($order->total_amount) }} at delivery.</p>
                @endif
            </div>
        </section>

        <section class="section">
            <h3>Product Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th class="right">Qty</th>
                        <th class="right">Unit Price</th>
                        <th class="right">GST</th>
                        <th class="right">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->colour }} / {{ $item->size }}</td>
                            <td class="right">{{ $item->quantity }}</td>
                            <td class="right">INR {{ number_format($item->unit_price) }}</td>
                            <td class="right">{{ number_format($item->gst_rate, 2) }}%</td>
                            <td class="right">INR {{ number_format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="totals">
            <div><span>Subtotal</span><strong>INR {{ number_format($order->subtotal) }}</strong></div>
            <div><span>Shipping</span><strong>{{ $shippingLabel }}</strong></div>
            <div><span>GST Included</span><strong>INR {{ number_format($order->tax_amount) }}</strong></div>
            <div><span>CGST</span><strong>INR {{ number_format($order->cgst_amount) }}</strong></div>
            <div><span>SGST</span><strong>INR {{ number_format($order->sgst_amount) }}</strong></div>
            <div><span>{{ $amountLabel }}</span><strong>INR {{ number_format($order->total_amount) }}</strong></div>
        </section>

        @if ($invoiceSettings->terms_conditions)
            <section class="note">
                <strong>Terms & Conditions:</strong> {{ $invoiceSettings->terms_conditions }}
            </section>
        @endif

        <section class="note">
            <strong>{{ $invoiceSettings->authorized_signatory_name }}</strong><br>
            {{ $invoiceSettings->authorized_signatory_designation }}
        </section>

        <section class="note">
            @if ($isPaidOnline)
                This invoice is marked paid because the Razorpay online payment was captured successfully.
            @elseif ($isCod)
                This is a Cash on Delivery invoice. The customer has to pay INR {{ number_format($order->total_amount) }} at delivery.
            @else
                Payment is pending. Please complete payment before treating this order as paid.
            @endif
        </section>

        <footer class="footer">
            {{ $invoiceSettings->invoice_footer ?: 'Thank you for shopping with Sushako. This computer-generated invoice can be shared for order support and delivery reference.' }}
        </footer>
    </main>
</body>
</html>
