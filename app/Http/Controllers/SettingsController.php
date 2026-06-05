<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('hubs.settings', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_notifications' => ['sometimes', 'boolean'],
        ]);

        $request->user()->update([
            'email_notifications' => $request->boolean('email_notifications'),
        ]);

        return back()->with('success', 'Settings saved.');
    }
}
