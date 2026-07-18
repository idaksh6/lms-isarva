@extends('layouts.lms')

@section('title', 'Calendar')
@section('page_title', 'Calendar')

@section('content')
@php
    $prev = $date->copy()->subMonth();
    $next = $date->copy()->addMonth();
    $navBase = ['month' => $date->month, 'year' => $date->year];
    $prevQuery = array_filter(array_merge($navBase, [
        'session_date' => $selectedSessionDate?->format('Y-m-d'),
        'due_date' => $selectedDueDate?->format('Y-m-d'),
    ], ['month' => $prev->month, 'year' => $prev->year]));
    $nextQuery = array_filter(array_merge($navBase, [
        'session_date' => $selectedSessionDate?->format('Y-m-d'),
        'due_date' => $selectedDueDate?->format('Y-m-d'),
    ], ['month' => $next->month, 'year' => $next->year]));
    $sessionGridQuery = array_filter([
        'month' => $date->month,
        'year' => $date->year,
        'due_date' => $selectedDueDate?->format('Y-m-d'),
    ]);
    $dueGridQuery = array_filter([
        'month' => $date->month,
        'year' => $date->year,
        'session_date' => $selectedSessionDate?->format('Y-m-d'),
    ]);
    $clearSessionQuery = array_filter([
        'month' => $date->month,
        'year' => $date->year,
        'due_date' => $selectedDueDate?->format('Y-m-d'),
    ]);
    $clearDueQuery = array_filter([
        'month' => $date->month,
        'year' => $date->year,
        'session_date' => $selectedSessionDate?->format('Y-m-d'),
    ]);
@endphp

<div class="lms-page-stack">
    <x-lms.module-hero module="calendar" title="Calendar" subtitle="Monthly class schedule and assignment deadlines in a structured corporate view.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ $monthSessions->count() }}</strong> classes this month</span>
            <span class="lms-stat-chip"><strong>{{ $monthAssignments->count() }}</strong> due dates this month</span>
        </div>
    </x-lms.module-hero>

    <div class="corp-calendar-dual">
        <div class="corp-panel corp-calendar-month-bar">
            <div class="corp-cal-month-copy">
                <p class="corp-cal-side-eyebrow">Planning period</p>
                <h2 class="corp-calendar-month-title">{{ $date->format('F Y') }}</h2>
                <p class="corp-calendar-month-desc">Navigate the month and review schedule details in each section.</p>
            </div>
            <div class="corp-cal-nav">
                <a href="{{ route('calendar.index', $prevQuery) }}" class="corp-cal-nav-btn" aria-label="Previous month">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    <span>{{ $prev->format('M Y') }}</span>
                </a>
                <a href="{{ route('calendar.index', $nextQuery) }}" class="corp-cal-nav-btn" aria-label="Next month">
                    <span>{{ $next->format('M Y') }}</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>
            </div>
        </div>

        <section id="calendar-sessions" class="corp-panel corp-cal-block corp-cal-block--sessions">
            <div class="corp-cal-block-head">
                <div class="corp-cal-block-icon corp-cal-block-icon--sessions" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="corp-cal-block-copy">
                    <h3 class="corp-cal-block-title">Class schedule</h3>
                    <p class="corp-cal-block-desc">Online and offline sessions with time, mode, and join details.</p>
                </div>
                <span class="corp-sidebar-badge">{{ $monthSessions->count() }} classes</span>
            </div>

            <div class="corp-cal-block-body">
                <div class="corp-cal-block-split">
                    <div class="corp-cal-block-calendar">
                        <p class="corp-cal-col-label">Monthly view</p>
                        <x-lms.calendar-grid
                            class="corp-calendar--split"
                            variant="sessions"
                            date-param="session_date"
                            :date="$date"
                            :events-by-date="$sessionEventsByDate"
                            :selected-date="$selectedSessionDate"
                            :month="$date->month"
                            :year="$date->year"
                            :query-params="$sessionGridQuery"
                        />
                    </div>

                    <aside class="corp-cal-block-side">
                        <p class="corp-cal-col-label">Schedule details</p>
                        @if ($selectedSessionDate)
                            <div class="corp-cal-day-panel corp-cal-day-panel--sessions">
                                <div class="corp-cal-day-panel-head">
                                    <div>
                                        <p class="corp-cal-side-eyebrow">Selected class day</p>
                                        <h4 class="corp-cal-day-panel-title">{{ $selectedSessionDate->format('l, F j') }}</h4>
                                        <p class="corp-cal-day-panel-meta">
                                            {{ $selectedSessionSessions->count() }} class{{ $selectedSessionSessions->count() === 1 ? '' : 'es' }} scheduled
                                        </p>
                                    </div>
                                    <a href="{{ route('calendar.index', $clearSessionQuery) }}" class="lms-btn-secondary lms-btn-secondary--xs">Clear</a>
                                </div>

                                @if ($selectedSessionSessions->isNotEmpty())
                                    <ul class="corp-cal-cards corp-cal-cards--scroll">
                                        @foreach ($selectedSessionSessions as $session)
                                            <li @class([
                                                'corp-cal-card',
                                                'corp-cal-card--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                                'corp-cal-card--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                            ])>
                                                <div class="corp-cal-card-icon" aria-hidden="true">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </div>
                                                <div class="corp-cal-card-body">
                                                    <div class="corp-cal-card-top">
                                                        <p class="corp-cal-card-title">{{ $session->displayTitle() }}</p>
                                                        <span @class([
                                                            'corp-schedule-badge corp-schedule-badge--sm',
                                                            'corp-schedule-badge--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                                            'corp-schedule-badge--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                                        ])>{{ $session->mode->label() }}</span>
                                                    </div>
                                                    <p class="corp-cal-card-meta">{{ $session->course->code }} · {{ $session->timeRangeLabel() }}</p>
                                                    @if ($session->mode === \App\Enums\SessionDeliveryMode::Online && $session->meeting_link)
                                                        <a href="{{ $session->meeting_link }}" target="_blank" rel="noopener" class="corp-cal-card-action">Join online class ↗</a>
                                                    @elseif ($session->mode === \App\Enums\SessionDeliveryMode::Offline && $session->location)
                                                        <p class="corp-cal-card-location">{{ $session->location }}</p>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="corp-cal-empty corp-cal-empty--inline">
                                        <p class="corp-cal-empty-title">No classes on this date</p>
                                        <p class="corp-cal-empty-desc">Pick another day on the calendar.</p>
                                    </div>
                                @endif
                            </div>
                        @elseif ($monthSessions->isNotEmpty())
                            <div class="corp-cal-preview corp-cal-preview--sessions">
                                <h4 class="corp-cal-side-heading corp-cal-side-heading--session">Upcoming this month</h4>
                                <ul class="corp-cal-cards corp-cal-cards--scroll">
                                    @foreach ($monthSessions as $session)
                                        <li>
                                            <a href="{{ route('calendar.index', array_merge($sessionGridQuery, ['session_date' => $session->starts_at->format('Y-m-d')])) }}" @class([
                                                'corp-cal-card corp-cal-card--link',
                                                'corp-cal-card--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                                'corp-cal-card--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                            ])>
                                                <div @class([
                                                    'corp-cal-card-date',
                                                    'corp-cal-card-date--online' => $session->mode === \App\Enums\SessionDeliveryMode::Online,
                                                    'corp-cal-card-date--offline' => $session->mode === \App\Enums\SessionDeliveryMode::Offline,
                                                ])>
                                                    <span class="corp-cal-card-date-day">{{ $session->starts_at->format('d') }}</span>
                                                    <span class="corp-cal-card-date-month">{{ $session->starts_at->format('M') }}</span>
                                                </div>
                                                <div class="corp-cal-card-body">
                                                    <p class="corp-cal-card-title">{{ $session->displayTitle() }}</p>
                                                    <p class="corp-cal-card-meta">{{ $session->course->code }} · {{ $session->timeRangeLabel() }} · {{ $session->mode->label() }}</p>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="corp-cal-empty corp-cal-empty--side">
                                <p class="corp-cal-empty-title">No classes this month</p>
                                <p class="corp-cal-empty-desc">Select a date when sessions are scheduled.</p>
                            </div>
                        @endif
                    </aside>
                </div>
            </div>
        </section>

        <section id="calendar-due-dates" class="corp-panel corp-cal-block corp-cal-block--due">
            <div class="corp-cal-block-head">
                <div class="corp-cal-block-icon corp-cal-block-icon--due" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <div class="corp-cal-block-copy">
                    <h3 class="corp-cal-block-title">Due dates</h3>
                    <p class="corp-cal-block-desc">Assignment and assessment deadlines — separate from class days.</p>
                </div>
                <span class="corp-sidebar-badge corp-sidebar-badge--due">{{ $monthAssignments->count() + $monthAssessments->count() }} due</span>
            </div>

            <div class="corp-cal-block-body">
                <div class="corp-cal-block-split">
                    <div class="corp-cal-block-calendar">
                        <p class="corp-cal-col-label">Monthly view</p>
                        <x-lms.calendar-grid
                            class="corp-calendar--split"
                            variant="due"
                            date-param="due_date"
                            :date="$date"
                            :events-by-date="$dueEventsByDate"
                            :selected-date="$selectedDueDate"
                            :month="$date->month"
                            :year="$date->year"
                            :query-params="$dueGridQuery"
                        />
                    </div>

                    <aside class="corp-cal-block-side">
                        <p class="corp-cal-col-label">Deadline details</p>
                        @if ($selectedDueDate)
                            <div class="corp-cal-day-panel corp-cal-day-panel--due">
                                <div class="corp-cal-day-panel-head">
                                    <div>
                                        <p class="corp-cal-side-eyebrow">Selected due day</p>
                                        <h4 class="corp-cal-day-panel-title">{{ $selectedDueDate->format('l, F j') }}</h4>
                                        <p class="corp-cal-day-panel-meta">
                                            {{ $selectedDueAssignments->count() + $selectedDueAssessments->count() }} item{{ ($selectedDueAssignments->count() + $selectedDueAssessments->count()) === 1 ? '' : 's' }} due
                                        </p>
                                    </div>
                                    <a href="{{ route('calendar.index', $clearDueQuery) }}" class="lms-btn-secondary lms-btn-secondary--xs">Clear</a>
                                </div>

                                @if ($selectedDueAssignments->isNotEmpty() || $selectedDueAssessments->isNotEmpty())
                                    <ul class="corp-cal-cards corp-cal-cards--scroll">
                                        @foreach ($selectedDueAssignments as $assignment)
                                            <li class="corp-cal-card corp-cal-card--due">
                                                <div class="corp-cal-card-icon" aria-hidden="true">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                </div>
                                                <div class="corp-cal-card-body">
                                                    <a href="{{ route('assignments.show', $assignment) }}" class="corp-cal-card-title corp-cal-card-title--link">{{ $assignment->title }}</a>
                                                    <p class="corp-cal-card-meta">{{ $assignment->course->code }} · Assignment · Due {{ $assignment->due_at->format('g:i A') }}</p>
                                                </div>
                                            </li>
                                        @endforeach
                                        @foreach ($selectedDueAssessments as $assessment)
                                            <li class="corp-cal-card corp-cal-card--due">
                                                <div class="corp-cal-card-icon" aria-hidden="true">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </div>
                                                <div class="corp-cal-card-body">
                                                    <a href="{{ route('assessments.show', $assessment) }}" class="corp-cal-card-title corp-cal-card-title--link">{{ $assessment->title }}</a>
                                                    <p class="corp-cal-card-meta">{{ $assessment->course->code }} · Assessment · Due {{ $assessment->due_at->format('g:i A') }}</p>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="corp-cal-empty corp-cal-empty--inline">
                                        <p class="corp-cal-empty-title">Nothing due on this date</p>
                                        <p class="corp-cal-empty-desc">Pick another day on the calendar.</p>
                                    </div>
                                @endif
                            </div>
                        @elseif ($monthAssignments->isNotEmpty() || $monthAssessments->isNotEmpty())
                            <div class="corp-cal-preview corp-cal-preview--due">
                                <h4 class="corp-cal-side-heading corp-cal-side-heading--due">Due this month</h4>
                                <ul class="corp-cal-cards corp-cal-cards--scroll">
                                    @foreach ($monthAssignments as $assignment)
                                        <li>
                                            <a href="{{ route('calendar.index', array_merge($dueGridQuery, ['due_date' => $assignment->due_at->format('Y-m-d')])) }}" class="corp-cal-card corp-cal-card--due corp-cal-card--link">
                                                <div class="corp-cal-card-date corp-cal-card-date--due">
                                                    <span class="corp-cal-card-date-day">{{ $assignment->due_at->format('d') }}</span>
                                                    <span class="corp-cal-card-date-month">{{ $assignment->due_at->format('M') }}</span>
                                                </div>
                                                <div class="corp-cal-card-body">
                                                    <p class="corp-cal-card-title">{{ $assignment->title }}</p>
                                                    <p class="corp-cal-card-meta">{{ $assignment->course->code }} · Assignment · {{ $assignment->due_at->format('g:i A') }}</p>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                    @foreach ($monthAssessments as $assessment)
                                        <li>
                                            <a href="{{ route('calendar.index', array_merge($dueGridQuery, ['due_date' => $assessment->due_at->format('Y-m-d')])) }}" class="corp-cal-card corp-cal-card--due corp-cal-card--link">
                                                <div class="corp-cal-card-date corp-cal-card-date--due">
                                                    <span class="corp-cal-card-date-day">{{ $assessment->due_at->format('d') }}</span>
                                                    <span class="corp-cal-card-date-month">{{ $assessment->due_at->format('M') }}</span>
                                                </div>
                                                <div class="corp-cal-card-body">
                                                    <p class="corp-cal-card-title">{{ $assessment->title }}</p>
                                                    <p class="corp-cal-card-meta">{{ $assessment->course->code }} · Assessment · {{ $assessment->due_at->format('g:i A') }}</p>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="corp-cal-empty corp-cal-empty--side">
                                <p class="corp-cal-empty-title">No due dates this month</p>
                                <p class="corp-cal-empty-desc">Select a date when assignments or assessments are due.</p>
                            </div>
                        @endif
                    </aside>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
