@extends('layouts.lms')

@php
    use App\Support\GradeHelper;
@endphp

@section('title', 'Gradebook')
@section('page_title', 'Gradebook')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="gradebook" title="Gradebook" subtitle="See every student score per assignment and export marks for your records.">
        @if ($selectedCourse)
            <div class="lms-stat-chips">
                <span class="lms-stat-chip"><strong>{{ $selectedCourse->students_count ?? $selectedCourse->students->count() }}</strong> students</span>
                <span class="lms-stat-chip"><strong>{{ $selectedCourse->assignments->count() }}</strong> assignments</span>
            </div>
        @endif
    </x-lms.module-hero>

    @if ($selectedCourse)
        <form method="GET" class="lms-gradebook-toolbar">
            <div class="lms-gradebook-toolbar-field">
                <label for="gradebook-course" class="lms-gradebook-toolbar-label">Viewing course</label>
                <div class="lms-filter-select-wrap">
                    <select id="gradebook-course" name="course" class="lms-field-input lms-filter-select lms-gradebook-select" onchange="this.form.submit()">
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected($selectedCourse->id === $course->id) title="{{ $course->code }} — {{ $course->title }}">
                                {{ $course->code }} — {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <a href="{{ route('gradebook.export', ['course' => $selectedCourse->id]) }}" class="lms-btn-primary">Export CSV</a>
        </form>
    @else
        <section class="lms-gradebook-picker">
            <div class="lms-gradebook-picker-head">
                <span class="lms-gradebook-picker-step">Step 1</span>
                <h2 class="lms-gradebook-picker-title">Select a course to open its gradebook</h2>
                <p class="lms-gradebook-picker-hint">Pick from the list below or use the dropdown — student scores will appear once a course is chosen.</p>
            </div>

            <form method="GET" class="lms-gradebook-picker-form">
                <label for="gradebook-course" class="lms-gradebook-picker-label">Course</label>
                <div class="lms-gradebook-picker-row">
                    <div class="lms-filter-select-wrap lms-gradebook-picker-select-wrap">
                        <select id="gradebook-course" name="course" class="lms-field-input lms-filter-select lms-gradebook-select" required onchange="this.form.submit()">
                            <option value="" disabled selected>Choose a course…</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" title="{{ $course->code }} — {{ $course->title }}">
                                    {{ $course->code }} — {{ $course->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="lms-btn-primary">Open gradebook</button>
                </div>
            </form>

            @if ($courses->isNotEmpty())
                <div class="lms-gradebook-picker-divider">
                    <span>Or pick a course</span>
                </div>
                <div class="lms-gradebook-course-grid">
                    @foreach ($courses as $course)
                        <a href="{{ route('gradebook.index', ['course' => $course->id]) }}" class="lms-gradebook-course-card">
                            <span class="lms-gradebook-course-code">{{ $course->code }}</span>
                            <span class="lms-gradebook-course-title">{{ $course->title }}</span>
                            <span class="lms-gradebook-course-meta">{{ $course->students_count ?? 0 }} students</span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="lms-gradebook-picker-empty">No active courses available yet.</p>
            @endif
        </section>
    @endif

    @if ($selectedCourse && $rows->isNotEmpty())
        <section class="lms-panel">
            <div class="lms-panel-header">
                <div class="min-w-0">
                    <h2 class="lms-panel-title">{{ $selectedCourse->code }}</h2>
                    <p class="truncate text-sm text-slate-500">{{ $selectedCourse->title }}</p>
                </div>
                <span class="lms-panel-count shrink-0">{{ $rows->count() }} students</span>
            </div>
            <div class="lms-gradebook-scroll">
                <table class="lms-gradebook-table">
                    <thead>
                        <tr>
                            <th class="lms-gradebook-col-student">Student</th>
                            @foreach ($selectedCourse->assignments as $assignment)
                                <th class="lms-gradebook-col-assignment">{{ $assignment->title }}</th>
                            @endforeach
                            <th class="lms-gradebook-col-avg">Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="lms-gradebook-col-student">
                                    <span class="font-medium text-slate-900">{{ $row['student']->name }}</span>
                                    @if ($row['student']->student_id)
                                        <span class="block text-xs text-slate-500">{{ $row['student']->student_id }}</span>
                                    @endif
                                </td>
                                @foreach ($row['cells'] as $cell)
                                    <td class="lms-gradebook-col-assignment">
                                        @if ($cell['submission']?->isGraded())
                                            <div class="lms-gradebook-grade-wrap">
                                                <x-lms.grade-badge :score="$cell['submission']->score" :letter="$cell['submission']->letter_grade" size="sm" />
                                            </div>
                                        @elseif ($cell['submission'])
                                            <span class="lms-gradebook-status">{{ $cell['submission']->status->label() }}</span>
                                        @else
                                            <span class="lms-gradebook-dash">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="lms-gradebook-col-avg">
                                    @if ($row['average'] !== null)
                                        @php $avgLetter = GradeHelper::letterFromScore((float) $row['average']); @endphp
                                        <span @class(['lms-gradebook-avg-chip', GradeHelper::colorClass($avgLetter)])>
                                            {{ $row['average'] }}%
                                        </span>
                                    @else
                                        <span class="lms-gradebook-dash">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @elseif ($selectedCourse)
        <x-lms.empty-state title="No students enrolled" message="Add students to this course to start tracking grades." variant="analytics" />
    @endif
</div>
@endsection
