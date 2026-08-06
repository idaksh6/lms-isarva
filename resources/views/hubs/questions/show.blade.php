@extends('layouts.lms')

@section('title', $question->title)
@section('page_title', 'Q&A')

@section('content')
<div class="lms-page-stack corp-qa-page corp-qa-show">
    <div class="lms-page-actions gchat-show-toolbar">
        <a href="{{ route('questions.index', ['thread' => $question->id]) }}" class="lms-btn-back">← Back to Q&amp;A</a>
    </div>

    @include('hubs.questions.partials.thread-shell', [
        'question' => $question,
        'answerCount' => $answerCount,
        'embedded' => false,
    ])
</div>
@endsection
