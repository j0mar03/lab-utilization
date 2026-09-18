<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Room;
use App\Models\Subject;
use App\Models\Tool;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ReportController
 *
 * Aggregates laboratory, facility, and tool utilization data for analytics & academic audits.
 * Supports multi-dimensional filtering by Department, Room, Faculty, Subject, and Date Range.
 * Generates audit-ready cross-matrices for institutional compliance (OPCR, CHED, ISO).
 *
 * Accessible to: lab_head only.
 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->gatherAnalytics($request);
        return view('admin.reports.index', $data);
    }

    public function audit(Request $request): View
    {
        $data = $this->gatherAnalytics($request);
        return view('admin.reports.audit', $data);
    }

    /**
     * Centralized analytics aggregation supporting interactive filters and audit export.
     */
    private function gatherAnalytics(Request $request): array
    {
        // ── 1. Parse Filters & Date Presets ──────────────────────────────────
        $preset           = $request->input('preset');
        $dateFrom         = $request->input('date_from');
        $dateTo           = $request->input('date_to');
        $departmentFilter = $request->input('department');
        $roomIdFilter     = $request->input('room_id') ? (int) $request->input('room_id') : null;
        $borrowerFilter   = $request->input('borrower');
        $subjectFilter    = $request->input('subject');

        if ($preset && empty($dateFrom) && empty($dateTo)) {
            $now = now();
            match ($preset) {
                'today' => [
                    $dateFrom = $now->copy()->startOfDay()->format('Y-m-d'),
                    $dateTo   = $now->copy()->endOfDay()->format('Y-m-d'),
                ],
                'this_week' => [
                    $dateFrom = $now->copy()->startOfWeek()->format('Y-m-d'),
                    $dateTo   = $now->copy()->endOfWeek()->format('Y-m-d'),
                ],
                'this_month' => [
                    $dateFrom = $now->copy()->startOfMonth()->format('Y-m-d'),
                    $dateTo   = $now->copy()->endOfMonth()->format('Y-m-d'),
                ],
                'semester_1' => [
                    $year     = $now->month >= 8 ? $now->year : $now->year - 1,
                    $dateFrom = "{$year}-08-01",
                    $dateTo   = "{$year}-12-31",
                ],
                'semester_2' => [
                    $year     = $now->month <= 7 ? $now->year : $now->year + 1,
                    $dateFrom = "{$year}-01-01",
                    $dateTo   = "{$year}-06-30",
                ],
                default => null,
            };
        }

        // Helper to resolve department search terms
        $resolveDeptVariants = function (?string $dept) {
            if (! $dept) return [];
            return match (strtoupper($dept)) {
                'DECET' => ['DECET', 'Department of Computer and Electronics Engineering Technology', 'Computer and Electronics'],
                'DOMIT' => ['DOMIT', 'Department of Office Management and Information Technology', 'Office Management and Information Technology'],
                'DEMET' => ['DEMET', 'Department of Electrical and Mechanical Engineering Technology', 'Electrical and Mechanical'],
                default => [$dept],
            };
        };

        // ── 2. Filtered Queries ──────────────────────────────────────────────
        $roomTxQuery = Transaction::rooms()->with(['room', 'user']);
        $toolTxQuery = Transaction::tools()->with(['tool', 'items.tool', 'user']);

        if ($departmentFilter) {
            $variants = $resolveDeptVariants($departmentFilter);
            $roomTxQuery->where(function ($q) use ($variants) {
                foreach ($variants as $v) {
                    $q->orWhere('department', 'like', "%{$v}%");
                }
            });
            $toolTxQuery->where(function ($q) use ($variants) {
                foreach ($variants as $v) {
                    $q->orWhere('department', 'like', "%{$v}%");
                }
            });
        }

        if ($roomIdFilter) {
            $roomTxQuery->where('room_id', $roomIdFilter);
        }

        if ($borrowerFilter) {
            $roomTxQuery->where('borrower_name', 'like', "%{$borrowerFilter}%");
            $toolTxQuery->where('borrower_name', 'like', "%{$borrowerFilter}%");
        }

        if ($subjectFilter) {
            $roomTxQuery->where('subject', 'like', "%{$subjectFilter}%");
            $toolTxQuery->where('subject', 'like', "%{$subjectFilter}%");
        }

        if ($dateFrom) {
            $roomTxQuery->whereDate('checked_out_at', '>=', $dateFrom);
            $toolTxQuery->whereDate('checked_out_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $roomTxQuery->whereDate('checked_out_at', '<=', $dateTo);
            $toolTxQuery->whereDate('checked_out_at', '<=', $dateTo);
        }

        $filteredRoomTransactions = $roomTxQuery->get();
        $filteredToolTransactions = $toolTxQuery->get();

        // Minutes calculation helper
        $calcMinutes = function ($tx) {
            $end = $tx->returned_at ?: ($tx->status === 'open' ? now() : $tx->expected_return_at);
            if ($end && $tx->checked_out_at) {
                return max(0, $tx->checked_out_at->diffInMinutes($end));
            }
            return 0;
        };

        // ── 3. Room Utilization Specifics (Filtered) ─────────────────────────
        $totalRoomTransactions = $filteredRoomTransactions->count();
        $returnedRoomCount     = $filteredRoomTransactions->where('status', 'returned')->count();

        $allRoomMinutes = 0;
        foreach ($filteredRoomTransactions as $rtx) {
            $allRoomMinutes += $calcMinutes($rtx);
        }
        $totalRoomHours = round($allRoomMinutes / 60, 1);
        $avgRoomDurationHours = $totalRoomTransactions > 0 ? round(($allRoomMinutes / $totalRoomTransactions) / 60, 1) : 0;

        // ── 4. Tool Utilization Specifics (Filtered) ─────────────────────────
        $totalToolTransactions = $filteredToolTransactions->count();
        $returnedToolCount     = $filteredToolTransactions->where('status', 'returned')->count();

        $totalToolUnitsBorrowed = 0;
        foreach ($filteredToolTransactions as $ttx) {
            $totalToolUnitsBorrowed += $ttx->total_quantity_borrowed;
        }

        // ── 5. Room × Student Department Audit Matrix ────────────────────────
        $roomTransactionsByRoom = $filteredRoomTransactions->groupBy('room_id');

        $allRoomsQuery = Room::orderByRaw("
            CASE 
                WHEN department LIKE '%Computer%' THEN 1
                WHEN department LIKE '%Office%' THEN 2
                WHEN department LIKE '%Electrical%' THEN 3
                ELSE 4
            END,
            CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END,
            name
        ");

        if ($roomIdFilter) {
            $allRoomsQuery->where('id', $roomIdFilter);
        }
        $roomsList = $allRoomsQuery->get();

        $roomAuditMatrix = [];
        foreach ($roomsList as $room) {
            $txs = $roomTransactionsByRoom->get($room->id, collect());
            $sessionCount = $txs->count();

            $totalMinutes = 0;
            $deptMinutes = [
                'DECET' => 0,
                'DOMIT' => 0,
                'DEMET' => 0,
                'OTHER' => 0,
            ];
            $subjectsList = [];
            $softwareList = [];

            foreach ($txs as $tx) {
                $mins = $calcMinutes($tx);
                $totalMinutes += $mins;

                $dShort = $tx->departmentShort() ?: 'OTHER';
                if (isset($deptMinutes[$dShort])) {
                    $deptMinutes[$dShort] += $mins;
                } else {
                    $deptMinutes['OTHER'] += $mins;
                }

                if (! empty($tx->subject)) {
                    $subjectsList[$tx->subject] = ($subjectsList[$tx->subject] ?? 0) + 1;
                }

                if ($tx->hasSoftwareUtilized()) {
                    foreach ((array) $tx->software_utilized as $sw) {
                        $softwareList[$sw] = ($softwareList[$sw] ?? 0) + 1;
                    }
                }
            }

            arsort($subjectsList);
            arsort($softwareList);

            $totalHours = round($totalMinutes / 60, 1);
            $decetHours = round($deptMinutes['DECET'] / 60, 1);
            $domitHours = round($deptMinutes['DOMIT'] / 60, 1);
            $demetHours = round($deptMinutes['DEMET'] / 60, 1);
            $otherHours = round($deptMinutes['OTHER'] / 60, 1);

            $roomAuditMatrix[] = [
                'room'          => $room,
                'sessions'      => $sessionCount,
                'total_hours'   => $totalHours,
                'decet_hours'   => $decetHours,
                'decet_pct'     => $totalHours > 0 ? round(($decetHours / $totalHours) * 100) : 0,
                'domit_hours'   => $domitHours,
                'domit_pct'     => $totalHours > 0 ? round(($domitHours / $totalHours) * 100) : 0,
                'demet_hours'   => $demetHours,
                'demet_pct'     => $totalHours > 0 ? round(($demetHours / $totalHours) * 100) : 0,
                'other_hours'   => $otherHours,
                'other_pct'     => $totalHours > 0 ? round(($otherHours / $totalHours) * 100) : 0,
                'top_subjects'  => array_slice(array_keys($subjectsList), 0, 3),
                'software_used' => array_keys($softwareList),
            ];
        }

        // Sort room matrix by total hours descending, then name
        usort($roomAuditMatrix, function ($a, $b) {
            if ($a['total_hours'] === $b['total_hours']) {
                return strcmp($a['room']->name, $b['room']->name);
            }
            return $b['total_hours'] <=> $a['total_hours'];
        });

        // ── 6. Faculty Utilization Breakdown ─────────────────────────────────
        $facultyGroups = $filteredRoomTransactions->groupBy('borrower_name');
        $facultyAudit = [];

        foreach ($facultyGroups as $borrowerName => $txs) {
            $totalMins = 0;
            $roomsUsed = [];
            $subjectsTaught = [];
            $softwareUsed = [];
            $primaryDept = null;

            foreach ($txs as $tx) {
                $mins = $calcMinutes($tx);
                $totalMins += $mins;

                if ($tx->room) {
                    $roomsUsed[$tx->room->name] = ($roomsUsed[$tx->room->name] ?? 0) + 1;
                }
                if ($tx->subject) {
                    $subjectsTaught[$tx->subject] = ($subjectsTaught[$tx->subject] ?? 0) + 1;
                }
                if ($tx->hasSoftwareUtilized()) {
                    foreach ((array) $tx->software_utilized as $sw) {
                        $softwareUsed[$sw] = ($softwareUsed[$sw] ?? 0) + 1;
                    }
                }
                if (! $primaryDept && $tx->departmentShort()) {
                    $primaryDept = $tx->departmentShort();
                }
            }

            arsort($roomsUsed);
            arsort($subjectsTaught);
            arsort($softwareUsed);

            $facultyAudit[] = [
                'name'         => $borrowerName,
                'department'   => $primaryDept ?: 'General',
                'sessions'     => $txs->count(),
                'total_hours'  => round($totalMins / 60, 1),
                'avg_duration' => round(($totalMins / max(1, $txs->count())) / 60, 1),
                'rooms'        => array_keys($roomsUsed),
                'subjects'     => array_keys($subjectsTaught),
                'software'     => array_keys($softwareUsed),
            ];
        }
        usort($facultyAudit, fn ($a, $b) => $b['total_hours'] <=> $a['total_hours']);

        // ── 7. Curriculum Subject Utilization Breakdown ──────────────────────
        $subjectGroups = $filteredRoomTransactions->groupBy(function ($tx) {
            return trim($tx->subject ?: 'Unspecified Subject');
        });
        $subjectAudit = [];

        foreach ($subjectGroups as $subjectName => $txs) {
            $totalMins = 0;
            $roomsUsed = [];
            $facultyList = [];
            $softwareUsed = [];
            $dept = null;

            foreach ($txs as $tx) {
                $mins = $calcMinutes($tx);
                $totalMins += $mins;

                if ($tx->room) {
                    $roomsUsed[$tx->room->name] = ($roomsUsed[$tx->room->name] ?? 0) + 1;
                }
                if ($tx->borrower_name) {
                    $facultyList[$tx->borrower_name] = ($facultyList[$tx->borrower_name] ?? 0) + 1;
                }
                if ($tx->hasSoftwareUtilized()) {
                    foreach ((array) $tx->software_utilized as $sw) {
                        $softwareUsed[$sw] = ($softwareUsed[$sw] ?? 0) + 1;
                    }
                }
                if (! $dept && $tx->departmentShort()) {
                    $dept = $tx->departmentShort();
                }
            }

            arsort($roomsUsed);
            arsort($facultyList);
            arsort($softwareUsed);

            $subjectAudit[] = [
                'subject'     => $subjectName,
                'department'  => $dept ?: 'General',
                'sessions'    => $txs->count(),
                'total_hours' => round($totalMins / 60, 1),
                'rooms'       => array_keys($roomsUsed),
                'faculties'   => array_keys($facultyList),
                'software'    => array_keys($softwareUsed),
            ];
        }
        usort($subjectAudit, fn ($a, $b) => $b['total_hours'] <=> $a['total_hours']);

        // ── 8. Software & Applications Utilization (Filtered) ────────────────
        $totalSoftwareRoomSessions = 0;
        $softwareCounts = [];
        $softwareDeptCounts = [];

        foreach ($filteredRoomTransactions as $stx) {
            if ($stx->hasSoftwareUtilized()) {
                $totalSoftwareRoomSessions++;
                $deptShort = $stx->departmentShort() ?: 'General';
                foreach ((array) $stx->software_utilized as $suite) {
                    $suite = trim($suite);
                    if ($suite === '') continue;
                    $softwareCounts[$suite] = ($softwareCounts[$suite] ?? 0) + 1;
                    $softwareDeptCounts[$suite][$deptShort] = ($softwareDeptCounts[$suite][$deptShort] ?? 0) + 1;
                }
            }
        }

        arsort($softwareCounts);
        $softwareChartLabels = array_keys($softwareCounts);
        $softwareChartData   = array_values($softwareCounts);

        $softwareAdoptionRate = $totalRoomTransactions > 0
            ? round(($totalSoftwareRoomSessions / $totalRoomTransactions) * 100, 1)
            : 0;

        $softwareCatalog = Transaction::SOFTWARE_CATALOG;

        // ── 9. Department Utilization Comparison (DECET, DOMIT, DEMET) ───────
        $allDeptNames = ['DECET', 'DOMIT', 'DEMET'];
        $deptStats = [];

        foreach ($allDeptNames as $short) {
            $fullName = match ($short) {
                'DECET' => 'Department of Computer and Electronics Engineering Technology',
                'DOMIT' => 'Department of Office Management and Information Technology',
                'DEMET' => 'Department of Electrical and Mechanical Engineering Technology',
            };

            $rTx = $filteredRoomTransactions->filter(fn ($t) => $t->departmentShort() === $short || str_contains($t->department ?? '', $short));
            $tTx = $filteredToolTransactions->filter(fn ($t) => $t->departmentShort() === $short || str_contains($t->department ?? '', $short));

            $rCount = $rTx->count();
            $tCount = $tTx->count();

            $tUnits = 0;
            foreach ($tTx as $tx) {
                $tUnits += $tx->total_quantity_borrowed;
            }

            $rMins = 0;
            foreach ($rTx as $tx) {
                $rMins += $calcMinutes($tx);
            }
            $rHours = round($rMins / 60, 1);

            $deptStats[] = [
                'name'           => $fullName,
                'short'          => $short,
                'room_count'     => $rCount,
                'room_hours'     => $rHours,
                'tool_count'     => $tCount,
                'tool_units'     => $tUnits,
                'total_activity' => $rCount + $tCount,
            ];
        }

        usort($deptStats, fn ($a, $b) => $b['total_activity'] <=> $a['total_activity']);

        $deptChartLabels = array_column($deptStats, 'short');
        $deptChartRooms  = array_column($deptStats, 'room_count');
        $deptChartTools  = array_column($deptStats, 'tool_count');
        $deptChartHours  = array_column($deptStats, 'room_hours');
        $deptChartUnits  = array_column($deptStats, 'tool_units');

        // ── 10. Top Rooms & Top Tools ─────────────────────────────────────────
        $topRoomsGrouped = $filteredRoomTransactions->groupBy('room_id');
        $topRoomsData = [];
        foreach ($topRoomsGrouped as $rid => $txs) {
            $r = Room::find($rid);
            if ($r) {
                $topRoomsData[$r->name] = $txs->count();
            }
        }
        arsort($topRoomsData);
        $topRoomLabels = array_slice(array_keys($topRoomsData), 0, 8);
        $topRoomData   = array_slice(array_values($topRoomsData), 0, 8);

        // Average duration by room
        $avgRoomLabels = [];
        $avgRoomData   = [];
        $roomDurations = [];
        foreach ($topRoomsGrouped as $rid => $txs) {
            $r = Room::find($rid);
            if ($r && $txs->isNotEmpty()) {
                $sumMins = 0;
                foreach ($txs as $tx) {
                    $sumMins += $calcMinutes($tx);
                }
                $roomDurations[$r->name] = round(($sumMins / $txs->count()) / 60, 1);
            }
        }
        arsort($roomDurations);
        $avgRoomLabels = array_slice(array_keys($roomDurations), 0, 8);
        $avgRoomData   = array_slice(array_values($roomDurations), 0, 8);

        // Top Tools
        $topToolCounts = [];
        foreach ($filteredToolTransactions as $ttx) {
            if ($ttx->items->isNotEmpty()) {
                foreach ($ttx->items as $item) {
                    $tname = $item->tool?->name ?? 'Tool';
                    $topToolCounts[$tname] = ($topToolCounts[$tname] ?? 0) + $item->quantity_borrowed;
                }
            } elseif ($ttx->tool) {
                $topToolCounts[$ttx->tool->name] = ($topToolCounts[$ttx->tool->name] ?? 0) + $ttx->quantity;
            }
        }
        arsort($topToolCounts);
        $topToolLabels = array_slice(array_keys($topToolCounts), 0, 8);
        $topToolData   = array_slice(array_values($topToolCounts), 0, 8);

        // Equipment Categories
        $toolCategories = [];
        foreach ($filteredToolTransactions as $ttx) {
            $cat = $ttx->tool?->category ?? 'General Equipment';
            $toolCategories[$cat] = ($toolCategories[$cat] ?? 0) + 1;
        }
        arsort($toolCategories);
        $toolCatLabels = array_keys($toolCategories);
        $toolCatData   = array_values($toolCategories);

        // ── 11. Daily Timeline (Last 14 Days) ────────────────────────────────
        $days = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->format('Y-m-d'));

        $dailyRoomCounts = [];
        $dailyToolCounts = [];
        foreach ($days as $day) {
            $dailyRoomCounts[$day] = $filteredRoomTransactions->filter(fn ($t) => $t->checked_out_at && $t->checked_out_at->format('Y-m-d') === $day)->count();
            $dailyToolCounts[$day] = $filteredToolTransactions->filter(fn ($t) => $t->checked_out_at && $t->checked_out_at->format('Y-m-d') === $day)->count();
        }

        $dailyLabels   = $days->map(fn ($d) => date('M d', strtotime($d)))->values();
        $dailyRoomData = array_values($dailyRoomCounts);
        $dailyToolData = array_values($dailyToolCounts);

        // Day of Week
        $dowRoomData = [];
        $dowToolData = [];
        for ($i = 1; $i <= 7; $i++) {
            $dowRoomData[$i] = $filteredRoomTransactions->filter(fn ($t) => $t->checked_out_at && $t->checked_out_at->dayOfWeekIso === $i)->count();
            $dowToolData[$i] = $filteredToolTransactions->filter(fn ($t) => $t->checked_out_at && $t->checked_out_at->dayOfWeekIso === $i)->count();
        }
        $dowLabels     = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $dowRoomValues = array_values($dowRoomData);
        $dowToolValues = array_values($dowToolData);

        // Total combined activity
        $totalTransactions = $totalRoomTransactions + $totalToolTransactions;
        $totalReturned     = $returnedRoomCount + $returnedToolCount;

        // ── 12. Filter Dropdown Options ──────────────────────────────────────
        $departmentsDropdown = ['DECET', 'DOMIT', 'DEMET'];
        $roomsDropdown       = Room::orderByRaw("CASE WHEN name LIKE 'LAB%' THEN 0 ELSE 1 END, name")->get();
        $facultiesDropdown   = Faculty::where('is_active', true)->orderBy('name')->get();
        $subjectsDropdown    = Subject::where('is_active', true)->orderBy('code')->get();

        $activeFilters = [
            'department' => $departmentFilter,
            'room_id'    => $roomIdFilter,
            'borrower'   => $borrowerFilter,
            'subject'    => $subjectFilter,
            'date_from'  => $dateFrom,
            'date_to'    => $dateTo,
            'preset'     => $preset,
        ];

        return compact(
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
            'totalSoftwareRoomSessions',
            'softwareCounts',
            'softwareDeptCounts',
            'softwareChartLabels',
            'softwareChartData',
            'softwareAdoptionRate',
            'softwareCatalog',
            'totalTransactions',
            'totalReturned',
            'roomAuditMatrix',
            'facultyAudit',
            'subjectAudit',
            'departmentsDropdown',
            'roomsDropdown',
            'facultiesDropdown',
            'subjectsDropdown',
            'activeFilters'
        );
    }
}
