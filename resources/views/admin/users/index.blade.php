@extends('layouts.lms')

@section('title', 'Users')
@section('page_title', 'Users')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-isarva-muted">{{ $users->total() }} accounts in the system</p>
    <a href="{{ route('admin.users.create') }}" class="lms-btn-primary">Add user <span aria-hidden="true">→</span></a>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @forelse ($users as $user)
        <article class="lms-user-card">
            <div class="lms-user-card-visual">
                <x-lms.user-illustration :role="$user->role->value" />
            </div>
            <div class="lms-user-card-body">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="lms-user-card-name">{{ $user->name }}</h3>
                    <x-lms.role-badge :role="$user->role" />
                </div>
                <p class="lms-user-card-email">{{ $user->email }}</p>
                @if ($user->student_id)
                    <p class="text-xs font-medium text-isarva-muted">ID: {{ $user->student_id }}</p>
                @endif
            </div>
            <div class="lms-user-card-actions">
                <a href="{{ route('admin.users.edit', $user) }}" class="lms-user-card-btn">Edit user</a>
            </div>
        </article>
    @empty
        <div class="sm:col-span-2 lg:col-span-4">
            <x-lms.empty-state
                title="No users yet"
                message="Add lecturers and students to get your programme running."
                variant="sky"
            >
                <a href="{{ route('admin.users.create') }}" class="lms-btn-primary">Add user <span aria-hidden="true">→</span></a>
            </x-lms.empty-state>
        </div>
    @endforelse
</div>

@if ($users->hasPages())
    <div class="mt-6">{{ $users->links() }}</div>
@endif
@endsection
