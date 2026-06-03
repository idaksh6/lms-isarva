<section>
    <header class="lms-form-header">
        <h2 class="lms-form-title">{{ __('Update Password') }}</h2>
        <p class="lms-form-desc">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="lms-field-label">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password"
                   class="lms-field-input mt-1.5" autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password" class="lms-field-label">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password"
                   class="lms-field-input mt-1.5" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="lms-field-label">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                   class="lms-field-input mt-1.5" autocomplete="new-password">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="flex flex-wrap items-center gap-3 border-t border-isarva-border pt-5">
            <x-primary-button>{{ __('Update password') }}</x-primary-button>

            @if (session('status') === 'password-updated')
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
