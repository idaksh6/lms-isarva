@props(['user'])

@php
    $nameParts = preg_split('/\s+/', trim($user->name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = collect($nameParts)
        ->take(2)
        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
        ->join('');

    if ($initials === '') {
        $initials = strtoupper(substr($user->email, 0, 2));
    }

    $photoUrl = $user->profile_photo_url ?? null;
@endphp

<div class="lms-sidebar-profile">
    <a href="{{ route('profile.edit') }}" class="lms-sidebar-profile-card" @click="sidebarOpen = false">
        <span class="lms-sidebar-profile-avatar" aria-hidden="true">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="" class="h-full w-full object-cover">
            @else
                <span class="lms-sidebar-profile-initials">{{ $initials }}</span>
                <svg class="lms-sidebar-profile-default-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 19.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
            @endif
        </span>

        <span class="lms-sidebar-profile-meta">
            <span class="lms-sidebar-profile-name">{{ $user->name }}</span>
            <x-lms.role-badge :role="$user->role" />
            <span class="lms-sidebar-profile-email">{{ $user->email }}</span>
            @if ($user->student_id)
                <span class="lms-sidebar-profile-id">ID: {{ $user->student_id }}</span>
            @endif
        </span>
    </a>
</div>
