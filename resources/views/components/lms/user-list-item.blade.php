@props(['user'])

@php
    $roleTone = match ($user->role->value) {
        'admin' => 'lms-user-row-card--admin',
        'lecturer' => 'lms-user-row-card--lecturer',
        'student' => 'lms-user-row-card--student',
        default => '',
    };

    $avatarTone = match ($user->role->value) {
        'admin' => 'lms-directory-avatar--admin',
        'lecturer' => 'lms-directory-avatar--lecturer',
        'student' => 'lms-directory-avatar--student',
        default => '',
    };
@endphp

<article @class(['lms-user-row-card', $roleTone, 'is-inactive' => ! $user->is_active])>
    <div class="lms-user-row-card-identity">
        <span @class(['lms-directory-avatar', $avatarTone])>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
        <div class="min-w-0">
            <p class="truncate font-semibold text-slate-900">{{ $user->name }}</p>
            <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
        </div>
    </div>

    <div class="lms-user-row-card-details">
        <div class="lms-user-detail-box">
            <span class="lms-user-detail-label">Role</span>
            <x-lms.role-badge :role="$user->role" class="mt-0.5" />
        </div>

        <div class="lms-user-detail-box">
            <span class="lms-user-detail-label">Student ID</span>
            @if ($user->student_id)
                <span class="lms-user-detail-value lms-user-detail-value--mono">{{ $user->student_id }}</span>
            @else
                <span class="lms-user-detail-value lms-user-detail-value--muted">Not a student</span>
            @endif
        </div>

        <div class="lms-user-detail-box">
            <span class="lms-user-detail-label">Account status</span>
            @if ($user->is_active)
                <span class="lms-user-detail-value lms-user-detail-value--active">Active</span>
            @else
                <span class="lms-user-detail-value lms-user-detail-value--inactive">Inactive</span>
            @endif
        </div>
    </div>

    <div class="lms-user-row-card-actions">
        <span class="lms-user-detail-label lg:sr-only">Actions</span>
        <div class="lms-user-action-group">
            <a href="{{ route('admin.users.edit', $user) }}" class="lms-btn-primary lms-btn-primary--xs">Edit</a>
            @if ($user->id !== auth()->id())
                @if ($user->is_active)
                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Deactivate</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Reactivate</button>
                    </form>
                @endif
            @endif
        </div>
    </div>
</article>
