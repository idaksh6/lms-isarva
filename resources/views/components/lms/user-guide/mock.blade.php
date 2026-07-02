@props(['type'])

@php
    $mocks = [
        'login' => ['label' => 'Sign in', 'accent' => '#1d4ed8'],
        'dashboard' => ['label' => 'Dashboard', 'accent' => '#1d4ed8'],
        'courses' => ['label' => 'Courses', 'accent' => '#0ea5e9'],
        'assignment' => ['label' => 'Assignment', 'accent' => '#6366f1'],
        'submit-file' => ['label' => 'Upload file', 'accent' => '#059669'],
        'submit-link' => ['label' => 'Cloud link', 'accent' => '#7c3aed'],
        'grade' => ['label' => 'Your grade', 'accent' => '#d97706'],
        'create-course' => ['label' => 'New course', 'accent' => '#1d4ed8'],
        'enroll' => ['label' => 'Enroll students', 'accent' => '#0891b2'],
        'new-assignment' => ['label' => 'New assignment', 'accent' => '#4f46e5'],
        'review' => ['label' => 'Review work', 'accent' => '#be123c'],
        'admin-users' => ['label' => 'Manage users', 'accent' => '#334155'],
        'announcement' => ['label' => 'Announcements', 'accent' => '#ea580c'],
    ];
    $meta = $mocks[$type] ?? ['label' => 'Portal', 'accent' => '#1d4ed8'];
@endphp

<figure {{ $attributes->merge(['class' => 'ug-mock']) }} aria-hidden="true">
    <div class="ug-mock-chrome">
        <span class="ug-mock-dot ug-mock-dot--red"></span>
        <span class="ug-mock-dot ug-mock-dot--amber"></span>
        <span class="ug-mock-dot ug-mock-dot--green"></span>
        <span class="ug-mock-url">lms.isarvait.com</span>
    </div>

    <div class="ug-mock-body">
        <div class="ug-mock-sidebar">
            <span class="ug-mock-sidebar-logo"></span>
            @foreach (range(1, 4) as $i)
                <span @class(['ug-mock-nav-line', 'is-active' => $i === 2])></span>
            @endforeach
        </div>

        <div class="ug-mock-main">
            <div class="ug-mock-topbar">
                <span class="ug-mock-pill" style="--mock-accent: {{ $meta['accent'] }}">{{ $meta['label'] }}</span>
            </div>

            @switch($type)
                @case('login')
                    <div class="ug-mock-card ug-mock-card--center">
                        <span class="ug-mock-heading">Welcome back</span>
                        <span class="ug-mock-field"></span>
                        <span class="ug-mock-field"></span>
                        <span class="ug-mock-btn ug-mock-btn--primary"></span>
                    </div>
                    @break

                @case('dashboard')
                    <div class="ug-mock-kpi-row">
                        <span class="ug-mock-kpi"><small>Due soon</small><strong>2</strong></span>
                        <span class="ug-mock-kpi"><small>Submitted</small><strong>4</strong></span>
                        <span class="ug-mock-kpi"><small>Graded</small><strong>3</strong></span>
                    </div>
                    <div class="ug-mock-list">
                        @foreach (['Linear Regression Lab', 'Model Evaluation Report'] as $item)
                            <span class="ug-mock-list-row">{{ $item }}</span>
                        @endforeach
                    </div>
                    @break

                @case('courses')
                    <div class="ug-mock-grid">
                        @foreach (['DS501 · ML Foundations', 'DS502 · Data Engineering'] as $card)
                            <span class="ug-mock-course-card">{{ $card }}</span>
                        @endforeach
                    </div>
                    @break

                @case('assignment')
                    <span class="ug-mock-heading-sm">Week 1 Lab Report</span>
                    <span class="ug-mock-text-block"></span>
                    <span class="ug-mock-text-block ug-mock-text-block--short"></span>
                    <span class="ug-mock-btn ug-mock-btn--primary ug-mock-btn--wide">Submit your work</span>
                    @break

                @case('submit-file')
                    <span class="ug-mock-dropzone">
                        <svg class="h-8 w-8 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <small>Drop file or browse</small>
                    </span>
                    <span class="ug-mock-btn ug-mock-btn--primary ug-mock-btn--wide">Submit assignment</span>
                    @break

                @case('submit-link')
                    <span class="ug-mock-folder-banner">Open shared folder → upload zip</span>
                    <span class="ug-mock-field ug-mock-field--link"></span>
                    <span class="ug-mock-link-preview">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m3.536-.536l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                        drive.google.com/…/project.zip
                    </span>
                    @break

                @case('grade')
                    <div class="ug-mock-grade-card">
                        <span class="ug-mock-grade-score">B · 88</span>
                        <span class="ug-mock-text-block"></span>
                        <span class="ug-mock-feedback">Strong work. Clear explanations in your notebook.</span>
                    </div>
                    @break

                @case('create-course')
                    <span class="ug-mock-field"></span>
                    <span class="ug-mock-field"></span>
                    <span class="ug-mock-text-block"></span>
                    <span class="ug-mock-btn ug-mock-btn--primary">Create course</span>
                    @break

                @case('enroll')
                    <div class="ug-mock-split">
                        <div class="ug-mock-split-col">
                            <small>Enrolled</small>
                            @foreach (['Student 1', 'Student 2'] as $s)
                                <span class="ug-mock-student-chip">{{ $s }}</span>
                            @endforeach
                        </div>
                        <div class="ug-mock-split-col">
                            <small>Add students</small>
                            @foreach (['Student 3', 'Student 4'] as $s)
                                <span class="ug-mock-student-chip is-selectable">☑ {{ $s }}</span>
                            @endforeach
                        </div>
                    </div>
                    @break

                @case('new-assignment')
                    <span class="ug-mock-field"></span>
                    <span class="ug-mock-select">File upload ▾</span>
                    <span class="ug-mock-select">Cloud link ▾</span>
                    <span class="ug-mock-btn ug-mock-btn--primary">Publish assignment</span>
                    @break

                @case('review')
                    <div class="ug-mock-split">
                        <div class="ug-mock-split-col">
                            <small>Score</small>
                            <span class="ug-mock-field ug-mock-field--short"></span>
                            <span class="ug-mock-btn ug-mock-btn--primary">Post grade</span>
                        </div>
                        <div class="ug-mock-split-col">
                            <small>Submitted file</small>
                            <span class="ug-mock-file-row">lab_report.pdf</span>
                        </div>
                    </div>
                    @break

                @case('admin-users')
                    @foreach (['LMS Administrator', 'Dr. Priya Sharma', 'Student 1'] as $u)
                        <span class="ug-mock-user-row">{{ $u }}</span>
                    @endforeach
                    <span class="ug-mock-btn ug-mock-btn--primary ug-mock-btn--compact">Add user</span>
                    @break

                @case('announcement')
                    <span class="ug-mock-announcement is-pinned">📌 Due date extended — submit by Friday</span>
                    <span class="ug-mock-announcement">New dataset uploaded for Lab 2</span>
                    @break

                @default
                    <span class="ug-mock-text-block"></span>
            @endswitch
        </div>
    </div>

    <figcaption class="sr-only">{{ $meta['label'] }} screen preview</figcaption>
</figure>
