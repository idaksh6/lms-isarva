<x-guest-layout>
    <div>
        <div class="hidden lg:block">
            <h2 class="text-2xl font-bold tracking-tight text-isarva-heading">Welcome back</h2>
            <p class="mt-1.5 text-[15px] text-isarva-muted">Sign in to access {{ config('app.name') }}</p>
        </div>

        <x-auth-session-status class="mt-5 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-isarva-heading">Email address</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-isarva-muted">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                        </svg>
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           autocomplete="username" class="lms-input lms-input--icon" placeholder="name@university.edu">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-isarva-heading">Password</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-isarva-muted">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                    </span>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="lms-input lms-input--icon" placeholder="Enter your password">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                    <input id="remember_me" type="checkbox" name="remember"
                           class="rounded border-isarva-border text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-isarva-muted">Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit" class="isarva-btn isarva-btn-block group">
                Sign in to LMS
                <span class="transition group-hover:translate-x-0.5" aria-hidden="true">→</span>
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-isarva-muted">
            Need access? Contact your programme administrator.
        </p>

        <x-demo-credentials />
    </div>
</x-guest-layout>
