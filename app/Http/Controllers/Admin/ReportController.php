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
 * Aggregates laboratory and tool utilization data for analytics & research.
 * Separates Room Utilization from Tool Utilization and breaks down metrics by Department
 * (DOMIT, DCEET, DEMET, etc.).
 *
 * Accessible to: lab_head only.
 */
class ReportController extends Controller
{
    public function index(): View
    {
        // ── 1. Department Breakdown ────────────────────────────────────────
        $allDeptNames = Transaction::whereNotNull('department')->distinct()->pluck('department')
            ->merge(Tool::whereNotNull('department')->distinct()->pluck('department'))
            ->merge(collect(Tool::DEPARTMENTS))
            ->unique()
            ->filter();

        $deptStats = [];
        foreach ($allDeptNames as $dept) {
            $short = match($dept) {
                'Department of Office Management and Information Technology' => 'DOMIT',
                'Department of Computer and Electronics Engineering Technology' => 'DCEET',
                'Department of Electrical and Mechanical Engineering Technology' => 'DEMET',
                'Department of Civil and Railway Engineering Technology' => 'DCRET',
                'College of Science' => 'CS',
                default => substr($dept, 0, 15),
            };

            $roomTx = Transaction::rooms()->where('department', $dept)->get();
            $toolTx = Transaction::tools()->where('department', $dept)->with('items')->get();

            $roomCount = $roomTx->count();
            $toolCount = $toolTx->count();

            // Total tool physical units
            $toolUnits = 0;
            foreach ($toolTx as $tx) {
                $toolUnits += $tx->total_quantity_borrowed;
            }

            // Room duration in hours
            $roomMinutes = 0;
            foreach ($roomTx as $tx) {
                $end = $tx->returned_at ?: ($tx->status === 'open' ? now() : $tx->expected_return_at);
                if ($end && $tx->checked_out_at) {
                    $roomMinutes += max(0, $tx->checked_out_at->diffInMinutes($end));
                }
            }
            $roomHours = round($roomMinutes / 60, 1);

            $deptStats[] = [
                'name'           => $dept,
                'short'          => $short,
                'room_count'     => $roomCount,
                'room_hours'     => $roomHours,
                'tool_count'     => $toolCount,
                'tool_units'     => $toolUnits,
                'total_activity' => $roomCount + $toolCount,
            ];
        }

        // Sort departments by total activity descending
        usort($deptStats, fn($a, $b) => $b['total_activity'] <=> $a['total_activity']);

        $deptChartLabels = array_column($deptStats, 'short');
        $deptChartRooms  = array_column($deptStats, 'room_count');
        $deptChartTools  = array_column($deptStats, 'tool_count');
        $deptChartHours  = array_column($deptStats, 'room_hours');
        $deptChartUnits  = array_column($deptStats, 'tool_units');

        // ── 2. Timeline: Checkouts per day (last 14 days) ──────────────────
        $days = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->format('Y-m-d'));

        $dailyRooms = Transaction::rooms()
            ->selectRaw('DATE(checked_out_at) as date, COUNT(*) as count')
            ->where('checked_out_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $dailyTools = Transaction::tools()
            ->selectRaw('DATE(checked_out_at) as date, COUNT(*) as count')
            ->where('checked_out_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $dailyLabels   = $days->map(fn ($d) => date('M d', strtotime($d)))->values();
        $dailyRoomData = $days->map(fn ($d) => $dailyRooms->get($d, 0))->values();
        $dailyToolData = $days->map(fn ($d) => $dailyTools->get($d, 0))->values();

        // ── 3. Checkouts by day of week ────────────────────────────────────
        $dowRoomData = Transaction::rooms()
            ->selectRaw('DAYOFWEEK(checked_out_at) as dow, COUNT(*) as count')
            ->groupBy('dow')
            ->pluck('count', 'dow');

        $dowToolData = Transaction::tools()
            ->selectRaw('DAYOFWEEK(checked_out_at) as dow, COUNT(*) as count')
            ->groupBy('dow')
            ->pluck('count', 'dow');

        $dowLabels     = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $dowRoomValues = collect(range(1, 7))->map(fn ($d) => $dowRoomData->get($d, 0))->values();
        $dowToolValues = collect(range(1, 7))->map(fn ($d) => $dowToolData->get($d, 0))->values();

        // ── 4. Room Utilization Specifics ──────────────────────────────────
        $totalRoomTransactions = Transaction::rooms()->count();
        $returnedRoomCount     = Transaction::rooms()->returned()->count();

        // Total room hours across all logged transactions
        $allRoomMinutes = 0;
        foreach (Transaction::rooms()->get() as $rtx) {
            $end = $rtx->returned_at ?: ($rtx->status === 'open' ? now() : $rtx->expected_return_at);
            if ($end && $rtx->checked_out_at) {
                $allRoomMinutes += max(0, $rtx->checked_out_at->diffInMinutes($end));
            }
        }
        $totalRoomHours = round($allRoomMinutes / 60, 1);

        // Average room checkout duration (hours)
        $avgRoomDuration = Transaction::rooms()
            ->returned()
            ->whereNotNull('returned_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, checked_out_at, returned_at)) as avg_minutes')
            ->value('avg_minutes');
        $avgRoomDurationHours = $avgRoomDuration ? round($avgRoomDuration / 60, 1) : 0;

        // Top 8 rooms by checkout count
        $topRooms = Transaction::rooms()
            ->select('room_id', DB::raw('COUNT(*) as count'))
            ->groupBy('room_id')
            ->orderByDesc('count')
            ->limit(8)
            ->with('room')
            ->get();

        $topRoomLabels = $topRooms->map(fn ($t) => $t->room?->name ?? '?')->values();
        $topRoomData   = $topRooms->pluck('count')->values();

        // Average duration by room
        $avgByRoom = Transaction::rooms()
            ->returned()
            ->whereNotNull('returned_at')
            ->select('room_id', DB::raw('AVG(TIMESTAMPDIFF(MINUTE, checked_out_at, returned_at)) as avg_minutes'))
            ->groupBy('room_id')
            ->orderByDesc('avg_minutes')
            ->limit(8)
            ->with('room')
            ->get();

        $avgRoomLabels = $avgByRoom->map(fn ($r) => $r->room?->name ?? '?')->values();
        $avgRoomData   = $avgByRoom->map(fn ($r) => round($r->avg_minutes / 60, 1))->values();

        // ── 5. Tool Utilization Specifics ──────────────────────────────────
        $totalToolTransactions = Transaction::tools()->count();
        $returnedToolCount     = Transaction::tools()->returned()->count();

        // Total physical units borrowed
        $itemToolCounts = DB::table('transaction_items')
            ->select('tool_id', DB::raw('SUM(quantity_borrowed) as total_qty'))
            ->groupBy('tool_id')
            ->pluck('total_qty', 'tool_id');

        $directToolCounts = DB::table('transactions')
            ->whereNotNull('tool_id')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('transaction_items')->whereColumn('transaction_items.transaction_id', 'transactions.id');
            })
            ->select('tool_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('tool_id')
            ->pluck('total_qty', 'tool_id');

        $combinedToolCounts = [];
        foreach ($itemToolCounts as $tid => $qty) {
            $combinedToolCounts[$tid] = ($combinedToolCounts[$tid] ?? 0) + (int) $qty;
        }
        foreach ($directToolCounts as $tid => $qty) {
            $combinedToolCounts[$tid] = ($combinedToolCounts[$tid] ?? 0) + (int) $qty;
        }

        $totalToolUnitsBorrowed = array_sum($combinedToolCounts);

        // Top 8 tools by units borrowed
        arsort($combinedToolCounts);
        $topToolIds  = array_slice(array_keys($combinedToolCounts), 0, 8);
        $toolsMap    = Tool::whereIn('id', $topToolIds)->get()->keyBy('id');

        $topToolLabels = [];
        $topToolData   = [];
        foreach ($topToolIds as $tid) {
            if (isset($toolsMap[$tid])) {
                $topToolLabels[] = $toolsMap[$tid]->name;
                $topToolData[]   = (int) $combinedToolCounts[$tid];
            }
        }

        // Tool categories breakdown
        $toolCategories = DB::table('tools')
            ->join('transactions', 'tools.id', '=', 'transactions.tool_id')
            ->select('tools.category', DB::raw('COUNT(*) as count'))
            ->groupBy('tools.category')
            ->orderByDesc('count')
            ->pluck('count', 'category');

        $toolCatLabels = $toolCategories->keys()->values();
        $toolCatData   = $toolCategories->values()->values();

        // Overall totals for top stat row
        $totalTransactions = Transaction::count();
        $totalReturned     = Transaction::returned()->count();

        return view('admin.reports.index', compact(
            'deptStats',
            'deptChartLabels',
            'deptChartRooms',
            'deptChartTools',
            'deptChartHours',
            'deptChartUnits',
            'dailyLabels',
            'dailyRoomData',
            'dailyToolData',
            'dowLabels',
            'dowRoomValues',
            'dowToolValues',
            'totalRoomTransactions',
            'returnedRoomCount',
            'totalRoomHours',
            'avgRoomDurationHours',
            'topRoomLabels',
            'topRoomData',
            'avgRoomLabels',
            'avgRoomData',
            'totalToolTransactions',
            'returnedToolCount',
            'totalToolUnitsBorrowed',
            'topToolLabels',
            'topToolData',
            'toolCatLabels',
            'toolCatData',
            'totalTransactions',
            'totalReturned'
        ));
    }
}
