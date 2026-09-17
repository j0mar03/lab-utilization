<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Tool;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * DashboardController
 *
 * Renders the main authenticated dashboard, role-aware.
 * Separates Room Utilization and Tool Borrowing metrics and displays
 * department utilization breakdowns.
 *
 * Lab Head          → Full occupancy & tool view + stats + overdue alerts + quick actions
 * Student Assistant → Occupancy + tool status + overdue (for follow up)
 * Faculty           → Their own recent room and tool checkouts
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // ── Room Metrics ───────────────────────────────────────────────────
        $openRoomCount    = Transaction::open()->rooms()->count();
        $overdueRoomCount = Transaction::overdue()->rooms()->count();
        $todayRoomCount   = Transaction::rooms()->whereDate('checked_out_at', today())->count();
        $totalRooms       = Room::count();

        // Available rooms: rooms with no open checkout right now
        $availableRoomsCount = Room::whereDoesntHave(
            'transactions', fn ($q) => $q->where('status', 'open')
        )->count();

        // ── Tool Metrics ───────────────────────────────────────────────────
        $openToolCount    = Transaction::active()->tools()->count();
        $overdueToolCount = Transaction::overdue()->tools()->count();
        $todayToolCount   = Transaction::tools()->whereDate('checked_out_at', today())->count();
        $totalTools       = Tool::active()->count();

        // Available tools (active tools with at least 1 unit free)
        $availableToolsCount = Tool::active()->get()
            ->filter(fn ($t) => $t->available_quantity > 0)
            ->count();

        // ── Combined Totals (backward-compatible) ──────────────────────────
        $openCount    = $openRoomCount + $openToolCount;
        $overdueCount = $overdueRoomCount + $overdueToolCount;
        $todayCount   = $todayRoomCount + $todayToolCount;

        // ── Department Active Breakdown ────────────────────────────────────
        $deptActive = Transaction::active()
            ->select('department', DB::raw('COUNT(*) as total_active'))
            ->whereNotNull('department')
            ->groupBy('department')
            ->pluck('total_active', 'department');

        // ── Current Room Occupancy ─────────────────────────────────────────
        $occupiedRooms = Room::whereHas(
            'transactions', fn ($q) => $q->where('status', 'open')
        )->with([
            'transactions' => fn ($q) => $q
                ->where('status', 'open')
                ->latest('checked_out_at'),
        ])->orderByRaw("CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END, name")
          ->get();

        // ── Current Borrowed Tools (Direct or Multi-item) ──────────────────
        $borrowedTools = Tool::where(function ($query) {
            $query->whereHas('transactions', fn ($q) => $q->whereIn('status', ['open', 'partially_returned']))
                  ->orWhereHas('transactionItems', fn ($q) => $q->where('status', 'borrowed'));
        })->with([
            'transactions' => fn ($q) => $q->whereIn('status', ['open', 'partially_returned'])->latest('checked_out_at'),
            'transactionItems' => fn ($q) => $q->where('status', 'borrowed')->with('transaction'),
        ])->orderBy('department')->orderBy('category')
          ->get();

        // ── Overdue Transactions ───────────────────────────────────────────
        $overdueTransactions = Transaction::overdue()
            ->with(['room', 'tool', 'items.tool'])
            ->latest('checked_out_at')
            ->get();

        // ── Recent Activity (Role-aware & Separated) ──────────────────────
        $roomQuery = Transaction::rooms()
            ->with(['room'])
            ->latest('checked_out_at')
            ->limit(10);

        $toolQuery = Transaction::tools()
            ->with(['tool', 'items.tool'])
            ->latest('checked_out_at')
            ->limit(10);

        $allQuery = Transaction::with(['room', 'tool', 'items.tool'])
            ->latest('checked_out_at')
            ->limit(10);

        if ($user->isFaculty()) {
            $roomQuery->where('borrower_email', $user->email);
            $toolQuery->where('borrower_email', $user->email);
            $allQuery->where('borrower_email', $user->email);
        }

        $recentRoomTransactions = $roomQuery->get();
        $recentToolTransactions = $toolQuery->get();
        $recentTransactions     = $allQuery->get();

        return view('dashboard', compact(
            'openCount',
            'overdueCount',
            'todayCount',
            'openRoomCount',
            'overdueRoomCount',
            'todayRoomCount',
            'totalRooms',
            'availableRoomsCount',
            'openToolCount',
            'overdueToolCount',
            'todayToolCount',
            'totalTools',
            'availableToolsCount',
            'deptActive',
            'occupiedRooms',
            'borrowedTools',
            'overdueTransactions',
            'recentRoomTransactions',
            'recentToolTransactions',
            'recentTransactions'
        ));
    }
}
