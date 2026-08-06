@php
    $viewer = auth()->user();
    $embedded = $embedded ?? false;
    $latestAnswerId = $question->answers->last()?->id ?? 0;
@endphp

<div
    class="gchat-panel"
    data-question-id="{{ $question->id }}"
    x-data="lmsQaThread({
        storeUrl: @js(route('questions.answers.store', $question)),
        feedUrl: @js(route('questions.feed', $question)),
        csrf: @js(csrf_token()),
        totalCount: {{ (int) $answerCount }},
        latestId: {{ (int) $latestAnswerId }},
        pollMs: 4000,
    })"
>
    <header class="gchat-panel-head">
        <div class="min-w-0 flex-1">
            <div class="gchat-panel-head-row">
                <p class="gchat-panel-kicker">Thread</p>
                @if ($embedded)
                    <button type="button" class="gchat-panel-close" @click="$dispatch('qa-close-thread')" aria-label="Close thread">×</button>
                @endif
            </div>
            <h1 class="gchat-panel-title">{{ $question->title }}</h1>
            <div class="gchat-panel-meta">
                @if ($question->course)
                    <span class="corp-code-badge">{{ $question->course->code }}</span>
                    <span class="gchat-panel-sub">{{ $question->course->title }}</span>
                @else
                    <span class="corp-qa-scope">General</span>
                @endif
            </div>
        </div>
        @can('delete', $question)
            <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Remove this question and all replies?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Remove</button>
            </form>
        @endcan
    </header>

    <div class="gchat-thread-search">
        <label class="sr-only" for="thread-search-{{ $question->id }}">Search in thread</label>
        <input
            id="thread-search-{{ $question->id }}"
            type="search"
            class="gchat-thread-search-input"
            placeholder="Search"
            x-model="threadSearch"
            autocomplete="off"
        >
    </div>

    <div class="gchat-panel-body" data-thread-scroll>
        <article class="gchat-msg gchat-msg--parent" data-search-text="{{ strtolower($question->author->name.' '.$question->body) }}" x-show="matchesSearch($el)">
            <div class="gchat-msg-avatar gchat-msg-avatar--lg" aria-hidden="true">{{ strtoupper(substr($question->author->name, 0, 1)) }}</div>
            <div class="gchat-msg-main">
                <div class="gchat-msg-meta">
                    <span class="gchat-msg-name">{{ $question->author->name }}</span>
                    <time class="gchat-msg-time" datetime="{{ $question->created_at->toIso8601String() }}">
                        {{ $question->created_at->format('M j, g:i A') }}
                    </time>
                </div>
                <div class="gchat-bubble gchat-bubble--parent">
                    <div class="gchat-bubble-text whitespace-pre-wrap">{{ $question->body }}</div>
                </div>
            </div>
        </article>

        <div class="gchat-replies-divider" x-show="totalCount > 0" @if ($answerCount === 0) x-cloak @endif>
            <span class="gchat-replies-count">
                <span x-text="totalCount">{{ $answerCount }}</span>
                <span x-text="totalCount === 1 ? 'reply' : 'replies'">{{ Str::plural('reply', $answerCount) }}</span>
            </span>
        </div>

        <div class="gchat-replies" data-discussion-root>
            @forelse ($question->answers as $answer)
                @include('hubs.questions.partials.chat-message', [
                    'answer' => $answer,
                    'question' => $question,
                    'isMine' => $viewer && $viewer->id === $answer->user_id,
                ])
            @empty
                <div class="gchat-empty" data-empty-answers>
                    <p class="gchat-empty-title">No replies yet</p>
                    <p class="gchat-empty-desc">Start the conversation below — replies appear here instantly.</p>
                </div>
            @endforelse
        </div>

        <p class="gchat-search-empty" x-show="threadSearch.trim() !== '' && !hasSearchMatches" x-cloak>
            No messages match “<span x-text="threadSearch.trim()"></span>”.
        </p>
    </div>

    <footer class="gchat-composer">
        <div class="gchat-quote-preview" x-show="replyTo" x-cloak>
            <div class="gchat-quote">
                <span class="gchat-quote-mark" aria-hidden="true">“</span>
                <div class="gchat-quote-body">
                    <div class="gchat-quote-author">
                        <span class="gchat-quote-avatar" aria-hidden="true" x-text="replyTo?.initials"></span>
                        <span x-text="replyTo?.name"></span>
                    </div>
                    <p class="gchat-quote-text" x-text="replyTo?.body"></p>
                </div>
            </div>
            <button type="button" class="gchat-quote-clear" @click="clearReplyTo()" aria-label="Clear quote">×</button>
        </div>

        <form class="gchat-composer-form" @submit.prevent="submitMessage($event)">
            @csrf
            <div class="gchat-composer-row">
                <div class="gchat-msg-avatar" aria-hidden="true">{{ strtoupper(substr($viewer->name ?? 'U', 0, 1)) }}</div>
                <label class="sr-only" for="gchat-body-{{ $question->id }}">Write a reply</label>
                <textarea
                    id="gchat-body-{{ $question->id }}"
                    name="body"
                    rows="2"
                    class="gchat-composer-input"
                    required
                    maxlength="10000"
                    placeholder="Reply in thread…"
                    x-ref="composer"
                    @keydown.enter.meta.prevent="submitMessage($event)"
                    @keydown.enter.ctrl.prevent="submitMessage($event)"
                ></textarea>
                <button type="submit" class="gchat-send-btn" :disabled="submitting" title="Send">
                    <span x-show="!submitting">Send</span>
                    <span x-show="submitting" x-cloak>…</span>
                </button>
            </div>
            <p class="gchat-composer-error" x-show="error" x-text="error" x-cloak></p>
        </form>
    </footer>
</div>
