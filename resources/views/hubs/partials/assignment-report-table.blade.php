<div class="corp-table-wrap">
    <table class="corp-table corp-table--compact">
        <thead>
            <tr>
                <th>Student</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Days late</th>
                <th>Score</th>
                <th>Feedback</th>
                <th>Source</th>
                <th><span class="sr-only">Action</span></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php
                    /** @var \App\Models\User $student */
                    $student = $row['student'];
                    $submission = $row['submission'];
                    $daysLate = $row['days_late'];
                @endphp
                <tr class="corp-table-row group">
                    <td class="corp-table-cell">
                        <span class="corp-table-title">{{ $student->name }}</span>
                        <span class="corp-table-meta">
                            {{ $student->student_id ?: 'No ID' }}
                            · {{ $student->email }}
                            @unless ($student->is_active)
                                · Inactive
                            @endunless
                        </span>
                    </td>
                    <td class="corp-table-cell">
                        @if ($submission)
                            <x-status-badge :status="$submission->status" />
                        @else
                            <span class="lms-report-status-missing">Not submitted</span>
                        @endif
                    </td>
                    <td class="corp-table-cell corp-table-cell--muted">
                        {{ $row['submitted_at']?->format('M j, Y g:i A') ?? '—' }}
                    </td>
                    <td class="corp-table-cell corp-table-cell--muted">
                        @if ($daysLate === null)
                            —
                        @elseif ($daysLate > 0)
                            <span class="text-amber-700 font-semibold">+{{ $daysLate }}d</span>
                        @elseif ($daysLate < 0)
                            <span class="text-emerald-700">{{ $daysLate }}d</span>
                        @else
                            0
                        @endif
                    </td>
                    <td class="corp-table-cell">
                        @if ($row['is_graded'])
                            <x-lms.grade-badge :score="$row['score']" :letter="$row['letter']" size="sm" />
                        @else
                            <span class="text-xs text-isarva-muted">—</span>
                        @endif
                    </td>
                    <td class="corp-table-cell corp-table-cell--muted">
                        <span class="lms-report-feedback" title="{{ $row['feedback'] }}">
                            {{ $row['feedback'] ? \Illuminate\Support\Str::limit($row['feedback'], 48) : '—' }}
                        </span>
                        @if ($row['reviewed_at'])
                            <span class="corp-table-meta">
                                Reviewed {{ $row['reviewed_at']->format('M j') }}
                                @if ($row['reviewer_name'])
                                    by {{ $row['reviewer_name'] }}
                                @endif
                            </span>
                        @endif
                    </td>
                    <td class="corp-table-cell corp-table-cell--muted">
                        {{ $row['source_label'] ?? '—' }}
                        @if ($row['file_or_link'])
                            <span class="corp-table-meta">{{ \Illuminate\Support\Str::limit($row['file_or_link'], 28) }}</span>
                        @endif
                    </td>
                    <td class="corp-table-cell corp-table-cell--action">
                        @if ($submission)
                            <a href="{{ route('submissions.show', $submission) }}" class="corp-table-action">View</a>
                        @else
                            <span class="text-xs text-isarva-muted">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
