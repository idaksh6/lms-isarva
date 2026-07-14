@php
    $user = auth()->user();

    $links = match (true) {
        $user->isAdmin() => [
            ['label' => 'Courses', 'route' => 'courses.index'],
            ['label' => 'Users', 'route' => 'admin.users.index'],
            ['label' => 'Reports', 'route' => 'reports.index'],
            ['label' => 'Calendar', 'route' => 'calendar.index'],
            ['label' => 'User guide', 'route' => 'help.index', 'new_tab' => true],
        ],
        $user->isLecturer() => [
            ['label' => 'My courses', 'route' => 'courses.index'],
            ['label' => 'Submissions', 'route' => 'submissions.index'],
            ['label' => 'Gradebook', 'route' => 'gradebook.index'],
            ['label' => 'Calendar', 'route' => 'calendar.index'],
            ['label' => 'User guide', 'route' => 'help.index', 'new_tab' => true],
        ],
        default => [
            ['label' => 'Assignments', 'route' => 'assignments.index'],
            ['label' => 'My courses', 'route' => 'courses.index'],
            ['label' => 'Calendar', 'route' => 'calendar.index'],
            ['label' => 'User guide', 'route' => 'help.index', 'new_tab' => true],
        ],
    };
@endphp

<section class="corp-panel corp-dash-quicklinks">
    <div class="corp-panel-head corp-dash-cal-head">
        <div>
            <h2 class="corp-panel-title">Quick links</h2>
            <p class="corp-panel-desc">Jump to common pages across the platform.</p>
        </div>
    </div>
    <div class="corp-dash-quicklinks-grid">
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="corp-dash-quicklink"
               @if (! empty($link['new_tab'])) target="_blank" rel="noopener noreferrer" @endif>
                <span>{{ $link['label'] }}</span>
                <span aria-hidden="true">→</span>
            </a>
        @endforeach
    </div>
</section>
