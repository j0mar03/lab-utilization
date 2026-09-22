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

        // ── Room Metrics & Live Room Board ─────────────────────────────────
        $allRooms = Room::with([
            'transactions' => fn ($q) => $q
                ->whereIn('status', ['open', 'partially_returned', 'overdue'])
                ->with(['items.tool'])
                ->latest('checked_out_at'),
        ])->orderByRaw("
            CASE 
                WHEN name LIKE 'LAB%' AND name NOT LIKE '%Office%' THEN 0 
                WHEN name LIKE 'LEC%' THEN 1 
                ELSE 2 
            END,
            CAST(REGEXP_SUBSTR(name, '[0-9]+') AS UNSIGNED),
            name
        ")->get();

        $occupiedRooms       = $allRooms->filter(fn ($r) => $r->transactions->isNotEmpty())->values();
        $vacantRooms         = $allRooms->filter(fn ($r) => $r->transactions->isEmpty())->values();
        $openRoomCount       = $occupiedRooms->count();
        $availableRoomsCount = $vacantRooms->count();
        $totalRooms          = $allRooms->count();
        $overdueRoomCount    = Transaction::overdue()->rooms()->count();
        $todayRoomCount      = Transaction::rooms()->whereDate('checked_out_at', today())->count();
        $roomUtilizationRate = $totalRooms > 0 ? (int) round(($openRoomCount / $totalRooms) * 100) : 0;
        $floors              = $allRooms->pluck('location')->filter()->unique()->values();

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

        // ── Current Borrowed Tools (Direct or Multi-item) ──────────────────
        $borrowedTools = Tool::where(function ($query) {
            $query->whereHas('transactions', fn ($q) => $q->whereIn('status', ['open', 'partially_returned', 'overdue']))
                  ->orWhereHas('transactionItems', fn ($q) => $q->where('status', 'borrowed'));
        })->with([
            'transactions' => fn ($q) => $q->whereIn('status', ['open', 'partially_returned', 'overdue'])->latest('checked_out_at'),
            'transactionItems' => fn ($q) => $q->where('status', 'borrowed')->with('transaction'),
        ])->orderBy('department')->orderBy('category')
          ->get();

        // ── Overdue Transactions ───────────────────────────────────────────
        $overdueTransactions = Transaction::overdue()
            ->with(['room', 'tool', 'items.tool'])
            ->latest('checked_out_at')
            ->get();

        // ── Stale Room Transactions (Unclosed from past days) ──────────────
        $staleRoomTransactions = Transaction::stale()
            ->rooms()
            ->with(['room'])
            ->latest('checked_out_at')
            ->get();
        $staleRoomCount = $staleRoomTransactions->count();

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
            'roomUtilizationRate',
            'allRooms',
            'vacantRooms',
            'floors',
            'openToolCount',
            'overdueToolCount',
            'todayToolCount',
            'totalTools',
            'availableToolsCount',
            'deptActive',
            'occupiedRooms',
            'borrowedTools',
            'overdueTransactions',
            'staleRoomTransactions',
            'staleRoomCount',
            'recentRoomTransactions',
            'recentToolTransactions',
            'recentTransactions'
        ));
    }
}
