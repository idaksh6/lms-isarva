@props(['messages'])

@php
    $flattened = collect(\Illuminate\Support\Arr::wrap($messages))
        ->flatten()
        ->filter(fn ($message) => is_string($message) && $message !== '')
        ->unique()
        ->values()
        ->all();
@endphp

@if (count($flattened) > 0)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 space-y-1']) }}>
        @foreach ($flattened as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
