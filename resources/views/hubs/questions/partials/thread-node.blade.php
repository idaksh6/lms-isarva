@props([
    'answer',
    'question',
    'depth' => 0,
])

@php
    $childCount = $answer->children->count();
    $collapseThreshold = 3;
    $startsCollapsed = $childCount >= $collapseThreshold;
    $canReply = $depth < \App\Models\Answer::MAX_DEPTH;
@endphp

<article
    class="corp-qa-thread-node"
    data-answer-id="{{ $answer->id }}"
    data-depth="{{ $depth }}"
    style="--thread-depth: {{ min($depth, 5) }};"
>
    <div @class(['corp-qa-thread-card', 'is-accepted' => $answer->is_accepted])>
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
                    <form method="POST" action="{{ route('answers.destroy', $answer) }}" onsubmit="return confirm('Remove this {{ $answer->isRoot() ? 'answer' : 'reply' }}{{ $childCount ? ' and its nested replies' : '' }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="lms-btn-danger lms-btn-danger--xs">Remove</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="corp-qa-answer-body whitespace-pre-wrap">{{ $answer->body }}</div>

        <div class="corp-qa-thread-footer">
            @if ($canReply)
                <button
                    type="button"
                    class="corp-qa-reply-toggle"
                    @click="openReply({{ $answer->id }})"
                    :aria-expanded="replyOpenId === {{ $answer->id }}"
                >
                    Reply
                </button>
            @endif
        </div>

        <div
            class="corp-qa-inline-reply"
            x-show="replyOpenId === {{ $answer->id }}"
            x-cloak
            x-transition
        >
            <form class="corp-qa-inline-reply-form" @submit.prevent="submitReply({{ $answer->id }}, $event)">
                <label class="sr-only" for="reply-body-{{ $answer->id }}">Reply</label>
                <textarea
                    id="reply-body-{{ $answer->id }}"
                    name="body"
                    rows="3"
                    class="lms-field-input"
                    required
                    maxlength="10000"
                    placeholder="Write a reply…"
                    x-ref="replyBody{{ $answer->id }}"
                ></textarea>
                <p class="corp-qa-inline-reply-error" x-show="error && replyOpenId === {{ $answer->id }}" x-text="error" x-cloak></p>
                <div class="corp-qa-inline-reply-actions">
                    <button type="button" class="lms-btn-secondary lms-btn-secondary--xs" @click="cancelReply()" :disabled="submitting">Cancel</button>
                    <button type="submit" class="lms-btn-primary lms-btn-primary--xs" :disabled="submitting">
                        <span x-show="!submitting">Post reply</span>
                        <span x-show="submitting" x-cloak>Posting…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="corp-qa-thread-children" data-children-for="{{ $answer->id }}">
        @if ($childCount > 0)
            <div
                class="corp-qa-thread-branch"
                data-branch-for="{{ $answer->id }}"
                data-starts-collapsed="{{ $startsCollapsed ? '1' : '0' }}"
                data-reply-count="{{ $childCount }}"
            >
                @if ($startsCollapsed)
                    <button
                        type="button"
                        class="corp-qa-view-replies"
                        x-show="isCollapsed({{ $answer->id }}, true)"
                        @click="expandThread({{ $answer->id }})"
                    >
                        View {{ $childCount }} {{ Str::plural('reply', $childCount) }}
                    </button>
                    <button
                        type="button"
                        class="corp-qa-view-replies corp-qa-view-replies--hide"
                        x-show="!isCollapsed({{ $answer->id }}, true)"
                        x-cloak
                        @click="collapseThread({{ $answer->id }})"
                    >
                        Hide replies
                    </button>
                @endif

                <div
                    class="corp-qa-thread-branch-list"
                    x-show="!isCollapsed({{ $answer->id }}, {{ $startsCollapsed ? 'true' : 'false' }})"
                    @if ($startsCollapsed) x-cloak @endif
                >
                    @foreach ($answer->children as $child)
                        @include('hubs.questions.partials.thread-node', [
                            'answer' => $child,
                            'question' => $question,
                            'depth' => $depth + 1,
                        ])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</article>
