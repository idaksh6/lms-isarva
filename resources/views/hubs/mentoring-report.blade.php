@extends('layouts.lms')

@section('title', 'Mentoring report')
@section('page_title', 'Reports')

@section('content')
<div class="corp-dashboard">
    <x-lms.module-hero
        module="reports"
        title="Mentoring effectiveness"
        subtitle="Improvement areas, mentor remarks, student progress, action plans, and overall mentoring outcomes."
    >
        @if ($relationships->isNotEmpty())
            <a href="{{ route('mentoring.report.export', array_filter(['course' => $filters['course'], 'mentor' => $filters['mentor'], 'format' => 'csv'])) }}" class="lms-btn-secondary lms-btn-secondary--xs">CSV</a>
            <a href="{{ route('mentoring.report.export', array_filter(['course' => $filters['course'], 'mentor' => $filters['mentor'], 'format' => 'xlsx'])) }}" class="lms-btn-secondary lms-btn-secondary--xs">Excel</a>
            <a href="{{ route('mentoring.report.export', array_filter(['course' => $filters['course'], 'mentor' => $filters['mentor'], 'format' => 'pdf'])) }}" class="lms-btn-primary lms-btn-primary--xs">PDF</a>
        @endif
        <a href="{{ route('mentoring.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Mentoring hub</a>
    </x-lms.module-hero>

    <x-lms.report-tabs active="mentoring" />

    <form method="GET" action="{{ route('mentoring.report') }}" class="lms-at-risk-toolbar">
        @if ($courses->isNotEmpty())
            <div class="lms-at-risk-toolbar-field">
                <label for="course" class="lms-field-label">Course</label>
                <select id="course" name="course" class="lms-field-input mt-1.5">
                    <option value="">All courses</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((int) ($filters['course'] ?? 0) === $course->id)>{{ $course->code }}</option>
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
        <div class="lms-at-risk-toolbar-actions">
            <button type="submit" class="lms-btn-secondary">Apply filters</button>
        </div>
    </form>

    <div class="corp-kpi-grid">
        <x-dashboard.kpi-card label="Active relationships" :value="$summary['active']" :sub="$summary['total'].' total'" icon="users" />
        <x-dashboard.kpi-card label="Sessions" :value="$summary['sessions']" :sub="($effectiveness['avg_sessions_per_mentee'] !== null ? $effectiveness['avg_sessions_per_mentee'].' avg / mentee' : '—')" icon="calendar" />
        <x-dashboard.kpi-card label="Area closure" :value="($effectiveness['area_closure_rate'] !== null ? $effectiveness['area_closure_rate'].'%' : '—')" :sub="$summary['areas_achieved'].' of '.$summary['areas'].' achieved'" icon="chart" />
        <x-dashboard.kpi-card label="Plan completion" :value="($effectiveness['plan_completion_rate'] !== null ? $effectiveness['plan_completion_rate'].'%' : '—')" :sub="($effectiveness['avg_plan_progress'] !== null ? 'Avg progress '.$effectiveness['avg_plan_progress'].'%' : '—')" icon="clipboard" />
    </div>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Mentoring roster</h2>
                <p class="corp-panel-desc">Progress and effectiveness by mentor–student pair.</p>
            </div>
        </div>
        <div class="corp-table-wrap">
            <table class="corp-table">
                <thead>
                    <tr>
                        <th>Mentee</th>
                        <th>Mentor</th>
                        <th>Course</th>
                        <th>Status</th>
                        <th>Sessions</th>
                        <th>Areas</th>
                        <th>Plans</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($relationships as $row)
                        <tr>
                            <td>{{ $row->mentee->name }}</td>
                            <td>{{ $row->mentor->name }}</td>
                            <td>{{ $row->course?->code ?? '—' }}</td>
                            <td>{{ $row->status->label() }}</td>
                            <td>{{ $row->sessions->count() }}</td>
                            <td>{{ $row->improvementAreas->where('status', App\Enums\ImprovementAreaStatus::Achieved)->count() }}/{{ $row->improvementAreas->count() }}</td>
                            <td>{{ $row->actionPlans->where('status', App\Enums\ActionPlanStatus::Completed)->count() }}/{{ $row->actionPlans->count() }}</td>
                            <td><a href="{{ route('mentoring.show', $row) }}" class="corp-panel-link">Open</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-sm text-isarva-muted">No mentoring relationships match these filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
