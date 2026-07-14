@props([
    'course' => null,
    'progress' => 0,
    'subtitle' => null,
])

@if ($course)
    @php
        $pct = min(100, max(0, (int) $progress));
        $students = $course->students_count ?? null;
        $tasks = $course->assignments_count ?? null;
    @endphp
    <a href="{{ route('courses.show', $course) }}" class="corp-highlight group">
        <div class="corp-highlight-top">
            <p class="corp-highlight-label">{{ $subtitle ?? 'Primary focus' }}</p>
            <span class="corp-highlight-link">View course →</span>
        </div>

        <h2 class="corp-highlight-title">{{ $course->title }}</h2>

        <div class="corp-highlight-meta">
            <span class="corp-code-badge">{{ $course->code }}</span>
            @if ($course->relationLoaded('lecturer') && $course->lecturer)
                <span>{{ $course->lecturer->name }}</span>
            @endif
            @if ($students !== null)
                <span class="corp-highlight-stat">{{ $students }} {{ $students === 1 ? 'student' : 'students' }}</span>
            @endif
            @if ($tasks !== null)
                <span class="corp-highlight-stat">{{ $tasks }} {{ $tasks === 1 ? 'task' : 'tasks' }}</span>
            @endif
        </div>

        @if ($tasks === 0)
            <div class="corp-highlight-setup">
                <div class="corp-highlight-setup-icon" aria-hidden="true">
                    @include('layouts.partials.stat-icon', ['name' => 'clipboard'])
                </div>
                <div class="corp-highlight-setup-body">
                    <p class="corp-highlight-setup-title">Awaiting assignments</p>
                    <p class="corp-highlight-setup-desc">
                        @if (($students ?? 0) > 0)
                            {{ $students }} {{ $students === 1 ? 'student' : 'students' }} enrolled — publish tasks to start tracking completion.
                        @else
                            Enroll students and add assignments to begin tracking course progress.
                        @endif
                    </p>
                    <div class="corp-highlight-setup-steps">
                        <span @class([
                            'corp-setup-step',
                            'corp-setup-step--done' => ($students ?? 0) > 0,
                            'corp-setup-step--pending' => ($students ?? 0) === 0,
                        ])>
                            @if (($students ?? 0) > 0)
                                <svg class="corp-setup-step-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                            @endif
                            Students enrolled
                        </span>
                        <span class="corp-setup-step corp-setup-step--pending">
                            Add assignments
                        </span>
                    </div>
                </div>
            </div>
        @else
            <div class="corp-highlight-foot">
                <span class="corp-highlight-foot-label">Completion</span>
                <div class="corp-progress-track corp-progress-track--highlight" role="presentation">
                    <div class="corp-progress-fill" style="width: {{ $pct }}%"></div>
                </div>
                <span class="corp-highlight-foot-value">{{ $pct }}%</span>
            </div>
        @endif
    </a>
@endif
