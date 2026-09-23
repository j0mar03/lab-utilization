<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomController extends Controller
{
    /**
     * Common building floor locations for easy selection.
     */
    public const LOCATIONS = [
        '1st Floor',
        '2nd Floor',
        '3rd Floor',
        '4th Floor',
        'Ground Floor',
        'Main Building',
    ];

    /**
     * Display a listing of rooms with search and category filtering.
     */
    public function index(Request $request): View
    {
        $query = Room::query()->with(['transactions' => function ($q) {
            $q->whereIn('status', ['open', 'partially_returned', 'overdue'])->latest('checked_out_at');
        }]);

        // Search by name or location
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('wifi_notes', 'like', "%{$search}%");
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

        // Filter by Room Type
        if ($request->filled('type')) {
            match ($request->input('type')) {
                'computer_lab'    => $query->computerLabs(),
                'engineering_lab' => $query->engineeringLabs(),
                'lecture'         => $query->lectures(),
                'office'          => $query->offices(),
                default           => null,
            };
        }

        // Filter by Occupancy Status
        if ($request->filled('status')) {
            if ($request->input('status') === 'occupied') {
                $query->occupied();
            } elseif ($request->input('status') === 'available') {
                $query->available();
            }
        }

        // Filter by Wi-Fi availability
        if ($request->filled('has_wifi')) {
            $query->where('has_wifi', $request->input('has_wifi') === '1');
        }

        $rooms = $query->orderBy('name')->paginate(20)->withQueryString();

        $departments = array_keys(Room::DEPARTMENTS);
        $locations   = self::LOCATIONS;

        $allRooms = Room::all();
        $counts = [
            'total'           => $allRooms->count(),
            'computer_labs'   => $allRooms->filter(fn($r) => $r->isComputerLab())->count(),
            'engineering_labs'=> $allRooms->filter(fn($r) => $r->isEngineeringLab())->count(),
            'lectures'        => $allRooms->filter(fn($r) => $r->isLecture())->count(),
            'offices'         => $allRooms->filter(fn($r) => $r->isOffice())->count(),
            'occupied'        => Room::occupied()->count(),
            'available'       => Room::available()->count(),
        ];

        return view('admin.rooms.index', compact('rooms', 'departments', 'locations', 'counts'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create(): View
    {
        $departments = Room::DEPARTMENTS;
        $locations   = self::LOCATIONS;
        $roomTypes   = Room::ROOM_TYPES;

        return view('admin.rooms.create', compact('departments', 'locations', 'roomTypes'));
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->whereNull('deleted_at')],
            'room_type'   => ['nullable', 'string', 'in:computer_lab,engineering_lab,lecture,office,general'],
            'department'  => ['nullable', 'string', 'max:255'],
            'location'    => ['nullable', 'string', 'max:255'],
            'capacity'    => ['nullable', 'integer', 'min:1', 'max:500'],
            'has_wifi'    => ['nullable', 'boolean'],
            'wifi_notes'  => ['nullable', 'string', 'max:500'],
            'manual_url'  => ['nullable', 'url', 'max:255'],
        ], [
            'name.unique' => 'A room with this name already exists in the system.',
        ]);

        $validated['has_wifi'] = $request->boolean('has_wifi');

        $room = Room::create($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room '{$room->name}' has been created successfully.");
    }

    /**
     * Display the specified room details, QR badge, and recent activity.
     */
    public function show(Room $room): View
    {
        $room->load(['tools']);

        $currentTx = $room->currentTransaction();

        $recentTransactions = Transaction::where('room_id', $room->id)
            ->with(['user', 'items.tool'])
            ->latest('checked_out_at')
            ->limit(10)
            ->get();

        return view('admin.rooms.show', compact('room', 'currentTx', 'recentTransactions'));
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit(Room $room): View
    {
        $departments = Room::DEPARTMENTS;
        $locations   = self::LOCATIONS;
        $roomTypes   = Room::ROOM_TYPES;

        return view('admin.rooms.edit', compact('room', 'departments', 'locations', 'roomTypes'));
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'name')->ignore($room->id)->whereNull('deleted_at')
            ],
            'room_type'   => ['nullable', 'string', 'in:computer_lab,engineering_lab,lecture,office,general'],
            'department'  => ['nullable', 'string', 'max:255'],
            'location'    => ['nullable', 'string', 'max:255'],
            'capacity'    => ['nullable', 'integer', 'min:1', 'max:500'],
            'has_wifi'    => ['nullable', 'boolean'],
            'wifi_notes'  => ['nullable', 'string', 'max:500'],
            'manual_url'  => ['nullable', 'url', 'max:255'],
        ], [
            'name.unique' => 'Another room already uses this name.',
        ]);

        $validated['has_wifi'] = $request->boolean('has_wifi');

        $room->update($validated);

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room '{$room->name}' has been updated successfully.");
    }

    /**
     * Remove the specified room from storage (soft delete).
     */
    public function destroy(Room $room): RedirectResponse
    {
        if ($room->isOccupied()) {
            return back()->with('error', "Cannot delete room '{$room->name}' because it currently has an active checkout session.");
        }

        $roomName = $room->name;
        $room->delete();

        return redirect()->route('admin.rooms.index')
            ->with('success', "Room '{$roomName}' has been removed.");
    }
}
