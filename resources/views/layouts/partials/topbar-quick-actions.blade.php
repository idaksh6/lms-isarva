@php
    $user = auth()->user();
    $quickLinks = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'active' => 'from-brand-500 to-brand-600', 'show' => true],
        ['label' => 'Courses', 'route' => 'courses.index', 'icon' => 'book', 'active' => 'from-brand-500 to-brand-600', 'show' => true],
        ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users', 'active' => 'from-brand-600 to-brand-700', 'show' => $user->isAdmin()],
        ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'user', 'active' => 'from-brand-500 to-brand-700', 'show' => true],
    ];
@endphp

<div class="flex items-center gap-2">
    @foreach ($quickLinks as $link)
        @if ($link['show'])
            @php $isActive = request()->routeIs($link['route'].'*'); @endphp
            <a href="{{ route($link['route']) }}"
               title="{{ $link['label'] }}"
               class="flex h-8 w-8 items-center justify-center rounded-lg transition {{ $isActive ? 'bg-gradient-to-br '.$link['active'].' text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                @include('layouts.partials.nav-icon', ['name' => $link['icon']])
            </a>
        @endif
    @endforeach
</div>
