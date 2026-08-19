<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $label->label_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #111; }
        .shipping-label { width: 100%; border: 1px solid #111; padding: 8px; font-size: 10px; }
        .shipping-label header, .shipping-label__delivery, .shipping-label__package, .shipping-label__meta, .shipping-label__codes, .shipping-label footer { border-bottom: 1px solid #111; padding: 6px 0; }
        .shipping-label header { display: table; width: 100%; }
        .shipping-label header img { width: 74px; height: 30px; object-fit: contain; display: inline-block; vertical-align: middle; }
        .shipping-label header div { display: inline-block; width: 58%; vertical-align: middle; }
        .shipping-label header strong, .shipping-label__package strong, .shipping-label__delivery strong { display: block; font-size: 13px; }
        .shipping-payment-badge { float: right; border: 1px solid #111; padding: 6px; font-weight: bold; }
        .shipping-label__package span, .shipping-label__meta span { display: inline-block; width: 48%; }
        .shipping-label__delivery > div { display: inline-block; width: 58%; vertical-align: top; }
        .shipping-label__maps { width: 38% !important; text-align: center; }
        .shipping-label__maps img { width: 96px; height: 96px; display: inline-block; }
        .shipping-label__payment { text-align: center; border-bottom: 1px solid #111; padding: 8px 0; }
        .shipping-label__payment strong { display: block; font-size: 24px; }
        .shipping-label__codes div { display: inline-block; width: 48%; text-align: center; }
        .shipping-label__codes img { max-width: 130px; max-height: 90px; display: inline-block; }
        .shipping-label footer { border-bottom: 0; text-align: center; }
        .shipping-label footer strong, .shipping-label footer span { display: block; }
    </style>
</head>
<body>
    @include('admin.shipping-labels.template', compact('label', 'order', 'locationQr', 'internalBarcode', 'internalQr', 'logoData'))
</body>
</html>
