@extends('layouts.lms')

@section('title', 'Notifications')
@section('page_title', 'Notifications')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero
        module="announcements"
        title="Notifications"
        subtitle="{{ $notifications->total() }} notifications · stay on top of course updates and deadlines."
    >
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Mark all read</button>
        </form>
    </x-lms.module-hero>

    <section class="lms-notify-page-shell">
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
                                    @case('announcement_published') New announcement @break
                                    @case('assignment_published') New assignment @break
                                    @case('assessment_published') New assessment @break
                                    @case('submission_received') New submission @break
                                    @case('grade_posted') Grade posted @break
                                    @case('assignment_due') Due soon @break
                                    @case('mentoring_assigned') Mentor assigned @break
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
                <div class="p-6">
                    <x-lms.empty-state title="No notifications" message="You're all caught up." variant="laptop" />
                </div>
            @endforelse
        </div>
    </section>

    @if ($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
