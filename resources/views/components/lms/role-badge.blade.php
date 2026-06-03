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
        'admin' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
        'lecturer' => 'bg-violet-100 text-violet-700 ring-1 ring-violet-200',
        'student' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
        default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'lms-role-badge '.$toneClasses]) }}>
    {{ $label }}
</span>
