@props([
    'title',
    'subtitle' => null,
    'badge' => null,
])

<div {{ $attributes->merge(['class' => 'dash-hero dash-hero-banner mb-6']) }}>
    <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between lg:gap-8">
        <div class="min-w-0 space-y-2.5">
            @if ($badge)
                <span class="inline-flex rounded-md bg-violet-600/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-violet-700 ring-1 ring-violet-200/80">
                    {{ $badge }}
                </span>
            @endif
            <h1 class="text-xl font-bold tracking-tight text-isarva-heading sm:text-2xl">{{ $title }}</h1>
            @if ($subtitle)
                <p class="max-w-2xl text-sm leading-relaxed text-isarva-muted sm:text-[15px]">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex shrink-0 flex-wrap gap-3 lg:pl-4">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
