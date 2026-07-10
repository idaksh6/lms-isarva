@extends('layouts.lms')

@section('title', 'Add user')
@section('page_title', 'Add user')

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('admin.users.index') }}" class="lms-btn-back">← Back to users</a>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="lms-form-card">
        @csrf

        <div class="lms-form-header">
            <h2 class="lms-form-title">New user account</h2>
            <p class="lms-form-desc">Create student, lecturer, or administrator access.</p>
        </div>

        <div class="lms-form-field">
            <label for="name" class="lms-field-label">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="email" class="lms-field-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="role" class="lms-field-label">Role</label>
            <select id="role" name="role" class="lms-field-input mt-1.5" required>
                @foreach (\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="student_id" class="lms-field-label">Student ID (for students)</label>
            <input id="student_id" type="text" name="student_id" value="{{ old('student_id') }}" class="lms-field-input mt-1.5" placeholder="e.g. DS2024001">
            <x-input-error :messages="$errors->get('student_id')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="password" class="lms-field-label">Password</label>
            <input id="password" type="password" name="password" class="lms-field-input mt-1.5" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="password_confirmation" class="lms-field-label">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="lms-field-input mt-1.5" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Create account</button>
            <a href="{{ route('admin.users.index') }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
