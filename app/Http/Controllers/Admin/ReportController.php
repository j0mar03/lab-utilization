<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Tool;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ReportController
 *
 * Aggregates utilization data for Chart.js charts on the Reports page.
 * Accessible to: lab_head only.
 *
 * All data is passed as JSON to Blade via @json() for Chart.js consumption.
 * No heavy queue workers needed — queries are fast on typical lab data volumes.
 */
class ReportController extends Controller
{
    public function index(): View
    {
        // ── Chart 1: Checkouts per day (last 14 days) ─────────────────────
        $days   = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->format('Y-m-d'));
        $daily  = Transaction::selectRaw('DATE(checked_out_at) as date, COUNT(*) as count')
            ->where('checked_out_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $dailyLabels = $days->map(fn ($d) => date('M d', strtotime($d)))->values();
        $dailyData   = $days->map(fn ($d) => $daily->get($d, 0))->values();

        // ── Chart 2: Checkouts by day of week ─────────────────────────────
        // MySQL DAYOFWEEK: 1=Sunday, 2=Monday ... 7=Saturday
        $dowData = Transaction::selectRaw('DAYOFWEEK(checked_out_at) as dow, COUNT(*) as count')
            ->groupBy('dow')
            ->pluck('count', 'dow');

        $dowLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dowValues = collect(range(1, 7))->map(fn ($d) => $dowData->get($d, 0))->values();

        // ── Chart 3: Top 8 rooms by checkout count ────────────────────────
        $topRooms = Transaction::whereNotNull('room_id')
            ->select('room_id', DB::raw('COUNT(*) as count'))
            ->groupBy('room_id')
            ->orderByDesc('count')
            ->limit(8)
            ->with('room')
            ->get();

        $topRoomLabels = $topRooms->map(fn ($t) => $t->room?->name ?? '?')->values();
        $topRoomData   = $topRooms->pluck('count')->values();

        // ── Chart 4: Top 8 tools by checkout count ────────────────────────
        $topTools = Transaction::whereNotNull('tool_id')
            ->select('tool_id', DB::raw('COUNT(*) as count'))
            ->groupBy('tool_id')
            ->orderByDesc('count')
            ->limit(8)
            ->with('tool')
            ->get();

        $topToolLabels = $topTools->map(fn ($t) => $t->tool?->name ?? '?')->values();
        $topToolData   = $topTools->pluck('count')->values();

        // ── Chart 5: Room vs Tool checkout split (doughnut) ───────────────
        $roomOnlyCount = Transaction::whereNotNull('room_id')->whereNull('tool_id')->count();
        $toolOnlyCount = Transaction::whereNull('room_id')->whereNotNull('tool_id')->count();
        $bothCount     = Transaction::whereNotNull('room_id')->whereNotNull('tool_id')->count();

        // ── Summary stats ─────────────────────────────────────────────────
        $totalTransactions = Transaction::count();
        $totalReturned     = Transaction::returned()->count();

        // Average checkout duration for returned room transactions (in hours)
        $avgDuration = Transaction::returned()
            ->whereNotNull('room_id')
            ->whereNotNull('returned_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, checked_out_at, returned_at)) as avg_minutes')
            ->value('avg_minutes');

        $avgDurationHours = $avgDuration ? round($avgDuration / 60, 1) : 0;

        // ── Average duration by room (for returned room transactions) ─────
        $avgByRoom = Transaction::returned()
            ->whereNotNull('room_id')
            ->whereNotNull('returned_at')
            ->select('room_id', DB::raw('AVG(TIMESTAMPDIFF(MINUTE, checked_out_at, returned_at)) as avg_minutes'))
            ->groupBy('room_id')
            ->orderByDesc('avg_minutes')
            ->limit(8)
            ->with('room')
            ->get();

        $avgRoomLabels = $avgByRoom->map(fn ($r) => $r->room?->name ?? '?')->values();
        $avgRoomData   = $avgByRoom->map(fn ($r) => round($r->avg_minutes / 60, 1))->values();

        return view('admin.reports.index', compact(
            'dailyLabels', 'dailyData',
            'dowLabels', 'dowValues',
            'topRoomLabels', 'topRoomData',
            'topToolLabels', 'topToolData',
            'roomOnlyCount', 'toolOnlyCount', 'bothCount',
            'totalTransactions', 'totalReturned', 'avgDurationHours',
            'avgRoomLabels', 'avgRoomData',
        ));
    }
}
