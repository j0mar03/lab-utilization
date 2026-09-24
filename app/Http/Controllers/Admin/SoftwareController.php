<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Software;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SoftwareController extends Controller
{
    /**
     * Recommended emoji icons for quick software category identification.
     */
    public const RECOMMENDED_ICONS = [
        '💻' => 'Computer / IDE / Code',
        '🎨' => 'Design / Creative',
        '🌐' => 'Network / Web / Internet',
        '📄' => 'Document / Office',
        '📐' => 'CAD / Engineering / Math',
        '🗄️' => 'Database / Server',
        '🔒' => 'Security / Cybersecurity',
        '📊' => 'Analytics / Statistics',
        '⚙️' => 'Engineering / Tools',
        '🤖' => 'AI / Robotics / Automation',
        '🧪' => 'Simulation / Lab',
        '🛠️' => 'Utilities / Dev Tools',
    ];

    /**
     * Display a listing of software with search and filters.
     */
    public function index(Request $request): View
    {
        $query = Software::query();

        // Search by name, short, category, or description
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by status (active/inactive)
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        // Filter by department
        if ($request->filled('department')) {
            $dept = $request->input('department');
            $query->whereJsonContains('departments', $dept);
        }

        $softwareList = $query->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate(20)
            ->withQueryString();

        $categories  = Software::CATEGORIES;
        $departments = Software::DEPARTMENTS;

        $allSoftware = Software::all();
        $counts = [
            'total'      => $allSoftware->count(),
            'active'     => $allSoftware->where('is_active', true)->count(),
            'inactive'   => $allSoftware->where('is_active', false)->count(),
            'categories' => $allSoftware->pluck('category')->filter()->unique()->count(),
        ];

        return view('admin.software.index', compact('softwareList', 'categories', 'departments', 'counts'));
    }

    /**
     * Show the form for creating a new software item.
     */
    public function create(): View
    {
        $categories  = Software::CATEGORIES;
        $departments = Software::DEPARTMENTS;
        $icons       = self::RECOMMENDED_ICONS;

        return view('admin.software.create', compact('categories', 'departments', 'icons'));
    }

    /**
     * Store a newly created software item in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100', Rule::unique('softwares', 'name')->whereNull('deleted_at')],
            'short'         => ['nullable', 'string', 'max:50'],
            'icon'          => ['nullable', 'string', 'max:30'],
            'category'      => ['nullable', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'departments'   => ['nullable', 'array'],
            'departments.*' => ['string', 'max:50'],
            'sort_order'    => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'name.required' => 'Please enter the software title or suite name.',
            'name.unique'   => 'A software suite with this name already exists in the catalog.',
        ]);

        $validated['icon']        = $request->filled('icon') ? $request->input('icon') : '💻';
        $validated['is_active']   = $request->boolean('is_active', true);
        $validated['sort_order']  = (int) ($request->input('sort_order', 0) ?? 0);
        $validated['departments'] = array_values(array_filter($request->input('departments', [])));

        $software = Software::create($validated);

        return redirect()->route('admin.software.index')
            ->with('success', "Software '{$software->name}' has been added to the catalog successfully.");
    }

    /**
     * Show the form for editing the specified software item.
     */
    public function edit(Software $software): View
    {
        $categories  = Software::CATEGORIES;
        $departments = Software::DEPARTMENTS;
        $icons       = self::RECOMMENDED_ICONS;

        return view('admin.software.edit', compact('software', 'categories', 'departments', 'icons'));
    }

    /**
     * Update the specified software item in storage.
     */
    public function update(Request $request, Software $software): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => [
                'required',
                'string',
                'max:100',
                Rule::unique('softwares', 'name')->ignore($software->id)->whereNull('deleted_at'),
            ],
            'short'         => ['nullable', 'string', 'max:50'],
            'icon'          => ['nullable', 'string', 'max:30'],
            'category'      => ['nullable', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'departments'   => ['nullable', 'array'],
            'departments.*' => ['string', 'max:50'],
            'sort_order'    => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'name.required' => 'Please enter the software title or suite name.',
            'name.unique'   => 'Another software suite already uses this name.',
        ]);

        $validated['icon']        = $request->filled('icon') ? $request->input('icon') : '💻';
        $validated['is_active']   = $request->boolean('is_active');
        $validated['sort_order']  = (int) ($request->input('sort_order', 0) ?? 0);
        $validated['departments'] = array_values(array_filter($request->input('departments', [])));

        $software->update($validated);

        return redirect()->route('admin.software.index')
            ->with('success', "Software '{$software->name}' has been updated successfully.");
    }

    /**
     * Remove the specified software item from storage (soft delete).
     */
    public function destroy(Software $software): RedirectResponse
    {
        $name = $software->name;
        $software->delete();

        return redirect()->route('admin.software.index')
            ->with('success', "Software '{$name}' has been archived.");
    }

    /**
     * Quick toggle active status.
     */
    public function toggle(Software $software): RedirectResponse
    {
        $software->update(['is_active' => !$software->is_active]);

        $statusText = $software->is_active ? 'activated and is now visible in checkouts' : 'disabled';

        return back()->with('success', "Software '{$software->name}' is now {$statusText}.");
    }
}
