<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📊 Lab Dashboard
            </h2>
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
                            <p class="font-bold text-red-800 dark:text-red-300">
                                {{ $overdueCount }} Overdue Item{{ $overdueCount !== 1 ? 's' : '' }} — Follow Up Required
                            </p>
                            <div class="mt-2 space-y-1">
                                @foreach ($overdueTransactions as $tx)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-red-700 dark:text-red-400">
                                            <strong>{{ $tx->borrower_name }}</strong>
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
                       class="flex flex-col items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-4 px-3 text-center transition">
                        <span class="text-2xl mb-1">🏫</span>
                        <span class="text-sm font-semibold">Print Room QRs</span>
                    </a>
                    <a href="{{ route('admin.qr.tools') }}"
                       class="flex flex-col items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-4 px-3 text-center transition">
                        <span class="text-2xl mb-1">🔧</span>
                        <span class="text-sm font-semibold">Print Tool QRs</span>
                    </a>
                    <a href="{{ route('admin.transactions.index') }}"
                       class="flex flex-col items-center justify-center bg-gray-600 hover:bg-gray-700 text-white rounded-xl py-4 px-3 text-center transition">
                        <span class="text-2xl mb-1">📋</span>
                        <span class="text-sm font-semibold">All Transactions</span>
                    </a>
                    <a href="{{ route('admin.reports.index') }}"
                       class="flex flex-col items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-4 px-3 text-center transition">
                        <span class="text-2xl mb-1">📈</span>
                        <span class="text-sm font-semibold">Reports</span>
                    </a>
                </div>
            @endif

            {{-- ── Stat Cards ──────────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Open Checkouts</div>
                    <div class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $openCount }}</div>
                    <div class="text-xs text-gray-400 mt-1">right now</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border {{ $overdueCount > 0 ? 'border-red-300 dark:border-red-700' : 'border-gray-100 dark:border-gray-700' }}">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Overdue</div>
                    <div class="text-3xl font-black {{ $overdueCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
                        {{ $overdueCount }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">need follow-up</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Rooms Available</div>
                    <div class="text-3xl font-black text-green-600 dark:text-green-400">{{ $availableRoomsCount }}</div>
                    <div class="text-xs text-gray-400 mt-1">of {{ $totalRooms }} total</div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Today's Checkouts</div>
                    <div class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ $todayCount }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ now()->format('M d, Y') }}</div>
                </div>

            </div>

            {{-- ── Main Content: Occupancy + Tools ────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Current Room Occupancy --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">🏫 Room Occupancy</h3>
                        <span class="text-xs text-gray-400">
                            {{ $occupiedRooms->count() }} / {{ $totalRooms }} occupied
                        </span>
                    </div>

                    @if ($occupiedRooms->isEmpty())
                        <div class="px-5 py-10 text-center text-gray-400 dark:text-gray-500">
                            <p class="text-3xl mb-2">🟢</p>
                            <p class="text-sm">All rooms are currently free</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto">
                            @foreach ($occupiedRooms as $room)
                                @foreach ($room->transactions as $tx)
                                    <div class="px-5 py-3">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <div class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $room->name }}
                                                </div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $tx->borrower_name }}
                                                    @if ($tx->subject)
                                                        — <span class="text-indigo-600 dark:text-indigo-400">{{ $tx->subject }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $tx->checked_out_at->format('g:i A') }}
                                                </div>
                                                <div class="text-xs {{ $tx->isOverdue() ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                                                    {{ $tx->checked_out_at->diffForHumans(null, true) }}
                                                    @if ($tx->isOverdue()) ⚠️ @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if (auth()->user()->isLabHead())
                                            <div class="mt-1.5 flex gap-2">
                                                <a href="{{ route('scan.return', $tx->id) }}"
                                                   class="text-xs text-orange-500 hover:text-orange-700">
                                                    ↩ Mark Returned
                                                </a>
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                                   class="text-xs text-blue-500 hover:text-blue-700">
                                                    Details
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
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">🔧 Borrowed Tools</h3>
                        <span class="text-xs text-gray-400">
                            {{ $borrowedTools->count() }} tool type(s) out
                        </span>
                    </div>

                    @if ($borrowedTools->isEmpty())
                        <div class="px-5 py-10 text-center text-gray-400 dark:text-gray-500">
                            <p class="text-3xl mb-2">✅</p>
                            <p class="text-sm">All tools are available</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto">
                            @foreach ($borrowedTools as $tool)
                                <div class="px-5 py-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="font-semibold text-sm text-gray-900 dark:text-gray-100">
                                            {{ $tool->name }}
                                        </div>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                            {{ $tool->borrowed_quantity }}/{{ $tool->total_quantity }} borrowed
                                        </span>
                                    </div>
                                    @foreach ($tool->transactions as $tx)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 ml-1 mb-0.5">
                                            {{ $tx->borrower_name }}
                                            @if ($tx->quantity > 1)(×{{ $tx->quantity }})@endif
                                            — {{ $tx->checked_out_at->format('g:i A') }}
                                        </div>
                                    @endforeach
                                    @if ($tool->available_quantity > 0)
                                        <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                                            {{ $tool->available_quantity }} still available
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- ── Recent Transactions ─────────────────────────────────────── --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                        📋 Recent Transactions
                        @if (auth()->user()->isFaculty())
                            <span class="text-xs font-normal text-gray-400 ml-1">(your activity)</span>
                        @endif
                    </h3>
                    @if (auth()->user()->isLabHead() || auth()->user()->isStudentAssistant())
                        <a href="{{ route('admin.transactions.index') }}"
                           class="text-xs text-blue-600 hover:underline">View all →</a>
                    @endif
                </div>

                @if ($recentTransactions->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400">
                        <p class="text-sm">No transactions yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Faculty</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Item</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subject</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time</th>
                                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($recentTransactions as $tx)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                        <td class="px-5 py-3 text-xs text-gray-400">#{{ $tx->id }}</td>
                                        <td class="px-5 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $tx->borrower_name }}
                                        </td>
                                        <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $tx->subjectDescription() }}
                                        </td>
                                        <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $tx->subject ?? '—' }}
                                        </td>
                                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $tx->checked_out_at->format('M d, g:i A') }}
                                        </td>
                                        <td class="px-5 py-3">
                                            @if ($tx->status === 'open')
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Open</span>
                                            @elseif ($tx->status === 'returned')
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Returned</span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Overdue</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
