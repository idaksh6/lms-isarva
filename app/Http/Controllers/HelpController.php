<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        $visibleTabs = match (true) {
            $user->isAdmin() => ['student', 'lecturer', 'admin'],
            $user->isLecturer() => ['student', 'lecturer'],
            default => ['student'],
        };

        $defaultTab = match (true) {
            $user->isAdmin() => 'admin',
            $user->isLecturer() => 'lecturer',
            default => 'student',
        };

        return view('hubs.help', compact('visibleTabs', 'defaultTab'));
    }
}
