<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnswerController extends Controller
{
    public function store(Request $request, Question $question): RedirectResponse|JsonResponse
    {
        $this->authorize('create', [Answer::class, $question]);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
            'parent_id' => ['nullable', 'integer', 'exists:answers,id'],
        ]);

        $quoted = null;

        if (! empty($validated['parent_id'])) {
            $quoted = Answer::query()->with('author')->findOrFail($validated['parent_id']);

            if ($quoted->question_id !== $question->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Replies must belong to the same question thread.',
                ]);
            }
        }

        $answer = Answer::query()->create([
            'question_id' => $question->id,
            'parent_id' => $quoted?->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $answer->load(['author', 'quoted.author']);

        if (! $question->is_resolved) {
            $question->update(['is_resolved' => true]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            $html = view('hubs.questions.partials.chat-message', [
                'answer' => $answer,
                'question' => $question,
                'isMine' => true,
            ])->render();

            return response()->json([
                'answer' => [
                    'id' => $answer->id,
                    'parent_id' => $answer->parent_id,
                ],
                'html' => $html,
                'total_answers' => $question->answers()->count(),
                'message' => 'Reply posted.',
            ]);
        }

        return back()->with('success', 'Your reply has been posted.');
    }

    public function accept(Question $question, Answer $answer): RedirectResponse
    {
        abort(404);
    }

    public function destroy(Answer $answer): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $answer);

        $question = $answer->question;
        $answerId = $answer->id;

        $answer->delete();

        if ($question->answers()->count() === 0) {
            $question->update(['is_resolved' => false]);
        }

        if (request()->expectsJson()) {
            return response()->json([
                'deleted' => $answerId,
                'total_answers' => $question->answers()->count(),
            ]);
        }

        return back()->with('success', 'Reply removed.');
    }
}
