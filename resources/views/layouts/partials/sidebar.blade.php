@php
    $user = auth()->user();
    $role = $user->role?->value ?? 'student';
    $menuItems = collect(config('lms-menu.items', []))
        ->filter(fn ($item) => in_array($role, $item['roles'] ?? [], true));
@endphp

<aside class="lms-sidebar" :class="{ 'is-open': sidebarOpen }" @keydown.escape.window="sidebarOpen = false">
    <div class="lms-sidebar-inner">
        <div class="flex items-center justify-end lg:hidden">
            <button type="button" @click="sidebarOpen = false" class="lms-sidebar-close-btn" aria-label="Close menu">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <a href="{{ route('dashboard') }}" class="lms-sidebar-brand" @click="sidebarOpen = false">
            <x-isarva-logo variant="sidebar" />
            <span class="lms-sidebar-appname">{{ config('app.name') }}</span>
        </a>

        <nav class="lms-sidebar-nav" aria-label="Main menu">
            @foreach ($menuItems as $item)
                @if (! empty($item['coming_soon']))
                    <span class="lms-sidebar-link is-soon" title="Coming soon">
                        @include('layouts.partials.nav-icon', ['name' => $item['icon']])
                        <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
                        <span class="lms-soon-badge">Soon</span>
                    </span>
                @else
                    @php
                        $active = match ($item['route'] ?? '') {
                            'dashboard' => request()->routeIs('dashboard'),
                            'courses.index' => request()->routeIs('courses.*') && ! request()->routeIs('assignments.*', 'submissions.*'),
                            'assignments.index' => request()->routeIs('assignments.*') && ! request()->routeIs('assignments.submit', 'assignments.submissions.*'),
                            'submissions.index' => request()->routeIs('submissions.*'),
                            'admin.users.index' => request()->routeIs('admin.users.*'),
                            'profile.edit' => request()->routeIs('profile.*'),
                            default => isset($item['route']) && request()->routeIs($item['route'].'*'),
                        };
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       @if (! empty($item['new_tab'])) target="_blank" rel="noopener noreferrer" @else @click="sidebarOpen = false" @endif
                       class="lms-sidebar-link {{ $active ? 'is-active' : '' }}">
                        @include('layouts.partials.nav-icon', ['name' => $item['icon']])
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <x-lms.sidebar-user :user="$user" />

        <div class="lms-sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="lms-sidebar-link is-logout w-full">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
