<?php

namespace App\Http\Controllers;

use App\Support\LmsQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentHubController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = LmsQuery::assignmentsFor($user);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('course', fn ($c) => $c
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            if ($request->string('status')->toString() === 'published') {
                $query->where('is_published', true);
            } elseif ($request->string('status')->toString() === 'draft') {
                $query->where('is_published', false);
            }
        }

        $assignments = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => LmsQuery::assignmentsFor($user)->count(),
            'published' => LmsQuery::assignmentsFor($user)->where('is_published', true)->count(),
            'due_this_week' => LmsQuery::assignmentsFor($user)
                ->where('is_published', true)
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [now()->startOfDay(), now()->addWeek()])
                ->count(),
        ];

        return view('hubs.assignments', compact('assignments', 'stats'));
    }
}
