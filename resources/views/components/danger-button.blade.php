<button {{ $attributes->merge(['type' => 'submit', 'class' => 'lms-btn-danger disabled:opacity-50']) }}>
    {{ $slot }}
</button>
