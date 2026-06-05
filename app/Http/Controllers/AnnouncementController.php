<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Course;
use App\Support\LmsQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $courseIds = match (true) {
            $user->isAdmin() => Course::query()->pluck('courses.id'),
            $user->isLecturer() => Course::query()->where('lecturer_id', $user->id)->pluck('courses.id'),
            default => Course::query()
                ->whereHas('students', fn ($q) => $q->where('users.id', $user->id))
                ->pluck('courses.id'),
        };

        $announcements = Announcement::query()
            ->where(function ($q) use ($courseIds) {
                $q->whereNull('course_id')
                    ->orWhereIn('course_id', $courseIds);
            })
            ->with(['author', 'course'])
            ->orderByDesc('is_pinned')
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $courses = $user->isAdmin() || $user->isLecturer()
            ? LmsQuery::coursesFor($user)->where('is_active', true)->orderBy('code')->get()
            : collect();

        return view('hubs.announcements', compact('announcements', 'courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $course = null;

        if ($request->filled('course_id')) {
            $course = Course::query()->findOrFail($request->integer('course_id'));
            $this->authorize('create', [Announcement::class, $course]);
        } else {
            if (! $user->isAdmin()) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'course_id' => ['nullable', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'is_pinned' => ['sometimes', 'boolean'],
        ]);

        Announcement::query()->create([
            'course_id' => $course?->id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => now(),
        ]);

        return back()->with('success', 'Announcement published.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);
        $announcement->delete();

        return back()->with('success', 'Announcement removed.');
    }
}
