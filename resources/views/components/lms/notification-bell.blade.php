@php
    $user = auth()->user();
    $unread = $user->unreadNotifications()->limit(6)->get();
    $unreadCount = $user->unreadNotifications()->count();
@endphp

<div class="lms-notify" x-data="{ open: false }" @click.outside="open = false">
    <button type="button"
            @class([
                'lms-icon-btn lms-notify-trigger',
                'has-unread' => $unreadCount > 0,
            ])
            @click="open = !open"
            aria-label="Notifications"
            :aria-expanded="open">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unreadCount > 0)
            <span class="lms-notify-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="lms-notify-panel"
         x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1 scale-95">
        <div class="lms-notify-panel-hero">
            <div>
                <p class="lms-notify-panel-eyebrow">Inbox</p>
                <h3 class="lms-notify-panel-title">Notifications</h3>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="lms-notify-mark-all">Mark all read</button>
                </form>
            @endif
        </div>

        <ul class="lms-notify-list">
            @forelse ($unread as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? 'update';
                    [$label, $iconClass, $iconPath] = match ($type) {
                        'announcement_published' => ['New announcement', 'is-announcement', 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
                        'assignment_published' => ['New assignment', 'is-assignment', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        'assessment_published' => ['New assessment', 'is-assignment', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'submission_received' => ['New submission', 'is-submission', 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'],
                        'grade_posted' => ['Grade posted', 'is-grade', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                        'assignment_due' => ['Due soon', 'is-due', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        default => ['Update', 'is-default', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    };
                @endphp
                <li>
                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="lms-notify-item">
                            <span @class(['lms-notify-item-icon', $iconClass]) aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                                </svg>
                            </span>
                            <span class="lms-notify-item-content">
                                <span class="lms-notify-item-title">{{ $label }}</span>
                                <span class="lms-notify-item-body">
                                    {{ $data['title'] ?? $data['assignment_title'] ?? $data['student_name'] ?? 'Open to view details' }}
                                </span>
                                <span class="lms-notify-item-time">{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                            <span class="lms-notify-item-dot" aria-hidden="true"></span>
                        </button>
                    </form>
                </li>
            @empty
                <li class="lms-notify-empty">
                    <span class="lms-notify-empty-icon" aria-hidden="true">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <p class="lms-notify-empty-title">All caught up</p>
                    <p class="lms-notify-empty-text">No new alerts right now.</p>
                </li>
            @endforelse
        </ul>

        <a href="{{ route('notifications.index') }}" class="lms-notify-footer">
            View all notifications
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
