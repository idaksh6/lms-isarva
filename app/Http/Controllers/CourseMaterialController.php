<?php

namespace App\Http\Controllers;

use App\Support\UploadLimits;
use App\Enums\MaterialCategory;
use App\Models\Course;
use App\Models\CourseMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseMaterialController extends Controller
{
    public function index(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        $materials = $course->materials()
            ->with('uploader')
            ->get()
            ->groupBy(fn ($m) => $m->category->value);

        $aiGeneration = null;
        if ($request->integer('ai')) {
            $aiGeneration = \App\Models\AiGeneration::query()
                ->where('id', $request->integer('ai'))
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return view('courses.materials.index', [
            'course' => $course->load('lecturer')->loadCount(['students', 'assignments', 'classSessions', 'materials']),
            'materialsByCategory' => $materials,
            'categories' => MaterialCategory::cases(),
            'aiGeneration' => $aiGeneration,
            'aiMaterialId' => $request->integer('material') ?: null,
            'aiEnabled' => (bool) config('ai.enabled') && (bool) config('ai.features.material_summary'),
        ]);
    }

    public function create(Course $course): View
    {
        $this->authorize('create', CourseMaterial::class);
        $this->authorize('update', $course);

        return view('courses.materials.create', [
            'course' => $course->load('lecturer')->loadCount(['students', 'assignments', 'classSessions', 'materials']),
            'categories' => MaterialCategory::cases(),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('create', CourseMaterial::class);
        $this->authorize('update', $course);

        $maxKb = UploadLimits::courseMaterialMaxKilobytes();

        $validated = $request->validate([
            'category' => ['required', Rule::enum(MaterialCategory::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url', 'max:2048', 'required_without:file'],
            'file' => ['nullable', 'file', 'max:'.$maxKb, 'required_without:external_url'],
        ], [
            'file.required_without' => 'Upload a file or provide a link.',
            'external_url.required_without' => 'Upload a file or provide a link.',
            'file.max' => 'Each file must be '.UploadLimits::courseMaterialMaxMegabytes().' MB or smaller.',
            'file.uploaded' => UploadLimits::fileUploadErrorMessage($request->file('file')?->getError() ?? UPLOAD_ERR_NO_FILE),
        ]);

        $path = null;
        $fileName = null;
        $mime = null;

        if ($request->hasFile('file')) {
            /** @var UploadedFile $file */
            $file = $request->file('file');
            $path = $file->store('course-materials/'.$course->id, 'public');
            $fileName = $file->getClientOriginalName();
            $mime = $file->getClientMimeType();
        }

        $course->materials()->create([
            'uploaded_by' => $request->user()->id,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'file_name' => $fileName,
            'mime' => $mime,
            'external_url' => $validated['external_url'] ?? null,
        ]);

        return redirect()
            ->route('courses.materials.index', $course)
            ->with('success', 'Material added.');
    }

    public function edit(CourseMaterial $material): View
    {
        $this->authorize('update', $material);

        $course = $material->course->load('lecturer')->loadCount(['students', 'assignments', 'classSessions', 'materials']);

        return view('courses.materials.edit', [
            'course' => $course,
            'material' => $material,
            'categories' => MaterialCategory::cases(),
        ]);
    }

    public function update(Request $request, CourseMaterial $material): RedirectResponse
    {
        $this->authorize('update', $material);

        $maxKb = UploadLimits::courseMaterialMaxKilobytes();

        $validated = $request->validate([
            'category' => ['required', Rule::enum(MaterialCategory::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'file' => ['nullable', 'file', 'max:'.$maxKb],
        ], [
            'file.max' => 'Each file must be '.UploadLimits::courseMaterialMaxMegabytes().' MB or smaller.',
            'file.uploaded' => UploadLimits::fileUploadErrorMessage($request->file('file')?->getError() ?? UPLOAD_ERR_NO_FILE),
        ]);

        if (! $request->hasFile('file') && blank($validated['external_url'] ?? null) && ! $material->hasFile()) {
            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'Upload a file or provide a link.',
                    'external_url' => 'Upload a file or provide a link.',
                ]);
        }

        $data = [
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'external_url' => $validated['external_url'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $material->deleteFile();
            /** @var UploadedFile $file */
            $file = $request->file('file');
            $data['file_path'] = $file->store('course-materials/'.$material->course_id, 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['mime'] = $file->getClientMimeType();
        }

        $material->update($data);

        return redirect()
            ->route('courses.materials.index', $material->course)
            ->with('success', 'Material updated.');
    }

    public function destroy(CourseMaterial $material): RedirectResponse
    {
        $this->authorize('delete', $material);

        $course = $material->course;
        $material->deleteFile();
        $material->delete();

        return redirect()
            ->route('courses.materials.index', $course)
            ->with('success', 'Material removed.');
    }
}
