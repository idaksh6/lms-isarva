@props(['role' => 'student', 'class' => ''])

@php
    $themes = [
        'student' => ['bg' => 'from-brand-50 via-slate-50 to-white', 'accent' => '#2563eb', 'ring' => '#bfdbfe'],
        'lecturer' => ['bg' => 'from-slate-100 via-slate-50 to-white', 'accent' => '#475569', 'ring' => '#cbd5e1'],
        'admin' => ['bg' => 'from-brand-100 via-brand-50 to-white', 'accent' => '#1d4ed8', 'ring' => '#93c5fd'],
    ];
    $theme = $themes[$role] ?? $themes['student'];
@endphp

<div {{ $attributes->merge(['class' => 'lms-user-illustration bg-gradient-to-br '.$theme['bg'].' '.$class]) }} aria-hidden="true">
    <svg class="lms-user-illustration-svg" viewBox="0 0 120 96" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="60" cy="48" r="34" fill="white" fill-opacity="0.55"/>
        <circle cx="60" cy="36" r="14" fill="{{ $theme['accent'] }}" fill-opacity="0.9"/>
        <path d="M34 78c5-16 16-24 26-24s21 8 26 24" fill="{{ $theme['accent'] }}" fill-opacity="0.85"/>
        <circle cx="60" cy="36" r="14" fill="none" stroke="white" stroke-width="2" stroke-opacity="0.65"/>

        @if ($role === 'admin')
            <g class="lms-user-illustration-badge">
                <circle cx="88" cy="28" r="12" fill="white"/>
                <path d="M88 22v12M82 28h12" stroke="{{ $theme['accent'] }}" stroke-width="2" stroke-linecap="round"/>
                <path d="M84 24h8l-1 3H85l-1-3z" fill="{{ $theme['accent'] }}" fill-opacity="0.35"/>
            </g>
        @elseif ($role === 'lecturer')
            <g class="lms-user-illustration-badge">
                <circle cx="88" cy="28" r="12" fill="white"/>
                <path d="M82 30h12l-6-8-6 8z" fill="{{ $theme['accent'] }}" fill-opacity="0.85"/>
                <rect x="79" y="30" width="18" height="3" rx="1" fill="{{ $theme['accent'] }}"/>
            </g>
        @else
            <g class="lms-user-illustration-badge">
                <circle cx="88" cy="28" r="12" fill="white"/>
                <circle cx="88" cy="27" r="3.5" fill="{{ $theme['accent'] }}"/>
                <path d="M82 34c2-4 4-5 6-5s4 1 6 5" stroke="{{ $theme['accent'] }}" stroke-width="2" stroke-linecap="round"/>
            </g>
        @endif
    </svg>
</div>
