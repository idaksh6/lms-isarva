<?php

namespace App\Http\Controllers;

use App\Enums\SessionDeliveryMode;
use App\Models\Course;
use App\Support\UploadLimits;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class TimetableImportController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $request->validate([
            'timetable' => ['required', 'file', 'mimes:csv,txt', 'max:'.UploadLimits::TIMETABLE_CSV_MAX_KB],
        ], [
            'timetable.max' => 'The CSV must be '.UploadLimits::timetableCsvMaxMegabytes().' MB or smaller.',
            'timetable.uploaded' => UploadLimits::fileUploadErrorMessage($request->file('timetable')?->getError() ?? UPLOAD_ERR_NO_FILE),
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('timetable');
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return back()->withErrors(['timetable' => 'Could not read the uploaded file.']);
        }

        $header = fgetcsv($handle);
        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row)) === 0) {
                continue;
            }

            $data = $this->mapRow($header ?: [], $row);

            if (! $data) {
                $skipped++;

                continue;
            }

            if (isset($data['semester']) && $course->semester && $data['semester'] !== $course->semester) {
                $skipped++;

                continue;
            }

            try {
                $startsAt = Carbon::parse($data['starts_at']);
                $endsAt = isset($data['ends_at']) && $data['ends_at'] !== ''
                    ? Carbon::parse($data['ends_at'])
                    : null;
            } catch (\Throwable) {
                $skipped++;

                continue;
            }

            $mode = SessionDeliveryMode::tryFrom(strtolower($data['mode'] ?? '')) ?? SessionDeliveryMode::Offline;

            $course->classSessions()->create([
                'created_by' => $request->user()->id,
                'title' => $data['title'] ?? null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'mode' => $mode,
                'meeting_link' => $mode === SessionDeliveryMode::Online ? ($data['meeting_link'] ?? null) : null,
                'location' => $mode === SessionDeliveryMode::Offline ? ($data['location'] ?? null) : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $created++;
        }

        fclose($handle);

        return redirect()
            ->route('courses.sessions.index', $course)
            ->with('success', "Timetable imported: {$created} class sessions created".($skipped ? ", {$skipped} rows skipped" : '').'.');
    }

    /**
     * @param  array<int, string|null>  $header
     * @param  array<int, string|null>  $row
     * @return array<string, string|null>|null
     */
    private function mapRow(array $header, array $row): ?array
    {
        if ($header !== []) {
            $mapped = [];
            foreach ($header as $index => $column) {
                $key = strtolower(trim((string) $column));
                $mapped[$key] = $row[$index] ?? null;
            }

            if (! isset($mapped['starts_at'])) {
                return null;
            }

            return $mapped;
        }

        if (count($row) < 2) {
            return null;
        }

        return [
            'title' => $row[0] ?? null,
            'starts_at' => $row[1] ?? null,
            'ends_at' => $row[2] ?? null,
            'mode' => $row[3] ?? 'offline',
            'meeting_link' => $row[4] ?? null,
            'location' => $row[5] ?? null,
            'semester' => $row[6] ?? null,
        ];
    }
}
