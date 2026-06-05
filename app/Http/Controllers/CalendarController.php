<?php

namespace App\Http\Controllers;

use App\Support\LmsQuery;
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

        $assignments = LmsQuery::assignmentsFor($user)
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->with('course')
            ->orderBy('due_at')
            ->get()
            ->groupBy(fn ($a) => $a->due_at->format('Y-m-d'));

        $highlightDates = $assignments->keys()->all();

        return view('hubs.calendar', [
            'date' => $date,
            'start' => $start,
            'end' => $end,
            'assignmentsByDate' => $assignments,
            'highlightDates' => $highlightDates,
        ]);
    }
}
