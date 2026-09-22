<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Common curriculum year levels for easy selection.
     */
    public const YEAR_LEVELS = [
        '1st Year',
        '2nd Year',
        '3rd Year',
        '4th Year',
        'General Education / Bridging',
    ];

    /**
     * Display a listing of curriculum subjects with search and filtering.
     */
    public function index(Request $request): View
    {
        $query = Subject::query();

        // Search by code or course title
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
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

        // Filter by year level
        if ($request->filled('year_level')) {
            $query->where('year_level', $request->input('year_level'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $subjects = $query->orderBy('department')->orderBy('code')->paginate(25)->withQueryString();

        $departments = array_keys(Subject::DEPARTMENTS);
        $yearLevels  = self::YEAR_LEVELS;

        $counts = [
            'total'    => Subject::count(),
            'active'   => Subject::where('is_active', true)->count(),
            'inactive' => Subject::where('is_active', false)->count(),
            'decet'    => Subject::where('department', 'like', '%Computer and Electronics%')->count(),
            'domit'    => Subject::where('department', 'like', '%Office Management%')->count(),
            'demet'    => Subject::where('department', 'like', '%Electrical and Mechanical%')->count(),
        ];

        return view('admin.subjects.index', compact('subjects', 'departments', 'yearLevels', 'counts'));
    }

    /**
     * Show the form for creating a new curriculum subject.
     */
    public function create(): View
    {
        $departments = Subject::DEPARTMENTS;
        $yearLevels  = self::YEAR_LEVELS;

        return view('admin.subjects.create', compact('departments', 'yearLevels'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:255'],
            'department'  => ['required', 'string', 'max:255'],
            'year_level'  => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ], [
            'code.required'       => 'Please enter the subject course code (e.g. CPET 201, INTE 205).',
            'name.required'       => 'Please enter the course name or title.',
            'department.required' => 'Please select the department offering this subject.',
        ]);

        $subject = Subject::create([
            'code'       => strtoupper(trim($validated['code'])),
            'name'       => trim($validated['name']),
            'department' => $validated['department'],
            'year_level' => $validated['year_level'] ?? null,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.subjects.index')
            ->with('success', "Subject '{$subject->code} - {$subject->name}' was added successfully.");
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject): View
    {
        $departments = Subject::DEPARTMENTS;
        $yearLevels  = self::YEAR_LEVELS;

        return view('admin.subjects.edit', compact('subject', 'departments', 'yearLevels'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'code'        => ['required', 'string', 'max:50'],
            'name'        => ['required', 'string', 'max:255'],
            'department'  => ['required', 'string', 'max:255'],
            'year_level'  => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ], [
            'code.required'       => 'Please enter the subject course code.',
            'name.required'       => 'Please enter the course name or title.',
            'department.required' => 'Please select the department offering this subject.',
        ]);

        $subject->update([
            'code'       => strtoupper(trim($validated['code'])),
            'name'       => trim($validated['name']),
            'department' => $validated['department'],
            'year_level' => $validated['year_level'] ?? null,
            'is_active'  => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.subjects.index')
            ->with('success', "Subject '{$subject->code} - {$subject->name}' was updated successfully.");
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $title = "{$subject->code} - {$subject->name}";
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', "Subject '{$title}' has been deleted.");
    }
}
