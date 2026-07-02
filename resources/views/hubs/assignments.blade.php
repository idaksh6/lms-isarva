@extends('layouts.lms')

@section('title', 'Assignments')
@section('page_title', 'Assignments')

@section('content')
<div class="lms-page-stack">
    <x-lms.module-hero module="assignments" title="All assignments" subtitle="Browse every assignment across your courses in one place.">
        <div class="lms-stat-chips">
            <span class="lms-stat-chip"><strong>{{ $stats['total'] }}</strong> total</span>
            <span class="lms-stat-chip"><strong>{{ $stats['published'] }}</strong> published</span>
            <span class="lms-stat-chip"><strong>{{ $stats['due_this_week'] }}</strong> due this week</span>
        </div>
    </x-lms.module-hero>

    <form method="GET" class="lms-filter-bar">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title or course..." class="lms-field-input lms-filter-search">
        <div class="lms-filter-select-wrap">
            <select name="status" class="lms-field-input lms-filter-select">
                <option value="">All statuses</option>
                <option value="published" @selected(request('status') === 'published')>Published</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            </select>
        </div>
        <button type="submit" class="lms-btn-secondary lms-btn-secondary--xs">Filter</button>
    </form>

    <div class="lms-hub-list lms-hub-list--cards">
        @forelse ($assignments as $assignment)
            <x-lms.assignment-list-item :assignment="$assignment" :showCourse="true" />
        @empty
            <x-lms.empty-state title="No assignments found" message="Try a different search or create one from a course page." variant="assignment" />
        @endforelse
    </div>

    @if ($assignments->hasPages())
        <div>{{ $assignments->links() }}</div>
    @endif
</div>
@endsection
