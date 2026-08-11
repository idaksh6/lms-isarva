@extends('layouts.lms')

@section('title', 'Q&A')
@section('page_title', 'Q&A')

@section('content')
<div
    class="lms-page-stack corp-qa-page"
    x-data="lmsQaBoard({
        panelUrlTemplate: @js(url('/questions/__ID__/panel')),
        initialThreadId: {{ $threadId ? (int) $threadId : 'null' }},
        csrf: @js(csrf_token()),
    })"
    @qa-close-thread.window="closeThread()"
    @qa-toggle-fullscreen.window="fullscreen = $event.detail.open"
>
    <x-lms.module-hero module="questions" title="Questions & answers" subtitle="Ask the community, share knowledge, and get help from students, lecturers, and administrators.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ $stats['total'] }}</strong> questions</span>
            <span class="lms-stat-chip"><strong>{{ $stats['open'] }}</strong> open</span>
            <span class="lms-stat-chip"><strong>{{ $stats['answered'] }}</strong> answered</span>
        </div>
        <a href="{{ route('questions.create') }}" class="lms-btn-primary lms-btn-primary--xs">Ask a question</a>
    </x-lms.module-hero>

    <form method="GET" class="lms-filter-bar gchat-global-search-bar">
        <div class="gchat-global-search-wrap">
            <label for="q" class="sr-only">Search Q&amp;A</label>
            <input
                id="q"
                type="search"
                name="q"
                value="{{ $search }}"
                class="lms-field-input gchat-global-search-input"
                placeholder="Search questions, replies, and names…"
                autocomplete="off"
            >
        </div>
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
        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Search</button>
        @if ($status || $courseId || $search)
            <a href="{{ route('questions.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Clear</a>
        @endif
    </form>

    <div class="gchat-board" :class="{ 'is-open': threadOpen, 'is-fullscreen': fullscreen && threadOpen }">
        <section class="gchat-board-feed corp-panel">
            <div class="corp-panel-head">
                <div>
                    <h2 class="corp-panel-title">Discussion feed</h2>
                    <p class="corp-panel-desc">
                        @if ($search !== '')
                            Showing matches for “{{ $search }}”.
                        @else
                            Open a question to view the full thread on the right.
                        @endif
                    </p>
                </div>
            </div>

            @if ($questions->isNotEmpty())
                <div class="gchat-feed-list">
                    @foreach ($questions as $question)
                        @php
                            $lastAt = $question->answers_max_created_at
                                ? \Illuminate\Support\Carbon::parse($question->answers_max_created_at)
                                : $question->created_at;
                        @endphp
                        <article
                            class="gchat-feed-item"
                            :class="{ 'is-active': activeThreadId === {{ $question->id }} }"
                        >
                            <button
                                type="button"
                                class="gchat-feed-open"
                                @click="openThread({{ $question->id }})"
                            >
                                <div class="gchat-feed-top">
                                    <span class="gchat-msg-avatar" aria-hidden="true">{{ strtoupper(substr($question->author->name, 0, 1)) }}</span>
                                    <div class="gchat-feed-meta">
                                        <span class="gchat-feed-author">{{ $question->author->name }}</span>
                                        <time class="gchat-feed-time" datetime="{{ $question->created_at->toIso8601String() }}">
                                            {{ $question->created_at->format('M j, g:i A') }}
                                        </time>
                                    </div>
                                    @if ($question->is_resolved)
                                        <span class="corp-qa-status corp-qa-status--answered">Answered</span>
                                    @else
                                        <span class="corp-qa-status corp-qa-status--open">Open</span>
                                    @endif
                                </div>
                                <h3 class="gchat-feed-title">{{ $question->title }}</h3>
                                <p class="gchat-feed-excerpt">{{ Str::limit(strip_tags($question->body), 140) }}</p>
                                <div class="gchat-feed-foot">
                                    @if ($question->course)
                                        <span class="corp-code-badge">{{ $question->course->code }}</span>
                                    @else
                                        <span class="corp-qa-scope">General</span>
                                    @endif
                                    @if ($question->answers_count > 0)
                                        <span class="gchat-feed-replies">
                                            {{ $question->answers_count }} {{ Str::plural('reply', $question->answers_count) }}
                                            · {{ $lastAt->format('g:i A') }}
                                        </span>
                                    @else
                                        <span class="gchat-feed-replies">No replies yet</span>
                                    @endif
                                </div>
                            </button>
                        </article>
                    @endforeach
                </div>
            @else
                <x-lms.empty-state title="No questions found" message="Try another search, or be the first to start a discussion." variant="books">
                    <a href="{{ route('questions.create') }}" class="lms-btn-primary">Ask a question</a>
                </x-lms.empty-state>
            @endif

            @if ($questions->hasPages())
                <div class="gchat-feed-pagination">{{ $questions->links() }}</div>
            @endif
        </section>

        <aside class="gchat-board-panel" x-show="threadOpen" x-cloak>
            <div class="gchat-board-panel-inner" x-ref="panelMount">
                <div class="gchat-panel-placeholder" x-show="!panelReady && !panelLoading">
                    <p class="gchat-empty-title">Select a conversation</p>
                    <p class="gchat-empty-desc">Click a question or its replies to open the thread here.</p>
                </div>
                <div class="gchat-panel-loading" x-show="panelLoading" x-cloak>
                    <p class="gchat-empty-desc">Loading thread…</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
