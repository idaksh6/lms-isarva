@extends('layouts.lms')

@section('title', 'Mentoring')
@section('page_title', 'Mentoring')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero
        module="users"
        title="Student mentoring"
        subtitle="Track mentor–student relationships, improvement areas, sessions, and action plans."
    >
        @can('create', App\Models\MentoringRelationship::class)
            <a href="{{ route('mentoring.report') }}" class="lms-btn-secondary lms-btn-secondary--xs">Effectiveness report</a>
            <a href="{{ route('mentoring.create') }}" class="lms-btn-primary lms-btn-primary--xs">Assign mentor</a>
        @endcan
    </x-lms.module-hero>

    <div class="corp-kpi-grid">
        <x-dashboard.kpi-card label="Relationships" :value="$summary['total']" :sub="$summary['active'].' active'" icon="users" />
        <x-dashboard.kpi-card label="Sessions logged" :value="$summary['sessions']" sub="Across all mentees" icon="calendar" />
        <x-dashboard.kpi-card label="Improvement areas" :value="$summary['areas']" :sub="$summary['areas_achieved'].' achieved'" icon="chart" />
        <x-dashboard.kpi-card
            label="Plan completion"
            :value="($effectiveness['plan_completion_rate'] !== null ? $effectiveness['plan_completion_rate'].'%' : '—')"
            :sub="($effectiveness['avg_plan_progress'] !== null ? 'Avg progress '.$effectiveness['avg_plan_progress'].'%' : 'No plans yet')"
            icon="clipboard"
        />
    </div>

    <form method="GET" action="{{ route('mentoring.index') }}" class="lms-filter-bar">
        <div class="lms-filter-select-wrap">
            <select name="status" class="lms-field-input lms-filter-select">
                <option value="">All statuses</option>
                @foreach (App\Enums\MentoringStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        @if ($courses->isNotEmpty())
            <div class="lms-filter-select-wrap">
                <select name="course" class="lms-field-input lms-filter-select">
                    <option value="">All courses</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((int) ($filters['course'] ?? 0) === $course->id)>{{ $course->code }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Filter</button>
    </form>

    <div class="lms-hub-list lms-hub-list--cards">
        @forelse ($relationships as $relationship)
            <a href="{{ route('mentoring.show', $relationship) }}" class="lms-mentoring-card">
                <div class="lms-mentoring-card-top">
                    <div>
                        <p class="lms-mentoring-card-kicker">{{ $relationship->course?->code ?? 'General mentoring' }}</p>
                        <h3 class="lms-mentoring-card-title">
                            @if (auth()->user()->isStudent())
                                Mentor: {{ $relationship->mentor->name }}
                            @else
                                {{ $relationship->mentee->name }}
                            @endif
                        </h3>
                        <p class="lms-mentoring-card-meta">
                            @unless (auth()->user()->isStudent())
                                Mentor {{ $relationship->mentor->name }} ·
                            @endunless
                            {{ $relationship->sessions_count }} sessions · {{ $relationship->improvement_areas_count }} areas · {{ $relationship->action_plans_count }} plans
                        </p>
                    </div>
                    <span class="lms-mentoring-status lms-mentoring-status--{{ $relationship->status->value }}">{{ $relationship->status->label() }}</span>
                </div>
                @if ($relationship->goals)
                    <p class="lms-mentoring-card-goals">{{ Str::limit($relationship->goals, 140) }}</p>
                @endif
            </a>
        @empty
            <x-lms.empty-state
                title="No mentoring relationships yet"
                :message="auth()->user()->isStudent() ? 'When a faculty mentor is assigned, it will appear here.' : 'Assign a mentor to a student to start tracking sessions and progress.'"
                variant="users"
            />
        @endforelse
    </div>

    @if ($relationships->hasPages())
        <div>{{ $relationships->links() }}</div>
    @endif
</div>
@endsection
