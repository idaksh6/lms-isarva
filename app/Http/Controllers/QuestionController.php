<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $courseId = $request->integer('course_id') ?: null;
        $search = $request->string('q')->trim()->toString();
        $threadId = $request->integer('thread') ?: null;

        $query = Question::query()
            ->with(['author', 'course'])
            ->withCount('answers')
            ->withMax('answers', 'created_at');

        if ($status === 'open') {
            $query->where('is_resolved', false);
        } elseif ($status === 'answered') {
            $query->where('is_resolved', true);
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('title', 'like', $like)
                    ->orWhere('body', 'like', $like)
                    ->orWhereHas('answers', fn ($answers) => $answers->where('body', 'like', $like))
                    ->orWhereHas('author', fn ($author) => $author->where('name', 'like', $like));
            });
        }

        $questions = $query
            ->latest()
            ->paginate(20)
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

        $openQuestion = null;
        if ($threadId) {
            $openQuestion = Question::query()->find($threadId);
            if ($openQuestion) {
                $this->authorize('view', $openQuestion);
            }
        }

        return view('hubs.questions.index', compact(
            'questions',
            'stats',
            'courses',
            'status',
            'courseId',
            'search',
            'threadId',
            'openQuestion',
        ));
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
            ->route('questions.index', ['thread' => $question->id])
            ->with('success', 'Your question has been posted.');
    }

    public function show(Question $question): View
    {
        $this->authorize('view', $question);

        $this->loadThread($question);

        $answerCount = $question->answers->count();

        return view('hubs.questions.show', compact('question', 'answerCount'));
    }

    public function panel(Question $question): JsonResponse
    {
        $this->authorize('view', $question);

        $this->loadThread($question);
        $answerCount = $question->answers->count();

        $html = view('hubs.questions.partials.thread-shell', [
            'question' => $question,
            'answerCount' => $answerCount,
            'embedded' => true,
        ])->render();

        return response()->json([
            'id' => $question->id,
            'title' => $question->title,
            'html' => $html,
            'total_answers' => $answerCount,
            'url' => route('questions.index', ['thread' => $question->id]),
        ]);
    }

    public function feed(Request $request, Question $question): JsonResponse
    {
        $this->authorize('view', $question);

        $afterId = $request->integer('after') ?: 0;

        $answers = Answer::query()
            ->with(['author', 'quoted.author'])
            ->where('question_id', $question->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->oldest()
            ->get();

        $viewerId = $request->user()?->id;
        $htmlItems = $answers->map(fn (Answer $answer) => [
            'id' => $answer->id,
            'html' => view('hubs.questions.partials.chat-message', [
                'answer' => $answer,
                'question' => $question,
                'isMine' => $viewerId === $answer->user_id,
            ])->render(),
        ])->values();

        return response()->json([
            'answers' => $htmlItems,
            'total_answers' => $question->answers()->count(),
            'latest_id' => $answers->last()?->id ?? $afterId,
        ]);
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);
        $question->delete();

        return redirect()
            ->route('questions.index')
            ->with('success', 'Question removed.');
    }

    private function loadThread(Question $question): void
    {
        $question->load([
            'author',
            'course',
            'answers' => fn ($q) => $q->with(['author', 'quoted.author'])->oldest(),
        ]);
    }
}
