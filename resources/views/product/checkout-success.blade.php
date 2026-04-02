<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

</head>

<body class="antialiased">
    <h1>
        Checkout Success
    </h1>

    @if (!empty($customerName))
        <p>Thank you, {{ $customerName }}!</p>
    @else
        <p>Payment completed successfully.</p>
    @endif
</body>

</html>