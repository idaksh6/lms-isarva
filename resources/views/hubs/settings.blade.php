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

    @if ($aiSettings)
        <form method="POST" action="{{ route('settings.ai') }}" class="lms-form-card mt-4">
            @csrf
            @method('PATCH')

            <div class="lms-form-header">
                <h2 class="lms-form-title">AI Teaching Copilot</h2>
                <p class="lms-form-desc">
                    Admin-only. API keys are stored encrypted in the database (not in <code>.env</code>).
                    Source: <strong>{{ $aiSettings['source'] === 'database' ? 'Database' : 'Environment (.env)' }}</strong>
                </p>
            </div>

            <label class="lms-form-check mb-4">
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $aiSettings['enabled']))>
                <span class="text-sm font-medium text-slate-700">Enable AI features</span>
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="lms-form-field">
                    <label for="ai-driver" class="lms-field-label">Driver</label>
                    <select id="ai-driver" name="driver" class="lms-field-input mt-1.5" required>
                        <option value="fake" @selected(old('driver', $aiSettings['driver']) === 'fake')>Fake (demo / no key)</option>
                        <option value="openai" @selected(old('driver', $aiSettings['driver']) === 'openai')>OpenAI-compatible API</option>
                    </select>
                    <x-input-error :messages="$errors->get('driver')" class="mt-1.5" />
                </div>

                <div class="lms-form-field">
                    <label for="ai-model" class="lms-field-label">Model</label>
                    <input id="ai-model" type="text" name="model" value="{{ old('model', $aiSettings['model']) }}" class="lms-field-input mt-1.5" required maxlength="120" placeholder="gpt-4o-mini">
                    <x-input-error :messages="$errors->get('model')" class="mt-1.5" />
                </div>
            </div>

            <div class="lms-form-field mt-4">
                <label for="ai-base-url" class="lms-field-label">API base URL</label>
                <input id="ai-base-url" type="url" name="base_url" value="{{ old('base_url', $aiSettings['base_url']) }}" class="lms-field-input mt-1.5" required maxlength="500" placeholder="https://api.openai.com/v1">
                <p class="mt-1.5 text-xs text-isarva-muted">Use any OpenAI-compatible endpoint (OpenAI, Azure OpenAI proxy, etc.).</p>
                <x-input-error :messages="$errors->get('base_url')" class="mt-1.5" />
            </div>

            <div class="lms-form-field mt-4">
                <label for="ai-api-key" class="lms-field-label">API key</label>
                <input id="ai-api-key" type="password" name="api_key" value="" class="lms-field-input mt-1.5" maxlength="500" autocomplete="new-password" placeholder="{{ $aiSettings['api_key_set'] ? 'Leave blank to keep current key' : 'sk-…' }}">
                @if ($aiSettings['api_key_set'])
                    <p class="mt-1.5 text-xs text-isarva-muted">Current key: <span class="font-mono">{{ $aiSettings['api_key_hint'] }}</span></p>
                @else
                    <p class="mt-1.5 text-xs text-isarva-muted">No API key stored yet.</p>
                @endif
                <x-input-error :messages="$errors->get('api_key')" class="mt-1.5" />
            </div>

            @if ($aiSettings['api_key_set'])
                <label class="lms-form-check mt-3">
                    <input type="checkbox" name="clear_api_key" value="1">
                    <span class="text-sm font-medium text-slate-700">Clear stored API key</span>
                </label>
            @endif

            <div class="lms-form-actions mt-6">
                <button type="submit" class="lms-btn-primary">Save AI settings</button>
            </div>
        </form>
    @endif
</div>
@endsection
