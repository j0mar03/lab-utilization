<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    <span>⭐</span>
                    <span>Client Satisfaction & Diagnostic Survey Dashboard</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Academic facility evaluation, construct mean benchmarks (EduQUAL), diagnostic error reporting & research data export
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.qr.feedback-rooms') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <span>🖨️ Door QR Posters</span>
                </a>
                <a href="{{ route('admin.feedbacks.export', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                    <span>📥 Export CSV (SPSS/Excel)</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Filter Toolbar -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <form method="GET" action="{{ route('admin.feedbacks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Facility / Room</label>
                    <select name="room_id" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Rooms & Labs</option>
                        @foreach ($roomsDropdown as $r)
                            <option value="{{ $r->id }}" {{ $activeFilters['room_id'] == $r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->roomType() }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Capacity / Role</label>
                    <select name="capacity" class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">All Roles</option>
                        <option value="Student" {{ $activeFilters['capacity'] === 'Student' ? 'selected' : '' }}>Student</option>
                        <option value="Faculty" {{ $activeFilters['capacity'] === 'Faculty' ? 'selected' : '' }}>Faculty</option>
                        <option value="Guest"   {{ $activeFilters['capacity'] === 'Guest' ? 'selected' : '' }}>Guest</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ $activeFilters['date_from'] }}"
                           class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ $activeFilters['date_to'] }}"
                           class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-red-800 hover:bg-red-900 text-white font-bold text-xs rounded-lg shadow-sm transition">
                        Filter
                    </button>
                    @if (array_filter($activeFilters))
                        <a href="{{ route('admin.feedbacks.index') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs rounded-lg transition text-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- KPI Scorecard Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Overall Satisfaction Mean -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-amber-200 dark:border-amber-800 shadow-sm relative overflow-hidden">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Overall Mean</span>
                <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1 flex items-baseline gap-1">
                    <span>{{ $avgOverall }}</span>
                    <span class="text-xs text-gray-400 font-normal">/ 5.0</span>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 font-medium">
                    {{ str_repeat('⭐', round($avgOverall)) }}
                </div>
            </div>

            <!-- OPCR Satisfaction Rate -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">OPCR Target (≥4★)</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                    {{ $satisfactionRate }}%
                </div>
                <div class="mt-2 text-[10px] text-gray-400">
                    Positive Satisfaction
                </div>
            </div>

            <!-- Total Responses -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 block">Total Responses</span>
                <div class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1">
                    {{ number_format($totalResponses) }}
                </div>
                <div class="mt-2 text-[10px] text-gray-400 truncate">
                    {{ $capacityCounts['Student'] }} Stu · {{ $capacityCounts['Faculty'] }} Fac · {{ $capacityCounts['Guest'] }} Gst
                </div>
            </div>

            <!-- Cleanliness Mean -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-blue-200 dark:border-blue-800 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400 block">🧹 Environment</span>
                <div class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1">
                    {{ $avgEnvironment }}
                    <span class="text-xs text-gray-400 font-normal">/ 5</span>
                </div>
                <div class="mt-2 text-[10px] text-gray-400">
                    Cleanliness & AC
                </div>
            </div>

            <!-- Resources & Tech Mean -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-purple-200 dark:border-purple-800 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">🖥️ AV & Tech</span>
                <div class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">
                    {{ $avgResources }}
                    <span class="text-xs text-gray-400 font-normal">/ 5</span>
                </div>
                <div class="mt-2 text-[10px] text-gray-400">
                    Projectors & PCs
                </div>
            </div>

            <!-- Service & Staff Mean -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-rose-200 dark:border-rose-800 shadow-sm">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block">🤝 Staff Service</span>
                <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                    {{ $avgService }}
                    <span class="text-xs text-gray-400 font-normal">/ 5</span>
                </div>
                <div class="mt-2 text-[10px] text-gray-400">
                    SA Counter Assistance
                </div>
            </div>
        </div>

        <!-- Research Constructs & Diagnostic Issues Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- EduQUAL Dimension Means (Spider / Bar View) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">
                    <div>
                        <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                            <span>📊</span>
                            <span>Evaluation Construct Means (EduQUAL Framework)</span>
                        </h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">
                            Scale: 1.00 (Poor) to 5.00 (Excellent) · Composite Index: <strong class="text-emerald-600">{{ $compositeIndex }}</strong>
                        </p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                        N = {{ $totalResponses }}
                    </span>
                </div>

                <div class="space-y-4 text-xs">
                    <div>
                        <div class="flex justify-between font-semibold mb-1">
                            <span class="text-gray-700 dark:text-gray-300">Q1. Environmental Quality, Ventilation & Cleanliness</span>
                            <span class="text-blue-600 font-bold">{{ $avgEnvironment }} / 5.00</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ ($avgEnvironment / 5) * 100 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold mb-1">
                            <span class="text-gray-700 dark:text-gray-300">Q2. Instructional Tech & Resource Availability (TV, Projector, PCs)</span>
                            <span class="text-purple-600 font-bold">{{ $avgResources }} / 5.00</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-purple-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ ($avgResources / 5) * 100 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold mb-1">
                            <span class="text-gray-700 dark:text-gray-300">Q3. Laboratory Safety & Workstation Rules Adherence</span>
                            <span class="text-amber-600 font-bold">{{ $avgSafety }} / 5.00</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ ($avgSafety / 5) * 100 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold mb-1">
                            <span class="text-gray-700 dark:text-gray-300">Q4. Laboratory Staff Service & Student Assistant Counter Efficiency</span>
                            <span class="text-rose-600 font-bold">{{ $avgService }} / 5.00</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-rose-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ ($avgService / 5) * 100 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-semibold mb-1">
                            <span class="text-gray-700 dark:text-gray-300">Q5. Digital System Experience & QR Scan Usability</span>
                            <span class="text-indigo-600 font-bold">{{ $avgSystem }} / 5.00</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ ($avgSystem / 5) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Diagnostic Issue Checklist Breakdown -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 flex flex-col justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 flex items-center gap-1.5 border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                        <span>⚠️</span>
                        <span>Diagnostic Issues Reported</span>
                    </h3>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 text-xs">
                        @foreach ($issueCounts as $code => $count)
                            @if ($code !== 'none')
                                <div class="py-2.5 flex items-center justify-between">
                                    <span class="text-gray-700 dark:text-gray-300 truncate max-w-[200px]">
                                        {{ $issueLabels[$code] ?? $code }}
                                    </span>
                                    <span class="font-bold px-2 py-0.5 rounded-full text-xs {{ $count > 0 ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $count }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 text-[11px] text-gray-500 flex justify-between">
                    <span>Smooth Sessions (No issues):</span>
                    <strong class="text-emerald-600">{{ $issueCounts['none'] ?? 0 }}</strong>
                </div>
            </div>
        </div>

        <!-- Room Satisfaction Ranking Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                        🏫 Facility Satisfaction Ranking by Room
                    </h3>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                        Comparative analysis identifying top-performing and maintenance-priority rooms
                    </p>
                </div>
                <span class="text-xs text-gray-400">Sorted by Overall Score</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Room</th>
                            <th class="py-3 px-3">Type</th>
                            <th class="py-3 px-3 text-center">Responses</th>
                            <th class="py-3 px-3 text-center">Overall</th>
                            <th class="py-3 px-3 text-center">Cleanliness</th>
                            <th class="py-3 px-3 text-center">AV / Tech</th>
                            <th class="py-3 px-3 text-center">Safety</th>
                            <th class="py-3 px-3 text-center">Staff</th>
                            <th class="py-3 px-3 text-center">System</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($roomsSummary as $r)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-gray-100">
                                    {{ $r['room']->name }}
                                </td>
                                <td class="py-3 px-3 text-gray-500">
                                    {{ $r['room']->roomType() }}
                                </td>
                                <td class="py-3 px-3 text-center font-semibold">
                                    {{ $r['count'] }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="font-bold px-2 py-0.5 rounded text-xs {{ $r['overall'] >= 4 ? 'bg-emerald-100 text-emerald-800' : ($r['overall'] >= 3 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $r['overall'] }} ★
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">{{ $r['environment'] }}</td>
                                <td class="py-3 px-3 text-center">{{ $r['resources'] }}</td>
                                <td class="py-3 px-3 text-center">{{ $r['safety'] }}</td>
                                <td class="py-3 px-3 text-center">{{ $r['service'] }}</td>
                                <td class="py-3 px-3 text-center">{{ $r['system'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-400">
                                    No room evaluation data recorded yet for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Responses & Qualitative Suggestions Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                    💬 Recent Survey Submissions & Thematic Suggestions
                </h3>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    Individual client evaluations with diagnostic flags and qualitative suggestions
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-3">Room</th>
                            <th class="py-3 px-3">Role</th>
                            <th class="py-3 px-3">Respondent</th>
                            <th class="py-3 px-3 text-center">Score</th>
                            <th class="py-3 px-3">Diagnostic Issues</th>
                            <th class="py-3 px-4">Suggestions / Comments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($feedbacks as $fb)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="py-3 px-4 whitespace-nowrap text-gray-500">
                                    {{ $fb->created_at->format('M d, Y') }}
                                    <span class="block text-[10px] text-gray-400">{{ $fb->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="py-3 px-3 font-bold text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                    {{ $fb->room?->name }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold
                                        {{ $fb->capacity === 'Faculty' ? 'bg-purple-100 text-purple-800' : ($fb->capacity === 'Student' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ $fb->capacity }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-gray-700 dark:text-gray-300">
                                    {{ $fb->respondent_name ?: 'Anonymous' }}
                                    @if ($fb->respondent_email)
                                        <span class="block text-[10px] text-gray-400 truncate max-w-[140px]">{{ $fb->respondent_email }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="font-bold px-2 py-0.5 rounded text-xs {{ $fb->scoreBadgeColor() }}">
                                        {{ $fb->overall_score }}★
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    @if (!empty($fb->reported_issues) && is_array($fb->reported_issues) && !in_array('none', $fb->reported_issues))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($fb->reported_issues as $iss)
                                                <span class="px-1.5 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded text-[10px]">
                                                    {{ $issueLabels[$iss] ?? $iss }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-emerald-600 text-[11px]">✓ No issues</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300 max-w-xs break-words">
                                    @if ($fb->suggestions)
                                        <span class="italic text-[11px]">"{{ $fb->suggestions }}"</span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">
                                    No client feedback records submitted yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($feedbacks->hasPages())
                <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $feedbacks->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
