@props(['role'])

@php
    use App\Enums\UserRole;

    $value = $role instanceof UserRole ? $role->value : (string) $role;
    $label = $role instanceof UserRole ? $role->label() : match ($value) {
        'admin' => 'Administrator',
        'lecturer' => 'Lecturer',
        'student' => 'Student',
        default => ucfirst($value),
    };

    $toneClasses = match ($value) {
        'admin' => 'bg-brand-100 text-brand-800 ring-1 ring-brand-200',
        'lecturer' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        'student' => 'bg-brand-50 text-brand-700 ring-1 ring-brand-100',
        default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'lms-role-badge '.$toneClasses]) }}>
    {{ $label }}
</span>
