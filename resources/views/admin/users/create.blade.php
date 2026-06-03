@extends('layouts.lms')

@section('title', 'Add user')
@section('heading', 'Add user')
@section('subheading', 'Create student or lecturer access')

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" class="lms-card w-full space-y-5">
    @csrf
    <div>
        <label class="block text-sm font-semibold text-slate-700">Full name</label>
        <input type="text" name="name" value="{{ old('name') }}" class="lms-input mt-1" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="lms-input mt-1" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700">Role</label>
        <select name="role" class="lms-input mt-1" required>
            @foreach (\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700">Student ID (for students)</label>
        <input type="text" name="student_id" value="{{ old('student_id') }}" class="lms-input mt-1" placeholder="e.g. DS2024001">
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700">Password</label>
        <input type="password" name="password" class="lms-input mt-1" required>
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700">Confirm password</label>
        <input type="password" name="password_confirmation" class="lms-input mt-1" required>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="lms-btn-primary">Create account</button>
        <a href="{{ route('admin.users.index') }}" class="lms-btn-secondary">Cancel</a>
    </div>
</form>
@endsection
