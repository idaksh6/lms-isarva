<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(Request $request, Question $question): RedirectResponse
    {
        $this->authorize('create', [Answer::class, $question]);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        Answer::query()->create([
            'question_id' => $question->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Your answer has been posted.');
    }

    public function accept(Question $question, Answer $answer): RedirectResponse
    {
        abort_unless($answer->question_id === $question->id, 404);
        $this->authorize('accept', $answer);

        $question->answers()->update(['is_accepted' => false]);

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
