<?php

namespace App\Http\Controllers;

use App\Support\AiSettings;
use App\Support\LmsTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('hubs.settings', [
            'user' => $user,
            'themes' => LmsTheme::all(),
            'aiSettings' => $user->isAdmin() ? AiSettings::formState() : null,
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

    public function updateAi(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'driver' => ['required', Rule::in(['fake', 'openai'])],
            'api_key' => ['nullable', 'string', 'max:500'],
            'clear_api_key' => ['sometimes', 'boolean'],
            'base_url' => ['required', 'url', 'max:500'],
            'model' => ['required', 'string', 'max:120'],
        ]);

        if (($validated['driver'] ?? '') === 'openai'
            && blank($validated['api_key'] ?? null)
            && ! $request->boolean('clear_api_key')
            && ! AiSettings::formState()['api_key_set']) {
            return back()
                ->withErrors(['api_key' => 'Add an API key when using the OpenAI driver, or switch to Fake.'])
                ->withInput();
        }

        AiSettings::save([
            'enabled' => $request->boolean('enabled'),
            'driver' => $validated['driver'],
            'api_key' => $validated['api_key'] ?? null,
            'clear_api_key' => $request->boolean('clear_api_key'),
            'base_url' => $validated['base_url'],
            'model' => $validated['model'],
        ]);

        return back()->with('success', 'AI Teaching Copilot settings saved.');
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
