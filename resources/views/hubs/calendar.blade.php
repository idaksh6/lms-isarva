@extends('layouts.lms')

@section('title', 'Calendar')
@section('page_title', 'Calendar')

@section('content')
@php
    $prev = $date->copy()->subMonth();
    $next = $date->copy()->addMonth();
@endphp

<div class="lms-page-stack">
    <x-lms.module-hero module="calendar" variant="notebook" title="Assignment calendar" subtitle="See due dates at a glance and plan your week.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ count($highlightDates) }}</strong> due dates this month</span>
        </div>
    </x-lms.module-hero>

    <div class="lms-calendar-page">
        <div class="lms-calendar-nav">
            <a href="{{ route('calendar.index', ['month' => $prev->month, 'year' => $prev->year]) }}" class="lms-btn-secondary">← {{ $prev->format('M Y') }}</a>
            <h2 class="text-lg font-bold text-isarva-heading">{{ $date->format('F Y') }}</h2>
            <a href="{{ route('calendar.index', ['month' => $next->month, 'year' => $next->year]) }}" class="lms-btn-secondary">{{ $next->format('M Y') }} →</a>
        </div>

        <div class="lms-calendar-layout">
            <div class="lms-calendar-grid-panel">
                @include('dashboard.partials.calendar', ['highlightDates' => $highlightDates])
            </div>

            <section class="lms-panel lms-calendar-events">
                <div class="lms-panel-header">
                    <h2 class="lms-panel-title">Due this month</h2>
                </div>
                <div class="lms-panel-body space-y-3">
                    @php $monthAssignments = $assignmentsByDate->flatten()->sortBy('due_at'); @endphp
                    @forelse ($monthAssignments as $assignment)
                        <a href="{{ route('assignments.show', $assignment) }}" class="lms-calendar-event">
                            <span class="lms-calendar-event-date">{{ $assignment->due_at->format('M j') }}</span>
                            <span class="min-w-0">
                                <span class="block font-semibold text-slate-900 truncate">{{ $assignment->title }}</span>
                                <span class="block text-sm text-slate-500">{{ $assignment->course->code }} · {{ $assignment->due_at->format('g:i A') }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">No due dates this month.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
