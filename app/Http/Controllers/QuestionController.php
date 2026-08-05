<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->string('status')->toString();
        $courseId = $request->integer('course_id') ?: null;

        $query = Question::query()
            ->with(['author', 'course'])
            ->withCount('answers');

        if ($status === 'open') {
            $query->where('is_resolved', false);
        } elseif ($status === 'answered') {
            $query->where('is_resolved', true);
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        $questions = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Question::query()->count(),
            'open' => Question::query()->where('is_resolved', false)->count(),
            'answered' => Question::query()->where('is_resolved', true)->count(),
        ];

        $courses = Course::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'title']);

        return view('hubs.questions.index', compact('questions', 'stats', 'courses', 'status', 'courseId'));
    }

    public function create(): View
    {
        $this->authorize('create', Question::class);

        $courses = Course::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'title']);

        return view('hubs.questions.create', compact('courses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Question::class);

        $validated = $request->validate([
            'course_id' => ['nullable', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $question = Question::query()->create([
            'user_id' => $request->user()->id,
            'course_id' => $validated['course_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        return redirect()
            ->route('questions.show', $question)
            ->with('success', 'Your question has been posted.');
    }

    public function show(Question $question): View
    {
        $this->authorize('view', $question);

        $question->load([
            'author',
            'course',
            'rootAnswers.author',
            'rootAnswers.childrenRecursive.author',
        ]);

        $answerCount = $question->answers()->count();

        return view('hubs.questions.show', compact('question', 'answerCount'));
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);
        $question->delete();

        return redirect()
            ->route('questions.index')
            ->with('success', 'Question removed.');
    }
}
