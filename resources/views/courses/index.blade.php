@extends('layouts.lms')

@section('title', 'Courses')
@section('page_title', 'Courses')

@section('content')
@if (auth()->user()->isAdmin() || auth()->user()->isLecturer())
    <div class="mb-6">
        <a href="{{ route('courses.create') }}" class="lms-btn-primary">Create course <span aria-hidden="true">→</span></a>
    </div>
@endif

@php
    $user = auth()->user();
@endphp

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
@endsection
