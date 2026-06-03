@props([
    'variant' => 'books',
    'class' => '',
])

@php
    $motif = match ($variant) {
        'emerald', 'books' => 'books',
        'violet', 'code', 'notebook' => 'notebook',
        'sky', 'data', 'analytics' => 'analytics',
        'amber', 'chart', 'laptop' => 'laptop',
        default => 'books',
    };
@endphp

<div {{ $attributes->merge(['class' => 'lms-banner lms-banner--'.$motif.' '.$class]) }} aria-hidden="true">
    <span class="lms-banner-base"></span>
    <span class="lms-banner-blob lms-banner-blob--1"></span>
    <span class="lms-banner-blob lms-banner-blob--2"></span>
    <span class="lms-banner-blob lms-banner-blob--3"></span>
    <span class="lms-banner-blob lms-banner-blob--4"></span>

    <div class="lms-banner-motif">
        @switch($motif)
            @case('notebook')
                <svg class="lms-banner-svg" viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g class="lms-motif-float lms-motif-float--delay">
                        <rect x="18" y="14" width="72" height="72" rx="10" fill="white" fill-opacity="0.22"/>
                    </g>
                    <g class="lms-motif-float">
                        <rect x="28" y="20" width="64" height="64" rx="8" fill="white" fill-opacity="0.92"/>
                        @for ($i = 0; $i < 5; $i++)
                            <circle cx="38" cy="{{ 32 + $i * 10 }}" r="2.5" fill="white" fill-opacity="0.55"/>
                        @endfor
                        <line x1="48" y1="34" x2="82" y2="34" stroke="white" stroke-opacity="0.45" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="48" y1="44" x2="78" y2="44" stroke="white" stroke-opacity="0.35" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="48" y1="54" x2="80" y2="54" stroke="white" stroke-opacity="0.35" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="48" y1="64" x2="72" y2="64" stroke="white" stroke-opacity="0.3" stroke-width="2.5" stroke-linecap="round"/>
                        <rect x="48" y="72" width="22" height="4" rx="2" fill="white" fill-opacity="0.5"/>
                    </g>
                    <g class="lms-motif-drift">
                        <path d="M96 26 L108 20 L108 44 L96 50 Z" fill="white" fill-opacity="0.35"/>
                    </g>
                </svg>
                @break

            @case('analytics')
                <svg class="lms-banner-svg" viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g class="lms-motif-float lms-motif-float--delay">
                        <rect x="22" y="18" width="76" height="64" rx="10" fill="white" fill-opacity="0.2"/>
                    </g>
                    <g class="lms-motif-float">
                        <rect x="30" y="24" width="68" height="56" rx="8" fill="white" fill-opacity="0.9"/>
                        <rect x="40" y="58" width="10" height="14" rx="2" fill="white" fill-opacity="0.45" class="lms-motif-bar lms-motif-bar--1"/>
                        <rect x="54" y="48" width="10" height="24" rx="2" fill="white" fill-opacity="0.6" class="lms-motif-bar lms-motif-bar--2"/>
                        <rect x="68" y="40" width="10" height="32" rx="2" fill="white" fill-opacity="0.75" class="lms-motif-bar lms-motif-bar--3"/>
                        <rect x="82" y="52" width="10" height="20" rx="2" fill="white" fill-opacity="0.55" class="lms-motif-bar lms-motif-bar--4"/>
                        <path d="M38 38 L52 46 L66 34 L80 42 L94 30" stroke="white" stroke-opacity="0.7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lms-motif-chart-line"/>
                    </g>
                    <circle class="lms-motif-drift" cx="108" cy="32" r="10" fill="white" fill-opacity="0.25"/>
                </svg>
                @break

            @case('laptop')
                <svg class="lms-banner-svg" viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g class="lms-motif-float">
                        <rect x="34" y="18" width="72" height="48" rx="6" fill="white" fill-opacity="0.92"/>
                        <rect x="40" y="24" width="60" height="36" rx="4" fill="white" fill-opacity="0.25"/>
                        <rect x="46" y="30" width="28" height="4" rx="2" fill="white" fill-opacity="0.5"/>
                        <rect x="46" y="38" width="40" height="3" rx="1.5" fill="white" fill-opacity="0.35"/>
                        <rect x="46" y="44" width="34" height="3" rx="1.5" fill="white" fill-opacity="0.3"/>
                        <circle cx="88" cy="42" r="8" fill="white" fill-opacity="0.4"/>
                        <path d="M85 42 L91 42 M88 39 L88 45" stroke="white" stroke-opacity="0.85" stroke-width="2" stroke-linecap="round"/>
                        <path d="M24 68 L116 68 L108 78 L32 78 Z" fill="white" fill-opacity="0.75"/>
                        <rect x="62" y="78" width="16" height="3" rx="1.5" fill="white" fill-opacity="0.45"/>
                    </g>
                    <g class="lms-motif-drift">
                        <rect x="100" y="22" width="18" height="14" rx="3" fill="white" fill-opacity="0.3"/>
                    </g>
                </svg>
                @break

            @default
                <svg class="lms-banner-svg" viewBox="0 0 140 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g class="lms-motif-float lms-motif-float--delay">
                        <path d="M18 58 L58 52 L58 78 L18 84 Z" fill="white" fill-opacity="0.28"/>
                        <path d="M26 48 L66 42 L66 68 L26 74 Z" fill="white" fill-opacity="0.38"/>
                    </g>
                    <g class="lms-motif-float">
                        <path d="M44 32 L44 76 Q44 80 48 80 L92 74 L98 70 L98 36 Q98 30 92 28 L48 28 Q44 28 44 32 Z" fill="white" fill-opacity="0.95"/>
                        <path d="M48 32 L48 74" stroke="white" stroke-opacity="0.35" stroke-width="2"/>
                        <rect x="54" y="38" width="36" height="4" rx="2" fill="white" fill-opacity="0.45"/>
                        <rect x="54" y="46" width="28" height="3" rx="1.5" fill="white" fill-opacity="0.35"/>
                        <rect x="54" y="52" width="32" height="3" rx="1.5" fill="white" fill-opacity="0.3"/>
                        <rect x="54" y="58" width="24" height="3" rx="1.5" fill="white" fill-opacity="0.28"/>
                    </g>
                    <g class="lms-motif-drift">
                        <path d="M100 24 L118 18 L118 40 L100 46 Z" fill="white" fill-opacity="0.4"/>
                        <path d="M104 28 L114 24 L114 36 L104 40 Z" fill="white" fill-opacity="0.55"/>
                    </g>
                </svg>
        @endswitch
    </div>

    <span class="lms-banner-shine"></span>
</div>
