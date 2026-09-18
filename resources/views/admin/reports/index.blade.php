<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    📈 Utilization & Department Analytics Reports
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Separated room occupancy, tool circulation metrics, and departmental usage comparisons
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.transactions.export') }}"
                   class="inline-flex items-center gap-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                    <span>⬇️</span>
                    <span>Export All CSV</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ── High-Level Separated Summary Stats ─────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Room Utilization Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-blue-100 dark:border-blue-900/40 shadow-sm p-5 relative overflow-hidden">
                    <div class="absolute top-0 left-0 h-1 w-full bg-blue-600"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                            🏫 Room Utilization
                        </span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                            Facilities
                        </span>
                    </div>
                    <div class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-2">
                        {{ number_format($totalRoomTransactions) }}
                        <span class="text-xs font-normal text-gray-400">sessions</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 flex items-center justify-between">
                        <span><strong>{{ $totalRoomHours }}</strong> total hours</span>
                        <span>Avg: <strong>{{ $avgRoomDurationHours }}h</strong></span>
                    </div>
                </div>

                {{-- Tool Borrowing Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-emerald-100 dark:border-emerald-900/40 shadow-sm p-5 relative overflow-hidden">
                    <div class="absolute top-0 left-0 h-1 w-full bg-emerald-600"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                            🔧 Equipment Circulation
                        </span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">
                            Tools
                        </span>
                    </div>
                    <div class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-2">
                        {{ number_format($totalToolTransactions) }}
                        <span class="text-xs font-normal text-gray-400">checkouts</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 flex items-center justify-between">
                        <span><strong>{{ $totalToolUnitsBorrowed }}</strong> units out</span>
                        <span><strong>{{ $returnedToolCount }}</strong> returned</span>
                    </div>
                </div>

                {{-- Software Utilization Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-purple-100 dark:border-purple-900/40 shadow-sm p-5 relative overflow-hidden">
                    <div class="absolute top-0 left-0 h-1 w-full bg-purple-600"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                            💻 Software Utilized
                        </span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">
                            Comp Labs
                        </span>
                    </div>
                    <div class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-2">
                        {{ number_format($totalSoftwareRoomSessions) }}
                        <span class="text-xs font-normal text-gray-400">sessions</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 flex items-center justify-between">
                        <span><strong>{{ $softwareAdoptionRate }}%</strong> lab adoption</span>
                        <span><strong>{{ count($softwareCounts) }}</strong> suites logged</span>
                    </div>
                </div>

                {{-- System Overall Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-indigo-100 dark:border-indigo-900/40 shadow-sm p-5 relative overflow-hidden">
                    <div class="absolute top-0 left-0 h-1 w-full bg-indigo-600"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                            📊 Total Activity
                        </span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300">
                            Combined
                        </span>
                    </div>
                    <div class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-2">
                        {{ number_format($totalTransactions) }}
                        <span class="text-xs font-normal text-gray-400">total</span>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400 flex items-center justify-between">
                        <span><strong>{{ $totalReturned }}</strong> returns ({{ $totalTransactions > 0 ? round(($totalReturned / $totalTransactions) * 100, 1) : 0 }}%)</span>
                        <span>Active: <strong>{{ $totalTransactions - $totalReturned }}</strong></span>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 1: DEPARTMENT UTILIZATION BREAKDOWN                     --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span>🏢</span>
                        <span>Departmental Utilization Analysis</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Detailed breakdown comparing facility and equipment utilization across academic departments
                    </p>
                </div>

                {{-- Department Table & Comparison Chart --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    {{-- Breakdown Table (7 cols) --}}
                    <div class="lg:col-span-7 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-750">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                Department Summary Table
                            </h4>
                            <span class="text-xs text-gray-400">Sorted by Total Activity</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Department</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Room Sessions</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Room Hours</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase">Tool Borrows</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase">Tool Units</th>
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Total Activity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                    @forelse ($deptStats as $stat)
                                        @if ($stat['total_activity'] > 0 || in_array($stat['name'], \App\Models\Tool::DEPARTMENTS))
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750/50 transition">
                                                <td class="px-4 py-3.5">
                                                    <div class="font-bold text-gray-900 dark:text-gray-100 text-xs sm:text-sm">
                                                        {{ $stat['short'] }}
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-[220px]">
                                                        {{ $stat['name'] }}
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3.5 text-center font-semibold text-blue-600 dark:text-blue-400">
                                                    {{ $stat['room_count'] }}
                                                </td>
                                                <td class="px-3 py-3.5 text-center text-gray-600 dark:text-gray-400 text-xs">
                                                    {{ $stat['room_hours'] }}h
                                                </td>
                                                <td class="px-3 py-3.5 text-center font-semibold text-emerald-600 dark:text-emerald-400">
                                                    {{ $stat['tool_count'] }}
                                                </td>
                                                <td class="px-3 py-3.5 text-center text-gray-600 dark:text-gray-400 text-xs">
                                                    {{ $stat['tool_units'] }} units
                                                </td>
                                                <td class="px-3 py-3.5 text-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-300">
                                                        {{ $stat['total_activity'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">No departmental records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Grouped Bar Chart: Room vs Tool by Department (5 cols) --}}
                    <div class="lg:col-span-5 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                    Department Utilization Comparison
                                </h4>
                                <span class="text-xs text-gray-400">Room vs Tool</span>
                            </div>
                            <canvas id="deptComparisonChart" height="230"></canvas>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 flex items-center justify-around">
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-xs bg-blue-500 inline-block"></span>
                                <span>Room Checkouts</span>
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-xs bg-emerald-500 inline-block"></span>
                                <span>Tool Borrowings</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 2: ROOM UTILIZATION SPECIFICS                           --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-2 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>🏫</span>
                            <span>Laboratory & Facility Utilization Analysis</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Occupancy rates, session durations, and most utilized laboratory spaces
                        </p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        {{ $totalRoomTransactions }} Total Room Sessions
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- 1. Daily Room Checkouts (14 Days) --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📅 Daily Room Sessions (Last 14 Days)
                            </h4>
                            <span class="text-xs text-gray-400">Sessions / Day</span>
                        </div>
                        <canvas id="dailyRoomChart" height="200"></canvas>
                    </div>

                    {{-- 2. Most Used Rooms --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                🏆 Top Utilized Rooms
                            </h4>
                            <span class="text-xs text-gray-400">Total Checkouts</span>
                        </div>
                        @if ($topRoomData->sum() === 0)
                            <p class="text-gray-400 text-sm text-center py-12">No room session data available yet.</p>
                        @else
                            <canvas id="topRoomsChart" height="200"></canvas>
                        @endif
                    </div>

                    {{-- 3. Room Sessions by Day of Week --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📆 Room Occupancy by Day of Week
                            </h4>
                            <span class="text-xs text-gray-400">Weekly Distribution</span>
                        </div>
                        <canvas id="dowRoomChart" height="200"></canvas>
                    </div>

                    {{-- 4. Average Duration by Room --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                ⏱️ Avg Checkout Duration per Room (Hours)
                            </h4>
                            <span class="text-xs text-gray-400">Completed Sessions</span>
                        </div>
                        @if ($avgRoomData->sum() === 0)
                            <p class="text-gray-400 text-sm text-center py-12">Not enough return data recorded yet.</p>
                        @else
                            <canvas id="avgRoomDurationChart" height="200"></canvas>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 3: TOOL & EQUIPMENT CIRCULATION SPECIFICS               --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-2 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>🔧</span>
                            <span>Tool & Equipment Circulation Analysis</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Circulation volume, popular apparatus, and equipment category breakdown
                        </p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                        {{ $totalToolTransactions }} Checkouts / {{ $totalToolUnitsBorrowed }} Units
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- 1. Daily Tool Checkouts (14 Days) --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📅 Daily Equipment Borrowings (Last 14 Days)
                            </h4>
                            <span class="text-xs text-gray-400">Borrowings / Day</span>
                        </div>
                        <canvas id="dailyToolChart" height="200"></canvas>
                    </div>

                    {{-- 2. Most Borrowed Tools --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                🏆 Top Borrowed Equipment & Tools
                            </h4>
                            <span class="text-xs text-gray-400">Total Units Borrowed</span>
                        </div>
                        @if (count($topToolData) === 0 || array_sum($topToolData) === 0)
                            <p class="text-gray-400 text-sm text-center py-12">No equipment borrowing data yet.</p>
                        @else
                            <canvas id="topToolsChart" height="200"></canvas>
                        @endif
                    </div>

                    {{-- 3. Tool Borrowings by Day of Week --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📆 Tool Circulation by Day of Week
                            </h4>
                            <span class="text-xs text-gray-400">Borrowing Days</span>
                        </div>
                        <canvas id="dowToolChart" height="200"></canvas>
                    </div>

                    {{-- 4. Equipment Categories Breakdown --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📦 Equipment Circulation by Category
                            </h4>
                            <span class="text-xs text-gray-400">Distribution</span>
                        </div>
                        @if ($toolCatData->sum() === 0)
                            <p class="text-gray-400 text-sm text-center py-12">No category circulation data recorded.</p>
                        @else
                            <div class="flex justify-center">
                                <div style="max-width: 250px; width: 100%;">
                                    <canvas id="toolCatChart" height="220"></canvas>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 3: COMPUTER LAB SOFTWARE UTILIZATION                    --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700 gap-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>💻</span>
                            <span>Computer Laboratory Software & Applications Utilization</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Tracking software suites deployed and utilized during computer lab sessions for curriculum delivery & OPCR compliance
                        </p>
                    </div>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 self-start sm:self-auto">
                        {{ $totalSoftwareRoomSessions }} Software Sessions ({{ $softwareAdoptionRate }}% Adoption)
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- 1. Software Utilization Chart --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📊 Sessions per Software Suite
                            </h4>
                            <span class="text-xs text-gray-400">Lab Checkouts</span>
                        </div>
                        @if (count($softwareChartData) === 0 || array_sum($softwareChartData) === 0)
                            <div class="my-auto py-12 text-center text-gray-400 dark:text-gray-500">
                                <p class="text-3xl mb-2">💻</p>
                                <p class="text-sm font-medium">No software utilization recorded yet.</p>
                                <p class="text-xs mt-1">Select software suites when checking out computer laboratory rooms to see usage analytics.</p>
                            </div>
                        @else
                            <canvas id="softwareChart" height="220"></canvas>
                        @endif
                    </div>

                    {{-- 2. Software Catalog & Department Breakdown --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📑 Software Suites Breakdown
                            </h4>
                            <span class="text-xs text-gray-400">{{ count($softwareCatalog) }} Catalog Suites</span>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto pr-1">
                            @foreach ($softwareCatalog as $swKey => $sw)
                                @php
                                    $sessionCount = $softwareCounts[$sw['name']] ?? 0;
                                    $deptBreakdown = $softwareDeptCounts[$sw['name']] ?? [];
                                @endphp
                                <div class="py-3 first:pt-0 last:pb-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="font-semibold text-sm text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                                <span>{{ $sw['icon'] }}</span>
                                                <span>{{ $sw['name'] }}</span>
                                            </span>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $sw['description'] }}
                                            </p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $sessionCount > 0 ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                                {{ $sessionCount }} session{{ $sessionCount === 1 ? '' : 's' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-2 flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[10px] text-gray-400">Target Depts:</span>
                                        @foreach ($sw['departments'] as $tDept)
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono">
                                                {{ $tDept }}
                                            </span>
                                        @endforeach

                                        @if (!empty($deptBreakdown))
                                            <span class="text-[10px] text-gray-400 ml-1">| Utilized by:</span>
                                            @foreach ($deptBreakdown as $dShort => $dCount)
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-purple-50 dark:bg-purple-950 text-purple-700 dark:text-purple-300 font-semibold border border-purple-200 dark:border-purple-800">
                                                    {{ $dShort }}: {{ $dCount }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            {{-- Any custom software logged --}}
                            @foreach ($softwareCounts as $swName => $swCount)
                                @if (!isset($softwareCatalog[$swName]))
                                    <div class="py-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <span class="font-semibold text-sm text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                                    <span>⚙️</span>
                                                    <span>{{ $swName }}</span>
                                                    <span class="text-[10px] px-1.5 py-0.2 rounded bg-amber-50 text-amber-700 border border-amber-200 font-normal">Custom</span>
                                                </span>
                                            </div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                                {{ $swCount }} session{{ $swCount === 1 ? '' : 's' }}
                                            </span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Data Privacy & Research Note ────────────────────────────── --}}
            <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-500 dark:text-gray-400">
                <strong>📊 Laboratory Analytics & Research Compliance:</strong>
                This data report complies with Institutional Performance Commitment Review (OPCR) and research metric evaluation standards.
                Aggregated departmental statistics contain no personally identifiable data. For detailed audit logs including faculty names, refer to the
                <a href="{{ route('admin.transactions.export') }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">CSV export</a>
                in adherence to Republic Act 10173 (Data Privacy Act of 2012).
            </div>

        </div>
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9CA3AF' : '#6B7280';
        const gridColor = isDark ? '#374151' : '#F3F4F6';

        const bluePalette = ['#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#1D4ED8', '#1E40AF', '#1E3A8A'];
        const emeraldPalette = ['#059669', '#10B981', '#34D399', '#6EE7B7', '#047857', '#065F46'];
        const mixedPalette = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EC4899', '#06B6D4'];

        // ── 1. Department Comparison Chart (Grouped Bar) ─────────────────────
        new Chart(document.getElementById('deptComparisonChart'), {
            type: 'bar',
            data: {
                labels: @json($deptChartLabels),
                datasets: [
                    {
                        label: 'Room Checkouts',
                        data: @json($deptChartRooms),
                        backgroundColor: '#3B82F6',
                        borderRadius: 4,
                    },
                    {
                        label: 'Tool Borrowings',
                        data: @json($deptChartTools),
                        backgroundColor: '#10B981',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });

        // ── 2. Daily Room Sessions Chart ────────────────────────────────────
        new Chart(document.getElementById('dailyRoomChart'), {
            type: 'bar',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    label: 'Room Sessions',
                    data: @json($dailyRoomData),
                    backgroundColor: '#3B82F6',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });

        // ── 3. Top Rooms Chart (Horizontal Bar) ──────────────────────────────
        @if ($topRoomData->sum() > 0)
        new Chart(document.getElementById('topRoomsChart'), {
            type: 'bar',
            data: {
                labels: @json($topRoomLabels),
                datasets: [{
                    label: 'Checkouts',
                    data: @json($topRoomData),
                    backgroundColor: bluePalette,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });
        @endif

        // ── 4. Day of Week: Rooms ───────────────────────────────────────────
        new Chart(document.getElementById('dowRoomChart'), {
            type: 'bar',
            data: {
                labels: @json($dowLabels),
                datasets: [{
                    label: 'Room Checkouts',
                    data: @json($dowRoomValues),
                    backgroundColor: '#60A5FA',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });

        // ── 5. Avg Room Duration Chart ──────────────────────────────────────
        @if ($avgRoomData->sum() > 0)
        new Chart(document.getElementById('avgRoomDurationChart'), {
            type: 'bar',
            data: {
                labels: @json($avgRoomLabels),
                datasets: [{
                    label: 'Avg Hours',
                    data: @json($avgRoomData),
                    backgroundColor: '#F59E0B',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });
        @endif

        // ── 6. Daily Tool Borrowings Chart ──────────────────────────────────
        new Chart(document.getElementById('dailyToolChart'), {
            type: 'bar',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    label: 'Tool Checkouts',
                    data: @json($dailyToolData),
                    backgroundColor: '#10B981',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });

        // ── 7. Top Borrowed Tools (Horizontal Bar) ──────────────────────────
        @if (count($topToolData) > 0 && array_sum($topToolData) > 0)
        new Chart(document.getElementById('topToolsChart'), {
            type: 'bar',
            data: {
                labels: @json($topToolLabels),
                datasets: [{
                    label: 'Units Borrowed',
                    data: @json($topToolData),
                    backgroundColor: emeraldPalette,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });
        @endif

        // ── 8. Day of Week: Tools ───────────────────────────────────────────
        new Chart(document.getElementById('dowToolChart'), {
            type: 'bar',
            data: {
                labels: @json($dowLabels),
                datasets: [{
                    label: 'Tool Borrows',
                    data: @json($dowToolValues),
                    backgroundColor: '#34D399',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });

        // ── 9. Equipment Category Doughnut Chart ────────────────────────────
        @if ($toolCatData->sum() > 0)
        new Chart(document.getElementById('toolCatChart'), {
            type: 'doughnut',
            data: {
                labels: @json($toolCatLabels),
                datasets: [{
                    data: @json($toolCatData),
                    backgroundColor: mixedPalette,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });
        @endif

        // ── 10. Software Utilization Bar Chart ──────────────────────────────
        @if (count($softwareChartData) > 0)
        new Chart(document.getElementById('softwareChart'), {
            type: 'bar',
            data: {
                labels: @json($softwareChartLabels),
                datasets: [{
                    label: 'Sessions Utilized',
                    data: @json($softwareChartData),
                    backgroundColor: [
                        '#8B5CF6', '#EC4899', '#3B82F6', '#10B981', '#F59E0B', '#6366F1', '#14B8A6', '#F43F5E'
                    ],
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } }
                }
            }
        });
        @endif
    </script>
</x-app-layout>
