@extends('layouts.guide')

@section('title', 'User guide')

@section('content')
<div
    class="ug-site"
    x-data="lmsUserGuide({
        defaultTab: @js($defaultTab),
        visibleTabs: @js($visibleTabs),
    })"
    :style="{
        '--ug-accent': theme.accent,
        '--ug-accent-light': theme.accentLight,
        '--ug-accent-dark': theme.accentDark,
    }"
    :data-role-tab="tab"
>
    {{-- Top bar --}}
    <header class="ug-topbar">
        <div class="ug-topbar-inner">
            <a href="{{ route('dashboard') }}" class="ug-back-btn">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Back to portal
            </a>
            <div class="ug-topbar-brand">
                <x-isarva-logo variant="sidebar" />
                <span>{{ config('app.name') }} · User guide</span>
            </div>
            <a href="https://isarvait.com" target="_blank" rel="noopener noreferrer" class="ug-topbar-link hidden sm:inline-flex">
                isarvait.com ↗
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="ug-hero">
        <div class="ug-hero-grid" aria-hidden="true"></div>
        <div class="ug-container ug-hero-inner">
            <p class="ug-hero-eyebrow">Documentation · Step-by-step</p>
            <h1 class="ug-hero-title">Everything you need to use the LMS with confidence</h1>
            <p class="ug-hero-lead">
                A complete, illustrated guide for students, lecturers, and administrators.
                Plain English, real workflows, and visual previews of every important screen.
            </p>

            @if (count($visibleTabs) > 1)
                <nav class="ug-tabs" aria-label="Choose your role">
                    @if (in_array('student', $visibleTabs, true))
                        <button type="button" class="ug-tab" :class="{ 'is-active': tab === 'student' }" @click="setTab('student')">
                            <span class="ug-tab-icon">🎓</span>
                            <span class="ug-tab-label">Student</span>
                            <span class="ug-tab-desc">Submit work & view grades</span>
                        </button>
                    @endif
                    @if (in_array('lecturer', $visibleTabs, true))
                        <button type="button" class="ug-tab" :class="{ 'is-active': tab === 'lecturer' }" @click="setTab('lecturer')">
                            <span class="ug-tab-icon">📚</span>
                            <span class="ug-tab-label">Lecturer</span>
                            <span class="ug-tab-desc">Courses, assignments & grading</span>
                        </button>
                    @endif
                    @if (in_array('admin', $visibleTabs, true))
                        <button type="button" class="ug-tab" :class="{ 'is-active': tab === 'admin' }" @click="setTab('admin')">
                            <span class="ug-tab-icon">⚙️</span>
                            <span class="ug-tab-label">Administrator</span>
                            <span class="ug-tab-desc">Users, platform & reports</span>
                        </button>
                    @endif
                </nav>
            @else
                <div class="ug-role-badge">🎓 Student guide</div>
            @endif
        </div>
    </section>

    {{-- Content --}}
    <main class="ug-main">
        <div class="ug-container">
            @if (in_array('student', $visibleTabs, true))
                <div x-show="tab === 'student'" x-cloak x-transition.opacity.duration.300ms id="guide-student" role="tabpanel">
                    <x-lms.user-guide.student-content />
                </div>
            @endif
            @if (in_array('lecturer', $visibleTabs, true))
                <div x-show="tab === 'lecturer'" x-cloak x-transition.opacity.duration.300ms id="guide-lecturer" role="tabpanel">
                    <x-lms.user-guide.lecturer-content />
                </div>
            @endif
            @if (in_array('admin', $visibleTabs, true))
                <div x-show="tab === 'admin'" x-cloak x-transition.opacity.duration.300ms id="guide-admin" role="tabpanel">
                    <x-lms.user-guide.admin-content />
                </div>
            @endif
        </div>
    </main>

    <footer class="ug-footer">
        <div class="ug-container ug-footer-inner">
            <div>
                <p class="ug-footer-title">Need personal support?</p>
                <p class="ug-footer-text">Contact your programme administrator or the ISARVA team.</p>
            </div>
            <div class="ug-footer-actions">
                <a href="{{ route('dashboard') }}" class="ug-btn ug-btn--ghost">← Back to portal</a>
                <a href="https://isarvait.com" target="_blank" rel="noopener noreferrer" class="ug-btn ug-btn--primary">Visit isarvait.com</a>
            </div>
        </div>
        <p class="ug-footer-copy">© {{ date('Y') }} ISARVA · {{ config('app.name') }} User Guide</p>
    </footer>
</div>
@endsection
