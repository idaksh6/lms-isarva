<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Assignment report — {{ $assignment->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #475569; margin-bottom: 12px; }
        .kpis { margin-bottom: 14px; }
        .kpi { display: inline-block; margin-right: 14px; margin-bottom: 6px; }
        .kpi strong { display: block; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; font-size: 9px; text-transform: uppercase; }
        tr:nth-child(even) td { background: #f8fafc; }
        .muted { color: #64748b; }
        .footer { margin-top: 12px; color: #64748b; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $assignment->title }}</h1>
    <p class="meta">
        {{ $assignment->course->code }} — {{ $assignment->course->title }}
        @if ($assignment->due_at)
            · Due {{ $assignment->due_at->format('M j, Y g:i A') }}
        @endif
        @if ($assignment->course->lecturer)
            · Lecturer {{ $assignment->course->lecturer->name }}
        @endif
    </p>

    @if (! empty($kpis))
        <div class="kpis">
            <span class="kpi"><strong>{{ $kpis['enrolled'] }}</strong> Enrolled</span>
            <span class="kpi"><strong>{{ $kpis['submitted'] }}</strong> Submitted</span>
            <span class="kpi"><strong>{{ $kpis['not_submitted'] }}</strong> Not submitted</span>
            <span class="kpi"><strong>{{ $kpis['late'] }}</strong> Late</span>
            <span class="kpi"><strong>{{ $kpis['reviewed'] }}</strong> Reviewed</span>
            <span class="kpi"><strong>{{ $kpis['avg_score'] !== null ? $kpis['avg_score'].'%' : '—' }}</strong> Avg score</span>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Student ID</th>
                <th>Email</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Days late</th>
                <th>Score</th>
                <th>Letter</th>
                <th>Feedback</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php $student = $row['student']; @endphp
                <tr>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->student_id ?: '—' }}</td>
                    <td>{{ $student->email }}</td>
                    <td>{{ $row['status_label'] }}</td>
                    <td>{{ $row['submitted_at']?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $row['days_late'] === null ? '—' : $row['days_late'] }}</td>
                    <td>{{ $row['score'] !== null ? $row['score'] : '—' }}</td>
                    <td>{{ $row['letter'] ?: '—' }}</td>
                    <td>{{ $row['feedback'] ?: '—' }}</td>
                    <td>{{ $row['source_label'] ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="muted">No students match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Generated {{ $generatedAt->format('Y-m-d H:i') }} · ISARVA LMS</p>
</body>
</html>
