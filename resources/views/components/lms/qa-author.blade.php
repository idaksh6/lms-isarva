@props([
    'user',
    'postedAt',
    'variant' => 'default',
])

@php
    $initials = strtoupper(substr($user->name, 0, 2));
    $roleLabel = $user->role?->label() ?? 'User';
@endphp

<div {{ $attributes->merge(['class' => 'corp-qa-author corp-qa-author--'.$variant]) }}>
    <span class="corp-qa-author-avatar" aria-hidden="true">{{ $initials }}</span>
    <div class="corp-qa-author-meta">
        <p class="corp-qa-author-name">{{ $user->name }}</p>
        <p class="corp-qa-author-detail">
            <span class="corp-qa-role-badge">{{ $roleLabel }}</span>
            <span class="corp-qa-author-sep" aria-hidden="true">·</span>
            <time datetime="{{ $postedAt->toIso8601String() }}">{{ $postedAt->format('M j, Y') }} at {{ $postedAt->format('g:i A') }}</time>
        </p>
    </div>
</div>
