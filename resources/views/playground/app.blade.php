<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Try the AKILIMO recommendations API — see the fertilizer, intercropping and planting schedule recommendations it returns for a farm.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Playground — {{ config('app.name', 'AKILIMO API') }}</title>

    @include('partials.theme-head')

    @viteReactRefresh
    @vite(['resources/scss/playground.scss', 'resources/js/playground/index.tsx', 'resources/js/theme-switcher.tsx'])
</head>
<body>

<x-playground.navbar badge="Playground" :health="true"/>

<div id="playground-root"></div>

</body>
</html>
