<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-vars')
</head>
<body class="lms-body font-sans antialiased">
    <div x-data="{ sidebarOpen: false }" class="lms-app">
        <div x-show="sidebarOpen"
             x-transition.opacity
             @click="sidebarOpen = false"
             class="lms-overlay lg:hidden"
             x-cloak></div>

        @include('layouts.partials.sidebar')

        <div class="lms-shell">
            @include('layouts.partials.topbar')

            <main class="lms-main">
                <x-flash-messages />
                @yield('content')
            </main>

            @include('layouts.partials.site-footer')
        </div>
    </div>
</body>
</html>
