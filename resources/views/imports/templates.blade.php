@extends('layouts.lms')

@section('title', 'Bulk import templates')
@section('page_title', 'Bulk import templates')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero
        module="assignments"
        title="Bulk import templates"
        subtitle="Download Word or plain-text templates, fill them offline, then upload on a quiz or course assignment page."
    />

    <div class="corp-panel space-y-6 p-6">
        <div>
            <h2 class="corp-panel-title">Question bank &amp; quiz</h2>
            <p class="corp-panel-desc mt-1">Same MCQ layout. Start with <code class="text-xs">LMS_IMPORT: QUIZ</code> or <code class="text-xs">LMS_IMPORT: QUESTION_BANK</code>, then use Q1. / A) / ANSWER: lines.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('imports.templates.download', ['kind' => 'quiz', 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Quiz (.docx)</a>
                <a href="{{ route('imports.templates.download', ['kind' => 'quiz', 'format' => 'txt']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Quiz (.txt)</a>
                <a href="{{ route('imports.templates.download', ['kind' => 'question-bank', 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Question bank (.docx)</a>
                <a href="{{ route('imports.templates.download', ['kind' => 'question-bank', 'format' => 'txt']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Question bank (.txt)</a>
            </div>
        </div>

        <div>
            <h2 class="corp-panel-title">Assignments</h2>
            <p class="corp-panel-desc mt-1">Header <code class="text-xs">LMS_IMPORT: ASSIGNMENTS</code>. Separate each assignment with <code class="text-xs">---</code> and include TITLE, INSTRUCTIONS, DELIVERY, DUE, PUBLISH.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('imports.templates.download', ['kind' => 'assignments', 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Assignments (.docx)</a>
                <a href="{{ route('imports.templates.download', ['kind' => 'assignments', 'format' => 'txt']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Assignments (.txt)</a>
            </div>
        </div>

        <div class="rounded-xl border border-isarva-border bg-slate-50 p-4 text-sm text-slate-700">
            <p class="font-semibold text-isarva-heading">Word macro</p>
            <p class="mt-1 text-isarva-muted">Install <code class="text-xs">samples/bulk-import/LmsBulkImportMacro.bas</code> in Word (Alt+F11 → Import File). Use InsertQuizQuestion / InsertAssignmentBlock, then ValidateLmsImport before upload.</p>
            <p class="mt-2 text-isarva-muted">Upload the finished .docx or PDF on the quiz edit screen or the course “New assignment” page.</p>
        </div>
    </div>
</div>
@endsection
