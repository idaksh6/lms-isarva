@extends('layouts.lms')

@section('title', 'Mentoring report')
@section('page_title', 'Reports')

@section('content')
@php
    $areaRate = $effectiveness['area_closure_rate'];
    $planRate = $effectiveness['plan_completion_rate'];
    $avgSessions = $effectiveness['avg_sessions_per_mentee'];
    $avgPlanProgress = $effectiveness['avg_plan_progress'];
@endphp

<div class="corp-dashboard">
    <x-lms.module-hero
        module="reports"
        title="Mentoring effectiveness"
        subtitle="Track mentor–student pairs, session cadence, improvement areas, and action-plan outcomes."
    >
        <div class="lms-report-export-group">
            @if ($relationships->isNotEmpty())
                <a href="{{ route('mentoring.report.export', array_filter(['course' => $filters['course'], 'mentor' => $filters['mentor'], 'format' => 'csv'])) }}" class="lms-btn-secondary lms-btn-secondary--xs">CSV</a>
                <a href="{{ route('mentoring.report.export', array_filter(['course' => $filters['course'], 'mentor' => $filters['mentor'], 'format' => 'xlsx'])) }}" class="lms-btn-secondary lms-btn-secondary--xs">Excel</a>
                <a href="{{ route('mentoring.report.export', array_filter(['course' => $filters['course'], 'mentor' => $filters['mentor'], 'format' => 'pdf'])) }}" class="lms-btn-primary lms-btn-primary--xs">PDF</a>
            @endif
            <a href="{{ route('mentoring.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Mentoring hub</a>
        </div>
    </x-lms.module-hero>

    <x-lms.report-tabs active="mentoring" />

    <form method="GET" action="{{ route('mentoring.report') }}" class="lms-at-risk-toolbar lms-mentoring-report-toolbar">
        <div class="lms-mentoring-report-toolbar-fields">
            @if ($courses->isNotEmpty())
                <div class="lms-at-risk-toolbar-field">
                    <label for="course" class="lms-field-label">Course</label>
                    <select id="course" name="course" class="lms-field-input mt-1.5">
                        <option value="">All courses</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) ($filters['course'] ?? 0) === $course->id)>
                                {{ $course->code }} — {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if ($mentors->isNotEmpty())
                <div class="lms-at-risk-toolbar-field">
                    <label for="mentor" class="lms-field-label">Mentor</label>
                    <select id="mentor" name="mentor" class="lms-field-input mt-1.5">
                        <option value="">All mentors</option>
                        @foreach ($mentors as $mentor)
                            <option value="{{ $mentor->id }}" @selected((int) ($filters['mentor'] ?? 0) === $mentor->id)>{{ $mentor->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <div class="lms-at-risk-toolbar-actions">
            <button type="submit" class="lms-btn-primary">Apply filters</button>
            @if (($filters['course'] ?? null) || ($filters['mentor'] ?? null))
                <a href="{{ route('mentoring.report') }}" class="lms-btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="lms-mentoring-report-summary">
        <div class="lms-mentoring-report-stat">
            <span class="lms-mentoring-report-stat-value {{ $summary['active'] > 0 ? 'is-ok' : '' }}">{{ $summary['active'] }}</span>
            <span class="lms-mentoring-report-stat-label">Active pairs</span>
            <span class="lms-mentoring-report-stat-sub">{{ $summary['total'] }} total relationships</span>
        </div>
        <div class="lms-mentoring-report-stat">
            <span class="lms-mentoring-report-stat-value">{{ $summary['sessions'] }}</span>
            <span class="lms-mentoring-report-stat-label">Sessions</span>
            <span class="lms-mentoring-report-stat-sub">{{ $avgSessions !== null ? $avgSessions.' avg / mentee' : 'No sessions yet' }}</span>
        </div>
        <div class="lms-mentoring-report-stat">
            <span class="lms-mentoring-report-stat-value {{ $areaRate !== null && $areaRate >= 50 ? 'is-ok' : '' }}">{{ $areaRate !== null ? $areaRate.'%' : '—' }}</span>
            <span class="lms-mentoring-report-stat-label">Area closure</span>
            <span class="lms-mentoring-report-stat-sub">{{ $summary['areas_achieved'] }} of {{ $summary['areas'] }} achieved</span>
            @if ($areaRate !== null)
                <div class="lms-mentoring-progress mt-3" aria-hidden="true">
                    <div class="lms-mentoring-progress-bar" style="width: {{ min(100, max(0, $areaRate)) }}%"></div>
                </div>
            @endif
        </div>
        <div class="lms-mentoring-report-stat">
            <span class="lms-mentoring-report-stat-value {{ $planRate !== null && $planRate >= 50 ? 'is-ok' : '' }}">{{ $planRate !== null ? $planRate.'%' : '—' }}</span>
            <span class="lms-mentoring-report-stat-label">Plan completion</span>
            <span class="lms-mentoring-report-stat-sub">{{ $avgPlanProgress !== null ? 'Avg progress '.$avgPlanProgress.'%' : 'No plans yet' }}</span>
            @if ($planRate !== null)
                <div class="lms-mentoring-progress mt-3" aria-hidden="true">
                    <div class="lms-mentoring-progress-bar" style="width: {{ min(100, max(0, $planRate)) }}%"></div>
                </div>
            @endif
        </div>
    </div>

    <section class="lms-at-risk-list">
        <div class="lms-at-risk-list-head">
            <div>
                <h2 class="lms-at-risk-list-title">Mentoring roster</h2>
                <p class="lms-at-risk-list-desc">
                    @if ($relationships->isNotEmpty())
                        Progress and effectiveness by mentor–student pair.
                    @else
                        Assign mentors or clear filters to see relationships here.
                    @endif
                </p>
            </div>
            <span class="lms-at-risk-list-count">{{ $relationships->count() }}</span>
        </div>

        @if ($relationships->isNotEmpty())
            <div class="lms-mentoring-report-cards">
                @foreach ($relationships as $row)
                    @php
                        $areasTotal = $row->improvementAreas->count();
                        $areasDone = $row->improvementAreas->where('status', App\Enums\ImprovementAreaStatus::Achieved)->count();
                        $plansTotal = $row->actionPlans->count();
                        $plansDone = $row->actionPlans->where('status', App\Enums\ActionPlanStatus::Completed)->count();
                        $sessionsCount = $row->sessions->count();
                        $planProgress = $plansTotal > 0
                            ? (int) round($row->actionPlans->avg('progress_percent'))
                            : null;
                        $areaPct = $areasTotal > 0 ? (int) round(($areasDone / $areasTotal) * 100) : null;
                    @endphp
                    <article class="lms-mentoring-report-card">
                        <div class="lms-mentoring-report-card-top">
                            <div class="lms-mentoring-report-identity">
                                <span class="lms-student-avatar lms-student-avatar--lg">{{ strtoupper(substr($row->mentee->name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <h3 class="lms-mentoring-report-card-name">{{ $row->mentee->name }}</h3>
                                    <p class="lms-mentoring-report-card-meta">
                                        Mentor {{ $row->mentor->name }}
                                        · {{ $row->course?->code ?? 'General mentoring' }}
                                    </p>
                                </div>
                            </div>
                            <span class="lms-mentoring-status lms-mentoring-status--{{ $row->status->value }}">{{ $row->status->label() }}</span>
                        </div>

                        <div class="lms-mentoring-report-metrics">
                            <div class="lms-mentoring-report-metric">
                                <span class="lms-mentoring-report-metric-label">Sessions</span>
                                <span class="lms-mentoring-report-metric-value">{{ $sessionsCount }}</span>
                            </div>
                            <div class="lms-mentoring-report-metric">
                                <span class="lms-mentoring-report-metric-label">Areas</span>
                                <span class="lms-mentoring-report-metric-value">{{ $areasDone }}/{{ $areasTotal }}</span>
                                @if ($areaPct !== null)
                                    <div class="lms-mentoring-progress mt-2" aria-hidden="true">
                                        <div class="lms-mentoring-progress-bar" style="width: {{ $areaPct }}%"></div>
                                    </div>
                                @endif
                            </div>
                            <div class="lms-mentoring-report-metric">
                                <span class="lms-mentoring-report-metric-label">Plans</span>
                                <span class="lms-mentoring-report-metric-value">{{ $plansDone }}/{{ $plansTotal }}</span>
                                @if ($planProgress !== null)
                                    <div class="lms-mentoring-progress mt-2" aria-hidden="true">
                                        <div class="lms-mentoring-progress-bar" style="width: {{ min(100, max(0, $planProgress)) }}%"></div>
                                    </div>
                                    <span class="lms-mentoring-report-metric-hint">{{ $planProgress }}% avg progress</span>
                                @endif
                            </div>
                            <div class="lms-mentoring-report-metric">
                                <span class="lms-mentoring-report-metric-label">Started</span>
                                <span class="lms-mentoring-report-metric-value">{{ $row->started_at?->format('M j, Y') ?? '—' }}</span>
                            </div>
                        </div>

                        @if ($row->goals)
                            <p class="lms-mentoring-report-goals">{{ Str::limit($row->goals, 160) }}</p>
                        @endif

                        <div class="lms-mentoring-report-card-actions">
                            <a href="{{ route('mentoring.show', $row) }}" class="lms-btn-primary lms-btn-primary--xs">Open relationship</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="lms-at-risk-empty">
                <div class="lms-at-risk-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <h3 class="lms-at-risk-empty-title">No mentoring relationships</h3>
                <p class="lms-at-risk-empty-text">
                    Nothing matches these filters yet. Assign a mentor from the mentoring hub to start tracking sessions and outcomes.
                </p>
                <a href="{{ route('mentoring.index') }}" class="lms-btn-primary lms-btn-primary--xs mt-4">Go to mentoring hub</a>
            </div>
        @endif
    </section>
</div>
@endsection
