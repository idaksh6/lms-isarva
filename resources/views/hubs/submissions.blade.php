@extends('layouts.lms')

@section('title', 'Submissions')
@section('page_title', 'Submissions')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="submissions" title="Submissions inbox" subtitle="Track student work, review status, and open files quickly.">
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
        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Filter</button>
    </form>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Student submissions</h2>
                <p class="corp-panel-desc">{{ $submissions->total() }} results · open a row to review or grade work</p>
            </div>
        </div>

        @if ($submissions->isNotEmpty())
            <div class="corp-table-wrap">
                <table class="corp-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="corp-table-col--md">Grade</th>
                            <th><span class="sr-only">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            <x-lms.submission-table-row :submission="$submission" />
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($submissions->hasPages())
                <div class="border-t border-isarva-border px-4 py-3 sm:px-5">
                    {{ $submissions->links() }}
                </div>
            @endif
        @else
            <x-lms.empty-state title="No submissions yet" message="Submissions appear here when students upload their work." variant="inbox" />
        @endif
    </section>
</div>
@endsection
