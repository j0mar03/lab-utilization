<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    📊 Laboratory & Tool Utilization Dashboard
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Real-time room occupancy and equipment circulation tracking across departments
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:inline">
                    Last refreshed: {{ now()->format('M d, g:i A') }}
                </span>
                @if (Auth::user()->isLabHead() || Auth::user()->isStudentAssistant())
                    <a href="{{ route('admin.transactions.create') }}"
                       class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                        + New Checkout
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Overdue Alert Banner ────────────────────────────────────── --}}
            @if ($overdueCount > 0)
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl mt-0.5">🚨</span>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-red-800 dark:text-red-300">
                                    {{ $overdueCount }} Overdue Item{{ $overdueCount !== 1 ? 's' : '' }}
                                    <span class="text-xs font-normal text-red-600 dark:text-red-400 ml-2">
                                        ({{ $overdueRoomCount }} Room{{ $overdueRoomCount !== 1 ? 's' : '' }}, {{ $overdueToolCount }} Tool{{ $overdueToolCount !== 1 ? 's' : '' }})
                                    </span>
                                </p>
                            </div>
                            <div class="mt-2 space-y-1">
                                @foreach ($overdueTransactions as $tx)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-red-700 dark:text-red-400">
                                            <span class="font-medium">{{ $tx->isRoom() ? '🏫 ' : '🔧 ' }}{{ $tx->borrower_name }}</span>
                                            <span class="text-xs px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-200 ml-1">
                                                {{ $tx->departmentShort() }}
                                            </span>
                                            — {{ $tx->subjectDescription() }}
                                            <span class="text-red-400 ml-1 text-xs">
                                                (due {{ $tx->expected_return_at->diffForHumans() }})
                                            </span>
                                        </span>
                                        @can('admin')
                                        <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                           class="text-xs text-red-600 hover:underline ml-3">Details →</a>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Quick Actions (Lab Head) ───────────────────────────────── --}}
            @if (auth()->user()->isLabHead())
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ route('admin.qr.rooms') }}"
                       class="flex flex-col items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3.5 px-3 text-center transition shadow-sm">
                        <span class="text-2xl mb-1">🏫</span>
                        <span class="text-sm font-semibold">Room QR Codes</span>
                    </a>
                    <a href="{{ route('admin.qr.tools') }}"
                       class="flex flex-col items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3.5 px-3 text-center transition shadow-sm">
                        <span class="text-2xl mb-1">🔧</span>
                        <span class="text-sm font-semibold">Tool QR Codes</span>
                    </a>
                    <a href="{{ route('admin.transactions.index') }}"
                       class="flex flex-col items-center justify-center bg-gray-700 hover:bg-gray-800 text-white rounded-xl py-3.5 px-3 text-center transition shadow-sm">
                        <span class="text-2xl mb-1">📋</span>
                        <span class="text-sm font-semibold">Transaction Logs</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}"
                       class="flex flex-col items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-3.5 px-3 text-center transition shadow-sm">
                        <span class="text-2xl mb-1">📈</span>
                        <span class="text-sm font-semibold">Utilization Reports</span>
                    </a>
                </div>
            @endif

            {{-- ── Separated Key Stat Cards ─────────────────────────────────── --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        ⚡ Real-Time Utilization Status
                    </h3>
                    @if ($deptActive->isNotEmpty())
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs text-gray-400">Active by Dept:</span>
                            @foreach ($deptActive as $dept => $count)
                                @php
                                    $short = match($dept) {
                                        'Department of Office Management and Information Technology' => 'DOMIT',
                                        'Department of Computer and Electronics Engineering Technology' => 'DCEET',
                                        'Department of Electrical and Mechanical Engineering Technology' => 'DEMET',
                                        'Department of Civil and Railway Engineering Technology' => 'DCRET',
                                        'College of Science' => 'CS',
                                        default => substr($dept, 0, 10),
                                    };
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-medium border border-blue-200 dark:border-blue-800">
                                    {{ $short }}: <strong>{{ $count }}</strong>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- 1. Room Occupancy --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                🏫 Rooms In Use
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-semibold">
                                Room
                            </span>
                        </div>
                        <div class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-2">
                            {{ $openRoomCount }}
                            <span class="text-sm font-normal text-gray-400">/ {{ $totalRooms }}</span>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                            <span>🟢 {{ $availableRoomsCount }} available</span>
                            <span>{{ $todayRoomCount }} today</span>
                        </div>
                    </div>

                    {{-- 2. Tool Borrowings --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                🔧 Active Tool Borrows
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 font-semibold">
                                Tool
                            </span>
                        </div>
                        <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                            {{ $openToolCount }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                            <span>📦 {{ $availableToolsCount }} free types</span>
                            <span>{{ $todayToolCount }} today</span>
                        </div>
                    </div>

                    {{-- 3. Overdue Items --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border {{ $overdueCount > 0 ? 'border-red-300 dark:border-red-700' : 'border-gray-100 dark:border-gray-700' }}">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                🚨 Overdue Items
                            </div>
                            @if ($overdueCount > 0)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 font-bold animate-pulse">
                                    Action
                                </span>
                            @endif
                        </div>
                        <div class="text-3xl font-black {{ $overdueCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }} mt-2">
                            {{ $overdueCount }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $overdueRoomCount }} room{{ $overdueRoomCount !== 1 ? 's' : '' }}, {{ $overdueToolCount }} tool checkout{{ $overdueToolCount !== 1 ? 's' : '' }}
                        </div>
                    </div>

                    {{-- 4. Today's Total Activity --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                📅 Total Today
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 font-semibold">
                                Combined
                            </span>
                        </div>
                        <div class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mt-2">
                            {{ $todayCount }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $todayRoomCount }} room / {{ $todayToolCount }} tool checkouts
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Main Content: Current Occupancy + Borrowed Tools ──────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Current Room Occupancy --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-blue-50/40 dark:bg-gray-750">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🏫</span>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Live Room Occupancy</h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 font-semibold">
                            {{ $occupiedRooms->count() }} of {{ $totalRooms }} Active
                        </span>
                    </div>

                    @if ($occupiedRooms->isEmpty())
                        <div class="px-5 py-12 text-center text-gray-400 dark:text-gray-500 my-auto">
                            <p class="text-3xl mb-2">🟢</p>
                            <p class="text-sm font-medium">All rooms are currently vacant and available</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            @foreach ($occupiedRooms as $room)
                                @foreach ($room->transactions as $tx)
                                    <div class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                                        {{ $room->name }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                        {{ $tx->departmentShort() }}
                                                    </span>
                                                </div>
                                                <div class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">
                                                    <span class="font-medium">{{ $tx->borrower_name }}</span>
                                                    @if ($tx->subject)
                                                        — <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $tx->subject }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                                                    {{ $tx->checked_out_at->format('g:i A') }}
                                                </div>
                                                <div class="text-[11px] {{ $tx->isOverdue() ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                                                    {{ $tx->checked_out_at->diffForHumans(null, true) }} in use
                                                    @if ($tx->isOverdue()) ⚠️ @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if (auth()->user()->isLabHead() || auth()->user()->isStudentAssistant())
                                            <div class="mt-2 flex items-center gap-3 text-xs">
                                                <a href="{{ route('scan.return', $tx->id) }}"
                                                   class="font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400">
                                                    ↩ Mark Free
                                                </a>
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                                   class="text-blue-600 hover:text-blue-700 dark:text-blue-400">
                                                    View Details
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Currently Borrowed Tools --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-emerald-50/40 dark:bg-gray-750">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🔧</span>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Live Tool & Equipment Borrows</h3>
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 font-semibold">
                            {{ $borrowedTools->count() }} Equipment Types Out
                        </span>
                    </div>

                    @if ($borrowedTools->isEmpty())
                        <div class="px-5 py-12 text-center text-gray-400 dark:text-gray-500 my-auto">
                            <p class="text-3xl mb-2">✅</p>
                            <p class="text-sm font-medium">All tools and equipment are currently available</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            @foreach ($borrowedTools as $tool)
                                <div class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                                                {{ $tool->name }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                {{ $tool->departmentShort() }}
                                            </span>
                                        </div>
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300">
                                            {{ $tool->borrowed_quantity }}/{{ $tool->total_quantity }} borrowed
                                        </span>
                                    </div>

                                    {{-- Direct transaction checkouts --}}
                                    @foreach ($tool->transactions as $tx)
                                        <div class="text-xs text-gray-600 dark:text-gray-300 ml-1 mb-1 flex items-center justify-between">
                                            <div>
                                                👤 <span class="font-medium">{{ $tx->borrower_name }}</span>
                                                <span class="text-gray-400 text-[11px]">({{ $tx->departmentShort() }})</span>
                                                @if ($tx->quantity > 1)<span class="font-semibold text-blue-600">×{{ $tx->quantity }}</span>@endif
                                            </div>
                                            <span class="text-gray-400 text-[11px]">{{ $tx->checked_out_at->format('g:i A') }}</span>
                                        </div>
                                    @endforeach

                                    {{-- Multi-item transaction checkouts --}}
                                    @foreach ($tool->transactionItems as $item)
                                        @if ($item->transaction && !in_array($item->transaction->id, $tool->transactions->pluck('id')->toArray()))
                                            <div class="text-xs text-gray-600 dark:text-gray-300 ml-1 mb-1 flex items-center justify-between">
                                                <div>
                                                    👤 <span class="font-medium">{{ $item->transaction->borrower_name }}</span>
                                                    <span class="text-gray-400 text-[11px]">({{ $item->transaction->departmentShort() }})</span>
                                                    <span class="font-semibold text-blue-600">×{{ $item->remaining_quantity }}</span>
                                                </div>
                                                <span class="text-gray-400 text-[11px]">{{ $item->transaction->checked_out_at->format('g:i A') }}</span>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if ($tool->available_quantity > 0)
                                        <div class="text-[11px] text-green-600 dark:text-green-400 mt-1">
                                            ✓ {{ $tool->available_quantity }} units still in stock
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- ── Separated Recent Transactions Section ────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
                 x-data="{ activeTab: 'rooms' }">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                            📋 Activity Logs & Utilization
                            @if (auth()->user()->isFaculty())
                                <span class="text-xs font-normal text-gray-400 ml-1">(your activity)</span>
                            @endif
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Separated logs for facility usage and equipment borrowing
                        </p>
                    </div>

                    {{-- Tabs: Rooms vs Tools vs All --}}
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700/60 p-1 rounded-lg">
                        <button type="button"
                                @click="activeTab = 'rooms'"
                                :class="activeTab === 'rooms' ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1 rounded-md text-xs transition flex items-center gap-1.5">
                            <span>🏫</span>
                            <span>Room Checkouts</span>
                            <span class="text-[10px] py-0.2 px-1.5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                {{ $recentRoomTransactions->count() }}
                            </span>
                        </button>
                        <button type="button"
                                @click="activeTab = 'tools'"
                                :class="activeTab === 'tools' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1 rounded-md text-xs transition flex items-center gap-1.5">
                            <span>🔧</span>
                            <span>Tool Borrowings</span>
                            <span class="text-[10px] py-0.2 px-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                {{ $recentToolTransactions->count() }}
                            </span>
                        </button>
                        <button type="button"
                                @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-bold shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                                class="px-3 py-1 rounded-md text-xs transition flex items-center gap-1.5">
                            <span>📋</span>
                            <span>All Feed</span>
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
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Room</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Faculty / Borrower</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Department</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subject / Purpose</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time In</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentRoomTransactions as $tx)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
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
                                            <td class="px-5 py-3 text-xs text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                                {{ $tx->subject ?? '—' }}
                                            </td>
                                            <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $tx->checked_out_at->format('M d, g:i A') }}
                                            </td>
                                            <td class="px-5 py-3">
                                                @if ($tx->status === 'open')
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">In Use</span>
                                                @elseif ($tx->status === 'returned')
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Vacated</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right text-xs">
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
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
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Equipment / Tools</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Borrower</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Department</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subject / Purpose</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Borrowed At</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentToolTransactions as $tx)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
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
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Out</span>
                                                @elseif ($tx->status === 'partially_returned')
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">Partial</span>
                                                @elseif ($tx->status === 'returned')
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Returned</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right text-xs">
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
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
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Item / Facility</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Borrower</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Department</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($recentTransactions as $tx)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                            <td class="px-5 py-3 text-xs">
                                                @if ($tx->isRoom())
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Room</span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">Tool</span>
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
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Open</span>
                                                @elseif ($tx->status === 'partially_returned')
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">Partial</span>
                                                @elseif ($tx->status === 'returned')
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Returned</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right text-xs">
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
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
                        <a href="{{ route('admin.transactions.index') }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                            View Complete Transaction Archive & Filter by Department →
                        </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
