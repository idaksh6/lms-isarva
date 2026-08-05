<x-guest-layout>
    <div class="auth-login">
        <header class="auth-login-header">
            <h2 class="auth-login-title">Welcome back</h2>
            <p class="auth-login-subtitle">Sign in to your learning workspace</p>
        </header>

        <x-auth-session-status class="auth-login-status" :status="session('status')" />

        <form method="POST" action="/login/" class="auth-login-form">
            @csrf

            <div class="auth-field">
                <x-input-label for="email" :value="__('Work email')" class="auth-label" />
                <x-text-input
                    id="email"
                    class="auth-input"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@company.com"
                />
                <x-input-error :messages="$errors->get('email')" class="auth-error" />
            </div>

            <div class="auth-field">
                <x-input-label for="password" :value="__('Password')" class="auth-label" />
                <x-text-input
                    id="password"
                    class="auth-input"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />
                <x-input-error :messages="$errors->get('password')" class="auth-error" />
            </div>

            <div class="auth-login-options">
                <label for="remember_me" class="auth-remember">
                    <input id="remember_me" type="checkbox" class="auth-checkbox" name="remember">
                    <span>{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="auth-forgot" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="lms-btn-primary auth-submit">
                <span>{{ __('Sign in') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                </svg>
            </button>
        </form>

    </div>
</x-guest-layout>
