@extends('layouts.lms')

@section('title', 'Notifications')
@section('page_title', 'Notifications')

@section('content')
<div class="lms-page-stack">
    <div class="lms-page-actions">
        <p class="text-sm text-isarva-muted">{{ $notifications->total() }} notifications</p>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="lms-btn-secondary">Mark all read</button>
        </form>
    </div>

    <div class="lms-notify-page-list">
        @forelse ($notifications as $notification)
            @php $data = $notification->data; @endphp
            <article @class(['lms-notify-page-item', 'is-unread' => $notification->read_at === null])>
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full text-left">
                        <span class="lms-notify-page-type">
                            @switch($data['type'] ?? '')
                                @case('assignment_published') New assignment @break
                                @case('submission_received') New submission @break
                                @case('grade_posted') Grade posted @break
                                @case('assignment_due') Due soon @break
                                @default Update
                            @endswitch
                        </span>
                        <span class="lms-notify-page-title">
                            {{ $data['title'] ?? $data['assignment_title'] ?? $data['student_name'] ?? 'View details' }}
                        </span>
                        <span class="lms-notify-page-time">{{ $notification->created_at->diffForHumans() }}</span>
                    </button>
                </form>
            </article>
        @empty
            <x-lms.empty-state title="No notifications" message="You're all caught up." variant="laptop" />
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
