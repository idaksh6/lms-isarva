@props([
    'course',
    'session' => null,
    'action',
    'method' => 'POST',
])

@php
    $isEdit = $session !== null;
    $mode = old('mode', $session?->mode?->value ?? \App\Enums\SessionDeliveryMode::Online->value);
@endphp

<form method="POST" action="{{ $action }}" class="lms-form-card corp-session-form" x-data="{ mode: '{{ $mode }}' }">
    @csrf
    @if (in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE'], true))
        @method($method)
    @endif

    <div class="lms-form-header">
        <h2 class="lms-form-title">{{ $isEdit ? 'Edit class session' : 'Schedule class session' }}</h2>
        <p class="lms-form-desc">Set the date, time, and delivery mode. Students will see this on the calendar.</p>
    </div>

    <div class="corp-session-form-section corp-session-form-section--first">
        <h3 class="corp-session-form-label">Session details</h3>
        <div class="lms-form-field">
            <label for="title" class="lms-field-label">Title <span class="text-isarva-muted font-normal">(optional)</span></label>
            <input id="title" type="text" name="title" value="{{ old('title', $session?->title) }}" class="lms-field-input mt-1.5" placeholder="e.g. Lecture 3 — Neural networks">
            <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
        </div>
    </div>

    <div class="corp-session-form-section">
        <h3 class="corp-session-form-label">Date & time</h3>
        <p class="corp-session-form-hint">Set when the class starts. Add an end time if the session has a fixed duration.</p>
        <div class="corp-datetime-grid">
            <div class="corp-datetime-field">
                <label for="starts_at" class="corp-datetime-label">Starts</label>
                <input id="starts_at" type="datetime-local" name="starts_at" value="{{ old('starts_at', $session?->starts_at?->format('Y-m-d\TH:i')) }}" class="corp-datetime-input" required>
                <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
            </div>
            <div class="corp-datetime-field">
                <label for="ends_at" class="corp-datetime-label">Ends <span class="corp-datetime-optional">optional</span></label>
                <input id="ends_at" type="datetime-local" name="ends_at" value="{{ old('ends_at', $session?->ends_at?->format('Y-m-d\TH:i')) }}" class="corp-datetime-input">
                <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
            </div>
        </div>
    </div>

    <div class="corp-session-form-section">
        <h3 class="corp-session-form-label">Delivery mode</h3>
        <div class="corp-mode-grid">
            @foreach (\App\Enums\SessionDeliveryMode::cases() as $option)
                <label @class([
                    'corp-mode-option',
                    'corp-mode-option--online' => $option === \App\Enums\SessionDeliveryMode::Online,
                    'corp-mode-option--offline' => $option === \App\Enums\SessionDeliveryMode::Offline,
                ])>
                    <input type="radio" name="mode" value="{{ $option->value }}" class="corp-mode-option-input" x-model="mode" @checked(old('mode', $session?->mode?->value) === $option->value)>
                    <span class="corp-mode-option-icon" aria-hidden="true">
                        @if ($option === \App\Enums\SessionDeliveryMode::Online)
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        @endif
                    </span>
                    <span class="corp-mode-option-copy">
                        <span class="corp-mode-option-name">{{ $option->label() }}</span>
                        <span class="corp-mode-option-desc">{{ $option === \App\Enums\SessionDeliveryMode::Online ? 'Video call / meeting link' : 'Campus or classroom' }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('mode')" class="mt-2" />
    </div>

    <div class="corp-session-form-section" x-show="mode === 'online'" x-cloak>
        <div class="lms-form-field">
            <label for="meeting_link" class="lms-field-label">Meeting link</label>
            <input id="meeting_link" type="url" name="meeting_link" value="{{ old('meeting_link', $session?->meeting_link) }}" class="lms-field-input mt-1.5" placeholder="https://meet.google.com/...">
            <x-input-error :messages="$errors->get('meeting_link')" class="mt-1.5" />
        </div>
    </div>

    <div class="corp-session-form-section" x-show="mode === 'offline'" x-cloak>
        <div class="lms-form-field">
            <label for="location" class="lms-field-label">Location</label>
            <input id="location" type="text" name="location" value="{{ old('location', $session?->location) }}" class="lms-field-input mt-1.5" placeholder="e.g. Room 204, Data Science Block">
            <x-input-error :messages="$errors->get('location')" class="mt-1.5" />
        </div>
    </div>

    <div class="corp-session-form-section">
        <div class="lms-form-field">
            <label for="notes" class="lms-field-label">Notes <span class="text-isarva-muted font-normal">(optional)</span></label>
            <textarea id="notes" name="notes" rows="3" class="lms-field-input mt-1.5" placeholder="Any extra instructions for students">{{ old('notes', $session?->notes) }}</textarea>
        </div>
    </div>

    <div class="lms-form-actions">
        <button type="submit" class="lms-btn-primary">{{ $isEdit ? 'Save changes' : 'Schedule class' }}</button>
        <a href="{{ route('courses.sessions.index', $course) }}" class="lms-btn-secondary">Cancel</a>
    </div>
</form>
