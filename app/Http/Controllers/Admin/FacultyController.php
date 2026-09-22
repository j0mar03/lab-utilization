<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacultyController extends Controller
{
    /**
     * Display a listing of faculty members with search and department filtering.
     */
    public function index(Request $request): View
    {
        $query = Faculty::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $dept = $request->input('department');
            $query->where(function ($q) use ($dept) {
                $q->where('department', $dept)
                  ->orWhere('department', 'like', "%{$dept}%");
            });
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $faculties = $query->orderBy('department')->orderBy('name')->paginate(20)->withQueryString();

        $departments = array_keys(Faculty::DEPARTMENTS);

        $counts = [
            'total'    => Faculty::count(),
            'active'   => Faculty::where('is_active', true)->count(),
            'inactive' => Faculty::where('is_active', false)->count(),
            'decet'    => Faculty::where('department', 'like', '%Computer and Electronics%')->count(),
            'domit'    => Faculty::where('department', 'like', '%Office Management%')->count(),
            'demet'    => Faculty::where('department', 'like', '%Electrical and Mechanical%')->count(),
        ];

        return view('admin.faculties.index', compact('faculties', 'departments', 'counts'));
    }

    /**
     * Show the form for creating a new faculty member.
     */
    public function create(): View
    {
        $departments = Faculty::DEPARTMENTS;
        return view('admin.faculties.create', compact('departments'));
    }

    /**
     * Store a newly created faculty member in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:faculties,email'],
            'department' => ['required', 'string', 'max:255'],
            'gender'     => ['nullable', 'in:M,F,Other'],
            'is_active'  => ['nullable', 'boolean'],
        ], [
            'name.required'       => 'Please enter the faculty member’s full name.',
            'email.required'      => 'Please provide a valid PUP or institutional email.',
            'email.unique'        => 'A faculty member with this email is already registered.',
            'department.required' => 'Please select the faculty member’s department.',
        ]);

        $faculty = Faculty::create([
            'name'       => trim($validated['name']),
            'email'      => strtolower(trim($validated['email'])),
            'department' => $validated['department'],
            'gender'     => $validated['gender'] ?? null,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.faculties.index')
            ->with('success', "Faculty member '{$faculty->name}' was added successfully.");
    }

    /**
     * Show the form for editing the specified faculty member.
     */
    public function edit(Faculty $faculty): View
    {
        $departments = Faculty::DEPARTMENTS;
        return view('admin.faculties.edit', compact('faculty', 'departments'));
    }

    /**
     * Update the specified faculty member in storage.
     */
    public function update(Request $request, Faculty $faculty): RedirectResponse
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('faculties', 'email')->ignore($faculty->id)],
            'department' => ['required', 'string', 'max:255'],
            'gender'     => ['nullable', 'in:M,F,Other'],
            'is_active'  => ['nullable', 'boolean'],
        ], [
            'name.required'       => 'Please enter the faculty member’s full name.',
            'email.required'      => 'Please provide a valid PUP or institutional email.',
            'email.unique'        => 'This email is already assigned to another faculty member.',
            'department.required' => 'Please select a department.',
        ]);

        $faculty->update([
            'name'       => trim($validated['name']),
            'email'      => strtolower(trim($validated['email'])),
            'department' => $validated['department'],
            'gender'     => $validated['gender'] ?? null,
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.faculties.index')
            ->with('success', "Faculty member '{$faculty->name}' was updated successfully.");
    }

    /**
     * Remove the specified faculty member from storage.
     */
    public function destroy(Faculty $faculty): RedirectResponse
    {
        $name = $faculty->name;
        $faculty->delete();

        return redirect()->route('admin.faculties.index')
            ->with('success', "Faculty member '{$name}' has been deleted.");
    }
}
