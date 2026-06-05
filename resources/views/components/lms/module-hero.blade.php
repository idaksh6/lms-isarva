@props([
    'module' => 'assignments',
    'title' => '',
    'subtitle' => '',
    'variant' => 'assignment',
])

@php
    use App\Support\ModuleHeroImage;
    $imageUrl = ModuleHeroImage::url($module);
    $imageAlt = ModuleHeroImage::alt($module);
@endphp

<section {{ $attributes->merge(['class' => 'lms-module-hero']) }}>
    <div class="lms-module-hero-media" aria-hidden="true">
        <img src="{{ $imageUrl }}" alt="" class="lms-hero-photo" loading="lazy">
        <div class="lms-hero-theme-overlay" aria-hidden="true"></div>
        <div class="lms-module-hero-art">
            <x-lms.illustration :variant="$variant" class="lms-module-hero-illustration" />
        </div>
    </div>
    <div class="lms-module-hero-content">
        <p class="lms-hero-eyebrow">{{ config('app.name') }}</p>
        <h1 class="lms-hero-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="lms-hero-subtitle">{{ $subtitle }}</p>
        @endif
        @if (trim($slot ?? '') !== '')
            <div class="lms-module-hero-stats">{{ $slot }}</div>
        @endif
    </div>
</section>
