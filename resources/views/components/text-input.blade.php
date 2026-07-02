@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'lms-field-input']) }}>
