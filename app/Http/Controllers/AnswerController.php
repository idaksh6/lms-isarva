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

        $parent = null;
        $depth = 0;

        if (! empty($validated['parent_id'])) {
            $parent = Answer::query()->findOrFail($validated['parent_id']);

            if ($parent->question_id !== $question->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Replies must belong to the same question.',
                ]);
            }

            $depth = $parent->depth() + 1;

            if ($depth > Answer::MAX_DEPTH) {
                throw ValidationException::withMessages([
                    'parent_id' => 'This thread has reached the maximum nesting depth.',
                ]);
            }
        }

        $answer = Answer::query()->create([
            'question_id' => $question->id,
            'parent_id' => $parent?->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $answer->load('author');

        if ($request->expectsJson()) {
            $html = view('hubs.questions.partials.thread-node', [
                'answer' => $answer->setRelation('children', collect()),
                'question' => $question,
                'depth' => $depth,
            ])->render();

            return response()->json([
                'answer' => [
                    'id' => $answer->id,
                    'parent_id' => $answer->parent_id,
                    'depth' => $depth,
                ],
                'html' => $html,
                'total_answers' => $question->answers()->count(),
                'message' => $parent ? 'Your reply has been posted.' : 'Your answer has been posted.',
            ]);
        }

        return back()->with('success', $parent ? 'Your reply has been posted.' : 'Your answer has been posted.');
    }

    public function accept(Question $question, Answer $answer): RedirectResponse
    {
        abort_unless($answer->question_id === $question->id, 404);
        abort_unless($answer->isRoot(), 422, 'Only top-level answers can be accepted.');
        $this->authorize('accept', $answer);

        $question->answers()->whereNull('parent_id')->update(['is_accepted' => false]);

        $answer->update(['is_accepted' => true]);
        $question->update(['is_resolved' => true]);

        return back()->with('success', 'Answer marked as accepted.');
    }

    public function destroy(Answer $answer): RedirectResponse
    {
        $this->authorize('delete', $answer);

        $question = $answer->question;
        $wasAccepted = $answer->is_accepted;

        $answer->delete();

        if ($wasAccepted) {
            $question->update(['is_resolved' => false]);
        }

        return back()->with('success', 'Answer removed.');
    }
}
