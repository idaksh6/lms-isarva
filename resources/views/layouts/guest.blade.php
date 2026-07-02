<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Sign in</title>
    @include('layouts.partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-vars')
</head>
<body class="font-sans antialiased text-isarva-heading">
    <div class="auth-page">
        <header class="relative z-10 shrink-0 border-b border-isarva-border bg-white/95 backdrop-blur-sm">
            <div class="isarva-container flex items-center justify-between py-3.5">
                <a href="https://www.isarvait.com" target="_blank" rel="noopener" class="text-sm font-medium text-isarva-muted transition hover:text-brand-600">
                    ← isarvait.com
                </a>
                <nav class="hidden items-center gap-8 text-sm font-medium text-isarva-heading sm:flex">
                    <a href="https://www.isarvait.com" target="_blank" rel="noopener" class="transition hover:text-brand-600">Home</a>
                    <a href="https://www.isarvait.com/service/website-services" target="_blank" rel="noopener" class="transition hover:text-brand-600">Services</a>
                    <a href="https://www.isarvait.com" target="_blank" rel="noopener" class="transition hover:text-brand-600">Support</a>
                </nav>
                <a href="https://www.isarvait.com" target="_blank" rel="noopener" class="isarva-btn hidden sm:inline-flex group">
                    Contact Us
                    <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
                </a>
            </div>
        </header>

        <main class="relative z-10 flex-1 py-8 sm:py-10 lg:py-12">
            <div class="isarva-container">
                <div class="auth-card grid lg:grid-cols-2">
                    <aside class="auth-left-panel hidden lg:flex lg:flex-col lg:justify-between">
                        <div>
                            <div class="auth-brand-stack">
                                <a href="https://www.isarvait.com" target="_blank" rel="noopener" class="auth-brand-logo">
                                    <x-isarva-logo variant="large" />
                                </a>
                                <p class="auth-tagline">
                                    <span class="isarva-pill-dot" aria-hidden="true"></span>
                                    Data Science Learning Platform
                                </p>
                            </div>

                            <h1 class="auth-brand-headline">
                                Learn and grow <span class="auth-brand-accent">with clarity.</span>
                            </h1>
                            <p class="auth-brand-lead">
                                Courses, assignments, and progress tracking in one workspace built for teams that take learning seriously.
                            </p>

                            <ul class="auth-feature-list">
                                <li class="auth-feature-card">
                                    <span class="auth-feature-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m6-18.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-18.292V18"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="auth-feature-title">Structured courses</p>
                                        <p class="auth-feature-desc">Modules, deadlines, and materials in one place.</p>
                                    </div>
                                </li>
                                <li class="auth-feature-card">
                                    <span class="auth-feature-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="auth-feature-title">Secure submissions</p>
                                        <p class="auth-feature-desc">Assignments and reviews with clear audit trails.</p>
                                    </div>
                                </li>
                                <li class="auth-feature-card">
                                    <span class="auth-feature-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="auth-feature-title">Progress at a glance</p>
                                        <p class="auth-feature-desc">Dashboards and reports for learners and admins.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="auth-left-foot">
                            <span class="inline-flex h-2 w-2 rounded-full bg-brand-300"></span>
                            lms.isarvait.com · {{ config('app.name') }}
                        </div>
                    </aside>

                    <div class="auth-right-panel">
                        <div class="auth-brand-stack mb-6 lg:hidden">
                            <x-isarva-logo variant="large" class="auth-brand-logo !max-w-[220px]" />
                            <p class="auth-tagline auth-tagline--light">
                                <span class="isarva-pill-dot" aria-hidden="true"></span>
                                Data Science Learning Platform
                            </p>
                        </div>

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </main>

        @include('layouts.partials.auth-footer')
    </div>
</body>
</html>
