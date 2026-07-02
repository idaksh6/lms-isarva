<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\BulkStudentImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->string('q')->trim()->toString(), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')->toString()))
            ->when($request->string('status')->toString() === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->string('status')->toString() === 'active', fn ($q) => $q->where('is_active', true))
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'lecturer' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'admins' => User::where('role', UserRole::Admin)->count(),
            'lecturers' => User::where('role', UserRole::Lecturer)->count(),
            'students' => User::where('role', UserRole::Student)->count(),
            'active' => User::where('is_active', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function bulkImportForm(): View
    {
        return view('admin.users.bulk-import');
    }

    public function bulkImportStore(Request $request, BulkStudentImporter $importer): View|RedirectResponse
    {
        $validated = $request->validate([
            'emails' => ['required', 'string', 'max:50000'],
        ]);

        $results = $importer->import($validated['emails']);

        if ($results['created'] === [] && $results['skipped'] === [] && $results['invalid'] === []) {
            return back()
                ->withInput()
                ->withErrors(['emails' => 'Enter at least one valid student email address.']);
        }

        return view('admin.users.bulk-import-results', compact('results'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'student_id' => ['nullable', 'string', 'max:50', 'unique:users,student_id'],
        ]);

        if ($validated['role'] === UserRole::Student->value && empty($validated['student_id'])) {
            return back()
                ->withInput()
                ->withErrors(['student_id' => 'Student ID is required for student accounts.']);
        }

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'student_id' => $validated['student_id'] ?? null,
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'student_id' => ['nullable', 'string', 'max:50', Rule::unique('users', 'student_id')->ignore($user->id)],
        ]);

        if ($validated['role'] === UserRole::Student->value && empty($validated['student_id'])) {
            return back()
                ->withInput()
                ->withErrors(['student_id' => 'Student ID is required for student accounts.']);
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'student_id' => $validated['role'] === UserRole::Student->value
                ? ($validated['student_id'] ?? null)
                : null,
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account updated.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);
        $user->update(['is_active' => false]);

        return back()->with('success', $user->name.' was deactivated.');
    }

    public function activate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);
        $user->update(['is_active' => true]);

        return back()->with('success', $user->name.' was reactivated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->submissions()->exists()) {
            return back()->with('error', 'Cannot delete this user because they have submissions. Deactivate the account instead.');
        }

        $user->enrolledCourses()->detach();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account deleted.');
    }
}
