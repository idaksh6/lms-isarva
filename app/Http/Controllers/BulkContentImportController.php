<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Notifications\AssignmentPublishedNotification;
use App\Support\BulkImport\AssessmentQuestionImporter;
use App\Support\BulkImport\AssignmentImportParser;
use App\Support\BulkImport\DocumentTextExtractor;
use App\Support\BulkImport\ImportTemplateBuilder;
use App\Support\BulkImport\McqImportParser;
use App\Support\UploadLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkContentImportController extends Controller
{
    public function templatesIndex(): View
    {
        $this->authorize('create', Assignment::class);

        return view('imports.templates');
    }

    public function downloadTemplate(Request $request, ImportTemplateBuilder $builder): StreamedResponse
    {
        $this->authorize('create', Assignment::class);

        $kind = $request->route('kind');
        $format = $request->query('format', 'docx');

        return $format === 'txt'
            ? $builder->downloadTxt($kind)
            : $builder->downloadDocx($kind);
    }

    public function importAssessmentQuestions(
        Request $request,
        Assessment $assessment,
        DocumentTextExtractor $extractor,
        McqImportParser $parser,
        AssessmentQuestionImporter $importer,
    ): RedirectResponse {
        $this->authorize('update', $assessment);

        if ($assessment->isGoogleForm()) {
            return back()->withErrors(['import_file' => 'Use this import only for in-LMS quizzes.']);
        }

        $request->validate([
            'import_file' => [
                'required',
                'file',
                'mimes:doc,docx,pdf,txt',
                'max:'.UploadLimits::BULK_IMPORT_MAX_KB,
            ],
        ], [
            'import_file.max' => 'The import file must be '.UploadLimits::bulkImportMaxMegabytes().' MB or smaller.',
            'import_file.uploaded' => UploadLimits::fileUploadErrorMessage($request->file('import_file')?->getError() ?? UPLOAD_ERR_NO_FILE),
        ]);

        try {
            $extracted = $extractor->extract($request->file('import_file'));
            $questions = $parser->parse($extracted['text']);
            $count = $importer->replace($assessment, $questions);
        } catch (RuntimeException $e) {
            return back()->withErrors(['import_file' => $e->getMessage()]);
        }

        return redirect()
            ->route('assessments.edit', $assessment)
            ->with('success', "Imported {$count} questions from template. Review them below, then publish when ready.");
    }

    public function importCourseAssignments(
        Request $request,
        Course $course,
        DocumentTextExtractor $extractor,
        AssignmentImportParser $parser,
    ): RedirectResponse {
        $this->authorize('create', Assignment::class);
        $this->authorize('update', $course);

        $request->validate([
            'import_file' => [
                'required',
                'file',
                'mimes:doc,docx,pdf,txt',
                'max:'.UploadLimits::BULK_IMPORT_MAX_KB,
            ],
        ], [
            'import_file.max' => 'The import file must be '.UploadLimits::bulkImportMaxMegabytes().' MB or smaller.',
            'import_file.uploaded' => UploadLimits::fileUploadErrorMessage($request->file('import_file')?->getError() ?? UPLOAD_ERR_NO_FILE),
        ]);

        try {
            $extracted = $extractor->extract($request->file('import_file'));
            $rows = $parser->parse($extracted['text']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['import_file' => $e->getMessage()]);
        }

        $created = 0;
        $published = 0;

        foreach ($rows as $row) {
            $assignment = $course->assignments()->create([
                'created_by' => $request->user()->id,
                'title' => $row['title'],
                'instructions' => $row['instructions'],
                'delivery_method' => $row['delivery_method'],
                'drop_folder_url' => $row['drop_folder_url'],
                'due_at' => $row['due_at'],
                'is_published' => $row['is_published'],
            ]);

            $created++;

            if ($assignment->is_published) {
                $published++;
                $course->loadMissing('students');
                foreach ($course->students as $student) {
                    if ($student->isActive()) {
                        $student->notify(new AssignmentPublishedNotification($assignment));
                    }
                }
            }
        }

        return redirect()
            ->route('courses.show', $course)
            ->with('success', "Imported {$created} assignment".($created === 1 ? '' : 's')
                .($published ? " ({$published} published)" : ' as drafts').'.');
    }
}
