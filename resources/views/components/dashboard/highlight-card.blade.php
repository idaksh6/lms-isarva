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
            <div class="corp-highlight-foot">
                <span class="corp-highlight-foot-label">Completion</span>
                <div class="corp-progress-track corp-progress-track--highlight" role="presentation" aria-hidden="true"></div>
                <span class="corp-highlight-foot-value text-slate-500">No tasks yet</span>
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
