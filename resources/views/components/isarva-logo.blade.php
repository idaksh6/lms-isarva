@props([
    'class' => 'h-12',
    'variant' => 'default', // default | large | footer | sidebar
])

@php
    $sizes = match ($variant) {
        'large' => 'h-14 sm:h-16 lg:h-[4.25rem] max-w-[min(100%,280px)]',
        'sidebar' => 'h-11 w-auto max-w-[180px]',
        'footer' => 'h-10 max-w-[200px]',
        default => $class.' w-auto max-w-[220px]',
    };
@endphp

<img src="{{ asset('images/isarva-logo.avif') }}" alt="Isarva Infotech" {{ $attributes->merge(['class' => ($variant === 'default' ? $sizes : $sizes).' block w-auto object-contain object-left']) }}>
