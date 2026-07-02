@php
    $user = auth()->user();

    $links = match (true) {
        $user->isAdmin() => [
            ['label' => 'Courses', 'route' => 'courses.index'],
            ['label' => 'Users', 'route' => 'admin.users.index'],
            ['label' => 'Reports', 'route' => 'reports.index'],
            ['label' => 'Calendar', 'route' => 'calendar.index'],
        ],
        $user->isLecturer() => [
            ['label' => 'My courses', 'route' => 'courses.index'],
            ['label' => 'Submissions', 'route' => 'submissions.index'],
            ['label' => 'Gradebook', 'route' => 'gradebook.index'],
            ['label' => 'Calendar', 'route' => 'calendar.index'],
        ],
        default => [
            ['label' => 'Assignments', 'route' => 'assignments.index'],
            ['label' => 'My courses', 'route' => 'courses.index'],
            ['label' => 'Calendar', 'route' => 'calendar.index'],
            ['label' => 'Help', 'route' => 'help.index'],
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
                <a href="{{ route($link['route']) }}" class="corp-quick-link">
                    <span>{{ $link['label'] }}</span>
                    <span aria-hidden="true">→</span>
                </a>
            </li>
        @endforeach
    </ul>
</section>
