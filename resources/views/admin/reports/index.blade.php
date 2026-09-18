<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    <span>📈</span>
                    <span>Laboratory Utilization & Audit Reports</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Cross-matrix room utilization by student department, faculty workload, and curriculum compliance analytics
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.reports.audit', request()->query()) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-bold px-3.5 py-2 rounded-lg shadow-sm transition">
                    <span>🖨️</span>
                    <span>Official Audit Sheet (Print/PDF)</span>
                </a>
                <a href="{{ route('admin.transactions.export') }}"
                   class="inline-flex items-center gap-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-3.5 py-2 rounded-lg shadow-sm transition">
                    <span>⬇️</span>
                    <span>Export All CSV</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- MULTI-DIMENSIONAL AUDIT FILTER BAR                              --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 space-y-4"
                 x-data="{ showAdvanced: {{ request()->hasAny(['borrower', 'subject', 'date_from', 'date_to']) ? 'true' : 'false' }} }">
                
                <form method="GET" action="{{ route('admin.reports.index') }}" class="space-y-4">
                    {{-- Row 1: Primary Dropdown Filters --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                        {{-- Department Filter --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                🏢 Department Scope
                            </label>
                            <select name="department" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                                <option value="">— All Academic Departments —</option>
                                <option value="DECET" {{ ($activeFilters['department'] === 'DECET') ? 'selected' : '' }}>DECET (Computer & Electronics)</option>
                                <option value="DOMIT" {{ ($activeFilters['department'] === 'DOMIT') ? 'selected' : '' }}>DOMIT (Office Mgmt & IT)</option>
                                <option value="DEMET" {{ ($activeFilters['department'] === 'DEMET') ? 'selected' : '' }}>DEMET (Electrical & Mechanical)</option>
                            </select>
                        </div>

                        {{-- Room Filter --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                🚪 Specific Room / Lab
                            </label>
                            <select name="room_id" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                                <option value="">— All Laboratories & Rooms —</option>
                                @foreach ($roomsDropdown->groupBy(fn($r) => $r->departmentShort() ?: 'General / Shared') as $dept => $roomList)
                                    <optgroup label="{{ $dept }}">
                                        @foreach ($roomList as $r)
                                            <option value="{{ $r->id }}" {{ ($activeFilters['room_id'] == $r->id) ? 'selected' : '' }}>
                                                {{ $r->isLab() ? '🔬 ' : '📖 ' }}{{ $r->name }} ({{ $r->departmentShort() }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- Faculty Filter --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                👤 Faculty / Borrower
                            </label>
                            <input type="text" name="borrower" value="{{ $activeFilters['borrower'] }}" list="facultyList"
                                   placeholder="Filter by Faculty name..."
                                   class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                            <datalist id="facultyList">
                                @foreach ($facultiesDropdown as $f)
                                    <option value="{{ $f->name }}">{{ $f->department }}</option>
                                @endforeach
                            </datalist>
                        </div>

                        {{-- Subject Filter --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">
                                📚 Subject / Course Code
                            </label>
                            <input type="text" name="subject" value="{{ $activeFilters['subject'] }}" list="subjectList"
                                   placeholder="e.g. CPE 302, COMP 001..."
                                   class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500">
                            <datalist id="subjectList">
                                @foreach ($subjectsDropdown as $s)
                                    <option value="{{ $s->code }}">{{ $s->name }}</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    {{-- Row 2: Quick Semester / Date Presets & Custom Date Range --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                        {{-- Presets Buttons --}}
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mr-1">Period:</span>
                            <button type="submit" name="preset" value=""
                                    class="px-2.5 py-1 rounded text-xs font-medium {{ empty($activeFilters['preset']) && empty($activeFilters['date_from']) ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                All Time
                            </button>
                            <button type="submit" name="preset" value="today"
                                    class="px-2.5 py-1 rounded text-xs font-medium {{ $activeFilters['preset'] === 'today' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                Today
                            </button>
                            <button type="submit" name="preset" value="this_week"
                                    class="px-2.5 py-1 rounded text-xs font-medium {{ $activeFilters['preset'] === 'this_week' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                This Week
                            </button>
                            <button type="submit" name="preset" value="this_month"
                                    class="px-2.5 py-1 rounded text-xs font-medium {{ $activeFilters['preset'] === 'this_month' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                This Month
                            </button>
                            <button type="submit" name="preset" value="semester_1"
                                    class="px-2.5 py-1 rounded text-xs font-medium {{ $activeFilters['preset'] === 'semester_1' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                1st Sem (Aug–Dec)
                            </button>
                            <button type="submit" name="preset" value="semester_2"
                                    class="px-2.5 py-1 rounded text-xs font-medium {{ $activeFilters['preset'] === 'semester_2' ? 'bg-blue-600 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                2nd Sem (Jan–Jun)
                            </button>
                        </div>

                        {{-- Custom Date Inputs & Action Buttons --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                                <span>From:</span>
                                <input type="date" name="date_from" value="{{ $activeFilters['date_from'] }}"
                                       class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded text-xs py-1 px-2">
                                <span>To:</span>
                                <input type="date" name="date_to" value="{{ $activeFilters['date_to'] }}"
                                       class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded text-xs py-1 px-2">
                            </div>
                            <button type="submit"
                                    class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                Filter Report
                            </button>
                            <a href="{{ route('admin.reports.index') }}"
                               class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-xs rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- Active Filter Tags --}}
                @if (array_filter($activeFilters))
                    <div class="flex items-center gap-2 flex-wrap pt-2 border-t border-gray-100 dark:border-gray-700 text-xs">
                        <span class="text-gray-400">Active Filters:</span>
                        @if ($activeFilters['department'])
                            <span class="px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-semibold">
                                Dept: {{ $activeFilters['department'] }}
                            </span>
                        @endif
                        @if ($activeFilters['room_id'])
                            <span class="px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-semibold">
                                Room: {{ $roomsDropdown->firstWhere('id', $activeFilters['room_id'])?->name }}
                            </span>
                        @endif
                        @if ($activeFilters['borrower'])
                            <span class="px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 font-semibold">
                                Faculty: {{ $activeFilters['borrower'] }}
                            </span>
                        @endif
                        @if ($activeFilters['subject'])
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-semibold">
                                Subject: {{ $activeFilters['subject'] }}
                            </span>
                        @endif
                        @if ($activeFilters['date_from'] || $activeFilters['date_to'])
                            <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium">
                                Date: {{ $activeFilters['date_from'] ?: 'Start' }} → {{ $activeFilters['date_to'] ?: 'Present' }}
                            </span>
                        @endif
                    </div>
                @endif
            </div>

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
            {{-- SECTION 1: DEPARTMENT UTILIZATION OVERVIEW                      --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-700 pb-2">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span>🏢</span>
                        <span>Departmental Utilization Analysis</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Aggregated comparison of room occupancy hours and equipment circulation by Academic Department
                    </p>
                </div>

                {{-- Grouped Bar Chart & Dept Breakdown Table --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200">
                                📊 Room Checkouts vs. Tool Circulation by Department
                            </h4>
                            <span class="text-xs text-gray-400">Transaction count</span>
                        </div>
                        <canvas id="deptComparisonChart" height="150"></canvas>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between">
                        <div>
                            <h4 class="font-semibold text-sm text-gray-800 dark:text-gray-200 mb-3">
                                🏛️ Department Summary Matrix
                            </h4>
                            <div class="divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                                @foreach ($deptStats as $dept)
                                    <div class="py-2.5 flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-gray-100">{{ $dept['short'] }}</span>
                                            <span class="text-gray-400 block text-[11px] truncate max-w-[150px]">{{ $dept['name'] }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-blue-600 dark:text-blue-400">{{ $dept['room_hours'] }}h</span>
                                            <span class="text-gray-400 text-[11px] block">{{ $dept['room_count'] }} sessions / {{ $dept['tool_units'] }} tools</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 2: AUDIT CROSS-MATRIX — ROOMS BY STUDENT DEPARTMENT     --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700 gap-2">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>🏛️</span>
                            <span>Room Utilization by Student Department (Cross-Matrix Audit)</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Key audit matrix showing how each facility is shared and utilized across DECET, DOMIT, and DEMET students
                        </p>
                    </div>
                    <a href="{{ route('admin.reports.audit', request()->query()) }}" target="_blank"
                       class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 self-start sm:self-auto">
                        <span>🖨️ View Formatted Audit Print Sheet →</span>
                    </a>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-750">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Room Name</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Type / Home Dept</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 dark:text-gray-400 uppercase">Sessions</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 dark:text-gray-400 uppercase">Total Hours</th>
                                    <th class="px-4 py-3 text-center font-bold text-blue-700 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-950/20 uppercase">DECET Usage</th>
                                    <th class="px-4 py-3 text-center font-bold text-purple-700 dark:text-purple-400 bg-purple-50/50 dark:bg-purple-950/20 uppercase">DOMIT Usage</th>
                                    <th class="px-4 py-3 text-center font-bold text-amber-700 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-950/20 uppercase">DEMET Usage</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Top Subjects / Software</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($roomAuditMatrix as $matrix)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $matrix['room']->name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $matrix['room']->isLab() ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                                {{ $matrix['room']->isLab() ? '🔬 Lab' : ($matrix['room']->isOffice() ? '🏢 Office' : '📖 Lecture') }}
                                            </span>
                                            <span class="text-gray-400 text-[11px] ml-1">({{ $matrix['room']->departmentShort() }})</span>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $matrix['sessions'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-black text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $matrix['total_hours'] }}h
                                        </td>
                                        <td class="px-4 py-3 text-center bg-blue-50/20 dark:bg-blue-950/10 whitespace-nowrap">
                                            @if ($matrix['decet_hours'] > 0)
                                                <span class="font-bold text-blue-700 dark:text-blue-300">{{ $matrix['decet_hours'] }}h</span>
                                                <span class="text-gray-400 text-[10px]">({{ $matrix['decet_pct'] }}%)</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center bg-purple-50/20 dark:bg-purple-950/10 whitespace-nowrap">
                                            @if ($matrix['domit_hours'] > 0)
                                                <span class="font-bold text-purple-700 dark:text-purple-300">{{ $matrix['domit_hours'] }}h</span>
                                                <span class="text-gray-400 text-[10px]">({{ $matrix['domit_pct'] }}%)</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center bg-amber-50/20 dark:bg-amber-950/10 whitespace-nowrap">
                                            @if ($matrix['demet_hours'] > 0)
                                                <span class="font-bold text-amber-700 dark:text-amber-300">{{ $matrix['demet_hours'] }}h</span>
                                                <span class="text-gray-400 text-[10px]">({{ $matrix['demet_pct'] }}%)</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs">
                                            <div class="truncate font-medium">
                                                {{ !empty($matrix['top_subjects']) ? implode(', ', $matrix['top_subjects']) : '—' }}
                                            </div>
                                            @if (!empty($matrix['software_used']))
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach ($matrix['software_used'] as $sw)
                                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                            💻 {{ $sw }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8 text-gray-400">No room records match the active filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 3: FACULTY & CURRICULUM UTILIZATION AUDIT TABS          --}}
            {{-- ═══════════════════════════════════════════════════════════════ --}}
            <div class="space-y-4" x-data="{ auditTab: 'faculty' }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700 gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>👥</span>
                            <span>Faculty Workload & Curriculum Lab Hours Audit</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Audit breakdown verifying which professors and courses generated laboratory contact hours
                        </p>
                    </div>

                    {{-- Tab Switcher --}}
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700/60 p-1 rounded-lg self-start sm:self-auto">
                        <button type="button" @click="auditTab = 'faculty'"
                                :class="auditTab === 'faculty' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1.5 rounded-md text-xs transition flex items-center gap-1.5">
                            <span>👤</span>
                            <span>Faculty Workload ({{ count($facultyAudit) }})</span>
                        </button>
                        <button type="button" @click="auditTab = 'subject'"
                                :class="auditTab === 'subject' ? 'bg-white dark:bg-gray-800 text-purple-600 dark:text-purple-400 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1.5 rounded-md text-xs transition flex items-center gap-1.5">
                            <span>📚</span>
                            <span>Curriculum Subjects ({{ count($subjectAudit) }})</span>
                        </button>
                    </div>
                </div>

                {{-- TAB A: Faculty Laboratory Workload Table --}}
                <div x-show="auditTab === 'faculty'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-750">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Faculty / Borrower Name</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Department</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 dark:text-gray-400 uppercase">Total Sessions</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 dark:text-gray-400 uppercase">Total Hours</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Rooms Utilized</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Subjects Conducted</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Software Used</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($facultyAudit as $fac)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $fac['name'] }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-blue-700 dark:text-blue-300 whitespace-nowrap">
                                            {{ $fac['department'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $fac['sessions'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-black text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $fac['total_hours'] }}h
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ implode(', ', $fac['rooms']) }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                            {{ implode(', ', $fac['subjects']) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if (!empty($fac['software']))
                                                <span class="text-purple-700 dark:text-purple-300 font-medium">💻 {{ implode(', ', $fac['software']) }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-8 text-gray-400">No faculty sessions match active filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB B: Curriculum Subject / Course Contact Hours Table --}}
                <div x-show="auditTab === 'subject'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-750">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Curriculum Course / Subject Title</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Department</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 dark:text-gray-400 uppercase">Sessions</th>
                                    <th class="px-4 py-3 text-center font-bold text-gray-600 dark:text-gray-400 uppercase">Hours Conducted</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Designated Laboratories</th>
                                    <th class="px-4 py-3 text-left font-bold text-gray-600 dark:text-gray-400 uppercase">Assigned Faculty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($subjectAudit as $subj)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-gray-100">
                                            {{ $subj['subject'] }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-purple-700 dark:text-purple-300 whitespace-nowrap">
                                            {{ $subj['department'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $subj['sessions'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-black text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                            {{ $subj['total_hours'] }}h
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                            {{ implode(', ', $subj['rooms']) }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                            {{ implode(', ', $subj['faculties']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-gray-400">No subject hours match active filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 4: COMPUTER LAB SOFTWARE UTILIZATION                    --}}
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

            {{-- ═══════════════════════════════════════════════════════════════ --}}
            {{-- SECTION 5: TOOL & EQUIPMENT CIRCULATION SPECIFICS               --}}
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
                        @if (count($toolCatData) === 0 || array_sum($toolCatData) === 0)
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

            {{-- ── Data Privacy & Research Note ────────────────────────────── --}}
            <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-500 dark:text-gray-400">
                <strong>📊 Academic Audit & OPCR Research Compliance:</strong>
                This analytics report complies with university Institutional Performance Commitment Review (OPCR) and research metric evaluation standards.
                Aggregated departmental statistics contain no personally identifiable student data.
                You can print the <a href="{{ route('admin.reports.audit', request()->query()) }}" target="_blank" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Official Audit Sheet</a>
                or download the complete <a href="{{ route('admin.transactions.export') }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">CSV Audit Trail</a>
                in full adherence to Republic Act 10173 (Data Privacy Act of 2012).
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

        // ── 2. Daily Tool Checkouts ─────────────────────────────────────────
        new Chart(document.getElementById('dailyToolChart'), {
            type: 'bar',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    label: 'Tool Borrows',
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

        // ── 3. Top Tools Chart ──────────────────────────────────────────────
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

        // ── 4. Day of Week: Tools ───────────────────────────────────────────
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

        // ── 5. Equipment Category Doughnut Chart ────────────────────────────
        @if (count($toolCatData) > 0 && array_sum($toolCatData) > 0)
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

        // ── 6. Software Utilization Bar Chart ──────────────────────────────
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
