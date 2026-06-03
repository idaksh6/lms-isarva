@extends('layouts.lms')

@section('title', 'Manage students — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="enrollments" />

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="lms-panel lms-panel--fill">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Enrolled students</h2>
                <span class="lms-panel-count">{{ $course->students->count() }}</span>
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

        <section class="lms-panel lms-panel--fill">
            <div class="lms-panel-header">
                <h2 class="lms-panel-title">Add students</h2>
                @if ($availableStudents->isNotEmpty())
                    <span class="lms-panel-count">{{ $availableStudents->count() }} available</span>
                @endif
            </div>
            <div class="lms-panel-body">
                @if ($availableStudents->isEmpty())
                    <div class="lms-empty-panel">
                        <p class="text-sm font-medium text-isarva-muted">All students are already enrolled.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('courses.enrollments.store', $course) }}" class="space-y-4">
                        @csrf
                        <div class="lms-student-picker">
                            @foreach ($availableStudents as $student)
                                <label class="lms-student-picker-item">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}">
                                    <span class="lms-student-avatar">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-medium text-isarva-heading">{{ $student->name }}</span>
                                        <span class="block truncate text-xs text-isarva-muted">{{ $student->student_id }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="lms-btn-primary w-full sm:w-auto">Enroll selected</button>
                    </form>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
