<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>{{ config('app.name', 'KD Governance') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Vuetify -->
    <link href="https://cdn.jsdelivr.net/npm/vuetify@3.6.14/dist/vuetify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body>
    @inertia

    <script src="https://cdn.jsdelivr.net/npm/vuetify@3.6.14/dist/vuetify.min.js"></script>
</body>
</html>