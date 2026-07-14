@extends('layouts.lms')

@section('title', 'Schedule class — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="sessions" />
    <x-lms.class-session-form :course="$course" :action="route('courses.sessions.store', $course)" />
</div>
@endsection
