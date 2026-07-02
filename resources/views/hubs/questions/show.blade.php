@extends('layouts.lms')

@section('title', $question->title)
@section('page_title', 'Q&A')

@section('content')
<div class="lms-page-stack corp-qa-page corp-qa-show">
    <div class="lms-page-actions">
        <a href="{{ route('questions.index') }}" class="lms-btn-back">← Back to Q&amp;A</a>
    </div>

    <section class="corp-panel corp-qa-thread corp-qa-thread--question">
        <div class="corp-panel-head">
            <div class="min-w-0 flex-1">
                <div class="corp-qa-thread-meta">
                    @if ($question->is_resolved)
                        <span class="corp-qa-status corp-qa-status--answered">Answered</span>
                    @else
                        <span class="corp-qa-status corp-qa-status--open">Open</span>
                    @endif
                    @if ($question->course)
                        <span class="corp-code-badge">{{ $question->course->code }}</span>
                    @else
                        <span class="corp-qa-scope">General</span>
                    @endif
                </div>
                <h1 class="corp-qa-thread-title">{{ $question->title }}</h1>
                @if ($question->course)
                    <p class="corp-qa-thread-sub">{{ $question->course->title }}</p>
                @endif
            </div>
            @can('delete', $question)
                <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Remove this question and all answers?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Remove question</button>
                </form>
            @endcan
        </div>

        <div class="corp-qa-thread-body">
            <x-lms.qa-author :user="$question->author" :posted-at="$question->created_at" variant="question" />
            <div class="corp-qa-thread-content whitespace-pre-wrap">{{ $question->body }}</div>
        </div>
    </section>

    <section class="corp-panel">
        <div class="corp-panel-head corp-panel-head--compact">
            <div>
                <h2 class="corp-panel-title">{{ $question->answers->count() }} {{ Str::plural('answer', $question->answers->count()) }}</h2>
                <p class="corp-panel-desc">The question author or an administrator can mark the best response.</p>
            </div>
        </div>

        @forelse ($question->answers as $answer)
            <article @class(['corp-qa-answer-item', 'is-accepted' => $answer->is_accepted])>
                <div class="corp-qa-answer-top">
                    <x-lms.qa-author :user="$answer->author" :posted-at="$answer->created_at" variant="answer" />
                    <div class="corp-qa-answer-actions">
                        @if ($answer->is_accepted)
                            <span class="corp-qa-accepted-badge">Accepted</span>
                        @endif
                        @can('accept', $answer)
                            @if (! $answer->is_accepted)
                                <form method="POST" action="{{ route('questions.answers.accept', [$question, $answer]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Accept</button>
                                </form>
                            @endif
                        @endcan
                        @can('delete', $answer)
                            <form method="POST" action="{{ route('answers.destroy', $answer) }}" onsubmit="return confirm('Remove this answer?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Remove</button>
                            </form>
                        @endcan
                    </div>
                </div>
                <div class="corp-qa-answer-body whitespace-pre-wrap">{{ $answer->body }}</div>
            </article>
        @empty
            <div class="corp-qa-empty-answers">
                <p class="corp-qa-empty-title">No answers yet</p>
                <p class="corp-qa-empty-desc">Be the first to help by posting a reply below.</p>
            </div>
        @endforelse
    </section>

    <section class="corp-panel">
        <div class="corp-panel-head corp-panel-head--compact">
            <div>
                <h2 class="corp-panel-title">Post your answer</h2>
                <p class="corp-panel-desc">Your name, role, and posting time will be recorded.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('questions.answers.store', $question) }}" class="corp-qa-reply-form">
            @csrf
            <div class="lms-form-field">
                <label for="body" class="lms-field-label">Response</label>
                <textarea id="body" name="body" rows="5" class="lms-field-input mt-1.5" required maxlength="10000" placeholder="Write a clear, helpful response…">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="lms-form-actions">
                <button type="submit" class="lms-btn-primary">Submit answer</button>
            </div>
        </form>
    </section>
</div>
@endsection
