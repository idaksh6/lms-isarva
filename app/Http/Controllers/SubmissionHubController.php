<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Support\LmsQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionHubController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = LmsQuery::submissionsFor($user);

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('assignment', fn ($a) => $a->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('student', fn ($s) => $s
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            if (in_array($status, array_column(SubmissionStatus::cases(), 'value'), true)) {
                $query->where('status', $status);
            }
        }

        $submissions = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => LmsQuery::submissionsFor($user)->count(),
            'pending_review' => LmsQuery::submissionsFor($user)
                ->whereIn('status', [SubmissionStatus::Submitted, SubmissionStatus::Late])
                ->count(),
            'graded' => LmsQuery::submissionsFor($user)
                ->where('status', SubmissionStatus::Reviewed)
                ->count(),
        ];

        return view('hubs.submissions', compact('submissions', 'stats'));
    }
}
