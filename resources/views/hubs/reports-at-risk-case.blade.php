@extends('layouts.lms')

@section('title', 'Support case — '.$case->student->name)
@section('page_title', 'Reports')

@section('content')
<div class="corp-dashboard">
    <x-lms.module-hero
        module="reports"
        title="{{ $case->student->name }}"
        subtitle="Support case · {{ $case->course->code }} — {{ $case->course->title }}"
    >
        <a href="{{ route('reports.at-risk', ['course' => $case->course_id]) }}" class="lms-btn-secondary lms-btn-secondary--xs">← Back to at-risk</a>
    </x-lms.module-hero>

    <nav class="lms-report-tabs" aria-label="Report type">
        <a href="{{ route('reports.index') }}" class="lms-report-tab">Overview</a>
        <a href="{{ route('reports.assignments') }}" class="lms-report-tab">Individual assignment</a>
        <a href="{{ route('reports.activity') }}" class="lms-report-tab">Course activity</a>
        <a href="{{ route('reports.at-risk', ['course' => $case->course_id]) }}" class="lms-report-tab is-active">Course at-risk</a>
    </nav>

    <div class="corp-kpi-grid">
        <x-dashboard.kpi-card label="Status" :value="$case->status->label()" :sub="'Opened '.($case->identified_at?->diffForHumans() ?? '—')" icon="clipboard" />
        <x-dashboard.kpi-card label="Student ID" :value="$case->student->student_id ?: '—'" :sub="$case->student->email" icon="users" />
        <x-dashboard.kpi-card
            label="Assignment avg"
            :value="($latest['assignment_avg'] !== null ? $latest['assignment_avg'].'%' : '—')"
            :sub="($delta['assignment_avg'] !== null ? 'Δ '.$delta['assignment_avg'] : 'vs baseline')"
            icon="chart"
        />
        <x-dashboard.kpi-card
            label="Participation"
            :value="($latest['participation_rate'] !== null ? $latest['participation_rate'].'%' : '—')"
            :sub="($delta['participation_rate'] !== null ? 'Δ '.$delta['participation_rate'] : 'vs baseline')"
            icon="inbox"
        />
    </div>

    @if ($aiEnabled ?? false)
        @php
            $aiFailed = ($aiGeneration ?? null) && $aiGeneration->status->value === 'failed';
            $aiPending = ($aiGeneration ?? null)?->isPending();
            $aiReady = ($aiGeneration ?? null) && ($aiGeneration->isReady() || $aiGeneration->status->value === 'accepted');
            $aiError = $aiFailed ? \App\Support\AiErrorPresenter::present($aiGeneration->error_message) : null;
            $out = $aiReady ? ($aiGeneration->output ?? []) : [];
        @endphp
        <section class="lms-ai-shell" x-data="{ tab: 'why' }">
            <div class="lms-ai-shell-head">
                <div class="lms-ai-shell-brand">
                    <span class="lms-ai-shell-badge">AI</span>
                    <div>
                        <h2 class="lms-ai-shell-title">Teaching Copilot</h2>
                        <p class="lms-ai-shell-desc">Draft a remediation pack from risk reasons, metrics, and course materials. Nothing is saved until you accept it.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('ai.cases.remediation', $case) }}">
                    @csrf
                    <button type="submit" class="lms-btn-primary lms-btn-primary--xs" @disabled($aiPending)>
                        {{ $aiPending ? 'Generating…' : (($aiReady || $aiFailed) ? 'Regenerate pack' : 'Generate remediation pack') }}
                    </button>
                </form>
            </div>

            <div class="lms-ai-shell-body">
                <x-input-error :messages="$errors->get('ai')" class="mt-0" />

                @if ($aiPending)
                    <x-lms.ai-alert
                        title="Generating remediation pack"
                        message="This usually takes a few seconds. Refresh if the page still shows pending."
                        tone="info"
                    />
                @elseif ($aiFailed && $aiError)
                    <x-lms.ai-alert
                        :title="$aiError['title']"
                        :message="$aiError['message']"
                        :tone="$aiError['tone']"
                        :action-label="$aiError['action_label']"
                        :action-url="$aiError['action_url']"
                    />
                @elseif ($aiReady)
                    <div class="lms-ai-tabs" role="tablist" aria-label="Remediation pack sections">
                        <button type="button" class="lms-ai-tab" role="tab" :class="tab === 'why' && 'is-active'" @click="tab = 'why'">Why</button>
                        <button type="button" class="lms-ai-tab" role="tab" :class="tab === 'agenda' && 'is-active'" @click="tab = 'agenda'">Agenda</button>
                        <button type="button" class="lms-ai-tab" role="tab" :class="tab === 'study' && 'is-active'" @click="tab = 'study'">Study brief</button>
                        <button type="button" class="lms-ai-tab" role="tab" :class="tab === 'quiz' && 'is-active'" @click="tab = 'quiz'">Quiz ({{ count($out['quiz'] ?? []) }})</button>
                        @if (! empty($out['feedback_starter']))
                            <button type="button" class="lms-ai-tab" role="tab" :class="tab === 'feedback' && 'is-active'" @click="tab = 'feedback'">Feedback</button>
                        @endif
                    </div>

                    <div class="lms-ai-tab-panels">
                        <div class="lms-ai-tab-panel" x-show="tab === 'why'" x-cloak>
                            <h3 class="lms-ai-panel-heading">Why this student</h3>
                            <p class="lms-ai-panel-copy">{{ $out['why'] ?? '—' }}</p>
                        </div>

                        <div class="lms-ai-tab-panel" x-show="tab === 'agenda'" x-cloak>
                            <h3 class="lms-ai-panel-heading">Mentoring agenda</h3>
                            <ul class="lms-ai-agenda">
                                @foreach ($out['agenda'] ?? [] as $item)
                                    <li class="lms-ai-agenda-item">
                                        <div class="lms-ai-agenda-top">
                                            <strong>{{ $item['title'] ?? 'Action' }}</strong>
                                            <span class="lms-ai-chip">{{ str_replace('_', ' ', $item['type'] ?? 'strategy') }}</span>
                                        </div>
                                        <p>{{ $item['notes'] ?? '' }}</p>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($aiGeneration->isReady())
                                <form method="POST" action="{{ route('ai.generations.accept-agenda', $aiGeneration) }}" class="lms-ai-actions">
                                    @csrf
                                    <button type="submit" class="lms-btn-primary lms-btn-primary--xs">Accept agenda into case log</button>
                                </form>
                            @endif
                        </div>

                        <div class="lms-ai-tab-panel" x-show="tab === 'study'" x-cloak>
                            <h3 class="lms-ai-panel-heading">Study brief</h3>
                            <p class="lms-ai-panel-copy whitespace-pre-wrap">{{ $out['study_brief'] ?? '—' }}</p>
                        </div>

                        <div class="lms-ai-tab-panel" x-show="tab === 'quiz'" x-cloak>
                            <h3 class="lms-ai-panel-heading">Remediation quiz draft</h3>
                            <ol class="lms-ai-quiz">
                                @foreach ($out['quiz'] ?? [] as $q)
                                    <li>
                                        <p class="lms-ai-quiz-prompt">{{ $q['prompt'] ?? '' }}</p>
                                        <ul class="lms-ai-quiz-options">
                                            @foreach ($q['options'] ?? [] as $idx => $opt)
                                                <li @class(['is-correct' => ((int) ($q['correct'] ?? 0)) === $idx + 1])>
                                                    {{ $opt['label'] ?? '' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ol>
                            @if ($aiGeneration->isReady() && ! empty($out['quiz']))
                                <form method="POST" action="{{ route('ai.generations.accept-quiz', $aiGeneration) }}" class="lms-ai-actions">
                                    @csrf
                                    <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Create draft assessment</button>
                                </form>
                            @endif
                        </div>

                        @if (! empty($out['feedback_starter']))
                            <div class="lms-ai-tab-panel" x-show="tab === 'feedback'" x-cloak>
                                <h3 class="lms-ai-panel-heading">Feedback starter</h3>
                                <p class="lms-ai-panel-copy whitespace-pre-wrap">{{ $out['feedback_starter'] }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($aiGeneration->isReady())
                        <div class="lms-ai-shell-footer">
                            <form method="POST" action="{{ route('ai.generations.discard', $aiGeneration) }}">
                                @csrf
                                <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Discard draft</button>
                            </form>
                            <p class="lms-ai-footnote">Review before accepting. Drafts stay private to staff.</p>
                        </div>
                    @endif
                @else
                    <x-lms.ai-alert
                        title="No pack generated yet"
                        message="Click Generate remediation pack to draft mentoring actions, a study brief, and a practice quiz for this student."
                        tone="info"
                    />
                @endif
            </div>
        </section>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Case status</h2>
                    <p class="corp-panel-desc">Update progress and refresh the latest performance snapshot.</p>
                </div>
            </div>
            <div class="corp-panel-body space-y-4">
                <form method="POST" action="{{ route('reports.at-risk.cases.update', $case) }}" class="space-y-3" x-data>
                    @csrf
                    @method('PATCH')
                    <div class="lms-form-field">
                        <label for="case-status" class="lms-field-label">Status</label>
                        <select id="case-status" name="status" class="lms-field-input mt-1.5">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected($case->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        type="submit"
                        class="lms-btn-primary"
                        x-on:click.prevent="
                            const status = document.getElementById('case-status').value;
                            if (status === 'resolved' || status === 'dismissed') {
                                Swal.fire({
                                    title: status === 'resolved' ? 'Resolve this case?' : 'Dismiss this case?',
                                    text: 'Latest metrics will be saved as the closing snapshot.',
                                    icon: 'question',
                                    showCancelButton: true,
                                    focusCancel: true,
                                    confirmButtonText: status === 'resolved' ? 'Yes, resolve' : 'Yes, dismiss',
                                    cancelButtonText: 'Cancel',
                                    confirmButtonColor: '#0f766e',
                                    cancelButtonColor: '#64748b',
                                }).then((result) => { if (result.isConfirmed) $el.closest('form').submit(); });
                            } else {
                                $el.closest('form').submit();
                            }
                        "
                    >Save status</button>
                </form>

                <form method="POST" action="{{ route('reports.at-risk.cases.refresh', $case) }}">
                    @csrf
                    <button type="submit" class="lms-btn-secondary">Refresh latest metrics</button>
                </form>

                @if ($case->reasons)
                    <div>
                        <h3 class="text-sm font-semibold text-isarva-ink mb-1">Reasons at open</h3>
                        <ul class="list-disc pl-5 text-sm text-isarva-muted space-y-0.5">
                            @foreach ($case->reasons as $reason)
                                <li>{{ is_array($reason) ? ($reason['label'] ?? '') : $reason }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </section>

        <section class="corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Baseline vs latest</h2>
                    <p class="corp-panel-desc">Improvement delta since the case was opened.</p>
                </div>
            </div>
            <div class="corp-panel-body">
                @php
                    $baseline = $case->baseline_metrics ?? [];
                    $rows = [
                        ['Assignment avg %', 'assignment_avg', '%'],
                        ['Missing overdue', 'missing_overdue', ''],
                        ['Late submissions', 'late_count', ''],
                        ['Quiz avg %', 'quiz_avg', '%'],
                        ['Participation %', 'participation_rate', '%'],
                    ];
                @endphp
                <div class="corp-table-wrap">
                    <table class="corp-table corp-table--compact">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Baseline</th>
                                <th>Latest</th>
                                <th>Δ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as [$label, $key, $suffix])
                                <tr class="corp-table-row">
                                    <td class="corp-table-cell">{{ $label }}</td>
                                    <td class="corp-table-cell">{{ ($baseline[$key] ?? null) !== null ? $baseline[$key].$suffix : '—' }}</td>
                                    <td class="corp-table-cell">{{ ($latest[$key] ?? null) !== null ? $latest[$key].$suffix : '—' }}</td>
                                    <td class="corp-table-cell">
                                        @if (($delta[$key] ?? null) !== null)
                                            {{ $delta[$key] > 0 ? '+' : '' }}{{ $delta[$key] }}{{ $suffix }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Log intervention</h2>
                <p class="corp-panel-desc">Record mentoring, extra classes, strategies, support, or improvement notes.</p>
            </div>
        </div>
        <div class="corp-panel-body">
            <form method="POST" action="{{ route('reports.at-risk.cases.actions.store', $case) }}" class="grid gap-3 md:grid-cols-2">
                @csrf
                <div class="lms-form-field">
                    <label for="action-type" class="lms-field-label">Type</label>
                    <select id="action-type" name="type" class="lms-field-input mt-1.5" required>
                        @foreach ($actionTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lms-form-field">
                    <label for="action-conducted" class="lms-field-label">Conducted at</label>
                    <input id="action-conducted" type="datetime-local" name="conducted_at" class="lms-field-input mt-1.5" value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="lms-form-field md:col-span-2">
                    <label for="action-title" class="lms-field-label">Title</label>
                    <input id="action-title" type="text" name="title" class="lms-field-input mt-1.5" required maxlength="255" placeholder="e.g. Mentoring on assignment workflow">
                </div>
                <div class="lms-form-field md:col-span-2">
                    <label for="action-notes" class="lms-field-label">Notes</label>
                    <textarea id="action-notes" name="notes" rows="3" class="lms-field-input mt-1.5" placeholder="What was done, agreed next steps, observed improvement…"></textarea>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="lms-btn-primary">Add entry</button>
                </div>
            </form>
        </div>
    </section>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Intervention timeline</h2>
                <p class="corp-panel-desc">Chronological log of support actions for this student.</p>
            </div>
            <span class="corp-sidebar-badge">{{ $case->actions->count() }}</span>
        </div>
        @if ($case->actions->isNotEmpty())
            <div class="corp-table-wrap">
                <table class="corp-table corp-table--compact">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Type</th>
                            <th>Title / notes</th>
                            <th>By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($case->actions as $action)
                            <tr class="corp-table-row">
                                <td class="corp-table-cell corp-table-cell--muted">
                                    {{ $action->conducted_at?->format('Y-m-d H:i') ?? $action->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="corp-table-cell">{{ $action->type->label() }}</td>
                                <td class="corp-table-cell">
                                    <span class="corp-table-title">{{ $action->title }}</span>
                                    @if ($action->notes)
                                        <div class="text-sm text-isarva-muted mt-0.5">{{ $action->notes }}</div>
                                    @endif
                                </td>
                                <td class="corp-table-cell corp-table-cell--muted">{{ $action->creator?->name ?? '—' }}</td>
                                <td class="corp-table-cell">
                                    <form
                                        method="POST"
                                        action="{{ route('reports.at-risk.actions.destroy', $action) }}"
                                        x-data
                                        x-on:submit.prevent="
                                            Swal.fire({
                                                title: 'Remove this entry?',
                                                text: 'This cannot be undone.',
                                                icon: 'warning',
                                                showCancelButton: true,
                                                focusCancel: true,
                                                confirmButtonText: 'Yes, remove',
                                                cancelButtonText: 'Cancel',
                                                confirmButtonColor: '#b91c1c',
                                                cancelButtonColor: '#64748b',
                                            }).then((result) => { if (result.isConfirmed) $el.submit(); })
                                        "
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="corp-panel-body">
                <p class="text-sm text-isarva-muted">No interventions logged yet.</p>
            </div>
        @endif
    </section>
</div>
@endsection
