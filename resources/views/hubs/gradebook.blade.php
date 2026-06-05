@extends('layouts.lms')

@php
    use App\Support\GradeHelper;
@endphp

@section('title', 'Gradebook')
@section('page_title', 'Gradebook')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="gradebook" variant="analytics" title="Gradebook" subtitle="See every student score per assignment and export marks for your records.">
        @if ($selectedCourse)
            <div class="lms-stat-chips">
                <span class="lms-stat-chip"><strong>{{ $selectedCourse->students_count ?? $selectedCourse->students->count() }}</strong> students</span>
                <span class="lms-stat-chip"><strong>{{ $selectedCourse->assignments->count() }}</strong> assignments</span>
            </div>
        @endif
    </x-lms.module-hero>

    <form method="GET" class="lms-filter-bar lms-filter-bar--gradebook">
        <div class="lms-filter-select-wrap">
            <label for="gradebook-course" class="sr-only">Select course</label>
            <select id="gradebook-course" name="course" class="lms-field-input lms-filter-select" onchange="this.form.submit()">
                <option value="">Select a course…</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected($selectedCourse?->id === $course->id) title="{{ $course->code }} — {{ $course->title }}">
                        {{ $course->code }} — {{ \Illuminate\Support\Str::limit($course->title, 32) }}
                    </option>
                @endforeach
            </select>
        </div>
        @if ($selectedCourse)
            <a href="{{ route('gradebook.export', ['course' => $selectedCourse->id]) }}" class="lms-btn-primary">Export CSV</a>
        @endif
    </form>

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
    @else
        <x-lms.empty-state title="Choose a course" message="Select a course above to view the gradebook." variant="analytics" />
    @endif
</div>
@endsection
