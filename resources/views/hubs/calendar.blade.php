@extends('layouts.lms')

@section('title', 'Calendar')
@section('page_title', 'Calendar')

@section('content')
@php
    $prev = $date->copy()->subMonth();
    $next = $date->copy()->addMonth();
    $monthAssignments = $assignmentsByDate->flatten()->sortBy('due_at');
@endphp

<div class="lms-page-stack">
    <x-lms.module-hero module="calendar" title="Assignment calendar" subtitle="See due dates at a glance and plan your week.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ count($highlightDates) }}</strong> due dates this month</span>
        </div>
    </x-lms.module-hero>

    <div class="corp-calendar-page">
        <section class="corp-panel corp-panel--calendar">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">{{ $date->format('F Y') }}</h2>
                    <p class="corp-panel-desc">Published assignments with due dates</p>
                </div>
                <div class="corp-dash-actions">
                    <a href="{{ route('calendar.index', ['month' => $prev->month, 'year' => $prev->year]) }}" class="lms-btn-secondary lms-btn-secondary--xs">← {{ $prev->format('M Y') }}</a>
                    <a href="{{ route('calendar.index', ['month' => $next->month, 'year' => $next->year]) }}" class="lms-btn-secondary lms-btn-secondary--xs">{{ $next->format('M Y') }} →</a>
                </div>
            </div>

            <div class="corp-panel-body-calendar">
                <x-lms.calendar-grid :date="$date" :highlight-dates="$highlightDates" />
            </div>
        </section>

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Due this month</h2>
                    <p class="corp-panel-desc">{{ $monthAssignments->count() }} assignments scheduled</p>
                </div>
            </div>

            <ul class="corp-deadline-list corp-deadline-list--page">
                @forelse ($monthAssignments as $assignment)
                    <li>
                        <a href="{{ route('assignments.show', $assignment) }}" class="corp-deadline-item">
                            <div class="corp-deadline-date">
                                <span class="corp-deadline-day">{{ $assignment->due_at->format('d') }}</span>
                                <span class="corp-deadline-month">{{ $assignment->due_at->format('M') }}</span>
                            </div>
                            <div class="corp-deadline-body">
                                <p class="corp-deadline-title">{{ $assignment->title }}</p>
                                <p class="corp-deadline-meta">{{ $assignment->course->code }} · {{ $assignment->due_at->format('g:i A') }}</p>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="corp-deadline-empty">No due dates this month.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection
