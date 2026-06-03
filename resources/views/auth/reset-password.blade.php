<x-guest-layout>
    <div>
        <h2 class="text-xl font-bold text-slate-800">New password</h2>
        <p class="mt-1 text-sm text-slate-500">Choose a strong password for your account</p>

        <form method="POST" action="{{ route('password.store') }}" class="mt-5 space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus class="lms-input mt-1">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="lms-input mt-1">
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wide text-slate-600">Confirm</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="lms-input mt-1">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>

            <button type="submit" class="isarva-btn isarva-btn-block">Update password</button>
        </form>
    </div>
</x-guest-layout>
