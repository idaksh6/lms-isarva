@extends('layouts.lms')

@section('title', 'Submissions')
@section('page_title', 'Submissions')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="submissions" variant="analytics" title="Submissions inbox" subtitle="Track student work, review status, and open files quickly.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ $stats['total'] }}</strong> total</span>
            <span class="lms-stat-chip"><strong>{{ $stats['pending_review'] }}</strong> awaiting review</span>
            <span class="lms-stat-chip"><strong>{{ $stats['graded'] }}</strong> reviewed</span>
        </div>
    </x-lms.module-hero>

    <form method="GET" class="lms-filter-bar">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search student or assignment..." class="lms-field-input lms-filter-search">
        <div class="lms-filter-select-wrap">
            <select name="status" class="lms-field-input lms-filter-select">
                <option value="">All statuses</option>
                @foreach (\App\Enums\SubmissionStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="lms-btn-secondary">Filter</button>
    </form>

    <div class="lms-hub-list lms-hub-list--submissions">
        @forelse ($submissions as $submission)
            <x-lms.submission-list-item :submission="$submission" />
        @empty
            <x-lms.empty-state title="No submissions yet" message="Submissions appear here when students upload their work." variant="analytics" />
        @endforelse
    </div>

    @if ($submissions->hasPages())
        <div>{{ $submissions->links() }}</div>
    @endif
</div>
@endsection
