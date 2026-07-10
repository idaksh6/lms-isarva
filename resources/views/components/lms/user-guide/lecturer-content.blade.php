<section class="ug-section">
    <header class="ug-section-head">
        <p class="ug-section-eyebrow">Lecturer workflow</p>
        <h2 class="ug-section-title">Create courses, collect work, and grade students</h2>
        <p class="ug-section-lead">
            This section covers your full teaching workflow — from creating a course to publishing assignments and posting grades.
            Switch to the <strong>Student</strong> tab above to see exactly what your class experiences when they submit work.
        </p>
    </header>

    <div class="ug-highlight-box">
        <h3 class="ug-highlight-title">What your students do (summary)</h3>
        <p class="ug-highlight-text">Sign in → open your course → read the assignment → upload a file <em>or</em> paste a Google Drive link → wait for your grade. Students only see courses they are enrolled in.</p>
        <div class="ug-mini-flow">
            <span>Login</span><span aria-hidden="true">→</span><span>Course</span><span aria-hidden="true">→</span><span>Submit</span><span aria-hidden="true">→</span><span>Grade</span>
        </div>
    </div>

    <div class="ug-flow">
        <div class="ug-flow-step is-active"><span>1</span> Create course</div>
        <div class="ug-flow-step"><span>2</span> Enroll students</div>
        <div class="ug-flow-step"><span>3</span> Publish assignment</div>
        <div class="ug-flow-step"><span>4</span> Review work</div>
        <div class="ug-flow-step"><span>5</span> Post grade</div>
    </div>

    <div class="ug-steps">
        <x-lms.user-guide.step
            :number="1"
            title="Create a new course"
            body="Go to Courses → Create course. Enter a unique course code (e.g. DS501), a clear title, and an optional description. The course is created empty — you add students and assignments next."
            :bullets="[
                'You are automatically set as the lecturer for courses you create.',
                'Administrators can create courses and assign a different lecturer from a dropdown.',
                'After creation you are taken to the course page with tabs: Edit course, Manage students, New assignment.',
            ]"
            mock="create-course"
        />

        <x-lms.user-guide.step
            :number="2"
            title="Enroll students in the course"
            body="Open your course → Manage students. On the right, tick the students to add, then click Enroll selected. Enrolled students immediately see the course in their portal."
            :bullets="[
                'Students must already have accounts — administrators create them under Users → Add user.',
                'The left panel lists currently enrolled students; use Remove to unenrol someone.',
                'Students not on the right list are either already enrolled or do not exist in the system yet.',
            ]"
            tip="Enrol students before publishing assignments so they receive notifications when work goes live."
            mock="enroll"
            reverse
        />

        <x-lms.user-guide.step
            :number="3"
            title="Create and publish an assignment"
            body="Click New assignment. Write a title, instructions, and due date. Choose how students submit work: File upload (standard), Cloud link (Google Drive), or File upload or cloud link (student chooses)."
            :bullets="[
                'File upload — students drag a file into the LMS (PDF, notebook, zip, etc.).',
                'Cloud link — you provide a shared Google Drive folder URL; students upload there and paste the file link back.',
                'Attach up to three resource files (10 MB each) for briefs, datasets, or templates.',
                'Keep Visible to students immediately checked to publish now; uncheck to save as draft.',
            ]"
            mock="new-assignment"
        />

        <x-lms.user-guide.step
            :number="4"
            title="Set up Google Drive for cloud assignments"
            body="For cloud-link assignments, create a folder in Google Drive, share it with your class, and paste the folder URL in Shared upload folder on the assignment form."
            :bullets="[
                'Students upload their zip to YOUR folder — not to the LMS server.',
                'They then copy the share link to their individual file and paste it in the submit form.',
                'You review by clicking Open in Google Drive on each submission — the LMS stores links only.',
            ]"
            mock="submit-link"
            reverse
        />

        <x-lms.user-guide.step
            :number="5"
            title="Notify students with announcements"
            body="Post course updates on the Announcements page. Choose a course or leave blank for a global message. Pin important posts so they stay at the top."
            :bullets="[
                'Enrolled students receive in-app notifications when you publish.',
                'Email notifications are sent if the student enabled them in Settings.',
                'Use announcements for due-date changes, new resources, or exam reminders.',
            ]"
            mock="announcement"
        />

        <x-lms.user-guide.step
            :number="6"
            title="Review student submissions"
            body="Open an assignment → scroll to Student submissions. Each row shows the student name, submission time, and status. Click View submission to open the full detail page."
            :bullets="[
                'File uploads — preview PDFs and images in the browser, or download other formats.',
                'Cloud links — click Open in Google Drive (or Dropbox / OneDrive) to review externally.',
                'The Submissions hub in the sidebar lists work across all your courses.',
            ]"
            mock="review"
            reverse
        />

        <x-lms.user-guide.step
            :number="7"
            title="Grade, give feedback, or request revision"
            body="On the submission page, enter a score from 0 to 100 and write feedback in the review form. Choose the action that fits: Post grade, Request revision, or Mark reviewed."
            :bullets="[
                'Post grade — saves score, letter grade, and feedback; student is notified immediately.',
                'Request revision — student can resubmit once; previous file or link is replaced.',
                'Mark reviewed — acknowledge you have seen the work without assigning a numeric grade.',
                'Use the Gradebook page for a spreadsheet-style overview across all courses.',
            ]"
            mock="grade"
        />
    </div>

    <aside class="ug-callout">
        <h3 class="ug-callout-title">Lecturer tools at a glance</h3>
        <div class="ug-callout-grid">
            <div class="ug-callout-card"><strong>Gradebook</strong><p>See every student score in one table. Filter by course.</p></div>
            <div class="ug-callout-card"><strong>Reports</strong><p>Export CSV files for records, accreditation, or offline analysis.</p></div>
            <div class="ug-callout-card"><strong>Q&amp;A</strong><p>Answer student questions on the community board. Only lecturers can post announcements.</p></div>
        </div>
    </aside>
</section>
