<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.rooms.index') }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                    ← Back to Rooms
                </a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    <span>{{ $room->isComputerLab() ? '💻' : ($room->isEngineeringLab() ? '⚙️' : ($room->isOffice() ? '🏢' : '📖')) }}</span>
                    <span>{{ $room->name }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.qr.room', $room) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                    <span>📱 View QR Code</span>
                </a>
                <a href="{{ route('admin.rooms.edit', $room) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    <span>✏️ Edit Room</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Room Summary Card & Live Status --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Left: Room Details (2 cols) --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $room->name }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $room->isComputerLab() ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : ($room->isEngineeringLab() ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : ($room->isOffice() ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300')) }}">
                                    {{ $room->roomType() }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $room->department ?: 'General / Shared Facility' }}
                                @if ($room->department)
                                    <span class="font-semibold text-gray-700 dark:text-gray-300">({{ $room->departmentShort() }})</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-sm">
                        <div>
                            <span class="text-xs text-gray-400 block">Location / Floor</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $room->location ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">Seating Capacity</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $room->capacity ? $room->capacity . ' students' : '—' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block">Wi-Fi Status</span>
                            <span class="font-semibold {{ $room->has_wifi ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500' }}">
                                {{ $room->has_wifi ? '📶 Available' : 'No Wi-Fi' }}
                            </span>
                        </div>
                    </div>

                    @if ($room->wifi_notes)
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-750 border border-gray-100 dark:border-gray-700 text-xs">
                            <span class="font-semibold text-gray-700 dark:text-gray-300 block mb-0.5">Wi-Fi Notes & Instructions:</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $room->wifi_notes }}</p>
                        </div>
                    @endif

                    @if ($room->manual_url)
                        <div class="flex items-center gap-2 pt-2">
                            <span class="text-xs text-gray-400">Documentation:</span>
                            <a href="{{ $room->manual_url }}" target="_blank"
                               class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                <span>📖 Open Lab Manual / SOP Document</span>
                                <span>↗</span>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Right: Live Occupancy Status (1 col) --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">
                            Live Occupancy
                        </h4>

                        @if ($currentTx)
                            <div class="p-4 rounded-xl {{ $currentTx->isStale() ? 'bg-amber-50 dark:bg-amber-950/40 border border-amber-300' : 'bg-red-50 dark:bg-red-950/40 border border-red-300' }} space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold {{ $currentTx->isStale() ? 'text-amber-800 dark:text-amber-300' : 'text-red-800 dark:text-red-300' }}">
                                        {{ $currentTx->isStale() ? '⚠️ Unclosed Past Session' : '🔴 Currently In Use' }}
                                    </span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-white/70 dark:bg-gray-800/70">
                                        #{{ $currentTx->id }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-800 dark:text-gray-200">
                                    <p class="font-bold text-sm">{{ $currentTx->borrower_name }}</p>
                                    @if ($currentTx->subject)
                                        <p class="text-gray-600 dark:text-gray-400 mt-0.5">📚 {{ $currentTx->subject }}</p>
                                    @endif
                                    <p class="text-gray-500 dark:text-gray-400 text-[11px] mt-1">
                                        ⏱️ Since {{ $currentTx->checked_out_at->format('M d, g:i A') }} ({{ $currentTx->checked_out_at->diffForHumans() }})
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 text-center space-y-1">
                                <span class="text-2xl">🟢</span>
                                <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Room is Vacant</p>
                                <p class="text-xs text-emerald-700 dark:text-emerald-400">Ready for class or lab session</p>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 mt-6">
                        @if ($currentTx)
                            <form method="POST" action="{{ route('admin.transactions.return', $currentTx->id) }}"
                                  onsubmit="return confirm('Vacate {{ $room->name }} now?');">
                                @csrf
                                <button type="submit" class="w-full py-2 px-3 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer">
                                    ↩ Vacate Room Now
                                </button>
                            </form>
                        @else
                            <a href="{{ route('admin.transactions.create', ['room_id' => $room->id]) }}"
                               class="w-full inline-flex items-center justify-center py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                                + Check In Room
                            </a>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Recent Utilization History --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>📋</span>
                            <span>Recent Room Checkouts & Sessions</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Last 10 utilization logs recorded for {{ $room->name }}.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-750 text-gray-500 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="px-5 py-3 text-left">Session</th>
                                <th class="px-5 py-3 text-left">Borrower / Instructor</th>
                                <th class="px-5 py-3 text-left">Course / Subject</th>
                                <th class="px-5 py-3 text-left">Checked Out</th>
                                <th class="px-5 py-3 text-left">Returned At</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse ($recentTransactions as $tx)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/50">
                                    <td class="px-5 py-3 font-mono font-bold text-gray-900 dark:text-white">
                                        #{{ $tx->id }}
                                    </td>
                                    <td class="px-5 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $tx->borrower_name }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                        {{ $tx->subject ?: '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                        {{ $tx->checked_out_at->format('M d, Y g:i A') }}
                                    </td>
                                    <td class="px-5 py-3 text-gray-600 dark:text-gray-400">
                                        {{ $tx->returned_at ? $tx->returned_at->format('M d, Y g:i A') : 'Active' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($tx->status === 'returned')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                                Completed
                                            </span>
                                        @elseif ($tx->status === 'overdue')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                                Open
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                           class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                            View →
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-8 text-center text-gray-400">
                                        No transaction history recorded yet for this room.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
