<header class="lms-topbar">
    @php
        $topbarTitle = match (true) {
            $__env->hasSection('page_title') => trim($__env->yieldContent('page_title')),
            request()->routeIs('dashboard') => e('Dashboard'),
            $__env->hasSection('title') => trim($__env->yieldContent('title')),
            default => e(config('app.name')),
        };
    @endphp
    <div class="lms-topbar-row">
        <div class="lms-topbar-left">
            <button type="button"
                    @click="sidebarOpen = true"
                    class="lms-icon-btn lg:hidden"
                    aria-label="Open menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <h1 class="lms-page-title" title="{!! $topbarTitle !!}">{!! $topbarTitle !!}</h1>
        </div>

        <form action="{{ route('courses.index') }}" method="GET" class="lms-search">
            <span class="lms-search-icon">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search courses..." class="lms-search-input">
        </form>

        <div class="lms-topbar-right">
            <a href="{{ route('courses.index') }}" class="lms-icon-btn md:hidden" title="Search courses">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </a>
            <div class="topbar-datetime hidden sm:flex"
                 x-data="{
                    h: '00', m: '00', s: '00', dateStr: '',
                    tick() {
                        const d = new Date();
                        this.h = String(d.getHours()).padStart(2, '0');
                        this.m = String(d.getMinutes()).padStart(2, '0');
                        this.s = String(d.getSeconds()).padStart(2, '0');
                        this.dateStr = d.toLocaleDateString(undefined, {
                            weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
                        });
                    }
                 }"
                 x-init="tick(); setInterval(() => tick(), 1000)">
                <span class="topbar-date" x-text="dateStr"></span>
                <span class="topbar-clock-time text-xs">
                    <span x-text="h"></span>:<span x-text="m"></span>:<span class="topbar-clock-seconds" x-text="s"></span>
                </span>
            </div>

            <x-lms.theme-picker :themes="$lmsThemes" :current="$lmsTheme['key']" />

            <x-lms.notification-bell />

            <a href="{{ route('courses.index') }}" class="lms-icon-btn hidden md:inline-flex" title="Courses">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </a>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button type="button" class="lms-user-chip">
                        <span class="lms-user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                        <span class="lms-user-name">{{ auth()->user()->name }}</span>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <div class="border-b border-gray-100 px-4 py-2 text-xs text-gray-500">{{ auth()->user()->role->label() }}</div>
                    <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" class="lms-dropdown-logout" onclick="event.preventDefault(); this.closest('form').submit();">
                            Log out
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>
