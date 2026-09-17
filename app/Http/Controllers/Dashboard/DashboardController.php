<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Tool;
use App\Models\Transaction;
use Illuminate\View\View;

/**
 * DashboardController
 *
 * Renders the main authenticated dashboard, role-aware.
 *
 * Lab Head  → Full occupancy view + stats + overdue alerts + quick actions
 * Student Assistant → Occupancy + overdue (for following up)
 * Faculty   → Their own recent usage only
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // ── Core stats (all roles) ────────────────────────────────────────
        $openCount    = Transaction::open()->count();
        $overdueCount = Transaction::overdue()->count();
        $todayCount   = Transaction::whereDate('checked_out_at', today())->count();

        // Available rooms: rooms with no open checkout right now
        $availableRoomsCount = Room::whereDoesntHave(
            'transactions', fn ($q) => $q->where('status', 'open')
        )->count();

        $totalRooms = Room::count();

        // Available tools (active tools with at least 1 unit free)
        $availableToolsCount = Tool::active()->get()
            ->filter(fn ($t) => $t->available_quantity > 0)
            ->count();

        $totalTools = Tool::active()->count();

        // ── Current occupancy ─────────────────────────────────────────────
        // Rooms that have at least one open transaction right now
        $occupiedRooms = Room::whereHas(
            'transactions', fn ($q) => $q->where('status', 'open')
        )->with([
            'transactions' => fn ($q) => $q
                ->where('status', 'open')
                ->latest('checked_out_at'),
        ])->orderByRaw("CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END, name")
          ->get();

        // Tools with at least one open transaction
        $borrowedTools = Tool::whereHas(
            'transactions', fn ($q) => $q->where('status', 'open')
        )->with([
            'transactions' => fn ($q) => $q->where('status', 'open'),
        ])->orderBy('category')
          ->get();

        // ── Overdue items ─────────────────────────────────────────────────
        $overdueTransactions = Transaction::overdue()
            ->with(['room', 'tool'])
            ->latest('checked_out_at')
            ->get();

        // ── Recent activity (last 10 checkouts) ───────────────────────────
        // Faculty see only their own; others see everyone
        $recentQuery = Transaction::with(['room', 'tool'])
            ->latest('checked_out_at')
            ->limit(10);

        if ($user->isFaculty()) {
            $recentQuery->where('borrower_email', $user->email);
        }

        $recentTransactions = $recentQuery->get();

        return view('dashboard', compact(
            'openCount',
            'overdueCount',
            'todayCount',
            'availableRoomsCount',
            'totalRooms',
            'availableToolsCount',
            'totalTools',
            'occupiedRooms',
            'borrowedTools',
            'overdueTransactions',
            'recentTransactions',
        ));
    }
}
