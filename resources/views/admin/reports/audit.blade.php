<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Lab Utilization Audit Report — PUP-ITECH</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; font-size: 11pt; }
            .page-break { page-break-before: always; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 antialiased p-4 sm:p-8">

    {{-- Floating Action Bar for On-Screen View --}}
    <div class="no-print max-w-6xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.index', request()->query()) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg transition">
                <span>← Back to Analytics</span>
            </a>
            <span class="text-xs text-gray-500 font-medium">
                Showing audit report based on currently applied filters.
            </span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-lg shadow-sm transition">
                <span>🖨️ Print / Save as PDF</span>
            </button>
        </div>
    </div>

    {{-- Main Document Sheet (A4 formatted container) --}}
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-200 p-8 sm:p-12 print-shadow-none space-y-8">

        {{-- Official Institutional Header --}}
        <div class="text-center border-b-2 border-maroon-800 pb-6 border-red-900">
            <p class="text-xs uppercase tracking-widest font-bold text-gray-500">Republic of the Philippines</p>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-gray-900 uppercase mt-0.5">
                Polytechnic University of the Philippines
            </h1>
            <p class="text-sm font-semibold text-red-900">Institute of Technology (PUP-ITECH)</p>
            <p class="text-xs text-gray-500 mt-1">Laboratory Utilization & Facilities Inventory Management Office</p>
            
            <div class="mt-4 pt-3 border-t border-gray-200 inline-block px-6">
                <h2 class="text-base sm:text-lg font-black uppercase tracking-wider text-gray-900">
                    Official Laboratory & Facility Utilization Audit Report
                </h2>
                <p class="text-xs text-gray-500">Academic & Institutional Performance Review (OPCR / CHED Compliance)</p>
            </div>
        </div>

        {{-- Audit Parameters Metadata Box --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-gray-50 border border-gray-200 text-xs">
            <div>
                <span class="text-gray-500 uppercase tracking-wider block font-bold text-[10px]">Department Scope</span>
                <span class="font-bold text-gray-900 text-sm">
                    {{ $activeFilters['department'] ?: 'All Academic Depts' }}
                </span>
            </div>
            <div>
                <span class="text-gray-500 uppercase tracking-wider block font-bold text-[10px]">Facility Scope</span>
                <span class="font-bold text-gray-900 text-sm">
                    @if ($activeFilters['room_id'])
                        {{ $roomsDropdown->firstWhere('id', $activeFilters['room_id'])?->name }}
                    @else
                        All Rooms & Labs
                    @endif
                </span>
            </div>
            <div>
                <span class="text-gray-500 uppercase tracking-wider block font-bold text-[10px]">Audit Period</span>
                <span class="font-bold text-gray-900 text-sm">
                    @if ($activeFilters['date_from'] || $activeFilters['date_to'])
                        {{ $activeFilters['date_from'] ?: 'Start' }} to {{ $activeFilters['date_to'] ?: 'Present' }}
                    @else
                        All Time / Cumulative
                    @endif
                </span>
            </div>
            <div>
                <span class="text-gray-500 uppercase tracking-wider block font-bold text-[10px]">Date Generated</span>
                <span class="font-bold text-gray-900 text-sm">
                    {{ now()->format('M d, Y - h:i A') }}
                </span>
            </div>
        </div>

        {{-- Executive KPI Summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50">
                <span class="text-xs font-semibold text-blue-700 block">Total Room Sessions</span>
                <span class="text-2xl font-black text-gray-900">{{ number_format($totalRoomTransactions) }}</span>
            </div>
            <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50">
                <span class="text-xs font-semibold text-blue-700 block">Total Room Hours</span>
                <span class="text-2xl font-black text-gray-900">{{ number_format($totalRoomHours, 1) }} hrs</span>
            </div>
            <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50">
                <span class="text-xs font-semibold text-emerald-700 block">Equipment Units Circulated</span>
                <span class="text-2xl font-black text-gray-900">{{ number_format($totalToolUnitsBorrowed) }}</span>
            </div>
            <div class="p-3.5 rounded-xl border border-purple-200 bg-purple-50/50">
                <span class="text-xs font-semibold text-purple-700 block">Software Sessions</span>
                <span class="text-2xl font-black text-gray-900">{{ number_format($totalSoftwareRoomSessions) }} ({{ $softwareAdoptionRate }}%)</span>
            </div>
        </div>

        {{-- ── SECTION 1: ROOM × STUDENT DEPARTMENT MATRIX ────────────────────── --}}
        <div class="space-y-3">
            <div class="border-b border-gray-200 pb-1">
                <h3 class="font-bold text-sm text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <span>1. Room Utilization by Student Department (Cross-Matrix Audit)</span>
                </h3>
                <p class="text-xs text-gray-500">
                    Distribution of laboratory contact hours across academic departments (DECET, DOMIT, DEMET).
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-xs">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Room Name</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Type / Home Dept</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700">Sessions</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700">Total Hours</th>
                            <th class="px-3 py-2 text-center font-bold text-blue-800 bg-blue-50/70">DECET Usage</th>
                            <th class="px-3 py-2 text-center font-bold text-purple-800 bg-purple-50/70">DOMIT Usage</th>
                            <th class="px-3 py-2 text-center font-bold text-amber-800 bg-amber-50/70">DEMET Usage</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Top Subjects / Software</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($roomAuditMatrix as $matrix)
                            <tr class="{{ $loop->even ? 'bg-gray-50/50' : 'bg-white' }}">
                                <td class="px-3 py-2 font-bold text-gray-900 whitespace-nowrap">
                                    {{ $matrix['room']->name }}
                                </td>
                                <td class="px-3 py-2 text-gray-600 whitespace-nowrap">
                                    <span class="font-medium">{{ $matrix['room']->isLab() ? '🔬 Lab' : ($matrix['room']->isOffice() ? '🏢 Office' : '📖 Lecture') }}</span>
                                    <span class="text-gray-400">({{ $matrix['room']->departmentShort() }})</span>
                                </td>
                                <td class="px-3 py-2 text-center font-semibold text-gray-800">
                                    {{ $matrix['sessions'] }}
                                </td>
                                <td class="px-3 py-2 text-center font-black text-gray-900 whitespace-nowrap">
                                    {{ $matrix['total_hours'] }}h
                                </td>
                                <td class="px-3 py-2 text-center bg-blue-50/30 whitespace-nowrap">
                                    @if ($matrix['decet_hours'] > 0)
                                        <span class="font-bold text-blue-700">{{ $matrix['decet_hours'] }}h</span>
                                        <span class="text-gray-400 text-[10px]">({{ $matrix['decet_pct'] }}%)</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center bg-purple-50/30 whitespace-nowrap">
                                    @if ($matrix['domit_hours'] > 0)
                                        <span class="font-bold text-purple-700">{{ $matrix['domit_hours'] }}h</span>
                                        <span class="text-gray-400 text-[10px]">({{ $matrix['domit_pct'] }}%)</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center bg-amber-50/30 whitespace-nowrap">
                                    @if ($matrix['demet_hours'] > 0)
                                        <span class="font-bold text-amber-700">{{ $matrix['demet_hours'] }}h</span>
                                        <span class="text-gray-400 text-[10px]">({{ $matrix['demet_pct'] }}%)</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-700 max-w-xs">
                                    <div class="truncate font-medium">
                                        {{ !empty($matrix['top_subjects']) ? implode(', ', $matrix['top_subjects']) : '—' }}
                                    </div>
                                    @if (!empty($matrix['software_used']))
                                        <div class="text-[10px] text-purple-700 truncate mt-0.5">
                                            💻 {{ implode(', ', $matrix['software_used']) }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-400">No room records match the current filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── SECTION 2: FACULTY LABORATORY WORKLOAD ────────────────────────── --}}
        <div class="space-y-3">
            <div class="border-b border-gray-200 pb-1">
                <h3 class="font-bold text-sm text-gray-900 uppercase tracking-wider">
                    2. Faculty Laboratory Utilization Workload
                </h3>
                <p class="text-xs text-gray-500">
                    Faculty contact hours conducted in university laboratories and classrooms for OPCR verification.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-xs">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Faculty / Borrower Name</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Dept</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700">Sessions</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700">Total Hours</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Rooms Utilized</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Subjects Taught</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Software Used</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($facultyAudit as $fac)
                            <tr class="{{ $loop->even ? 'bg-gray-50/50' : 'bg-white' }}">
                                <td class="px-3 py-2 font-bold text-gray-900 whitespace-nowrap">
                                    {{ $fac['name'] }}
                                </td>
                                <td class="px-3 py-2 font-semibold text-gray-700 whitespace-nowrap">
                                    {{ $fac['department'] }}
                                </td>
                                <td class="px-3 py-2 text-center font-semibold text-gray-800">
                                    {{ $fac['sessions'] }}
                                </td>
                                <td class="px-3 py-2 text-center font-black text-gray-900 whitespace-nowrap">
                                    {{ $fac['total_hours'] }}h
                                </td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                    {{ implode(', ', $fac['rooms']) }}
                                </td>
                                <td class="px-3 py-2 text-gray-700 max-w-xs truncate">
                                    {{ implode(', ', $fac['subjects']) }}
                                </td>
                                <td class="px-3 py-2 text-purple-700 text-[11px] whitespace-nowrap">
                                    {{ !empty($fac['software']) ? implode(', ', $fac['software']) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-gray-400">No faculty sessions recorded under current filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── SECTION 3: CURRICULUM SUBJECT UTILIZATION ─────────────────────── --}}
        <div class="space-y-3">
            <div class="border-b border-gray-200 pb-1">
                <h3 class="font-bold text-sm text-gray-900 uppercase tracking-wider">
                    3. Curriculum Course & Subject Contact Hours
                </h3>
                <p class="text-xs text-gray-500">
                    Documenting student hands-on contact hours generated per curriculum laboratory subject.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-xs">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Subject / Course Purpose</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Dept</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700">Sessions</th>
                            <th class="px-3 py-2 text-center font-bold text-gray-700">Hours Logged</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Designated Facility</th>
                            <th class="px-3 py-2 text-left font-bold text-gray-700">Assigned Faculty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($subjectAudit as $subj)
                            <tr class="{{ $loop->even ? 'bg-gray-50/50' : 'bg-white' }}">
                                <td class="px-3 py-2 font-bold text-gray-900">
                                    {{ $subj['subject'] }}
                                </td>
                                <td class="px-3 py-2 font-semibold text-gray-700 whitespace-nowrap">
                                    {{ $subj['department'] }}
                                </td>
                                <td class="px-3 py-2 text-center font-semibold text-gray-800">
                                    {{ $subj['sessions'] }}
                                </td>
                                <td class="px-3 py-2 text-center font-black text-gray-900 whitespace-nowrap">
                                    {{ $subj['total_hours'] }}h
                                </td>
                                <td class="px-3 py-2 text-gray-700 whitespace-nowrap">
                                    {{ implode(', ', $subj['rooms']) }}
                                </td>
                                <td class="px-3 py-2 text-gray-700 max-w-xs truncate">
                                    {{ implode(', ', $subj['faculties']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6 text-gray-400">No subject hours recorded under current filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── SECTION 4: OFFICIAL SIGN-OFF BLOCK ─────────────────────────────── --}}
        <div class="pt-8 border-t-2 border-gray-300 mt-12">
            <p class="text-xs text-gray-500 mb-8 italic">
                I hereby certify that the above laboratory and facilities utilization metrics are true, correct, and systematically recorded by the PUP-ITECH Lab Utilization Management System in adherence to university academic guidelines and Republic Act 10173 (Data Privacy Act of 2012).
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center text-xs">
                <div>
                    <div class="border-b border-gray-900 pb-1 mx-4">
                        <span class="font-bold uppercase text-gray-900 block">{{ auth()->user()->name }}</span>
                    </div>
                    <span class="text-gray-500 block mt-1">Prepared By:</span>
                    <span class="font-semibold text-gray-700">Laboratory Head / Custodian</span>
                </div>

                <div>
                    <div class="border-b border-gray-900 pb-1 mx-4">
                        <span class="font-bold uppercase text-gray-900 block">&nbsp;</span>
                    </div>
                    <span class="text-gray-500 block mt-1">Reviewed & Verified By:</span>
                    <span class="font-semibold text-gray-700">Department Chairperson</span>
                </div>

                <div>
                    <div class="border-b border-gray-900 pb-1 mx-4">
                        <span class="font-bold uppercase text-gray-900 block">&nbsp;</span>
                    </div>
                    <span class="text-gray-500 block mt-1">Approved By:</span>
                    <span class="font-semibold text-gray-700">Institute Director</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
