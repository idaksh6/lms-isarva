@extends('layouts.lms')

@section('title', 'Mentoring — '.$relationship->mentee->name)
@section('page_title', 'Mentoring')

@section('content')
<div class="lms-page-stack" x-data="{ tab: 'sessions' }">
    <x-lms.module-hero
        module="users"
        title="{{ $relationship->mentee->name }}"
        subtitle="Mentor {{ $relationship->mentor->name }}{{ $relationship->course ? ' · '.$relationship->course->code : '' }}"
    >
        <a href="{{ route('mentoring.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Back to mentoring</a>
        @if ($canManage)
            <a href="{{ route('mentoring.report') }}" class="lms-btn-secondary lms-btn-secondary--xs">Effectiveness report</a>
        @endif
    </x-lms.module-hero>

    <div class="corp-kpi-grid">
        <x-dashboard.kpi-card label="Status" :value="$relationship->status->label()" :sub="$relationship->started_at?->format('M j, Y') ?? '—'" icon="clipboard" />
        <x-dashboard.kpi-card label="Sessions" :value="$relationship->sessions->count()" sub="Recorded meetings" icon="calendar" />
        <x-dashboard.kpi-card label="Improvement areas" :value="$relationship->improvementAreas->count()" :sub="$relationship->improvementAreas->where('status', App\Enums\ImprovementAreaStatus::Achieved)->count().' achieved'" icon="chart" />
        <x-dashboard.kpi-card label="Action plans" :value="$relationship->actionPlans->count()" :sub="$relationship->actionPlans->where('status', App\Enums\ActionPlanStatus::Completed)->count().' completed'" icon="inbox" />
    </div>

    <section class="lms-form-card">
        <div class="lms-form-header">
            <h2 class="lms-form-title">Relationship overview</h2>
            <p class="lms-form-desc">Goals and mentoring status for this student.</p>
        </div>

        @if ($canManage)
            <form method="POST" action="{{ route('mentoring.update', $relationship) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="lms-form-field">
                        <label for="status" class="lms-field-label">Status</label>
                        <select id="status" name="status" class="lms-field-input mt-1.5" required>
                            @foreach (App\Enums\MentoringStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $relationship->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="lms-form-field">
                    <label for="goals" class="lms-field-label">Goals</label>
                    <textarea id="goals" name="goals" rows="3" class="lms-field-input mt-1.5">{{ old('goals', $relationship->goals) }}</textarea>
                </div>
                <div class="lms-form-actions">
                    <button type="submit" class="lms-btn-primary">Save relationship</button>
                </div>
            </form>
        @else
            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $relationship->goals ?: 'No goals recorded yet.' }}</p>
            <p class="mt-2 text-sm text-isarva-muted">Status: {{ $relationship->status->label() }}</p>
        @endif
    </section>

    <div class="lms-mentoring-tabs" role="tablist" aria-label="Mentoring sections">
        <button type="button" class="lms-mentoring-tab" :class="tab === 'sessions' && 'is-active'" @click="tab = 'sessions'">Sessions</button>
        <button type="button" class="lms-mentoring-tab" :class="tab === 'areas' && 'is-active'" @click="tab = 'areas'">Improvement areas</button>
        <button type="button" class="lms-mentoring-tab" :class="tab === 'plans' && 'is-active'" @click="tab = 'plans'">Action plans</button>
    </div>

    <div x-show="tab === 'sessions'" x-cloak class="space-y-4">
        @if ($canManage)
            <form method="POST" action="{{ route('mentoring.sessions.store', $relationship) }}" class="lms-form-card">
                @csrf
                <div class="lms-form-header">
                    <h2 class="lms-form-title">Record a session</h2>
                    <p class="lms-form-desc">Capture mentor remarks and student progress notes.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="lms-form-field">
                        <label for="conducted_at" class="lms-field-label">Date &amp; time</label>
                        <input id="conducted_at" type="datetime-local" name="conducted_at" value="{{ old('conducted_at', now()->format('Y-m-d\TH:i')) }}" class="lms-field-input mt-1.5" required>
                    </div>
                    <div class="lms-form-field">
                        <label for="duration_minutes" class="lms-field-label">Duration (minutes)</label>
                        <input id="duration_minutes" type="number" name="duration_minutes" min="5" max="480" value="{{ old('duration_minutes', 30) }}" class="lms-field-input mt-1.5">
                    </div>
                    <div class="lms-form-field">
                        <label for="mode" class="lms-field-label">Mode</label>
                        <select id="mode" name="mode" class="lms-field-input mt-1.5" required>
                            @foreach (App\Enums\MentoringSessionMode::cases() as $mode)
                                <option value="{{ $mode->value }}" @selected(old('mode', 'in_person') === $mode->value)>{{ $mode->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lms-form-field">
                        <label for="topic" class="lms-field-label">Topic</label>
                        <input id="topic" type="text" name="topic" value="{{ old('topic') }}" class="lms-field-input mt-1.5" placeholder="e.g. Assignment feedback">
                    </div>
                </div>
                <div class="lms-form-field">
                    <label for="remarks" class="lms-field-label">Mentor remarks</label>
                    <textarea id="remarks" name="remarks" rows="3" class="lms-field-input mt-1.5">{{ old('remarks') }}</textarea>
                </div>
                <div class="lms-form-field">
                    <label for="student_progress_notes" class="lms-field-label">Student progress notes</label>
                    <textarea id="student_progress_notes" name="student_progress_notes" rows="3" class="lms-field-input mt-1.5">{{ old('student_progress_notes') }}</textarea>
                </div>
                <div class="lms-form-actions">
                    <button type="submit" class="lms-btn-primary">Save session</button>
                </div>
            </form>
        @endif

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Session history</h2>
                    <p class="corp-panel-desc">Remarks and progress tracked over time.</p>
                </div>
            </div>
            <div class="lms-mentoring-timeline">
                @forelse ($relationship->sessions as $session)
                    <article class="lms-mentoring-timeline-item">
                        <div class="lms-mentoring-timeline-meta">
                            <strong>{{ $session->conducted_at->format('M j, Y g:i A') }}</strong>
                            <span>{{ $session->mode->label() }}{{ $session->duration_minutes ? ' · '.$session->duration_minutes.' min' : '' }}</span>
                        </div>
                        @if ($session->topic)
                            <p class="lms-mentoring-timeline-title">{{ $session->topic }}</p>
                        @endif
                        @if ($session->remarks)
                            <p class="lms-mentoring-timeline-copy"><span class="font-semibold text-isarva-heading">Remarks:</span> {{ $session->remarks }}</p>
                        @endif
                        @if ($session->student_progress_notes)
                            <p class="lms-mentoring-timeline-copy"><span class="font-semibold text-isarva-heading">Progress:</span> {{ $session->student_progress_notes }}</p>
                        @endif
                        @if ($canManage)
                            <form method="POST" action="{{ route('mentoring.sessions.destroy', $session) }}" class="mt-2" onsubmit="return confirm('Remove this session?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Remove</button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="p-5">
                        <x-lms.empty-state title="No sessions yet" message="Record the first mentoring meeting to start the timeline." variant="assignment" />
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <div x-show="tab === 'areas'" x-cloak class="space-y-4">
        @if ($canManage)
            <form method="POST" action="{{ route('mentoring.areas.store', $relationship) }}" class="lms-form-card">
                @csrf
                <div class="lms-form-header">
                    <h2 class="lms-form-title">Add improvement area</h2>
                    <p class="lms-form-desc">Identify skills or habits the student should work on.</p>
                </div>
                <div class="lms-form-field">
                    <label for="area_title" class="lms-field-label">Title</label>
                    <input id="area_title" type="text" name="title" value="{{ old('title') }}" class="lms-field-input mt-1.5" required>
                </div>
                <div class="lms-form-field">
                    <label for="area_description" class="lms-field-label">Description</label>
                    <textarea id="area_description" name="description" rows="3" class="lms-field-input mt-1.5">{{ old('description') }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="lms-form-field">
                        <label for="priority" class="lms-field-label">Priority</label>
                        <select id="priority" name="priority" class="lms-field-input mt-1.5" required>
                            @foreach (App\Enums\ImprovementAreaPriority::cases() as $priority)
                                <option value="{{ $priority->value }}" @selected(old('priority', 'medium') === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lms-form-field">
                        <label for="area_status" class="lms-field-label">Status</label>
                        <select id="area_status" name="status" class="lms-field-input mt-1.5" required>
                            @foreach (App\Enums\ImprovementAreaStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status', 'open') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="lms-form-actions">
                    <button type="submit" class="lms-btn-primary">Add area</button>
                </div>
            </form>
        @endif

        <div class="space-y-3">
            @forelse ($relationship->improvementAreas as $area)
                <section class="corp-panel">
                    <div class="corp-panel-head">
                        <div>
                            <h3 class="corp-panel-title">{{ $area->title }}</h3>
                            <p class="corp-panel-desc">{{ $area->priority->label() }} priority · {{ $area->status->label() }}</p>
                        </div>
                    </div>
                    <div class="space-y-3 px-4 py-4 sm:px-5">
                        @if ($area->description)
                            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $area->description }}</p>
                        @endif
                        @if ($canManage)
                            <form method="POST" action="{{ route('mentoring.areas.update', $area) }}" class="grid gap-3 sm:grid-cols-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="title" value="{{ $area->title }}">
                                <input type="hidden" name="description" value="{{ $area->description }}">
                                <input type="hidden" name="priority" value="{{ $area->priority->value }}">
                                <select name="status" class="lms-field-input" required>
                                    @foreach (App\Enums\ImprovementAreaStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected($area->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Update status</button>
                                <button form="delete-area-{{ $area->id }}" type="submit" class="lms-btn-danger lms-btn-danger--xs" onclick="return confirm('Remove this improvement area?')">Remove</button>
                            </form>
                            <form id="delete-area-{{ $area->id }}" method="POST" action="{{ route('mentoring.areas.destroy', $area) }}">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                </section>
            @empty
                <x-lms.empty-state title="No improvement areas" message="Identify focus areas to guide mentoring conversations." variant="chart" />
            @endforelse
        </div>
    </div>

    <div x-show="tab === 'plans'" x-cloak class="space-y-4">
        @if ($canManage)
            <form method="POST" action="{{ route('mentoring.plans.store', $relationship) }}" class="lms-form-card">
                @csrf
                <div class="lms-form-header">
                    <h2 class="lms-form-title">Add action plan</h2>
                    <p class="lms-form-desc">Define objectives and track progress percent over time.</p>
                </div>
                <div class="lms-form-field">
                    <label for="plan_title" class="lms-field-label">Title</label>
                    <input id="plan_title" type="text" name="title" class="lms-field-input mt-1.5" required>
                </div>
                <div class="lms-form-field">
                    <label for="objectives" class="lms-field-label">Objectives</label>
                    <textarea id="objectives" name="objectives" rows="3" class="lms-field-input mt-1.5"></textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="lms-form-field">
                        <label for="due_at" class="lms-field-label">Due date</label>
                        <input id="due_at" type="datetime-local" name="due_at" class="lms-field-input mt-1.5">
                    </div>
                    <div class="lms-form-field">
                        <label for="plan_status" class="lms-field-label">Status</label>
                        <select id="plan_status" name="status" class="lms-field-input mt-1.5" required>
                            @foreach (App\Enums\ActionPlanStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($status === App\Enums\ActionPlanStatus::Planned)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lms-form-field">
                        <label for="progress_percent" class="lms-field-label">Progress %</label>
                        <input id="progress_percent" type="number" name="progress_percent" min="0" max="100" value="0" class="lms-field-input mt-1.5" required>
                    </div>
                </div>
                <div class="lms-form-field">
                    <label for="progress_notes" class="lms-field-label">Progress notes</label>
                    <textarea id="progress_notes" name="progress_notes" rows="2" class="lms-field-input mt-1.5"></textarea>
                </div>
                <div class="lms-form-actions">
                    <button type="submit" class="lms-btn-primary">Add plan</button>
                </div>
            </form>
        @endif

        <div class="space-y-3">
            @forelse ($relationship->actionPlans as $plan)
                <section class="corp-panel">
                    <div class="corp-panel-head">
                        <div>
                            <h3 class="corp-panel-title">{{ $plan->title }}</h3>
                            <p class="corp-panel-desc">
                                {{ $plan->status->label() }} · {{ $plan->progress_percent }}%
                                {{ $plan->due_at ? ' · Due '.$plan->due_at->format('M j, Y') : '' }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-3 px-4 py-4 sm:px-5">
                        <div class="lms-mentoring-progress">
                            <div class="lms-mentoring-progress-bar" style="width: {{ $plan->progress_percent }}%"></div>
                        </div>
                        @if ($plan->objectives)
                            <p class="text-sm text-slate-700 whitespace-pre-wrap">{{ $plan->objectives }}</p>
                        @endif
                        @if ($plan->progress_notes)
                            <p class="text-sm text-isarva-muted">{{ $plan->progress_notes }}</p>
                        @endif
                        @if ($canManage)
                            <form method="POST" action="{{ route('mentoring.plans.update', $plan) }}" class="grid gap-3 sm:grid-cols-4">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="title" value="{{ $plan->title }}">
                                <input type="hidden" name="objectives" value="{{ $plan->objectives }}">
                                <input type="hidden" name="due_at" value="{{ $plan->due_at?->format('Y-m-d\TH:i') }}">
                                <input type="hidden" name="progress_notes" value="{{ $plan->progress_notes }}">
                                <select name="status" class="lms-field-input" required>
                                    @foreach (App\Enums\ActionPlanStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected($plan->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="progress_percent" min="0" max="100" value="{{ $plan->progress_percent }}" class="lms-field-input" required>
                                <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Update progress</button>
                                <button form="delete-plan-{{ $plan->id }}" type="submit" class="lms-btn-danger lms-btn-danger--xs" onclick="return confirm('Remove this action plan?')">Remove</button>
                            </form>
                            <form id="delete-plan-{{ $plan->id }}" method="POST" action="{{ route('mentoring.plans.destroy', $plan) }}">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    </div>
                </section>
            @empty
                <x-lms.empty-state title="No action plans" message="Create plans with objectives and track progress over time." variant="assignment" />
            @endforelse
        </div>
    </div>
</div>
@endsection
