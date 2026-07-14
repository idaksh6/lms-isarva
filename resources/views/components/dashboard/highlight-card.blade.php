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
        $hasStudents = ($students ?? 0) > 0;
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
            <div class="mt-4 flex items-start gap-3 rounded-lg border border-dashed border-amber-200 bg-amber-50/60 p-3.5 sm:items-center">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-amber-200 bg-white text-amber-700 shadow-sm" aria-hidden="true">
                    <svg class="h-4 w-4" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-amber-950">Awaiting assignments</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-amber-900/80">
                        @if ($hasStudents)
                            {{ $students }} {{ $students === 1 ? 'student' : 'students' }} enrolled — publish tasks to start tracking completion.
                        @else
                            Enroll students and add assignments to begin tracking course progress.
                        @endif
                    </p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span @class([
                            'inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium',
                            'border-emerald-200 bg-emerald-50 text-emerald-800' => $hasStudents,
                            'border-slate-200 bg-white text-slate-600' => ! $hasStudents,
                        ])>
                            @if ($hasStudents)
                                <span class="text-[10px] font-bold leading-none" aria-hidden="true">✓</span>
                            @endif
                            Students enrolled
                        </span>
                        <span class="inline-flex items-center rounded-full border border-amber-200 bg-white px-2 py-0.5 text-[11px] font-medium text-amber-800">
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
