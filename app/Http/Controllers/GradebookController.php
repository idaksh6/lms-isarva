<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Support\LmsQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradebookController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            abort(403);
        }

        $courses = LmsQuery::coursesFor($user)
            ->where('is_active', true)
            ->withCount('students')
            ->orderBy('code')
            ->get();

        $selectedCourse = null;
        $rows = collect();

        if ($courseId = $request->integer('course')) {
            $selectedCourse = $courses->firstWhere('id', $courseId)
                ?? Course::query()->find($courseId);

            if ($selectedCourse) {
                $this->authorize('view', $selectedCourse);

                $selectedCourse->load([
                    'students',
                    'assignments' => fn ($q) => $q->where('is_published', true)->orderBy('due_at')->with('submissions'),
                ]);

                $rows = $selectedCourse->students->map(function ($student) use ($selectedCourse) {
                    $cells = $selectedCourse->assignments->map(function ($assignment) use ($student) {
                        $submission = $assignment->submissions->firstWhere('user_id', $student->id);

                        return [
                            'assignment' => $assignment,
                            'submission' => $submission,
                        ];
                    });

                    $scores = $cells->pluck('submission')->filter(fn ($s) => $s?->score !== null)->pluck('score');
                    $average = $scores->isNotEmpty() ? round($scores->avg(), 1) : null;

                    return compact('student', 'cells', 'average');
                });
            }
        }

        return view('hubs.gradebook', compact('courses', 'selectedCourse', 'rows'));
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $user = $request->user();

        if ($user->isStudent()) {
            abort(403);
        }

        $course = Course::query()->findOrFail($request->integer('course'));
        $this->authorize('view', $course);

        $course->load([
            'students',
            'assignments' => fn ($q) => $q->where('is_published', true)->orderBy('due_at'),
        ]);

        $filename = 'gradebook-'.$course->code.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($course) {
            $handle = fopen('php://output', 'w');
            $header = ['Student', 'Student ID'];
            foreach ($course->assignments as $assignment) {
                $header[] = $assignment->title.' (%)';
            }
            $header[] = 'Average (%)';
            fputcsv($handle, $header);

            foreach ($course->students as $student) {
                $row = [$student->name, $student->student_id ?? ''];
                $scores = [];
                foreach ($course->assignments as $assignment) {
                    $submission = $assignment->submissions()->where('user_id', $student->id)->first();
                    $score = $submission?->score;
                    $row[] = $score !== null ? $score : '';
                    if ($score !== null) {
                        $scores[] = (float) $score;
                    }
                }
                $row[] = $scores ? round(array_sum($scores) / count($scores), 1) : '';
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
