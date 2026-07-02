<section class="ug-section">
    <header class="ug-section-head">
        <p class="ug-section-eyebrow">Administrator workflow</p>
        <h2 class="ug-section-title">Run the platform from end to end</h2>
        <p class="ug-section-lead">
            Administrators manage user accounts, oversee all courses, and can perform any lecturer action.
            Use the <strong>Student</strong> and <strong>Lecturer</strong> tabs above for illustrated guides to those workflows.
        </p>
    </header>

    <div class="ug-flow">
        <div class="ug-flow-step is-active"><span>1</span> Manage users</div>
        <div class="ug-flow-step"><span>2</span> Courses</div>
        <div class="ug-flow-step"><span>3</span> Support teaching</div>
        <div class="ug-flow-step"><span>4</span> Reports</div>
    </div>

    <div class="ug-steps">
        <x-lms.user-guide.step
            :number="1"
            title="Create and manage user accounts"
            body="Open Users in the portal menu. Add students, lecturers, or fellow administrators with Add user. Each account needs a name, email, role, and password."
            :bullets="[
                'Students can have an optional student ID (e.g. DS2024001) for display on submissions.',
                'Edit any user to change their role, email, or profile details.',
                'Deactivate accounts instead of deleting — inactive users cannot sign in.',
                'Students must exist before a lecturer can enrol them in a course.',
            ]"
            mock="admin-users"
        />

        <x-lms.user-guide.step
            :number="2"
            title="Create courses and assign lecturers"
            body="Administrators create courses the same way lecturers do — Courses → Create course. When creating a course, pick which lecturer owns it from the dropdown."
            :bullets="[
                'The assigned lecturer manages enrollments and assignments for that module.',
                'Archive inactive courses by editing and toggling status — courses with submissions are archived, not deleted.',
                'Admins can view any course on the platform regardless of assignment.',
            ]"
            mock="create-course"
            reverse
        />

        <x-lms.user-guide.step
            :number="3"
            title="Monitor the platform dashboard"
            body="Your admin dashboard shows live counts: total students, lecturers, active courses, published assignments, and submissions awaiting review."
            :bullets="[
                'Use these numbers to spot courses falling behind on grading.',
                'Quick links take you to Users, Courses, and other admin tools.',
                'You can step in as a lecturer if someone is unavailable — enrol students, publish work, and grade submissions.',
            ]"
            mock="dashboard"
        />

        <x-lms.user-guide.step
            :number="4"
            title="Export gradebooks and reports"
            body="Open Gradebook or Reports to download CSV files. These exports include student names, assignment titles, scores, and submission statuses."
            :bullets="[
                'Use exports for accreditation visits, audit trails, or backup records.',
                'Lecturers have the same export access for their own courses.',
                'Reports can be filtered before download depending on the page.',
            ]"
            mock="grade"
            reverse
        />
    </div>

    <div class="ug-matrix">
        <h3 class="ug-matrix-title">Permissions at a glance</h3>
        <div class="ug-matrix-table">
            <div class="ug-matrix-row ug-matrix-row--head">
                <span>Action</span><span>Student</span><span>Lecturer</span><span>Admin</span>
            </div>
            @foreach ([
                ['Submit assignments', true, false, false],
                ['View enrolled courses only', true, false, false],
                ['Create courses', false, true, true],
                ['Enrol students', false, true, true],
                ['Create & publish assignments', false, true, true],
                ['Grade submissions', false, true, true],
                ['Post announcements', false, true, false],
                ['Manage all users', false, false, true],
                ['View all courses', false, false, true],
                ['Export reports & gradebook', false, true, true],
            ] as [$label, $s, $l, $a])
                <div class="ug-matrix-row">
                    <span>{{ $label }}</span>
                    <span>@if($s)<em class="ug-yes">Yes</em>@else<em class="ug-no">—</em>@endif</span>
                    <span>@if($l)<em class="ug-yes">Yes</em>@else<em class="ug-no">—</em>@endif</span>
                    <span>@if($a)<em class="ug-yes">Yes</em>@else<em class="ug-no">—</em>@endif</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="ug-journey">
        <h3 class="ug-journey-title">Complete platform journey</h3>
        <ol class="ug-journey-list">
            <li><strong>Administrator</strong> creates student and lecturer accounts under Users.</li>
            <li><strong>Lecturer</strong> creates a course and enrols students via Manage students.</li>
            <li><strong>Lecturer</strong> publishes assignments — file upload, cloud link, or both.</li>
            <li><strong>Student</strong> signs in, opens the course, and submits before the due date.</li>
            <li><strong>Lecturer</strong> reviews each submission and posts a grade or requests revision.</li>
            <li><strong>Student</strong> reads feedback on the dashboard or submission detail page.</li>
            <li><strong>Lecturer / Admin</strong> exports CSV reports from Gradebook or Reports when needed.</li>
        </ol>
    </div>
</section>

<section class="ug-section ug-section--muted">
    <header class="ug-section-head">
        <p class="ug-section-eyebrow">Also in this guide</p>
        <h2 class="ug-section-title">Student &amp; lecturer walkthroughs</h2>
        <p class="ug-section-lead">Select the Student or Lecturer tab at the top of this page for full step-by-step guides with screen previews for those roles.</p>
    </header>
</section>
