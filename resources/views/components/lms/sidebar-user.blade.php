@props(['user'])

@php
    use App\Enums\UserRole;

    $nameParts = preg_split('/\s+/', trim($user->name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = collect($nameParts)
        ->take(2)
        ->map(fn (string $part) => strtoupper(substr($part, 0, 1)))
        ->join('');

    if ($initials === '') {
        $initials = strtoupper(substr($user->email, 0, 2));
    }

    $roleLabel = $user->role instanceof UserRole
        ? $user->role->label()
        : ucfirst((string) $user->role);

    $photoUrl = $user->profile_photo_url ?? null;
@endphp

<div class="lms-sidebar-profile">
    <a href="{{ route('profile.edit') }}" class="lms-sidebar-profile-card group" @click="sidebarOpen = false">
        <span class="lms-sidebar-profile-avatar" aria-hidden="true">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="" class="h-full w-full object-cover">
            @else
                <span class="lms-sidebar-profile-initials">{{ $initials }}</span>
            @endif
        </span>

        <span class="lms-sidebar-profile-meta">
            <span class="lms-sidebar-profile-name">{{ $user->name }}</span>
            <span class="lms-sidebar-profile-role">{{ $roleLabel }}</span>
            <span class="lms-sidebar-profile-email">{{ $user->email }}</span>
            @if ($user->student_id)
                <span class="lms-sidebar-profile-id">ID {{ $user->student_id }}</span>
            @endif
        </span>

        <svg class="lms-sidebar-profile-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
    </a>
</div>
