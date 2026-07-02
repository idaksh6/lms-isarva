@extends('layouts.lms')

@section('title', 'Help')
@section('page_title', 'Help')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="help" title="Help centre" subtitle="Quick answers for students, lecturers, and administrators." />

    <div class="lms-help-grid">
        @foreach ([
            ['q' => 'How do I submit an assignment?', 'a' => 'Open the course → assignment → Submit work. Upload one file and optional notes. You can resubmit only if your lecturer requests a revision.', 'icon' => 'clipboard'],
            ['q' => 'Where do I see my grades?', 'a' => 'Open your submission from the assignment or Submissions page. Scores and lecturer feedback appear after review.', 'icon' => 'chart'],
            ['q' => 'How do lecturers grade work?', 'a' => 'Open a submission, enter a score (0–100), add feedback, and choose Grade, Request revision, or Mark reviewed.', 'icon' => 'book'],
            ['q' => 'What are announcements?', 'a' => 'Lecturers post course or global updates on the Announcements page. Everyone can read them. New posts also appear in your notification bell (and email if enabled). Pinned posts stay at the top of the list.', 'icon' => 'book'],
            ['q' => 'How do due-date reminders work?', 'a' => 'The system sends in-app and email reminders 24 hours before a due date if you have not submitted.', 'icon' => 'chart'],
            ['q' => 'Who can export reports?', 'a' => 'Admins and lecturers can download CSV gradebook and report files from the Gradebook and Reports pages.', 'icon' => 'chart'],
        ] as $faq)
            <article class="lms-help-card">
                <div class="lms-help-card-head">
                    <div class="lms-help-card-icon" aria-hidden="true">
                        @include('layouts.partials.stat-icon', ['name' => $faq['icon']])
                    </div>
                    <h3 class="lms-help-question">{{ $faq['q'] }}</h3>
                </div>
                <p class="lms-help-answer">{{ $faq['a'] }}</p>
            </article>
        @endforeach
    </div>

    <section class="lms-panel lms-help-contact">
        <div class="lms-panel-body flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-isarva-heading">Need more help?</h2>
                <p class="text-sm text-isarva-muted">Contact your programme administrator or ISARVA support.</p>
            </div>
            <a href="https://isarvait.com" target="_blank" rel="noopener" class="lms-btn-primary">Visit isarvait.com</a>
        </div>
    </section>
</div>
@endsection
