@extends('layouts.lms')

@section('title', 'Reports')
@section('page_title', 'Reports')

@section('content')
@php
    $totalSubmissions = array_sum($statusBreakdown);

    $statusMeta = [
        'submitted' => ['label' => 'Submitted', 'tone' => 'is-submitted'],
        'late' => ['label' => 'Late', 'tone' => 'is-late'],
        'needs_revision' => ['label' => 'Needs revision', 'tone' => 'is-needs-revision'],
        'reviewed' => ['label' => 'Reviewed', 'tone' => 'is-reviewed'],
    ];
@endphp

<div class="lms-page-stack">
    <x-lms.module-hero module="reports" variant="analytics" title="Reports & analytics" subtitle="Overview of courses, submissions, and review progress across your programme.">
        <div class="lms-stat-chips">
            <a href="{{ route('reports.export') }}" class="lms-btn-primary lms-btn-primary--xs">Download CSV report</a>
        </div>
    </x-lms.module-hero>

    <div class="lms-reports-grid">
        <section class="lms-panel lms-panel--reports lms-report-status-panel">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Submission status</h2>
                <span class="lms-panel-count">{{ $totalSubmissions }} total</span>
            </div>
            <div class="lms-panel-body lms-report-status-body">
                @if ($totalSubmissions === 0)
                    <p class="lms-report-empty-note">No submissions yet — stats will appear when students upload work.</p>
                @endif

                <div class="lms-report-stat-grid">
                    @foreach ($statusBreakdown as $label => $count)
                        @php
                            $tone = $statusMeta[$label]['tone'] ?? 'is-submitted';
                            $pct = $totalSubmissions > 0 ? round(($count / $totalSubmissions) * 100) : 0;
                        @endphp
                        <div class="lms-report-stat-card {{ $tone }}">
                            <p class="lms-report-stat-value">{{ $count }}</p>
                            <p class="lms-report-stat-label">{{ $statusMeta[$label]['label'] }}</p>
                            @if ($totalSubmissions > 0)
                                <p class="lms-report-stat-pct">{{ $pct }}% of total</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="lms-panel lms-panel--reports">
            <div class="lms-panel-header"><h2 class="lms-panel-title">Course breakdown</h2></div>
            <div class="lms-panel-body lms-course-breakdown">
                @foreach ($courseBreakdown as $course)
                    <div class="lms-course-breakdown-row">
                        <div class="lms-course-breakdown-copy">
                            <p class="font-semibold text-slate-900">{{ $course->code }}</p>
                            <p class="text-sm text-slate-500">{{ $course->title }}</p>
                        </div>
                        <div class="lms-course-breakdown-stats">
                            <span class="lms-report-pill">{{ $course->students_count }} students</span>
                            <span class="lms-report-pill">{{ $course->assignments_count }} assignments</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="lms-panel lms-panel--reports">
        <div class="lms-panel-header"><h2 class="lms-panel-title">Recent submissions</h2></div>
        <div class="lms-panel-body">
            <div class="lms-hub-list lms-hub-list--submissions">
                @forelse ($recentSubmissions as $submission)
                    <x-lms.submission-list-item :submission="$submission" />
                @empty
                    <p class="text-sm text-slate-500">No submissions recorded yet.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection
