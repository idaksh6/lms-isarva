@extends('layouts.lms')

@section('title', $question->title)
@section('page_title', 'Q&A')

@section('content')
<div
    class="lms-page-stack corp-qa-page corp-qa-show"
    x-data="lmsQaThread({
        storeUrl: @js(route('questions.answers.store', $question)),
        csrf: @js(csrf_token()),
        totalCount: {{ (int) $answerCount }},
    })"
>
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
                <h2 class="corp-panel-title">
                    <span x-text="totalCount">{{ $answerCount }}</span>
                    <span x-text="totalCount === 1 ? 'response' : 'responses'">{{ Str::plural('response', $answerCount) }}</span>
                </h2>
                <p class="corp-panel-desc">Replies nest under their parent. The question author or an administrator can accept a top-level answer.</p>
            </div>
        </div>

        <div class="corp-qa-discussion" data-discussion-root>
            @forelse ($question->rootAnswers as $answer)
                @include('hubs.questions.partials.thread-node', [
                    'answer' => $answer,
                    'question' => $question,
                    'depth' => 0,
                ])
            @empty
                <div class="corp-qa-empty-answers" data-empty-answers>
                    <p class="corp-qa-empty-title">No responses yet</p>
                    <p class="corp-qa-empty-desc">Be the first to help by posting a reply below.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="corp-panel">
        <div class="corp-panel-head corp-panel-head--compact">
            <div>
                <h2 class="corp-panel-title">Post your answer</h2>
                <p class="corp-panel-desc">Your name, role, and posting time will be recorded. Use Reply on a comment to nest a response.</p>
            </div>
        </div>
        <form
            method="POST"
            action="{{ route('questions.answers.store', $question) }}"
            class="corp-qa-reply-form"
            @submit.prevent="submitRoot($event)"
        >
            @csrf
            <div class="lms-form-field">
                <label for="body" class="lms-field-label">Response</label>
                <textarea id="body" name="body" rows="5" class="lms-field-input mt-1.5" required maxlength="10000" placeholder="Write a clear, helpful response…">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-red-600" x-show="rootError" x-text="rootError" x-cloak></p>
            </div>
            <div class="lms-form-actions">
                <button type="submit" class="lms-btn-primary" :disabled="submitting">
                    <span x-show="!submitting">Submit answer</span>
                    <span x-show="submitting" x-cloak>Posting…</span>
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
