<section>
    <header class="lms-form-header">
        <h2 class="lms-form-title">{{ __('Profile Information') }}</h2>
        <p class="lms-form-desc">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="lms-field-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="lms-field-input mt-1.5"
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="lms-field-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="lms-field-input mt-1.5"
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2.5">
                    <p class="text-sm text-brand-900">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" type="submit"
                                class="font-semibold text-brand-700 underline hover:text-brand-800">
                            {{ __('Re-send verification email') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-brand-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-isarva-border pt-5">
            <x-primary-button>{{ __('Save changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-medium text-brand-700"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
