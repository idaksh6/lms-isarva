<section class="ug-section">
    <header class="ug-section-head">
        <p class="ug-section-eyebrow">Student workflow</p>
        <h2 class="ug-section-title">From sign-in to graded work</h2>
        <p class="ug-section-lead">
            Follow these steps every time you need to submit coursework. The portal shows only courses you are enrolled in.
            You can submit a file directly or paste a Google Drive link when your lecturer enables cloud submissions for large projects.
        </p>
    </header>

    <div class="ug-flow">
        <div class="ug-flow-step is-active"><span>1</span> Sign in</div>
        <div class="ug-flow-step"><span>2</span> Open course</div>
        <div class="ug-flow-step"><span>3</span> Read assignment</div>
        <div class="ug-flow-step"><span>4</span> Submit work</div>
        <div class="ug-flow-step"><span>5</span> View grade</div>
    </div>

    <div class="ug-steps">
        <x-lms.user-guide.step
            :number="1"
            title="Sign in to the portal"
            body="Go to the LMS login page and enter the email and password your institution gave you. After signing in you land on your personal dashboard with upcoming due dates and recent activity."
            :bullets="[
                'Use the same email address that appears on your enrollment letter.',
                'On local demo sites, click a demo email in the list to fill the login form quickly.',
                'If login fails, check that your account is active — contact your administrator if needed.',
            ]"
            tip="Use Forgot password on the login page, or ask your administrator to reset your account."
            mock="login"
        />

        <x-lms.user-guide.step
            :number="2"
            title="Find your course"
            body="Click Courses in the portal menu. You will see a card for each module you are enrolled in — for example DS501 Machine Learning Foundations. Click a course to open its assignment list."
            :bullets="[
                'Each card shows the course code, title, lecturer name, and how many assignments are published.',
                'If a course is missing, you are not enrolled yet — ask your lecturer or administrator.',
                'Use the search box on the Courses page to filter by code or title.',
            ]"
            mock="courses"
            reverse
        />

        <x-lms.user-guide.step
            :number="3"
            title="Open and read the assignment"
            body="Inside a course, every published assignment appears as a card. Click one to read the full instructions, download any resources your lecturer attached, and check the due date."
            :bullets="[
                'Red or highlighted text means the due date has passed — late submissions may still be accepted depending on policy.',
                'Resources (PDFs, briefs) can be previewed or downloaded from the assignment page.',
                'When you have not submitted yet, a blue Submit your work button appears at the top.',
            ]"
            mock="assignment"
        />

        <x-lms.user-guide.step
            :number="4"
            title="Submit a file (standard assignments)"
            body="Most assignments ask you to upload one file. Click Submit your work, drag your file into the upload zone or click browse, add optional notes for your lecturer, then click Submit assignment."
            :bullets="[
                'Accepted formats include PDF, Word, Jupyter notebooks (.ipynb), zip archives, and common image types.',
                'Maximum file size is shown on the upload screen — typically around 20 MB.',
                'You can submit only once per assignment unless your lecturer requests a revision.',
            ]"
            mock="submit-file"
            reverse
        />

        <x-lms.user-guide.step
            :number="5"
            title="Submit a Google Drive link (large projects)"
            body="Some assignments use cloud storage for big zip bundles. Your lecturer provides a shared folder link on the assignment page. Upload your file there first, then copy the share link to your file and paste it in the LMS."
            :bullets="[
                'Step A — Click Open shared folder and upload your zip to Google Drive.',
                'Step B — Right-click your uploaded file → Share → copy the file link (not the folder link).',
                'Step C — On the LMS submit page, paste the link, optionally name your file, and submit.',
                'Supported hosts: Google Drive, Dropbox, and OneDrive.',
            ]"
            tip="The LMS stores your link only — it does not download the file to the server. Your lecturer opens the link to review your work."
            mock="submit-link"
        />

        <x-lms.user-guide.step
            :number="6"
            title="Track progress on your dashboard"
            body="Your dashboard is your home base. It shows assignments due soon, items awaiting review, and recently graded work. Use Assignments and Submissions in the menu for full lists."
            :bullets="[
                'The Calendar page lists due dates across all your courses.',
                'Announcements show lecturer updates — check pinned posts first.',
                'Settings lets you pick a portal colour theme and toggle email notifications.',
            ]"
            mock="dashboard"
            reverse
        />

        <x-lms.user-guide.step
            :number="7"
            title="Read your grade and feedback"
            body="When your lecturer reviews your submission, you receive a notification. Open the assignment or go to Submissions to see your numeric score, letter grade, and written feedback."
            :bullets="[
                'If status is Needs revision, click Resubmit work to upload a new file or link.',
                'Feedback appears in a highlighted box on the submission detail page.',
                'Graded work also appears in your dashboard review snapshot.',
            ]"
            mock="grade"
        />
    </div>

    <aside class="ug-callout">
        <h3 class="ug-callout-title">Other tools students use</h3>
        <div class="ug-callout-grid">
            <div class="ug-callout-card">
                <strong>Q&amp;A</strong>
                <p>Ask questions publicly. Lecturers, admins, and classmates can reply. Mark an answer as accepted when your question is resolved.</p>
            </div>
            <div class="ug-callout-card">
                <strong>Announcements</strong>
                <p>Read course-wide or global updates from lecturers. Pinned announcements stay at the top of the list.</p>
            </div>
            <div class="ug-callout-card">
                <strong>Profile &amp; Settings</strong>
                <p>Update your name and email on Profile. Change theme colours and notification preferences on Settings.</p>
            </div>
        </div>
    </aside>
</section>
