<section class="space-y-5">
    <header class="lms-form-header">
        <h2 class="lms-form-title">{{ __('Delete Account') }}</h2>
        <p class="lms-form-desc">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        type="button"
        class="lms-btn-danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete account') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-base font-bold text-isarva-heading">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-isarva-muted">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.') }}
            </p>

            <div class="mt-5">
                <label for="password" class="lms-field-label">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="lms-field-input mt-1.5"
                    placeholder="{{ __('Password') }}"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1.5" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <button type="button" class="lms-btn-secondary" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="lms-btn-danger">
                    {{ __('Delete account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
