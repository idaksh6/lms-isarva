@props([
    'number',
    'title',
    'body',
    'bullets' => [],
    'tip' => null,
    'mock' => null,
    'reverse' => false,
])

<article
    {{ $attributes->merge(['class' => 'ug-step'.($reverse ? ' ug-step--reverse' : '')]) }}
    style="--ug-step-i: {{ (int) $number - 1 }}"
>
    <div class="ug-step-copy">
        <div class="ug-step-badge" aria-hidden="true">
            <span class="ug-step-number">{{ str_pad((string) $number, 2, '0', STR_PAD_LEFT) }}</span>
        </div>
        <h3 class="ug-step-title">{{ $title }}</h3>
        <p class="ug-step-body">{{ $body }}</p>
        @if (count($bullets))
            <ul class="ug-step-bullets">
                @foreach ($bullets as $bullet)
                    <li>{{ $bullet }}</li>
                @endforeach
            </ul>
        @endif
        @if ($tip)
            <div class="ug-tip">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                </svg>
                <span>{{ $tip }}</span>
            </div>
        @endif
    </div>

    @if ($mock)
        <div class="ug-step-visual">
            <x-lms.user-guide.mock :type="$mock" />
        </div>
    @endif
</article>
