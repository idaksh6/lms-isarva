@extends('layouts.lms')

@section('title', 'Announcements')
@section('page_title', 'Announcements')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="announcements" title="Announcements" subtitle="Important updates from your lecturers. Everyone can read them here.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ $announcements->total() }}</strong> posts</span>
        </div>
    </x-lms.module-hero>

    @if (auth()->user()->isLecturer())
        <form method="POST" action="{{ route('announcements.store') }}" class="lms-form-card">
            @csrf
            <div class="lms-form-header">
                <h2 class="lms-form-title">Post an announcement</h2>
                <p class="lms-form-desc">Share news with a course or the entire programme.</p>
            </div>
            <div class="lms-form-field">
                <label class="lms-field-label">Audience</label>
                <select name="course_id" class="lms-field-input mt-1.5">
                    <option value="">All users (global)</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->code }} — {{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lms-form-field">
                <label class="lms-field-label">Title</label>
                <input type="text" name="title" class="lms-field-input mt-1.5" required maxlength="255">
            </div>
            <div class="lms-form-field">
                <label class="lms-field-label">Message</label>
                <textarea name="body" rows="4" class="lms-field-input mt-1.5" required maxlength="10000"></textarea>
            </div>
            <label class="lms-form-check">
                <input type="checkbox" name="is_pinned" value="1">
                <span class="text-sm font-medium text-slate-700">Pin to top</span>
            </label>
            <div class="lms-form-actions">
                <button type="submit" class="lms-btn-primary">Publish</button>
            </div>
        </form>
    @endif

    <div class="lms-announcement-feed">
        @forelse ($announcements as $announcement)
            <article @class(['lms-announcement-card', 'is-pinned' => $announcement->is_pinned])>
                <div class="lms-announcement-card-head">
                    <div>
                        @if ($announcement->is_pinned)
                            <span class="lms-badge bg-brand-100 text-brand-800">Pinned</span>
                        @endif
                        <h3 class="lms-announcement-title">{{ $announcement->title }}</h3>
                        <p class="lms-announcement-meta">
                            {{ $announcement->author->name }} · {{ $announcement->published_at->format('M j, Y') }}
                            @if ($announcement->course)
                                · {{ $announcement->course->code }}
                            @else
                                · <span class="text-brand-600">Global</span>
                            @endif
                        </p>
                    </div>
                    @can('delete', $announcement)
                        <form method="POST" action="{{ route('announcements.destroy', $announcement) }}" onsubmit="return confirm('Remove this announcement?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Remove</button>
                        </form>
                    @endcan
                </div>
                <div class="lms-announcement-body whitespace-pre-wrap">{{ $announcement->body }}</div>
            </article>
        @empty
            <x-lms.empty-state title="No announcements yet" message="When your lecturers post updates, they will appear here." variant="books" />
        @endforelse
    </div>

    @if ($announcements->hasPages())
        <div>{{ $announcements->links() }}</div>
    @endif
</div>
@endsection
