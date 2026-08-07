<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>At-risk — {{ $course->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
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
    <h1>Weak students — {{ $course->code }}</h1>
    <p class="meta">
        {{ $course->title }}
        @if ($course->lecturer)
            · {{ $course->lecturer->name }}
        @endif
        · Generated {{ $generatedAt->format('Y-m-d H:i') }}
    </p>

    <div class="kpis">
        <span class="kpi"><strong>{{ $kpis['flagged'] }}</strong> Flagged</span>
        <span class="kpi"><strong>{{ $kpis['open_cases'] }}</strong> Open cases</span>
        <span class="kpi"><strong>{{ $kpis['resolved_cases'] }}</strong> Resolved</span>
        <span class="kpi"><strong>{{ $kpis['avg_risk_score'] !== null ? $kpis['avg_risk_score'] : '—' }}</strong> Avg risk</span>
        <span class="kpi"><strong>{{ $kpis['enrolled'] }}</strong> Enrolled</span>
    </div>

    @foreach ($report['notes'] as $note)
        <p class="note">{{ $note }}</p>
    @endforeach

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>ID</th>
                <th>Risk</th>
                <th>Reasons</th>
                <th>Avg</th>
                <th>Course avg</th>
                <th>Missing</th>
                <th>Late</th>
                <th>Quiz</th>
                <th>Part. %</th>
                <th>Case</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['flagged'] as $row)
                @php $m = $row['metrics']; @endphp
                <tr>
                    <td>{{ $row['student']->name }}</td>
                    <td>{{ $row['student']->student_id ?? '' }}</td>
                    <td>{{ $row['risk_score'] }}</td>
                    <td>{{ implode('; ', $row['reasons']) }}</td>
                    <td>{{ $m['assignment_avg'] ?? '—' }}</td>
                    <td>{{ $m['course_avg'] ?? '—' }}</td>
                    <td>{{ $m['missing_overdue'] ?? '—' }}</td>
                    <td>{{ $m['late_count'] ?? '—' }}</td>
                    <td>{{ $m['quiz_avg'] ?? '—' }}</td>
                    <td>{{ $m['participation_rate'] ?? '—' }}</td>
                    <td>{{ $row['active_case'] ? 'Yes' : 'No' }}</td>
                </tr>
            @empty
                <tr><td colspan="11">No students flagged.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">ISARVA LMS · Course at-risk report</p>
</body>
</html>
