@extends('layouts.lms')

@section('title', 'Import students')
@section('page_title', 'Import students')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="users" title="Import student accounts" subtitle="Paste student email addresses — we will create login IDs, student IDs, and passwords automatically.">
        <a href="{{ route('admin.users.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Back to users</a>
    </x-lms.module-hero>

    <form method="POST" action="{{ route('admin.users.bulk-import.store') }}" class="lms-form-card">
        @csrf
        <div class="lms-form-header">
            <h2 class="lms-form-title">Student email list</h2>
            <p class="lms-form-desc">Enter one email per line, or separate with commas. Each student gets a unique student ID and a generated password shown on the next screen.</p>
        </div>

        <div class="lms-form-field">
            <label for="emails" class="lms-field-label">Email addresses</label>
            <textarea id="emails" name="emails" rows="12" class="lms-field-input mt-1.5 font-mono text-sm" required placeholder="sai.kiran@university.edu&#10;priya.sharma@university.edu&#10;student3@university.edu">{{ old('emails') }}</textarea>
            @error('emails')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lms-form-actions">
            <button type="submit" class="lms-btn-primary">Create accounts</button>
            <a href="{{ route('admin.users.index') }}" class="lms-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
