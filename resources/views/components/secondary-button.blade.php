<button {{ $attributes->merge(['type' => 'button', 'class' => 'lms-btn-secondary disabled:opacity-50']) }}>
    {{ $slot }}
</button>
