@extends('layouts.lms')

@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="settings" title="Settings" subtitle="Manage notifications and account preferences." />

    <div class="lms-settings-grid">
        <form method="POST" action="{{ route('settings.update') }}" class="lms-form-card">
            @csrf
            @method('PATCH')
            <div class="lms-form-header">
                <h2 class="lms-form-title">Notifications</h2>
                <p class="lms-form-desc">Choose whether to receive email alerts for assignments, submissions, and grades.</p>
            </div>
            <label class="lms-form-check">
                <input type="checkbox" name="email_notifications" value="1" @checked(old('email_notifications', $user->email_notifications))>
                <span class="text-sm font-medium text-slate-700">Send email notifications</span>
            </label>
            <p class="text-xs text-slate-500">In-app notifications always appear in the bell icon. Emails require MAIL settings on the server.</p>
            <div class="lms-form-actions">
                <button type="submit" class="lms-btn-primary">Save settings</button>
            </div>
        </form>

        <section class="lms-panel">
            <div class="lms-panel-header"><h2 class="lms-panel-title">Your account</h2></div>
            <div class="lms-panel-body">
                <dl class="lms-meta-list">
                    <div class="lms-meta-list-row"><dt>Name</dt><dd>{{ $user->name }}</dd></div>
                    <div class="lms-meta-list-row"><dt>Email</dt><dd>{{ $user->email }}</dd></div>
                    <div class="lms-meta-list-row"><dt>Role</dt><dd>{{ $user->role->label() }}</dd></div>
                </dl>
                <a href="{{ route('profile.edit') }}" class="lms-btn-secondary mt-4">Edit profile</a>
            </div>
        </section>
    </div>
</div>
@endsection
