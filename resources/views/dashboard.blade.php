<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight">
                        📊 Facility & Tool Utilization Board
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        LIVE MONITORING
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Real-time room occupancy, equipment circulation, and laboratory turnstile tracking
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="text-xs text-gray-400 dark:text-gray-500 hidden md:inline">
                    Updated: {{ now()->format('g:i A') }}
                </span>
                @if (Auth::user()->isLabHead() || Auth::user()->isStudentAssistant())
                    <a href="{{ route('admin.transactions.create') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition">
                        <span>+</span>
                        <span>New Checkout</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- ── Overdue Alert Banner ────────────────────────────────────── --}}
            @if ($overdueCount > 0)
                <div class="bg-red-50 dark:bg-red-950/40 border-2 border-red-300 dark:border-red-800 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl mt-0.5">🚨</span>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-red-900 dark:text-red-200 text-sm sm:text-base">
                                    {{ $overdueCount }} Overdue Transaction{{ $overdueCount !== 1 ? 's' : '' }} Requiring Follow-up
                                    <span class="text-xs font-normal text-red-700 dark:text-red-400 ml-2">
                                        ({{ $overdueRoomCount }} Room{{ $overdueRoomCount !== 1 ? 's' : '' }}, {{ $overdueToolCount }} Tool{{ $overdueToolCount !== 1 ? 's' : '' }})
                                    </span>
                                </p>
                            </div>
                            <div class="mt-2 space-y-1.5">
                                @foreach ($overdueTransactions as $tx)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs sm:text-sm py-1 border-b border-red-100 dark:border-red-900/40 last:border-0">
                                        <span class="text-red-800 dark:text-red-300">
                                            <span class="font-semibold">{{ $tx->isRoom() ? '🏫 ' . ($tx->room?->name ?? 'Room') : '🔧 ' . ($tx->tool?->name ?? 'Tool') }}</span>
                                            — <span class="font-medium">{{ $tx->borrower_name }}</span>
                                            <span class="text-[11px] px-1.5 py-0.2 rounded bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 ml-1 font-semibold">
                                                {{ $tx->departmentShort() }}
                                            </span>
                                            <span class="text-red-600 dark:text-red-400 text-xs ml-1">
                                                (due {{ $tx->expected_return_at->diffForHumans() }})
                                            </span>
                                        </span>
                                        <div class="flex items-center gap-3 mt-1 sm:mt-0 shrink-0">
                                            <a href="{{ route('scan.return', $tx->id) }}"
                                               class="font-semibold text-orange-700 dark:text-orange-400 hover:underline">
                                                ↩ Mark Free
                                            </a>
                                            @can('admin')
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                                   class="text-red-600 dark:text-red-400 hover:underline font-medium">
                                                    Details →
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Utilization Summary Metrics ──────────────────────────────── --}}
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <span>⚡ Real-Time Utilization Summary</span>
                    </h3>
                    @if ($deptActive->isNotEmpty())
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[11px] text-gray-400 dark:text-gray-500">Active by Dept:</span>
                            @foreach ($deptActive as $dept => $count)
                                @php
                                    $short = match($dept) {
                                        'Department of Office Management and Information Technology' => 'DOMIT',
                                        'Department of Computer and Electronics Engineering Technology' => 'DECET',
                                        'Department of Electrical and Mechanical Engineering Technology' => 'DEMET',
                                        'Department of Civil and Railway Engineering Technology' => 'DCRET',
                                        'College of Science' => 'CS',
                                        default => substr($dept, 0, 10),
                                    };
                                @endphp
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 font-semibold border border-blue-200 dark:border-blue-800">
                                    {{ $short }}: <strong>{{ $count }}</strong>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    {{-- 1. Room Occupancy Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100 dark:border-gray-700 relative overflow-hidden flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                🏫 Rooms In Use
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $openRoomCount > 0 ? 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-300 font-bold' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium' }}">
                                {{ $openRoomCount > 0 ? 'Active' : 'All Free' }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-gray-100 tracking-tight">
                                {{ $openRoomCount }}
                                <span class="text-sm sm:text-base font-normal text-gray-400 dark:text-gray-500">/ {{ $totalRooms }}</span>
                            </div>
                            {{-- Mini Progress Bar --}}
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full mt-3 overflow-hidden">
                                <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full transition-all duration-500"
                                     style="width: {{ min(100, $roomUtilizationRate) }}%"></div>
                            </div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5 flex items-center justify-between font-medium">
                            <span class="text-emerald-600 dark:text-emerald-400">🟢 {{ $availableRoomsCount }} vacant</span>
                            <span>{{ $roomUtilizationRate }}% occupied</span>
                        </div>
                    </div>

                    {{-- 2. Tool Borrowings Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                🔧 Tool Borrows
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 font-bold">
                                Equipment
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl sm:text-4xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">
                                {{ $openToolCount }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Tools and accessories checked out
                            </div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5 flex items-center justify-between font-medium pt-2 border-t border-gray-100 dark:border-gray-700/60">
                            <span>📦 {{ $availableToolsCount }} free types</span>
                            <span>{{ $todayToolCount }} today</span>
                        </div>
                    </div>

                    {{-- 3. Overdue Items Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 sm:p-5 border {{ $overdueCount > 0 ? 'border-red-300 dark:border-red-700/80 bg-red-50/20' : 'border-gray-100 dark:border-gray-700' }} flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                🚨 Overdue Items
                            </span>
                            @if ($overdueCount > 0)
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 font-bold animate-pulse">
                                    Action Required
                                </span>
                            @else
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 font-semibold">
                                    Normal
                                </span>
                            @endif
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl sm:text-4xl font-black {{ $overdueCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }} tracking-tight">
                                {{ $overdueCount }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $overdueCount > 0 ? 'Items past expected return' : 'All items returned on schedule' }}
                            </div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5 flex items-center justify-between font-medium pt-2 border-t border-gray-100 dark:border-gray-700/60">
                            <span>{{ $overdueRoomCount }} room{{ $overdueRoomCount !== 1 ? 's' : '' }}</span>
                            <span>{{ $overdueToolCount }} tool{{ $overdueToolCount !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>

                    {{-- 4. Today's Turnover Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-4 sm:p-5 border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                📅 Today's Activity
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900/60 text-purple-800 dark:text-purple-300 font-bold">
                                Combined
                            </span>
                        </div>
                        <div class="mt-2">
                            <div class="text-3xl sm:text-4xl font-black text-indigo-600 dark:text-indigo-400 tracking-tight">
                                {{ $todayCount }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Total checkouts recorded today
                            </div>
                        </div>
                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-2.5 flex items-center justify-between font-medium pt-2 border-t border-gray-100 dark:border-gray-700/60">
                            <span>🏫 {{ $todayRoomCount }} rooms</span>
                            <span>🔧 {{ $todayToolCount }} tools</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- HERO: LIVE FACILITY ROOM STATUS BOARD                            --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                 x-data="{
                     filter: 'all',
                     floor: 'all',
                     search: '',
                     matchesFilter(isOccupied, isLab, isLecture, isOffice, location, haystack) {
                         if (this.filter === 'in_use' && !isOccupied) return false;
                         if (this.filter === 'available' && isOccupied) return false;
                         if (this.filter === 'labs' && !isLab) return false;
                         if (this.filter === 'lectures' && !isLecture) return false;
                         if (this.filter === 'offices' && !isOffice) return false;
                         if (this.floor !== 'all' && location !== this.floor) return false;
                         if (this.search.trim() !== '') {
                             const q = this.search.toLowerCase().trim();
                             return haystack.toLowerCase().includes(q);
                         }
                         return true;
                     }
                 }">
                {{-- Board Header --}}
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-gray-50 via-white to-gray-50 dark:from-gray-850 dark:via-gray-800 dark:to-gray-850">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">🏫</span>
                                <div>
                                    <h3 class="font-black text-lg text-gray-900 dark:text-gray-100 tracking-tight flex items-center gap-2">
                                        <span>Live Facility Room Board</span>
                                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200">
                                            {{ $openRoomCount }} of {{ $totalRooms }} In Use
                                        </span>
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Click any vacant room to immediately start a checkout, or manage active sessions
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Search and Floor Selector --}}
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Instant Search --}}
                            <div class="relative min-w-[220px] flex-1 sm:flex-initial">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400 text-xs">
                                    🔍
                                </span>
                                <input type="text"
                                       x-model="search"
                                       placeholder="Search room, faculty, subject..."
                                       class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500 transition">
                            </div>

                            {{-- Floor Filter --}}
                            <select x-model="floor"
                                    class="py-1.5 px-3 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <option value="all">🏢 All Floors</option>
                                @foreach ($floors as $fl)
                                    <option value="{{ $fl }}">{{ $fl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Filter Tabs Bar --}}
                    <div class="flex items-center gap-1.5 mt-4 overflow-x-auto pb-1 text-xs">
                        <button type="button"
                                @click="filter = 'all'"
                                :class="filter === 'all' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 font-bold shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1">
                            <span>All Rooms</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/10 dark:bg-black/20">{{ $totalRooms }}</span>
                        </button>

                        <button type="button"
                                @click="filter = 'in_use'"
                                :class="filter === 'in_use' ? 'bg-red-600 text-white font-bold shadow-xs' : 'bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/60 border border-red-200 dark:border-red-900'"
                                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1">
                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                            <span>In Use</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-red-200/60 dark:bg-red-900 text-red-900 dark:text-red-200 font-bold">{{ $openRoomCount }}</span>
                        </button>

                        <button type="button"
                                @click="filter = 'available'"
                                :class="filter === 'available' ? 'bg-emerald-600 text-white font-bold shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-900'"
                                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Available</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-emerald-200/60 dark:bg-emerald-900 text-emerald-900 dark:text-emerald-200 font-bold">{{ $availableRoomsCount }}</span>
                        </button>

                        <button type="button"
                                @click="filter = 'labs'"
                                :class="filter === 'labs' ? 'bg-blue-600 text-white font-bold shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1">
                            <span>🔬 Computer Labs</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/10 dark:bg-black/20">{{ $allRooms->filter(fn($r) => $r->isLab())->count() }}</span>
                        </button>

                        <button type="button"
                                @click="filter = 'lectures'"
                                :class="filter === 'lectures' ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1">
                            <span>📖 Lecture Rooms</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/10 dark:bg-black/20">{{ $allRooms->filter(fn($r) => $r->isLecture())->count() }}</span>
                        </button>

                        <button type="button"
                                @click="filter = 'offices'"
                                :class="filter === 'offices' ? 'bg-purple-600 text-white font-bold shadow-xs' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1">
                            <span>🏢 Lab Offices</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/10 dark:bg-black/20">{{ $allRooms->filter(fn($r) => $r->isOffice())->count() }}</span>
                        </button>
                    </div>
                </div>

                {{-- Room Cards Grid --}}
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($allRooms as $room)
                        @php
                            $activeTx = $room->transactions->first();
                            $isOcc = (bool) $activeTx;
                            $concurrentCount = $room->transactions->count();
                            $searchHaystack = $room->name . ' ' .
                                ($room->location ?? '') . ' ' .
                                ($room->department ?? '') . ' ' .
                                ($isOcc ? ($activeTx->borrower_name . ' ' . $activeTx->subject . ' ' . $activeTx->softwareSummary() . ' ' . $activeTx->items->pluck('tool.name')->join(' ')) : 'vacant available');
                        @endphp

                        <div x-show="matchesFilter({{ $isOcc ? 'true' : 'false' }}, {{ $room->isLab() ? 'true' : 'false' }}, {{ $room->isLecture() ? 'true' : 'false' }}, {{ $room->isOffice() ? 'true' : 'false' }}, '{{ addslashes($room->location ?? '') }}', '{{ addslashes(strtolower($searchHaystack)) }}')"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="rounded-2xl border transition-all duration-200 flex flex-col justify-between {{ $isOcc ? 'border-2 border-red-400 dark:border-red-600 bg-gradient-to-b from-red-50/60 via-white to-white dark:from-red-950/25 dark:via-gray-800 dark:to-gray-800 shadow-sm' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-emerald-400 dark:hover:border-emerald-500 shadow-xs hover:shadow-md' }} p-4">

                            {{-- ── CARD TOP ────────────────────────────────── --}}
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="font-black text-lg text-gray-900 dark:text-gray-100 tracking-tight">
                                                {{ $room->name }}
                                            </h4>
                                            @if ($concurrentCount > 1)
                                                <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                    👥 {{ $concurrentCount }} Classes
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                            <span>📍 {{ $room->location ?? 'ITECH Campus' }}</span>
                                        </div>
                                    </div>

                                    {{-- Status Badge --}}
                                    @if ($isOcc)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300 border border-red-300 dark:border-red-800 shrink-0">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                            </span>
                                            IN USE
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shrink-0">
                                            🟢 AVAILABLE
                                        </span>
                                    @endif
                                </div>

                                {{-- Department & Category Badges --}}
                                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        {{ $room->departmentShort() }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        {{ $room->isLab() ? '🔬 Lab' : ($room->isLecture() ? '📖 Lecture' : '🏢 Office') }}
                                    </span>
                                    @if ($room->has_wifi)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-cyan-50 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-300" title="WiFi Available">
                                            📶 WiFi
                                        </span>
                                    @endif
                                </div>

                                {{-- ── OCCUPIED STATE DETAILS ───────────────── --}}
                                @if ($isOcc)
                                    <div class="mt-3 p-3 rounded-xl bg-red-50/70 dark:bg-red-950/40 border border-red-100 dark:border-red-900/50 space-y-1.5">
                                        {{-- Faculty Borrower --}}
                                        <div class="flex items-center gap-1.5 text-xs text-red-950 dark:text-red-200 font-bold">
                                            <span class="text-sm">👤</span>
                                            <span class="truncate" title="{{ $activeTx->borrower_name }}">{{ $activeTx->borrower_name }}</span>
                                        </div>

                                        {{-- Subject --}}
                                        @if ($activeTx->subject)
                                            <div class="text-[11px] text-gray-700 dark:text-gray-300 font-medium truncate" title="{{ $activeTx->subject }}">
                                                📚 {{ $activeTx->subject }}
                                            </div>
                                        @endif

                                        {{-- Elapsed Time --}}
                                        <div class="text-[11px] text-gray-600 dark:text-gray-400 flex items-center justify-between pt-1 border-t border-red-100/60 dark:border-red-900/40">
                                            <span>⏱️ Started: <strong>{{ $activeTx->checked_out_at->format('g:i A') }}</strong></span>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                                {{ $activeTx->checked_out_at->diffForHumans(null, true) }}
                                            </span>
                                        </div>

                                        {{-- Overdue Warning Badge --}}
                                        @if ($activeTx->isOverdue())
                                            <div class="text-[10px] font-bold text-red-700 dark:text-red-300 bg-red-200/90 dark:bg-red-900/80 px-2 py-0.5 rounded text-center animate-pulse">
                                                ⚠️ OVERDUE (due {{ $activeTx->expected_return_at->diffForHumans() }})
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Software utilized badges --}}
                                    @if ($activeTx->hasSoftwareUtilized())
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @foreach ((array) $activeTx->software_utilized as $sw)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                    💻 {{ $sw }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Borrowed Accessories/Tools --}}
                                    @if ($activeTx->items->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 mt-1.5">
                                            @foreach ($activeTx->items as $it)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                    🔌 {{ $it->tool?->name ?? 'Tool' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                {{-- ── VACANT STATE DETAILS ─────────────────── --}}
                                @else
                                    <div class="mt-4 py-3 px-3 rounded-xl bg-gray-50/70 dark:bg-gray-750/50 border border-dashed border-gray-200 dark:border-gray-700 text-center">
                                        <span class="text-lg">✨</span>
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1">Room is Vacant</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Ready for lecture or lab session</p>
                                    </div>
                                @endif
                            </div>

                            {{-- ── CARD FOOTER / ACTIONS ─────────────────────── --}}
                            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/80">
                                @if ($isOcc)
                                    @if (Auth::user()->isLabHead() || Auth::user()->isStudentAssistant())
                                        <div class="flex items-center justify-between gap-2">
                                            <a href="{{ route('scan.return', $activeTx->id) }}"
                                               class="flex-1 text-center py-1.5 px-2 text-xs font-semibold rounded-lg bg-orange-600 hover:bg-orange-700 text-white shadow-xs transition">
                                                ↩ Vacate Room
                                            </a>
                                            <a href="{{ route('admin.transactions.show', $activeTx->id) }}"
                                               class="py-1.5 px-2 text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline shrink-0">
                                                Details →
                                            </a>
                                        </div>
                                    @else
                                        <div class="text-xs text-center text-red-600 dark:text-red-400 font-medium">
                                            ● In Use by {{ $activeTx->borrower_name }}
                                        </div>
                                    @endif
                                @else
                                    @if (Auth::user()->isLabHead() || Auth::user()->isStudentAssistant())
                                        <a href="{{ route('admin.transactions.create', ['room_id' => $room->id]) }}"
                                           class="w-full inline-flex items-center justify-center gap-1.5 py-1.5 px-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-xs hover:shadow transition">
                                            <span>+ Check In Room</span>
                                        </a>
                                    @else
                                        <div class="text-xs text-center text-emerald-600 dark:text-emerald-400 font-semibold">
                                            ✓ Available for Class
                                        </div>
                                    @endif
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- CIRCULATING EQUIPMENT & TOOL BORROWS SECTION                     --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-emerald-50/40 dark:bg-gray-850">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🔧</span>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm sm:text-base">
                                Circulating Equipment & Tool Borrows
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Active equipment, remotes, cables, and accessories currently borrowed
                            </p>
                        </div>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 font-bold self-start sm:self-auto">
                        {{ $borrowedTools->count() }} Equipment Item{{ $borrowedTools->count() !== 1 ? 's' : '' }} Checked Out
                    </span>
                </div>

                @if ($borrowedTools->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 dark:text-gray-500">
                        <p class="text-3xl mb-1.5">✅</p>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">All tools, remotes, and accessories are currently in inventory</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">No equipment borrows active at this time.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto">
                        @foreach ($borrowedTools as $tool)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-750/50 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                            {{ $tool->name }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ $tool->departmentShort() }}
                                        </span>
                                        @if ($tool->category)
                                            <span class="text-xs text-gray-400 dark:text-gray-500 hidden md:inline">• {{ $tool->category }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300">
                                            {{ $tool->borrowed_quantity }}/{{ $tool->total_quantity }} borrowed
                                        </span>
                                        @if ($tool->available_quantity > 0)
                                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                                ({{ $tool->available_quantity }} free)
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Borrowers list --}}
                                <div class="space-y-1 mt-2 pl-2 border-l-2 border-emerald-300 dark:border-emerald-700">
                                    {{-- Direct transaction checkouts --}}
                                    @foreach ($tool->transactions as $tx)
                                        <div class="text-xs text-gray-700 dark:text-gray-300 flex items-center justify-between py-0.5">
                                            <div>
                                                👤 <span class="font-bold">{{ $tx->borrower_name }}</span>
                                                <span class="text-gray-400 text-[11px]">({{ $tx->departmentShort() }})</span>
                                                @if ($tx->subject)
                                                    <span class="text-gray-500 dark:text-gray-400 text-[11px]">— {{ $tx->subject }}</span>
                                                @endif
                                                @if ($tx->quantity > 1)<span class="font-bold text-blue-600 ml-1">×{{ $tx->quantity }}</span>@endif
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-gray-400 text-[11px]">{{ $tx->checked_out_at->format('g:i A') }}</span>
                                                @if (auth()->user()->isLabHead() || auth()->user()->isStudentAssistant())
                                                    <a href="{{ route('scan.return', $tx->id) }}" class="text-[11px] font-semibold text-orange-600 hover:underline">
                                                        ↩ Return
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Multi-item checkouts --}}
                                    @foreach ($tool->transactionItems as $item)
                                        @if ($item->transaction && !in_array($item->transaction->id, $tool->transactions->pluck('id')->toArray()))
                                            <div class="text-xs text-gray-700 dark:text-gray-300 flex items-center justify-between py-0.5">
                                                <div>
                                                    👤 <span class="font-bold">{{ $item->transaction->borrower_name }}</span>
                                                    <span class="text-gray-400 text-[11px]">({{ $item->transaction->departmentShort() }})</span>
                                                    @if ($item->transaction->room)
                                                        <span class="text-indigo-600 dark:text-indigo-400 text-[11px] font-medium">in {{ $item->transaction->room->name }}</span>
                                                    @endif
                                                    <span class="font-bold text-blue-600 ml-1">×{{ $item->remaining_quantity }}</span>
                                                </div>
                                                <div class="flex items-center gap-2 shrink-0">
                                                    <span class="text-gray-400 text-[11px]">{{ $item->transaction->checked_out_at->format('g:i A') }}</span>
                                                    @if (auth()->user()->isLabHead() || auth()->user()->isStudentAssistant())
                                                        <a href="{{ route('scan.return', $item->transaction->id) }}" class="text-[11px] font-semibold text-orange-600 hover:underline">
                                                            ↩ Return
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ═════════════════════════════════════════════════════════════════ --}}
            {{-- ACTIVITY LOGS & UTILIZATION (TABBED)                             --}}
            {{-- ═════════════════════════════════════════════════════════════════ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                 x-data="{ activeTab: 'rooms' }">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm sm:text-base">
                            📋 Activity Logs & Utilization
                            @if (auth()->user()->isFaculty())
                                <span class="text-xs font-normal text-gray-400 ml-1">(your activity)</span>
                            @endif
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Separated chronological logs for facility occupancy and equipment loans
                        </p>
                    </div>

                    {{-- Tabs: Rooms vs Tools vs All --}}
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700/60 p-1 rounded-xl">
                        <button type="button"
                                @click="activeTab = 'rooms'"
                                :class="activeTab === 'rooms' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5">
                            <span>🏫 Rooms</span>
                            <span class="text-[10px] py-0.2 px-1.5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                {{ $recentRoomTransactions->count() }}
                            </span>
                        </button>
                        <button type="button"
                                @click="activeTab = 'tools'"
                                :class="activeTab === 'tools' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5">
                            <span>🔧 Tools</span>
                            <span class="text-[10px] py-0.2 px-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                {{ $recentToolTransactions->count() }}
                            </span>
                        </button>
                        <button type="button"
                                @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5">
                            <span>📋 All Feed</span>
                        </button>
                    </div>
                </div>

                {{-- TAB 1: Room Checkouts Table --}}
                <div x-show="activeTab === 'rooms'">
                    @if ($recentRoomTransactions->isEmpty())
                        <div class="px-5 py-10 text-center text-gray-400">
                            <p class="text-sm">No recent room checkouts.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">#</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Room</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Faculty / Borrower</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Department</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Subject / Purpose</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Time In</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentRoomTransactions as $tx)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                            <td class="px-5 py-3 text-xs text-gray-400">#{{ $tx->id }}</td>
                                            <td class="px-5 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">
                                                {{ $tx->room?->name ?? 'Room' }}
                                            </td>
                                            <td class="px-5 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">
                                                {{ $tx->borrower_name }}
                                            </td>
                                            <td class="px-5 py-3 text-xs">
                                                <span class="px-2 py-0.5 rounded font-semibold bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                    {{ $tx->departmentShort() }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-xs text-gray-600 dark:text-gray-400 max-w-xs">
                                                <div class="truncate font-medium text-gray-800 dark:text-gray-200">{{ $tx->subject ?? '—' }}</div>
                                                @if ($tx->hasSoftwareUtilized())
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach ((array) $tx->software_utilized as $sw)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                                💻 {{ $sw }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $tx->checked_out_at->format('M d, g:i A') }}
                                            </td>
                                            <td class="px-5 py-3">
                                                @if ($tx->status === 'open')
                                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">In Use</span>
                                                @elseif ($tx->status === 'returned')
                                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">Vacated</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 animate-pulse">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right text-xs">
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB 2: Tool Borrowings Table --}}
                <div x-show="activeTab === 'tools'">
                    @if ($recentToolTransactions->isEmpty())
                        <div class="px-5 py-10 text-center text-gray-400">
                            <p class="text-sm">No recent tool borrowings.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">#</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Equipment / Tools</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Borrower</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Department</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Subject / Purpose</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Borrowed At</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentToolTransactions as $tx)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                            <td class="px-5 py-3 text-xs text-gray-400">#{{ $tx->id }}</td>
                                            <td class="px-5 py-3 text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                                                @if ($tx->items->isNotEmpty())
                                                    <div class="space-y-0.5">
                                                        @foreach ($tx->items as $item)
                                                            <div class="text-xs">
                                                                • {{ $item->tool?->name ?? 'Tool' }}
                                                                <span class="text-gray-500 font-normal">({{ $item->quantity_borrowed }} borrowed{{ $item->quantity_returned > 0 ? ', ' . $item->quantity_returned . ' returned' : '' }})</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    {{ $tx->tool?->name ?? 'Tool' }}
                                                    @if ($tx->quantity > 1)
                                                        <span class="text-xs font-normal text-gray-500">(×{{ $tx->quantity }})</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-sm font-medium text-gray-800 dark:text-gray-200">
                                                {{ $tx->borrower_name }}
                                            </td>
                                            <td class="px-5 py-3 text-xs">
                                                <span class="px-2 py-0.5 rounded font-semibold bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    {{ $tx->departmentShort() }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-xs text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                                {{ $tx->subject ?? '—' }}
                                            </td>
                                            <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $tx->checked_out_at->format('M d, g:i A') }}
                                            </td>
                                            <td class="px-5 py-3">
                                                @if ($tx->status === 'open')
                                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Out</span>
                                                @elseif ($tx->status === 'partially_returned')
                                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">Partial</span>
                                                @elseif ($tx->status === 'returned')
                                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">Returned</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 animate-pulse">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right text-xs">
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- TAB 3: All Feed Table --}}
                <div x-show="activeTab === 'all'">
                    @if ($recentTransactions->isEmpty())
                        <div class="px-5 py-10 text-center text-gray-400">
                            <p class="text-sm">No activity recorded yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Item / Facility</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Borrower</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Department</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Time</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentTransactions as $tx)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                            <td class="px-5 py-3 text-xs">
                                                @if ($tx->isRoom())
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Room</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">Tool</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $tx->subjectDescription() }}
                                            </td>
                                            <td class="px-5 py-3 text-sm text-gray-800 dark:text-gray-200">
                                                {{ $tx->borrower_name }}
                                            </td>
                                            <td class="px-5 py-3 text-xs">
                                                <span class="px-2 py-0.5 rounded font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                                    {{ $tx->departmentShort() }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $tx->checked_out_at->format('M d, g:i A') }}
                                            </td>
                                            <td class="px-5 py-3">
                                                @if ($tx->status === 'open')
                                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">In Use</span>
                                                @elseif ($tx->status === 'partially_returned')
                                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">Partial</span>
                                                @elseif ($tx->status === 'returned')
                                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">Returned</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 animate-pulse">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right text-xs">
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                    Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Footer link to full history --}}
                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs">
                    <span class="text-gray-500 dark:text-gray-400">Showing recent 10 transactions per category</span>
                    @if (auth()->user()->isLabHead() || auth()->user()->isStudentAssistant())
                        <a href="{{ route('admin.transactions.index') }}" class="font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            View Complete Transaction Archive & Filter by Department →
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
