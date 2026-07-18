@extends('layouts.lms')

@section('title', 'Class schedule — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="sessions" />

    <section class="lms-panel">
        <div class="lms-panel-header">
            <div class="lms-panel-heading">
                <h2 class="lms-panel-title">Class schedule</h2>
                <span class="lms-panel-count">{{ $sessions->count() }}</span>
            </div>
            @can('update', $course)
                <a href="{{ route('courses.sessions.create', $course) }}" class="lms-btn-primary lms-btn-primary--sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add session
                </a>
            @endcan
        </div>
        <div class="lms-panel-body p-0">
            @if ($sessions->isEmpty())
                <div class="lms-empty-panel py-12">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 9.75h18M4.5 6.75h15a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5h-15a1.5 1.5 0 01-1.5-1.5v-12a1.5 1.5 0 011.5-1.5z"/></svg>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-isarva-heading">No classes scheduled yet</p>
                    <p class="mt-1 text-sm text-isarva-muted">Add sessions so students see class time and mode on the calendar.</p>
                    @can('update', $course)
                        <a href="{{ route('courses.sessions.create', $course) }}" class="mt-4 lms-btn-primary">Schedule first class</a>
                    @endcan
                </div>
            @else
                <ul class="corp-schedule-list">
                    @foreach ($sessions as $session)
                        <li class="corp-schedule-row">
                            <div class="corp-schedule-date">
                                <span class="corp-schedule-date-day">{{ $session->starts_at->format('d') }}</span>
                                <span class="corp-schedule-date-month">{{ $session->starts_at->format('M') }}</span>
                            </div>

                            <div class="corp-schedule-main">
                                <div class="corp-schedule-top">
                                    <h3 class="corp-schedule-title">{{ $session->displayTitle() }}</h3>
                                    <span @class([
                                        'corp-schedule-badge',
                                        'corp-schedule-badge--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                        'corp-schedule-badge--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                    ])>{{ $session->mode->label() }}</span>
                                </div>
                                <p class="corp-schedule-meta">
                                    <span>{{ $session->timeRangeLabel() }}</span>
                                    @if ($session->mode === \App\Enums\SessionDeliveryMode::Online && $session->meeting_link)
                                        <span class="corp-schedule-sep">·</span>
                                        <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="corp-schedule-link">Meeting link</a>
                                    @elseif ($session->location)
                                        <span class="corp-schedule-sep">·</span>
                                        <span>{{ $session->location }}</span>
                                    @endif
                                </p>
                            </div>

                            @can('update', $session)
                                <div class="corp-schedule-actions">
                                    <a href="{{ route('class-sessions.edit', $session) }}" class="lms-btn-secondary lms-btn-secondary--xs">Edit</a>
                                    <form method="POST" action="{{ route('class-sessions.destroy', $session) }}" onsubmit="return confirm('Remove this class session?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Delete</button>
                                    </form>
                                </div>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    @can('update', $course)
        <section class="lms-panel">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Import semester timetable</h2>
            </div>
            <div class="lms-panel-body">
                <p class="text-sm text-isarva-muted">Upload a CSV with columns: title, starts_at, ends_at, mode, meeting_link, location, semester. Rows matching this course's semester ({{ $course->semester ?: 'any' }}) become class sessions on the calendar.</p>
                <form method="POST" action="{{ route('courses.sessions.timetable.import', $course) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <x-lms.single-file-upload
                        name="timetable"
                        label="CSV file"
                        accept=".csv,.txt"
                        :max-upload-mb="\App\Support\UploadLimits::timetableCsvMaxMegabytes()"
                        :hint="'Semester timetable CSV · max '.\App\Support\UploadLimits::timetableCsvMaxMegabytes().' MB'"
                    />
                    <div class="lms-form-actions border-t-0 pt-0">
                        <button type="submit" class="lms-btn-primary">Import timetable</button>
                    </div>
                </form>
            </div>
        </section>
    @endcan
</div>
@endsection
