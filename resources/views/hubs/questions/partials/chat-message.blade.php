@props([
    'answer',
    'question',
    'isMine' => false,
])

@php
    $author = $answer->author;
    $initials = strtoupper(substr($author->name, 0, 1));
    $quoted = $answer->quoted;
    $isMine = $isMine || (auth()->id() === $answer->user_id);
@endphp

<article
    class="gchat-msg {{ $isMine ? 'is-mine' : '' }}"
    data-answer-id="{{ $answer->id }}"
    data-author-name="{{ $author->name }}"
    data-author-initials="{{ $initials }}"
    data-body="{{ $answer->snippet(160) }}"
    data-search-text="{{ strtolower($author->name.' '.$answer->body.($quoted ? ' '.($quoted->author->name ?? '').' '.$quoted->body : '')) }}"
>
    <div class="gchat-msg-avatar" aria-hidden="true">{{ $initials }}</div>
    <div class="gchat-msg-main">
        <div class="gchat-msg-meta">
            <span class="gchat-msg-name">{{ $author->name }}</span>
            <time class="gchat-msg-time" datetime="{{ $answer->created_at->toIso8601String() }}">
                {{ $answer->created_at->format('M j, g:i A') }}
            </time>
            @can('delete', $answer)
                <form
                    method="POST"
                    action="{{ route('answers.destroy', $answer) }}"
                    class="gchat-msg-delete"
                    @submit.prevent="removeMessage({{ $answer->id }}, $event)"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="gchat-msg-delete-btn" title="Remove">Remove</button>
                </form>
            @endcan
        </div>

        <div class="gchat-bubble">
            @if ($quoted)
                <div class="gchat-quote">
                    <span class="gchat-quote-mark" aria-hidden="true">“</span>
                    <div class="gchat-quote-body">
                        <div class="gchat-quote-author">
                            <span class="gchat-quote-avatar" aria-hidden="true">{{ strtoupper(substr($quoted->author->name ?? '?', 0, 1)) }}</span>
                            <span>{{ $quoted->author->name ?? 'Unknown' }}</span>
                        </div>
                        <p class="gchat-quote-text">{{ $quoted->snippet(140) }}</p>
                    </div>
                </div>
            @endif
            <div class="gchat-bubble-text whitespace-pre-wrap">{{ $answer->body }}</div>
        </div>

        <button
            type="button"
            class="gchat-reply-btn"
            @click="setReplyTo({
                id: {{ $answer->id }},
                name: @js($author->name),
                initials: @js($initials),
                body: @js($answer->snippet(160)),
            })"
        >
            Reply
        </button>
    </div>
</article>
