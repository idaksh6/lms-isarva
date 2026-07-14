@extends('layouts.lms')

@section('title', 'Manage students — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="enrollments" />

    <div class="lms-page-grid-2">
        <section class="lms-panel">
            <div class="lms-panel-header">
                <div class="lms-panel-heading">
                    <h2 class="lms-panel-title">Enrolled students</h2>
                    <span class="lms-panel-count">{{ $course->students->count() }}</span>
                </div>
            </div>
            <div class="lms-panel-body">
                @if ($course->students->isEmpty())
                    <div class="lms-empty-panel">
                        <p class="text-sm font-medium text-isarva-muted">No students enrolled yet.</p>
                        <p class="mt-1 text-xs text-slate-400">Select students on the right to add them to this course.</p>
                    </div>
                @else
                    <ul class="lms-student-list">
                        @foreach ($course->students as $student)
                            <li class="lms-student-row">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="lms-student-avatar">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-isarva-heading">{{ $student->name }}</p>
                                        <p class="truncate text-sm text-isarva-muted">{{ $student->student_id ?? $student->email }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('courses.enrollments.destroy', [$course, $student]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-700">Remove</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="lms-panel">
            <div class="lms-panel-header">
                <div class="lms-panel-heading">
                    <h2 class="lms-panel-title">Add students</h2>
                    @if ($availableStudents->isNotEmpty())
                        <span class="lms-panel-count">{{ $availableStudents->count() }} available</span>
                    @endif
                </div>
            </div>
            <div class="lms-panel-body">
                @if ($availableStudents->isEmpty())
                    <div class="lms-empty-panel">
                        <p class="text-sm font-medium text-isarva-muted">All students are already enrolled.</p>
                    </div>
                @else
                    <form
                        method="POST"
                        action="{{ route('courses.enrollments.store', $course) }}"
                        class="lms-enrollment-form"
                        x-data="{
                            query: '',
                            visibleCount: {{ $availableStudents->count() }},
                            matches(name, email, studentId) {
                                const q = this.query.trim().toLowerCase();
                                if (!q) {
                                    return true;
                                }

                                return name.toLowerCase().includes(q)
                                    || email.toLowerCase().includes(q)
                                    || (studentId || '').toLowerCase().includes(q);
                            },
                            updateVisibleCount() {
                                this.visibleCount = Array.from(this.$refs.picker.querySelectorAll('[data-enrollment-student]'))
                                    .filter((item) => item.offsetParent !== null)
                                    .length;
                            },
                        }"
                        x-init="$watch('query', () => $nextTick(() => updateVisibleCount()))"
                    >
                        @csrf

                        <div>
                            <label for="enrollment-search" class="sr-only">Search students</label>
                            <input
                                id="enrollment-search"
                                type="search"
                                x-model="query"
                                placeholder="Search by name, email, or student ID"
                                class="lms-field-input"
                                autocomplete="off"
                            >
                            <p class="mt-1.5 text-xs text-isarva-muted" x-show="query" x-cloak>
                                <span x-text="visibleCount"></span> matching student<span x-show="visibleCount !== 1">s</span>
                            </p>
                        </div>

                        <div class="lms-student-picker" x-ref="picker">
                            @foreach ($availableStudents as $student)
                                <label
                                    class="lms-student-picker-item"
                                    data-enrollment-student
                                    x-show="matches(@js($student->name), @js($student->email), @js($student->student_id ?? ''))"
                                    x-cloak
                                >
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="mt-1 shrink-0 sm:mt-0">
                                    <span class="lms-student-avatar shrink-0">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                    <span class="lms-student-picker-copy">
                                        <span class="block truncate font-medium text-isarva-heading">{{ $student->name }}</span>
                                        @if ($student->student_id)
                                            <span class="lms-student-picker-meta">{{ $student->student_id }}</span>
                                        @endif
                                        <span class="lms-student-picker-meta">{{ $student->email }}</span>
                                    </span>
                                </label>
                            @endforeach

                            <p
                                class="px-3 py-6 text-center text-sm text-isarva-muted"
                                x-show="query && visibleCount === 0"
                                x-cloak
                            >
                                No students match your search.
                            </p>
                        </div>

                        <button type="submit" class="lms-btn-primary w-full sm:w-auto">Enroll selected</button>
                    </form>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
