<?php

namespace App\Http\Controllers;

use App\Support\LmsTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        return view('hubs.settings', [
            'user' => $request->user(),
            'themes' => LmsTheme::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email_notifications' => ['sometimes', 'boolean'],
            'theme' => ['sometimes', 'string', Rule::in(LmsTheme::keys())],
        ]);

        $request->user()->update([
            'email_notifications' => $request->boolean('email_notifications'),
            'theme' => $validated['theme'] ?? $request->user()->theme,
        ]);

        return back()->with('success', 'Settings saved.');
    }

    public function updateTheme(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in(LmsTheme::keys())],
        ]);

        $request->user()->update([
            'theme' => $validated['theme'],
        ]);

        return response()->json([
            'ok' => true,
            'theme' => LmsTheme::resolve($validated['theme']),
        ]);
    }
}
