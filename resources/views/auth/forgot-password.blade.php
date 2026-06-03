<x-guest-layout>
    <div>
        <h2 class="text-xl font-bold text-slate-800">Reset password</h2>
        <p class="mt-1 text-sm text-slate-500">We will email you a reset link</p>

        <x-auth-session-status class="mt-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="lms-input mt-1">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>
            <button type="submit" class="isarva-btn isarva-btn-block">Send reset link</button>
        </form>

        <p class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">← Back to sign in</a>
        </p>
    </div>
</x-guest-layout>
