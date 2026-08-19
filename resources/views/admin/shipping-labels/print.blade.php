<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print {{ $label->label_number }}</title>
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/app.css') }}">
</head>
<body class="shipping-label-print-body">
    @include('admin.shipping-labels.template', compact('label', 'order', 'locationQr', 'internalBarcode', 'internalQr', 'logoData'))
    <script>window.print();</script>
</body>
</html>
