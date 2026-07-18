<?php

namespace App\Http\Controllers;

use App\Enums\SessionDeliveryMode;
use App\Support\LmsQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $month = $request->integer('month', now()->month);
        $year = $request->integer('year', now()->year);

        $date = now()->setDate($year, $month, 1);
        $start = $date->copy()->startOfMonth()->startOfWeek();
        $end = $date->copy()->endOfMonth()->endOfWeek();

        $rangeStart = $start->copy()->startOfDay();
        $rangeEnd = $end->copy()->endOfDay();

        $assignments = LmsQuery::assignmentsFor($user)
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$rangeStart, $rangeEnd])
            ->with('course')
            ->orderBy('due_at')
            ->get()
            ->groupBy(fn ($a) => $a->due_at->format('Y-m-d'));

        $assessments = LmsQuery::assessmentsFor($user)
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$rangeStart, $rangeEnd])
            ->with('course')
            ->orderBy('due_at')
            ->get()
            ->groupBy(fn ($a) => $a->due_at->format('Y-m-d'));

        $sessions = LmsQuery::classSessionsFor($user)
            ->whereBetween('starts_at', [$rangeStart, $rangeEnd])
            ->with('course')
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn ($s) => $s->starts_at->format('Y-m-d'));

        $sessionEventsByDate = [];
        foreach ($sessions as $day => $daySessions) {
            $sessionEventsByDate[$day] = [
                'online' => $daySessions->contains(fn ($s) => $s->mode === SessionDeliveryMode::Online),
                'offline' => $daySessions->contains(fn ($s) => $s->mode === SessionDeliveryMode::Offline),
            ];
        }

        $dueEventsByDate = [];
        foreach (collect($assignments->keys())->merge($assessments->keys())->unique() as $day) {
            $dueEventsByDate[$day] = ['due' => true];
        }

        $selectedSessionDate = $this->parseDateParam($request->string('session_date')->toString());
        $selectedDueDate = $this->parseDateParam($request->string('due_date')->toString());

        // Backward compatibility: legacy ?date= still opens the session calendar day.
        if (! $selectedSessionDate && ! $selectedDueDate) {
            $legacyDate = $this->parseDateParam($request->string('date')->toString());
            if ($legacyDate) {
                $key = $legacyDate->format('Y-m-d');
                if ($sessions->has($key)) {
                    $selectedSessionDate = $legacyDate;
                } elseif ($assignments->has($key) || $assessments->has($key)) {
                    $selectedDueDate = $legacyDate;
                } else {
                    $selectedSessionDate = $legacyDate;
                }
            }
        }

        $selectedSessionSessions = collect();
        if ($selectedSessionDate) {
            $key = $selectedSessionDate->format('Y-m-d');
            $selectedSessionSessions = $sessions->get($key, collect());
        }

        $selectedDueAssignments = collect();
        $selectedDueAssessments = collect();
        if ($selectedDueDate) {
            $key = $selectedDueDate->format('Y-m-d');
            $selectedDueAssignments = $assignments->get($key, collect());
            $selectedDueAssessments = $assessments->get($key, collect());
        }

        $monthAssignments = $assignments->flatten()->sortBy('due_at');
        $monthAssessments = $assessments->flatten()->sortBy('due_at');
        $monthSessions = $sessions->flatten()->sortBy('starts_at');

        $calendarQuery = array_filter([
            'month' => $month,
            'year' => $year,
            'session_date' => $selectedSessionDate?->format('Y-m-d'),
            'due_date' => $selectedDueDate?->format('Y-m-d'),
        ]);

        return view('hubs.calendar', [
            'date' => $date,
            'start' => $start,
            'end' => $end,
            'assignmentsByDate' => $assignments,
            'assessmentsByDate' => $assessments,
            'sessionsByDate' => $sessions,
            'sessionEventsByDate' => $sessionEventsByDate,
            'dueEventsByDate' => $dueEventsByDate,
            'monthAssignments' => $monthAssignments,
            'monthAssessments' => $monthAssessments,
            'monthSessions' => $monthSessions,
            'selectedSessionDate' => $selectedSessionDate,
            'selectedDueDate' => $selectedDueDate,
            'selectedSessionSessions' => $selectedSessionSessions,
            'selectedDueAssignments' => $selectedDueAssignments,
            'selectedDueAssessments' => $selectedDueAssessments,
            'calendarQuery' => $calendarQuery,
        ]);
    }

    private function parseDateParam(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
