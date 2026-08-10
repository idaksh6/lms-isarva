@extends('layouts.lms')

@section('title', 'Class materials — ' . $course->code)
@section('page_title', $course->title)

@section('content')
<div class="lms-page-stack">
    <x-lms.course-hero :course="$course" active="materials" />

    <section class="lms-panel lms-panel--list">
        <div class="lms-panel-header">
            <div class="lms-panel-heading">
                <h2 class="lms-panel-title">Class materials</h2>
                <span class="lms-panel-count">{{ $course->materials_count ?? $materialsByCategory->flatten()->count() }}</span>
            </div>
            @can('create', \App\Models\CourseMaterial::class)
                @can('update', $course)
                    <a href="{{ route('courses.materials.create', $course) }}" class="lms-btn-primary lms-btn-primary--sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add material
                    </a>
                @endcan
            @endcan
        </div>

        <div class="lms-panel-body p-0">
            @php $total = $materialsByCategory->flatten()->count(); @endphp
            @if ($total === 0)
                <div class="lms-empty-panel py-12">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-isarva-heading">No materials uploaded yet</p>
                    <p class="mt-1 text-sm text-isarva-muted">Share syllabus, notes, datasets, and reference files with students.</p>
                    @can('update', $course)
                        <a href="{{ route('courses.materials.create', $course) }}" class="mt-4 lms-btn-primary">Upload first material</a>
                    @endcan
                </div>
            @else
                <div class="lms-material-stack">
                    @foreach ($categories as $category)
                        @php $items = $materialsByCategory->get($category->value, collect()); @endphp
                        @if ($items->isNotEmpty())
                            <section class="lms-material-section" aria-labelledby="materials-{{ $category->value }}">
                                <header class="lms-material-section-head">
                                    <h3 id="materials-{{ $category->value }}" class="lms-material-section-title">{{ $category->label() }}</h3>
                                    <span class="lms-material-section-count">{{ $items->count() }} {{ $items->count() === 1 ? 'item' : 'items' }}</span>
                                </header>

                                <ul class="lms-material-list">
                                    @foreach ($items as $material)
                                        @php
                                            $hasFile = $material->hasFile();
                                            $hasLink = filled($material->external_url);
                                            $iconClass = match (true) {
                                                $hasFile && $hasLink => 'lms-material-icon--both',
                                                $hasFile => 'lms-material-icon--file',
                                                default => 'lms-material-icon--link',
                                            };
                                            $typeLabel = match (true) {
                                                $hasFile && $hasLink => 'File + link',
                                                $hasFile => 'Uploaded file',
                                                default => 'External link',
                                            };
                                        @endphp
                                        <li class="lms-material-row">
                                            <div @class(['lms-material-icon', $iconClass]) aria-hidden="true">
                                                @if ($hasFile && ! $hasLink)
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                @elseif ($hasLink && ! $hasFile)
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.193 2.121a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                                                @else
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h12A2.25 2.25 0 0120.25 6v3.776"/></svg>
                                                @endif
                                            </div>

                                            <div class="lms-material-main">
                                                <p class="lms-material-title">{{ $material->title }}</p>
                                                @if ($material->description)
                                                    <p class="lms-material-desc">{{ $material->description }}</p>
                                                @endif
                                                <div class="lms-material-meta">
                                                    <span class="lms-material-type-badge">{{ $typeLabel }}</span>
                                                    @if ($hasFile && $material->file_name)
                                                        <span class="text-xs text-isarva-muted">{{ $material->file_name }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="lms-material-actions">
                                                @if ($hasFile)
                                                    <a href="{{ route('media.course-material', $material) }}" target="_blank" rel="noopener" class="lms-btn-secondary lms-btn-secondary--xs">View</a>
                                                    <a href="{{ route('media.course-material.download', $material) }}" class="lms-btn-secondary lms-btn-secondary--xs">Download</a>
                                                @endif
                                                @if ($hasLink)
                                                    <a href="{{ $material->external_url }}" target="_blank" rel="noopener" class="lms-btn-secondary lms-btn-secondary--xs">Open link</a>
                                                @endif
                                                @can('update', $material)
                                                    <a href="{{ route('course-materials.edit', $material) }}" class="lms-btn-secondary lms-btn-secondary--xs">Edit</a>
                                                    @if ($aiEnabled ?? false)
                                                        <form method="POST" action="{{ route('ai.materials.summary', $material) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">AI summarise</button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                            @if (($aiGeneration ?? null)?->isReady() && ($aiMaterialId ?? null) === $material->id)
                                                <div class="lms-ai-block mt-3 w-full sm:col-span-2">
                                                    <h3 class="lms-ai-block-title">AI summary</h3>
                                                    <ul class="lms-ai-list">
                                                        @foreach ($aiGeneration->output['summary'] ?? [] as $line)
                                                            <li>{{ $line }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
