@php
    $user = auth()->user();

    $links = match (true) {
        $user->isAdmin() => [
            ['label' => 'Courses', 'route' => 'courses.index'],
            ['label' => 'Users', 'route' => 'admin.users.index'],
            ['label' => 'Reports', 'route' => 'reports.index'],
            ['label' => 'User guide', 'route' => 'help.index', 'new_tab' => true],
        ],
        $user->isLecturer() => [
            ['label' => 'My courses', 'route' => 'courses.index'],
            ['label' => 'Submissions', 'route' => 'submissions.index'],
            ['label' => 'Gradebook', 'route' => 'gradebook.index'],
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

<section class="corp-sidebar-panel">
    <div class="corp-sidebar-panel-head">
        <h3 class="corp-sidebar-panel-title">Quick links</h3>
        <p class="corp-sidebar-panel-desc">Jump to common pages.</p>
    </div>
    <ul class="corp-quick-links">
        @foreach ($links as $link)
            <li>
                <a href="{{ route($link['route']) }}" class="corp-quick-link"
                   @if (! empty($link['new_tab'])) target="_blank" rel="noopener noreferrer" @endif>
                    <span>{{ $link['label'] }}</span>
                    <span aria-hidden="true">→</span>
                </a>
            </li>
        @endforeach
    </ul>
</section>
