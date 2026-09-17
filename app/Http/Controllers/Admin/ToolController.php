<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Tool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin Tool Controller
 *
 * Full CRUD for managing tools (keys, cables, peripherals, etc.).
 * Only accessible to users with the 'lab_head' role.
 *
 * Routes:
 *   GET    /admin/tools            → index()   — list all tools
 *   GET    /admin/tools/create     → create()  — show create form
 *   POST   /admin/tools            → store()   — save new tool
 *   GET    /admin/tools/{tool}/edit → edit()   — show edit form
 *   PUT    /admin/tools/{tool}     → update()  — save edits
 *   DELETE /admin/tools/{tool}     → destroy() — soft delete
 */
class ToolController extends Controller
{
    /**
     * List all tools with their availability status.
     */
    public function index(Request $request): View
    {
        $query = Tool::withTrashed(false) // exclude soft-deleted
            ->with('room')
            ->withCount([
                'transactions as borrowed_count' => function ($q) {
                    $q->where('status', 'open');
                },
            ]);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        // Filter by active/inactive
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $tools = $query->orderBy('department')->orderBy('category')->orderBy('name')->paginate(20)->withQueryString();

        // Get distinct categories and departments for filter dropdowns
        $categories  = Tool::distinct()->orderBy('category')->pluck('category');
        $departments = Tool::DEPARTMENTS;

        return view('admin.tools.index', compact('tools', 'categories', 'departments'));
    }

    /**
     * Show the form to add a new tool.
     */
    public function create(): View
    {
        $rooms = Room::orderByRaw("
            CASE 
                WHEN name LIKE '%Office%' THEN 0 
                WHEN name LIKE 'LAB%' THEN 1 
                ELSE 2 
            END, 
            name
        ")->get();
        $categories  = Tool::distinct()->orderBy('category')->pluck('category');
        $departments = Tool::DEPARTMENTS;

        return view('admin.tools.create', compact('rooms', 'categories', 'departments'));
    }

    /**
     * Save a new tool to the database.
     */
    public function store(Request $request): RedirectResponse
    {
        // If a new category was entered, prioritize it as the category
        if ($request->filled('new_category')) {
            $request->merge(['category' => trim($request->input('new_category'))]);
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'max:100'],
            'new_category'   => ['nullable', 'string', 'max:100'],
            'department'     => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'total_quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'room_id'        => ['nullable', 'exists:rooms,id'],
            'is_active'      => ['boolean'],
        ], [
            'category.required' => 'Please select an existing category or enter a new category name.',
        ]);

        unset($validated['new_category']);
        $validated['is_active'] = $request->boolean('is_active', true);

        Tool::create($validated);

        return redirect()
            ->route('admin.tools.index')
            ->with('success', "Tool \"{$validated['name']}\" added successfully.");
    }

    /**
     * Show the form to edit an existing tool.
     */
    public function edit(Tool $tool): View
    {
        $rooms = Room::orderByRaw("
            CASE 
                WHEN name LIKE '%Office%' THEN 0 
                WHEN name LIKE 'LAB%' THEN 1 
                ELSE 2 
            END, 
            name
        ")->get();
        $categories  = Tool::distinct()->orderBy('category')->pluck('category');
        $departments = Tool::DEPARTMENTS;

        return view('admin.tools.edit', compact('tool', 'rooms', 'categories', 'departments'));
    }

    /**
     * Save changes to an existing tool.
     */
    public function update(Request $request, Tool $tool): RedirectResponse
    {
        // If a new category was entered, prioritize it as the category
        if ($request->filled('new_category')) {
            $request->merge(['category' => trim($request->input('new_category'))]);
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'category'       => ['required', 'string', 'max:100'],
            'new_category'   => ['nullable', 'string', 'max:100'],
            'department'     => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'total_quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'room_id'        => ['nullable', 'exists:rooms,id'],
            'is_active'      => ['boolean'],
        ], [
            'category.required' => 'Please select an existing category or enter a new category name.',
        ]);

        unset($validated['new_category']);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Warn if new total_quantity is less than currently borrowed
        $currentlyBorrowed = $tool->transactions()->where('status', 'open')->sum('quantity');
        if ($validated['total_quantity'] < $currentlyBorrowed) {
            return back()
                ->withInput()
                ->withErrors(['total_quantity' => "Cannot set quantity to {$validated['total_quantity']} — {$currentlyBorrowed} units are currently borrowed."]);
        }

        $tool->update($validated);

        return redirect()
            ->route('admin.tools.index')
            ->with('success', "Tool \"{$tool->name}\" updated successfully.");
    }

    /**
     * Soft-delete a tool.
     * Keeps all historical transaction records intact.
     */
    public function destroy(Tool $tool): RedirectResponse
    {
        // Prevent deleting if there are open transactions
        $openCount = $tool->transactions()->where('status', 'open')->count();
        if ($openCount > 0) {
            return back()->withErrors([
                'delete' => "Cannot delete \"{$tool->name}\" — it has {$openCount} open transaction(s). Mark it inactive instead.",
            ]);
        }

        $name = $tool->name;
        $tool->delete(); // soft delete

        return redirect()
            ->route('admin.tools.index')
            ->with('success', "Tool \"{$name}\" removed. Transaction history is preserved.");
    }
}
