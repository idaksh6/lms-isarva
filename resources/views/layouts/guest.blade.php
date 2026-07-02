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
</head>
<body class="font-sans">
    <div class="auth-page">
        <header class="relative z-10 border-b border-isarva-border bg-white/95 backdrop-blur-sm">
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
                    <div class="auth-left-panel hidden lg:flex lg:flex-col lg:justify-between">
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
                            <h1 class="mt-5 text-[1.75rem] font-semibold leading-snug tracking-tight text-white xl:text-[2rem]">
                                Enterprise learning management.<br>
                                <span class="text-brand-300">Built for professional programmes.</span>
                            </h1>
                            <p class="mt-4 max-w-md text-[15px] leading-relaxed text-slate-300">
                                A secure, structured platform for courses, assignments, grading, and communications — designed for institutions and corporate training teams.
                            </p>

                            <div class="mt-6 flex flex-wrap gap-2">
                                @foreach (['Courses', 'Assignments', 'Gradebook'] as $tag)
                                    <span class="rounded-md border border-slate-700 bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-300">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>

                        <ul class="mt-8 space-y-3">
                            @foreach (['Browse courses & deadlines', 'Submit assignments online', 'Lecturer review & tracking'] as $item)
                                <li class="flex items-center gap-3 text-sm text-slate-300">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand-600 text-white">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    {{ $item }}
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8 flex items-center gap-2 text-xs text-slate-500">
                            <span class="inline-flex h-2 w-2 rounded-full bg-brand-400"></span>
                            lms.isarvait.com · {{ config('app.name') }}
                        </div>
                    </div>

                    <div class="flex flex-col justify-center border-isarva-border bg-white p-7 sm:p-9 lg:border-l lg:p-10 xl:p-12">
                        <div class="auth-brand-stack mb-6 lg:hidden">
                            <x-isarva-logo variant="large" class="auth-brand-logo !max-w-[220px]" />
                            <p class="auth-tagline">
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
