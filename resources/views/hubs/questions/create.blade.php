@extends('layouts.lms')

@section('title', 'Ask a question')
@section('page_title', 'Ask a question')

@section('content')
<div class="lms-page-stack corp-qa-page">
    <x-lms.module-hero module="questions" title="Ask a question" subtitle="Share your question with the community. Include enough detail so others can help you effectively." />

    @if ($aiEnabled ?? false)
        <section class="lms-form-card lms-ai-panel">
            <div class="lms-form-header">
                <h2 class="lms-form-title">AI doubt assist</h2>
                <p class="lms-form-desc">Get a course-grounded draft answer from published materials. Review citations, then paste into your question if useful.</p>
            </div>
            <form method="POST" action="{{ $selectedCourseId ? route('ai.courses.doubt', $selectedCourseId) : '#' }}" class="space-y-3" x-data="{ courseId: @js((string) ($selectedCourseId ?: '')) }" x-on:submit.prevent="
                if (!courseId) { Swal.fire({icon:'info', title:'Select a course', text:'Pick a related course first for AI assist.'}); return; }
                $el.action = '{{ url('/ai/courses') }}/' + courseId + '/doubt-assist';
                $el.submit();
            ">
                @csrf
                <div class="lms-form-field">
                    <label for="ai-course" class="lms-field-label">Course for AI assist</label>
                    <select id="ai-course" class="lms-field-input mt-1.5" x-model="courseId">
                        <option value="">Choose a course…</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->code }} — {{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lms-form-field">
                    <label for="ai-question" class="lms-field-label">What are you stuck on?</label>
                    <textarea id="ai-question" name="question" rows="3" class="lms-field-input mt-1.5" maxlength="2000" required placeholder="Ask about a concept from your course materials…">{{ old('question', $aiGeneration?->input_snapshot['question'] ?? '') }}</textarea>
                </div>
                <x-input-error :messages="$errors->get('ai')" />
                <button type="submit" class="lms-btn-secondary">Ask AI (draft only)</button>
            </form>

            @if (($aiGeneration ?? null)?->isReady())
                @php $out = $aiGeneration->output ?? []; @endphp
                <div class="mt-4 rounded-xl border border-isarva-border bg-slate-50 p-4">
                    <h3 class="text-sm font-semibold text-isarva-heading">
                        {{ ! empty($out['refused']) ? 'AI could not answer from materials' : 'Suggested answer' }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-wrap">{{ $out['answer'] ?? '' }}</p>
                    @if (! empty($out['citations']))
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-isarva-muted">Citations</p>
                        <ul class="mt-1 list-disc pl-5 text-sm text-slate-600">
                            @foreach ($out['citations'] as $cite)
                                <li>{{ $cite }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <button
                        type="button"
                        class="lms-btn-primary lms-btn-primary--xs mt-3"
                        x-data
                        x-on:click="
                            const body = document.getElementById('body');
                            const answer = @js($out['answer'] ?? '');
                            if (body && answer) {
                                body.value = (body.value ? body.value + '\n\n' : '') + answer;
                            }
                        "
                    >Insert into question body</button>
                </div>
            @endif
        </section>
    @endif

    <form method="POST" action="{{ route('questions.store') }}" class="lms-form-card">
        @csrf
        <div class="lms-form-header">
            <h2 class="lms-form-title">Question details</h2>
            <p class="lms-form-desc">Your name and posting time will be shown on the thread.</p>
        </div>

        <div class="lms-form-field">
            <label for="course_id" class="lms-field-label">Related course (optional)</label>
            <select id="course_id" name="course_id" class="lms-field-input mt-1.5">
                <option value="">General — not tied to a specific course</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected((string) old('course_id', $selectedCourseId) === (string) $course->id)>{{ $course->code }} — {{ $course->title }}</option>
                @endforeach
            </select>
            @error('course_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Subject</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" class="lms-field-input mt-1.5" required maxlength="255" placeholder="Summarize your question in one line">
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lms-form-field">
            <label for="body" class="lms-field-label">Question</label>
            <textarea id="body" name="body" rows="6" class="lms-field-input mt-1.5" required maxlength="10000" placeholder="Describe your question with as much context as possible…">{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="lms-form-actions">
            <a href="{{ route('questions.index') }}" class="lms-btn-secondary">Cancel</a>
            <button type="submit" class="lms-btn-primary">Post question</button>
        </div>
    </form>
</div>
@endsection
