@extends('layouts.lms')

@section('title', 'Bulk import templates')
@section('page_title', 'Bulk import templates')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero
        module="assignments"
        title="Bulk import templates"
        subtitle="Download Excel, Word, or plain-text templates, fill them offline, then upload on a quiz or course assignment page."
    >
        <a href="{{ route('courses.index') }}" class="lms-btn-secondary lms-btn-secondary--xs">Back to courses</a>
    </x-lms.module-hero>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Question bank &amp; quiz</h2>
                <p class="corp-panel-desc">Excel columns: <code class="text-xs">prompt</code>, <code class="text-xs">option_a</code>–<code class="text-xs">option_d</code>, <code class="text-xs">answer</code> (A–D). Word/TXT use <code class="text-xs">LMS_IMPORT: QUIZ</code> or <code class="text-xs">QUESTION_BANK</code> blocks.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 px-4 py-4 sm:px-5">
            <a href="{{ route('imports.templates.download', ['kind' => 'quiz', 'format' => 'xlsx']) }}" class="lms-btn-primary lms-btn-primary--xs">Quiz (.xlsx)</a>
            <a href="{{ route('imports.templates.download', ['kind' => 'quiz', 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Quiz (.docx)</a>
            <a href="{{ route('imports.templates.download', ['kind' => 'quiz', 'format' => 'txt']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Quiz (.txt)</a>
            <a href="{{ route('imports.templates.download', ['kind' => 'question-bank', 'format' => 'xlsx']) }}" class="lms-btn-primary lms-btn-primary--xs">Question bank (.xlsx)</a>
            <a href="{{ route('imports.templates.download', ['kind' => 'question-bank', 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Question bank (.docx)</a>
            <a href="{{ route('imports.templates.download', ['kind' => 'question-bank', 'format' => 'txt']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Question bank (.txt)</a>
        </div>
    </section>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Assignments</h2>
                <p class="corp-panel-desc">Excel columns: <code class="text-xs">title</code>, <code class="text-xs">instructions</code>, <code class="text-xs">delivery</code>, <code class="text-xs">drop_folder_url</code>, <code class="text-xs">due</code>, <code class="text-xs">publish</code>. Word/TXT use <code class="text-xs">LMS_IMPORT: ASSIGNMENTS</code> blocks.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 px-4 py-4 sm:px-5">
            <a href="{{ route('imports.templates.download', ['kind' => 'assignments', 'format' => 'xlsx']) }}" class="lms-btn-primary lms-btn-primary--xs">Assignments (.xlsx)</a>
            <a href="{{ route('imports.templates.download', ['kind' => 'assignments', 'format' => 'docx']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Assignments (.docx)</a>
            <a href="{{ route('imports.templates.download', ['kind' => 'assignments', 'format' => 'txt']) }}" class="lms-btn-secondary lms-btn-secondary--xs">Assignments (.txt)</a>
        </div>
    </section>

    <section class="corp-panel">
        <div class="corp-panel-head">
            <div>
                <h2 class="corp-panel-title">Word macro</h2>
                <p class="corp-panel-desc">Optional helper for Word templates. For Excel, fill the spreadsheet columns directly — no macro needed.</p>
            </div>
        </div>
        <div class="space-y-3 px-4 py-4 text-sm text-slate-700 sm:px-5">
            <ol class="list-decimal space-y-2 pl-5 text-isarva-muted">
                <li>Open Word and press <strong class="text-isarva-heading">Alt+F11</strong>.</li>
                <li>Import <code class="text-xs">samples/bulk-import/LmsBulkImportMacro.bas</code>.</li>
                <li>Run <code class="text-xs">InsertQuizQuestion</code> or <code class="text-xs">InsertAssignmentBlock</code>, then <code class="text-xs">ValidateLmsImport</code>.</li>
                <li>Save as <strong class="text-isarva-heading">.docx</strong> (or export PDF) and upload on quiz edit or New assignment.</li>
            </ol>
        </div>
    </section>
</div>
@endsection
