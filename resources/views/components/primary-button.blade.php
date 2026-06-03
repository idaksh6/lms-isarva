<button {{ $attributes->merge(['type' => 'submit', 'class' => 'lms-btn-primary']) }}>
    {{ $slot }}
</button>
