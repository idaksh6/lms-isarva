@extends('layouts.lms')

@section('title', 'Courses')
@section('page_title', 'Courses')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="courses" variant="analytics" title="Courses" subtitle="Browse modules, view assignments, and track your progress.">
    </x-lms.module-hero>

@if (auth()->user()->isAdmin() || auth()->user()->isLecturer())
    <div class="mb-6">
        <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course <span aria-hidden="true">→</span></a>
    </div>
@endif

@php
    $user = auth()->user();
@endphp

<form method="GET" class="lms-filter-bar">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search by course title or code..." class="lms-field-input lms-filter-search">
    @if ($user->isAdmin() || $user->isLecturer())
        <div class="lms-filter-select-wrap">
            <select name="status" class="lms-field-input lms-filter-select">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Archived / Inactive</option>
            </select>
        </div>
    @endif
    <button type="submit" class="lms-btn-secondary">Filter</button>
</form>

<div class="lms-courses-grid">
    @forelse ($courses as $course)
        @php
            $progress = match (true) {
                $user->isStudent() => \App\Support\DashboardMetrics::studentCourseProgress($user, $course),
                $user->isLecturer() => \App\Support\DashboardMetrics::lecturerCourseProgress($course),
                default => \App\Support\DashboardMetrics::adminCourseProgress($course),
            };
        @endphp
        <x-lms.course-card :course="$course" :progress="$progress" />
    @empty
        <div class="sm:col-span-2 xl:col-span-3">
            <x-lms.empty-state
                title="No courses yet"
                message="Create your first Data Science module or check back when you are enrolled."
                variant="books"
            >
                @if ($user->isAdmin() || $user->isLecturer())
                    <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course <span aria-hidden="true">→</span></a>
                @endif
            </x-lms.empty-state>
        </div>
    @endforelse
</div>

</div>
@endsection
