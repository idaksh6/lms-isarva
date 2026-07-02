@extends('layouts.lms')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="users" title="People & accounts" subtitle="Manage administrators, lecturers, and students across your LMS.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ $stats['total'] }}</strong> total</span>
            <span class="lms-stat-chip"><strong>{{ $stats['active'] }}</strong> active</span>
            <span class="lms-stat-chip"><strong>{{ $stats['students'] }}</strong> students</span>
            <a href="{{ route('admin.users.bulk-import') }}" class="lms-btn-secondary lms-btn-secondary--xs">Import students</a>
            <a href="{{ route('admin.users.create') }}" class="lms-btn-primary lms-btn-primary--xs">Add user</a>
        </div>
    </x-lms.module-hero>

    <form method="GET" class="lms-filter-bar">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name, email, student ID..." class="lms-field-input lms-filter-search">
        <div class="lms-filter-select-wrap">
            <select name="role" class="lms-field-input lms-filter-select">
                <option value="">All roles</option>
                @foreach (\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="lms-filter-select-wrap">
            <select name="status" class="lms-field-input lms-filter-select">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Filter</button>
    </form>

    <section class="lms-user-list-section">
        <div class="lms-user-list-header">
            <h2 class="lms-user-list-title">All accounts</h2>
            <span class="lms-panel-count">{{ $users->total() }} shown</span>
        </div>

        <div class="lms-user-row-list">
            @forelse ($users as $user)
                <x-lms.user-list-item :user="$user" />
            @empty
                <x-lms.empty-state title="No users found" message="Try a different search or add a new account." variant="sky">
                    <a href="{{ route('admin.users.create') }}" class="lms-btn-primary">Add user</a>
                </x-lms.empty-state>
            @endforelse
        </div>
    </section>

    @if ($users->hasPages())
        <div>{{ $users->links() }}</div>
    @endif
</div>
@endsection
