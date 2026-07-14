@extends('layouts.lms')

@section('title', 'Edit class — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="sessions" />
    <x-lms.class-session-form :course="$course" :session="$session" :action="route('class-sessions.update', $session)" method="PATCH" />
</div>
@endsection
