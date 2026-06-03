@extends('layouts.lms')

@section('title', 'Edit ' . $user->name)
@section('page_title', 'Edit user')

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <a href="{{ route('admin.users.index') }}" class="lms-btn-back">← Back to users</a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="lms-form-card">
        @csrf
        @method('PATCH')

        <div class="lms-form-header">
            <h2 class="lms-form-title">{{ $user->name }}</h2>
            <p class="lms-form-desc">Update account details. Leave password blank to keep the current one.</p>
        </div>

        <div class="lms-form-field">
            <label for="name" class="lms-field-label">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="email" class="lms-field-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="lms-field-input mt-1.5" required>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="role" class="lms-field-label">Role</label>
            <select id="role" name="role" class="lms-field-input mt-1.5" required>
                @foreach (\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $user->role->value) === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="student_id" class="lms-field-label">Student ID (for students)</label>
            <input id="student_id" type="text" name="student_id" value="{{ old('student_id', $user->student_id) }}" class="lms-field-input mt-1.5" placeholder="e.g. DS2024001">
            <x-input-error :messages="$errors->get('student_id')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="password" class="lms-field-label">New password</label>
            <input id="password" type="password" name="password" class="lms-field-input mt-1.5" autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="lms-form-field">
            <label for="password_confirmation" class="lms-field-label">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="lms-field-input mt-1.5" autocomplete="new-password">
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Save changes</button>
            <a href="{{ route('admin.users.index') }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
