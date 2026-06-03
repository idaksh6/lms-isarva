@extends('layouts.lms')

@section('title', 'Submission')
@section('heading', 'Submission')
@section('subheading', $submission->assignment->title)

@section('content')
<div class="mb-6 flex flex-wrap gap-3">
    <a href="{{ route('assignments.show', $submission->assignment) }}" class="lms-btn-back">← Assignment</a>
    @if ((auth()->user()->isLecturer() || auth()->user()->isAdmin()) && $submission->status !== \App\Enums\SubmissionStatus::Reviewed)
        <form method="POST" action="{{ route('submissions.reviewed', $submission) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="lms-btn-primary">Mark as reviewed</button>
        </form>
    @endif
</div>

<div class="lms-card w-full space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500">Student</p>
            <p class="text-lg font-bold text-slate-900">{{ $submission->student->name }}</p>
            @if ($submission->student->student_id)
                <p class="text-sm text-slate-500">ID: {{ $submission->student->student_id }}</p>
            @endif
        </div>
        <x-status-badge :status="$submission->status" />
    </div>

    <div>
        <p class="text-sm text-slate-500">Submitted</p>
        <p class="font-medium text-slate-900">{{ $submission->submitted_at->format('l, F j, Y g:i A') }}</p>
    </div>

    @if ($submission->notes)
        <div>
            <p class="text-sm font-semibold text-slate-700">Student notes</p>
            <p class="mt-1 whitespace-pre-wrap text-slate-600">{{ $submission->notes }}</p>
        </div>
    @endif

    <a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank" download
       class="inline-flex lms-btn-primary">
        Download {{ $submission->file_name }}
    </a>
</div>
@endsection
