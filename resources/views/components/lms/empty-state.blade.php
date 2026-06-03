@props([
    'title' => 'Nothing here yet',
    'message' => '',
    'variant' => 'books',
])

<div {{ $attributes->merge(['class' => 'lms-empty-state']) }}>
    <div class="lms-empty-state-art">
        <div class="lms-course-card-cover mx-auto h-32 max-w-sm overflow-hidden rounded-2xl">
            <x-lms.illustration :variant="$variant" />
        </div>
    </div>
    <h3 class="mt-4 text-base font-bold text-isarva-heading">{{ $title }}</h3>
    @if ($message)
        <p class="mt-1 max-w-md text-sm text-isarva-muted">{{ $message }}</p>
    @endif
    @if (trim($slot ?? '') !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
