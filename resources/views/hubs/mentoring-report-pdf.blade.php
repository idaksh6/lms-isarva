<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Mentoring report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        p { margin: 0 0 12px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        .meta { margin-bottom: 16px; }
        .kpis td { border: none; padding: 4px 8px 4px 0; }
    </style>
</head>
<body>
    <h1>Mentoring effectiveness report</h1>
    <p class="meta">Generated {{ $generatedAt->format('M j, Y g:i A') }}</p>

    <table class="kpis">
        <tr>
            <td><strong>Active:</strong> {{ $summary['active'] }} / {{ $summary['total'] }}</td>
            <td><strong>Sessions:</strong> {{ $summary['sessions'] }}</td>
            <td><strong>Area closure:</strong> {{ $effectiveness['area_closure_rate'] !== null ? $effectiveness['area_closure_rate'].'%' : '—' }}</td>
            <td><strong>Plan completion:</strong> {{ $effectiveness['plan_completion_rate'] !== null ? $effectiveness['plan_completion_rate'].'%' : '—' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Mentee</th>
                <th>Mentor</th>
                <th>Course</th>
                <th>Status</th>
                <th>Sessions</th>
                <th>Areas achieved</th>
                <th>Plans completed</th>
                <th>Latest remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($relationships as $row)
                @php $latest = $row->sessions->first(); @endphp
                <tr>
                    <td>{{ $row->mentee->name }}</td>
                    <td>{{ $row->mentor->name }}</td>
                    <td>{{ $row->course?->code ?? '—' }}</td>
                    <td>{{ $row->status->label() }}</td>
                    <td>{{ $row->sessions->count() }}</td>
                    <td>{{ $row->improvementAreas->where('status', App\Enums\ImprovementAreaStatus::Achieved)->count() }}/{{ $row->improvementAreas->count() }}</td>
                    <td>{{ $row->actionPlans->where('status', App\Enums\ActionPlanStatus::Completed)->count() }}/{{ $row->actionPlans->count() }}</td>
                    <td>{{ $latest?->remarks ? \Illuminate\Support\Str::limit($latest->remarks, 80) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
