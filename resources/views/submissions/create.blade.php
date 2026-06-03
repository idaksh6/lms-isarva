@extends('layouts.lms')

@section('title', 'Submit work')
@section('heading', 'Submit your work')
@section('subheading', $assignment->title)

@section('content')
<div class="mb-6 lms-card w-full">
    <p class="text-sm text-slate-600">Course: <strong>{{ $assignment->course->code }}</strong></p>
    @if ($assignment->due_at)
        <p class="mt-1 text-sm {{ $assignment->isOverdue() ? 'text-rose-600 font-semibold' : 'text-slate-500' }}">
            Due: {{ $assignment->due_at->format('M j, Y g:i A') }}
        </p>
    @endif
</div>

<form method="POST" action="{{ route('assignments.submissions.store', $assignment) }}" enctype="multipart/form-data" class="lms-card w-full space-y-5">
    @csrf
    <div>
        <label class="block text-sm font-semibold text-slate-700">Upload file</label>
        <input type="file" name="file" class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 p-6 text-sm" required>
        <p class="mt-1 text-xs text-slate-500">Notebooks, PDFs, ZIP archives — max 20 MB</p>
        <x-input-error :messages="$errors->get('file')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-semibold text-slate-700">Notes (optional)</label>
        <textarea name="notes" rows="4" class="lms-input mt-1" placeholder="Link to repo, comments for your lecturer...">{{ old('notes') }}</textarea>
    </div>
    <div class="flex gap-3">
        <button type="submit" class="lms-btn-primary">Submit assignment</button>
        <a href="{{ route('assignments.show', $assignment) }}" class="lms-btn-secondary">Cancel</a>
    </div>
</form>
@endsection
