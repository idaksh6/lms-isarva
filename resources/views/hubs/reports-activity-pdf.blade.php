<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Course activity — {{ $course->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 14px 0 6px; }
        .meta { color: #475569; margin-bottom: 10px; }
        .kpis { margin-bottom: 10px; }
        .kpi { display: inline-block; margin-right: 12px; margin-bottom: 4px; }
        .kpi strong { display: block; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; font-size: 9px; text-transform: uppercase; }
        .note { color: #92400e; margin: 4px 0; }
        .footer { margin-top: 10px; color: #64748b; font-size: 9px; }
    </style>
</head>
<body>
    @php $kpis = $report['kpis']; @endphp
    <h1>Course activity — {{ $course->code }}</h1>
    <p class="meta">
        {{ $course->title }}
        @if ($course->lecturer)
            · {{ $course->lecturer->name }}
        @endif
    </p>

    <div class="kpis">
        <span class="kpi"><strong>{{ $kpis['enrolled'] }}</strong> Enrolled</span>
        <span class="kpi"><strong>{{ $kpis['sessions_total'] }}</strong> Sessions</span>
        <span class="kpi"><strong>{{ $kpis['assignments_published'] }}</strong> Assignments</span>
        <span class="kpi"><strong>{{ $kpis['assessments_published'] }}</strong> Quizzes</span>
        <span class="kpi"><strong>{{ $kpis['participation_rate'] ?? 0 }}%</strong> Participation</span>
        <span class="kpi"><strong>{{ $kpis['avg_assignment_score'] !== null ? $kpis['avg_assignment_score'].'%' : '—' }}</strong> Avg score</span>
    </div>

    <p class="note">Attendance is not recorded in the LMS. Google Form scores appear after lecturers record them on the assessment page.</p>

    <h2>Class sessions / activities</h2>
    <table>
        <thead>
            <tr><th>Title</th><th>Starts</th><th>Mode</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($report['sessions'] as $session)
                <tr>
                    <td>{{ $session->displayTitle() }}</td>
                    <td>{{ $session->starts_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $session->mode?->label() }}</td>
                    <td>{{ $session->starts_at->isPast() ? 'Past' : 'Upcoming' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No sessions scheduled.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Assignments</h2>
    <table>
        <thead>
            <tr><th>Assignment</th><th>Due</th><th>Submitted</th><th>Late</th><th>Avg %</th><th>Rate %</th></tr>
        </thead>
        <tbody>
            @forelse ($report['assignments'] as $row)
                <tr>
                    <td>{{ $row['assignment']->title }}</td>
                    <td>{{ $row['assignment']->due_at?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $row['submitted'] }}</td>
                    <td>{{ $row['late'] }}</td>
                    <td>{{ $row['avg_score'] ?? '—' }}</td>
                    <td>{{ $row['submission_rate'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No published assignments.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Quizzes / assessments</h2>
    <table>
        <thead>
            <tr><th>Assessment</th><th>Type</th><th>Completed</th><th>Rate %</th><th>Avg %</th></tr>
        </thead>
        <tbody>
            @forelse ($report['assessments'] as $row)
                <tr>
                    <td>{{ $row['assessment']->title }}</td>
                    <td>{{ $row['type_label'] }}</td>
                    <td>{{ $row['completed'] }}</td>
                    <td>{{ $row['completion_rate'] ?? '—' }}</td>
                    <td>{{ $row['avg_score'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No published assessments.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Student participation</h2>
    <table>
        <thead>
            <tr><th>Student</th><th>ID</th><th>Assignments</th><th>Quizzes</th><th>Q&A</th><th>Participation %</th></tr>
        </thead>
        <tbody>
            @forelse ($report['participation'] as $row)
                <tr>
                    <td>{{ $row['student']->name }}</td>
                    <td>{{ $row['student']->student_id ?: '—' }}</td>
                    <td>{{ $row['assignments_submitted'] }}/{{ $row['assignments_total'] }}</td>
                    <td>{{ $row['quizzes_completed'] }}/{{ $row['quizzes_total'] }}</td>
                    <td>{{ $row['questions_asked'] }}Q / {{ $row['answers_posted'] }}A</td>
                    <td>{{ $row['participation_rate'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No enrolled students.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Generated {{ $generatedAt->format('Y-m-d H:i') }} · ISARVA LMS</p>
</body>
</html>
