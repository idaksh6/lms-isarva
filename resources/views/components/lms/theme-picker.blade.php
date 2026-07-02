@props([
    'themes' => [],
    'current' => 'classic',
])

@php
    $themeOptions = collect($themes)->map(function ($theme, $key) {
        return [
            'key' => $key,
            'name' => $theme['name'],
            'preview' => \App\Support\LmsTheme::previewSwatch($key),
        ];
    })->values();
@endphp

<div
    class="lms-theme-picker"
    x-data="lmsThemePicker({
        current: @js($current),
        themes: @js(\App\Support\LmsTheme::clientPayload()),
        updateUrl: @js(route('settings.theme')),
        csrf: @js(csrf_token()),
    })"
    @keydown.escape.window="close()"
>
    <button
        type="button"
        class="lms-icon-btn lms-theme-picker-trigger"
        @click="toggle()"
        :aria-expanded="open"
        aria-haspopup="true"
        title="Change portal theme"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 006.16-6.16M9.53 16.122L13.38 20M9.53 16.122L5.68 20M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="sr-only">Change portal theme</span>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="close()"
        class="lms-theme-picker-menu"
        role="menu"
        aria-label="Portal theme colors"
    >
        <div class="lms-theme-picker-head">
            <p class="lms-theme-picker-title">Portal theme</p>
            <p class="lms-theme-picker-subtitle">Sidebar and button colors update instantly.</p>
        </div>

        <div class="lms-theme-picker-grid">
            @foreach ($themeOptions as $theme)
                <button
                    type="button"
                    class="lms-theme-picker-swatch"
                    :class="{ 'is-active': current === @js($theme['key']) }"
                    @click="pick(@js($theme['key']))"
                    :disabled="saving"
                    role="menuitem"
                    title="{{ $theme['name'] }}"
                >
                    <span class="lms-theme-picker-swatch-color" style="background: {{ $theme['preview'] }}"></span>
                    <span class="lms-theme-picker-swatch-name">{{ $theme['name'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="lms-theme-picker-foot">
            <a href="{{ route('settings.index') }}" class="lms-theme-picker-link">All settings</a>
        </div>
    </div>
</div>
