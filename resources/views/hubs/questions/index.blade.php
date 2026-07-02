@extends('layouts.lms')

@section('title', 'Q&A')
@section('page_title', 'Q&A')

@section('content')
<div class="lms-page-stack corp-qa-page">
    <x-lms.module-hero module="questions" title="Questions & answers" subtitle="Ask the community, share knowledge, and get help from students, lecturers, and administrators.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ $stats['total'] }}</strong> questions</span>
            <span class="lms-stat-chip"><strong>{{ $stats['open'] }}</strong> open</span>
            <span class="lms-stat-chip"><strong>{{ $stats['answered'] }}</strong> answered</span>
        </div>
        <a href="{{ route('questions.create') }}" class="lms-btn-primary lms-btn-primary--xs">Ask a question</a>
    </x-lms.module-hero>

    <form method="GET" class="lms-filter-bar">
        <div class="lms-filter-select-wrap">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="lms-field-input lms-filter-select" aria-label="Filter by status">
                <option value="">All questions</option>
                <option value="open" @selected($status === 'open')>Open</option>
                <option value="answered" @selected($status === 'answered')>Answered</option>
            </select>
        </div>
        <div class="lms-filter-select-wrap lms-filter-select-wrap--wide">
            <label for="course_id" class="sr-only">Course</label>
            <select id="course_id" name="course_id" class="lms-field-input lms-filter-select" aria-label="Filter by course">
                <option value="">All courses</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected($courseId === $course->id)>{{ $course->code }} — {{ $course->title }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Filter</button>
        @if ($status || $courseId)
            <a href="{{ route('questions.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Clear</a>
        @endif
    </form>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Discussion board</h2>
                <p class="corp-panel-desc">Browse questions posted by the community. Select a row to view the full thread and replies.</p>
            </div>
        </div>

        @if ($questions->isNotEmpty())
            <div class="corp-table-wrap">
                <table class="corp-table corp-table--qa">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th class="corp-table-col--sm">Asked by</th>
                            <th class="corp-table-col--sm">Posted</th>
                            <th class="corp-table-col--xs">Answers</th>
                            <th class="corp-table-col--xs">Status</th>
                            <th><span class="sr-only">Action</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($questions as $question)
                            <tr class="corp-table-row">
                                <td class="corp-table-cell">
                                    <a href="{{ route('questions.show', $question) }}" class="corp-qa-table-title">{{ $question->title }}</a>
                                    <p class="corp-qa-table-excerpt">{{ Str::limit(strip_tags($question->body), 100) }}</p>
                                    <p class="corp-qa-table-context">
                                        @if ($question->course)
                                            <span class="corp-code-badge">{{ $question->course->code }}</span>
                                        @else
                                            <span class="corp-qa-scope">General</span>
                                        @endif
                                    </p>
                                </td>
                                <td class="corp-table-cell corp-table-col--sm">
                                    <span class="corp-qa-table-author">{{ $question->author->name }}</span>
                                    <span class="corp-qa-table-role">{{ $question->author->role?->label() }}</span>
                                </td>
                                <td class="corp-table-cell corp-table-col--sm corp-qa-table-date">
                                    <span>{{ $question->created_at->format('M j, Y') }}</span>
                                    <span class="corp-qa-table-time">{{ $question->created_at->format('g:i A') }}</span>
                                </td>
                                <td class="corp-table-cell corp-table-col--xs corp-qa-table-count">{{ $question->answers_count }}</td>
                                <td class="corp-table-cell corp-table-col--xs">
                                    @if ($question->is_resolved)
                                        <span class="corp-qa-status corp-qa-status--answered">Answered</span>
                                    @else
                                        <span class="corp-qa-status corp-qa-status--open">Open</span>
                                    @endif
                                </td>
                                <td class="corp-table-cell corp-table-cell--action">
                                    <a href="{{ route('questions.show', $question) }}" class="corp-table-action">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-lms.empty-state title="No questions yet" message="Be the first to start a discussion. Anyone can ask and anyone can answer." variant="books">
                <a href="{{ route('questions.create') }}" class="lms-btn-primary">Ask a question</a>
            </x-lms.empty-state>
        @endif
    </section>

    @if ($questions->hasPages())
        <div>{{ $questions->links() }}</div>
    @endif
</div>
@endsection
