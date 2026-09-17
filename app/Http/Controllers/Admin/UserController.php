<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users with role filter and search.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        $roleCounts = [
            'all'               => User::count(),
            'lab_head'          => User::where('role', 'lab_head')->count(),
            'student_assistant' => User::where('role', 'student_assistant')->count(),
            'faculty'           => User::where('role', 'faculty')->count(),
        ];

        return view('admin.users.index', compact('users', 'roleCounts'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role'             => ['required', Rule::in(['lab_head', 'student_assistant', 'faculty'])],
            'password'         => ['required', 'confirmed', Password::defaults()],
            'telegram_chat_id' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$validated['name']} successfully created with role: " . self::roleLabel($validated['role']));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'             => ['required', Rule::in(['lab_head', 'student_assistant', 'faculty'])],
            'password'         => ['nullable', 'confirmed', Password::defaults()],
            'telegram_chat_id' => ['nullable', 'string', 'max:100'],
        ]);

        // Prevent self-demotion if you are the current user
        if ($user->id === auth()->id() && $validated['role'] !== 'lab_head') {
            return back()->withErrors(['role' => 'You cannot remove the Lab Head (admin) role from your own account.']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} successfully updated.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} was deleted successfully.");
    }

    private static function roleLabel(string $role): string
    {
        return match ($role) {
            'lab_head'          => 'Lab Head (Admin)',
            'student_assistant' => 'Student Assistant',
            'faculty'           => 'Faculty',
            default             => ucfirst($role),
        };
    }
}
