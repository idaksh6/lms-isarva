@extends('layouts.lms')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="settings" title="Settings" subtitle="Manage notifications, theme color, and account preferences." />

    <div class="lms-settings-grid">
        <form method="POST" action="{{ route('settings.update') }}" class="lms-form-card">
            @csrf
            @method('PATCH')

            <div class="lms-form-header">
                <h2 class="lms-form-title">Portal theme</h2>
                <p class="lms-form-desc">Each theme pairs a sidebar tone with a distinct button and link color. Swatch shows sidebar (left) and buttons (right).</p>
            </div>

            <div class="lms-theme-grid" role="radiogroup" aria-label="Portal theme color">
                @foreach ($themes as $key => $theme)
                    <label class="lms-theme-option">
                        <input
                            type="radio"
                            name="theme"
                            value="{{ $key }}"
                            class="lms-theme-option-input"
                            @checked(old('theme', $user->theme ?? \App\Support\LmsTheme::defaultKey()) === $key)
                        >
                        <span class="lms-theme-option-preview" style="background: {{ \App\Support\LmsTheme::previewSwatch($key) }}"></span>
                        <span class="lms-theme-option-copy">
                            <span class="lms-theme-option-name">{{ $theme['name'] }}</span>
                            <span class="lms-theme-option-desc">{{ $theme['description'] ?? '' }}</span>
                        </span>
                        <span class="lms-theme-option-check" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('theme')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="lms-form-header mt-8">
                <h2 class="lms-form-title">Notifications</h2>
                <p class="lms-form-desc">Choose whether to receive email alerts for assignments, submissions, and grades.</p>
            </div>
            <label class="lms-form-check">
                <input type="checkbox" name="email_notifications" value="1" @checked(old('email_notifications', $user->email_notifications))>
                <span class="text-sm font-medium text-slate-700">Send email notifications</span>
            </label>
            <p class="text-xs text-slate-500">In-app notifications always appear in the bell icon. Emails require MAIL settings on the server.</p>

            <div class="lms-form-actions">
                <button type="submit" class="lms-btn-primary">Save settings</button>
            </div>
        </form>

        <section class="lms-panel">
            <div class="lms-panel-header"><h2 class="lms-panel-title">Your account</h2></div>
            <div class="lms-panel-body">
                <dl class="lms-meta-list">
                    <div class="lms-meta-list-row"><dt>Name</dt><dd>{{ $user->name }}</dd></div>
                    <div class="lms-meta-list-row"><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                    <div class="lms-meta-list-row"><dt>Role</dt><dd>{{ $user->role->label() }}</dd></div>
                    <div class="lms-meta-list-row"><dt>Theme</dt><dd>{{ \App\Support\LmsTheme::resolve($user->theme)['name'] }}</dd></div>
                </dl>
                <a href="{{ route('profile.edit') }}" class="lms-btn-secondary mt-4">Edit profile</a>
            </div>
        </section>
    </div>
</div>
@endsection
