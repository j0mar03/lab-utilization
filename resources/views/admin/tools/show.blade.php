<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.tools.index') }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                    ← Tools Inventory
                </a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    🔧 {{ $tool->name }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.tools.edit', $tool) }}"
                   class="inline-flex items-center px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    ✏️ Edit Tool
                </a>
                <a href="{{ route('admin.transactions.index', ['tool_id' => $tool->id]) }}"
                   class="inline-flex items-center px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    📋 View All Transactions
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg px-4 py-3 text-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg px-4 py-3 text-sm">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            {{-- Tool Status Overview Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-700 pb-5">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $tool->name }}</h3>
                            @if ($tool->is_active)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">Active</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Inactive</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $tool->description ?? 'No additional description provided.' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            🏷️ {{ $tool->category }}
                        </span>
                        @if ($tool->department)
                            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                🏢 {{ $tool->departmentShort() }}
                            </span>
                        @endif
                        @if ($tool->room)
                            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                🏫 Home: {{ $tool->room->name }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Metrics Stat Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5">
                    <div class="bg-gray-50 dark:bg-gray-750 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Inventory</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-gray-100 mt-1">{{ $tool->total_quantity }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Registered stock units</p>
                    </div>

                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800/60">
                        <p class="text-xs text-emerald-800 dark:text-emerald-300 uppercase font-semibold">Available Right Now</p>
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ $tool->available_quantity }}</p>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Ready for checkout</p>
                    </div>

                    <div class="bg-amber-50 dark:bg-amber-950/40 p-4 rounded-xl border border-amber-200 dark:border-amber-800/60">
                        <p class="text-xs text-amber-800 dark:text-amber-300 uppercase font-semibold">Currently Borrowed</p>
                        <p class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-1">{{ $tool->borrowed_quantity }}</p>
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5">In use or checked out</p>
                    </div>

                    <div class="bg-red-50 dark:bg-red-950/40 p-4 rounded-xl border border-red-200 dark:border-red-800/60">
                        <p class="text-xs text-red-800 dark:text-red-300 uppercase font-semibold">Overdue Units</p>
                        <p class="text-2xl font-black text-red-700 dark:text-red-300 mt-1">{{ $tool->overdue_quantity }}</p>
                        <p class="text-[11px] text-red-600 dark:text-red-400 mt-0.5">Requiring immediate follow-up</p>
                    </div>
                </div>
            </div>

            {{-- Current Active Checkouts / In-Use Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                            <span>📦 Active Borrowers & Room Checkouts</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $tool->borrowed_quantity > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' : 'bg-gray-100 text-gray-600' }}">
                                {{ $tool->borrowed_quantity }} unit(s) out
                            </span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Real-time list of faculty and rooms currently utilizing this tool.
                        </p>
                    </div>
                </div>

                @if ($activeCheckouts->isEmpty() && $legacyActiveTransactions->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 dark:text-gray-500">
                        <p class="text-3xl mb-1.5">✅</p>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">All {{ $tool->total_quantity }} unit(s) are in inventory</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">No active checkouts or overdue sessions for this item.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Tx #</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Borrower</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Location / Room</th>
                                    <th class="px-5 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Units Out</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Time Out</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Due Time</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                                {{-- Multi-item / Room checkouts --}}
                                @foreach ($activeCheckouts as $item)
                                    @php $tx = $item->transaction; @endphp
                                    @if ($tx)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition {{ $tx->isOverdue() ? 'bg-red-50/60 dark:bg-red-950/20' : '' }}">
                                            <td class="px-5 py-3 text-xs text-gray-400">#{{ $tx->id }}</td>
                                            <td class="px-5 py-3">
                                                <div class="font-bold text-gray-900 dark:text-gray-100">{{ $tx->borrower_name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->departmentShort() }}</div>
                                            </td>
                                            <td class="px-5 py-3">
                                                @if ($tx->room)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300">
                                                        🏫 {{ $tx->room->name }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Standalone borrow</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-center font-bold text-amber-700 dark:text-amber-400">
                                                {{ $item->remaining_quantity }}
                                            </td>
                                            <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $tx->checked_out_at->format('M d, g:i A') }}
                                            </td>
                                            <td class="px-5 py-3 text-xs">
                                                @if ($tx->expected_return_at)
                                                    <span class="{{ $tx->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600 dark:text-gray-400' }}">
                                                        {{ $tx->expected_return_at->format('g:i A') }} ({{ $tx->expected_return_at->diffForHumans() }})
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3">
                                                @if ($tx->isOverdue())
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 animate-pulse">
                                                        🚨 Overdue
                                                    </span>
                                                @elseif ($item->status === 'partially_returned')
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                        Partial
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                        Checked Out
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3 text-right space-x-2">
                                                <form method="POST" action="{{ route('admin.transactions.return', $tx->id) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Return this tool (and room if applicable) for transaction #{{ $tx->id }}?');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="text-xs font-semibold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 px-2.5 py-1 rounded transition">
                                                        ✓ Return
                                                    </button>
                                                </form>
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                                   class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                    Details →
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach

                                {{-- Legacy direct checkouts --}}
                                @foreach ($legacyActiveTransactions as $tx)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition {{ $tx->isOverdue() ? 'bg-red-50/60 dark:bg-red-950/20' : '' }}">
                                        <td class="px-5 py-3 text-xs text-gray-400">#{{ $tx->id }}</td>
                                        <td class="px-5 py-3">
                                            <div class="font-bold text-gray-900 dark:text-gray-100">{{ $tx->borrower_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tx->departmentShort() }}</div>
                                        </td>
                                        <td class="px-5 py-3">
                                            @if ($tx->room)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300">
                                                    🏫 {{ $tx->room->name }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Standalone borrow</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center font-bold text-amber-700 dark:text-amber-400">
                                            {{ $tx->quantity }}
                                        </td>
                                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $tx->checked_out_at->format('M d, g:i A') }}
                                        </td>
                                        <td class="px-5 py-3 text-xs">
                                            @if ($tx->expected_return_at)
                                                <span class="{{ $tx->isOverdue() ? 'text-red-600 font-bold' : 'text-gray-600 dark:text-gray-400' }}">
                                                    {{ $tx->expected_return_at->format('g:i A') }} ({{ $tx->expected_return_at->diffForHumans() }})
                                                </span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3">
                                            @if ($tx->isOverdue())
                                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 animate-pulse">
                                                    🚨 Overdue
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                    Checked Out
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right space-x-2">
                                            <form method="POST" action="{{ route('admin.transactions.return', $tx->id) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Return this tool for transaction #{{ $tx->id }}?');">
                                                @csrf
                                                <button type="submit"
                                                        class="text-xs font-semibold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 px-2.5 py-1 rounded transition">
                                                    ✓ Return
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                               class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                                Details →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Transaction History for this Tool --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-base">
                        📜 Transaction History for {{ $tool->name }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Historical log of past and ongoing checkouts involving this equipment.
                    </p>
                </div>

                @if ($recentTransactions->isEmpty())
                    <div class="px-5 py-8 text-center text-gray-400 dark:text-gray-500">
                        No transactions recorded for this tool yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Tx #</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Faculty / Borrower</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Context</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Checked Out</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Returned</th>
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                    <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($recentTransactions as $tx)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <td class="px-5 py-3 text-xs text-gray-400">#{{ $tx->id }}</td>
                                        <td class="px-5 py-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $tx->borrower_name }}
                                        </td>
                                        <td class="px-5 py-3 text-xs">
                                            @if ($tx->room)
                                                <span class="inline-flex items-center gap-1 font-semibold text-blue-700 dark:text-blue-300">
                                                    🏫 {{ $tx->room->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">Standalone</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $tx->checked_out_at->format('M d, Y g:i A') }}
                                        </td>
                                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $tx->returned_at ? $tx->returned_at->format('M d, Y g:i A') : '—' }}
                                        </td>
                                        <td class="px-5 py-3">
                                            @if ($tx->status === 'returned')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Returned</span>
                                            @elseif ($tx->status === 'overdue')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 animate-pulse">Overdue</span>
                                            @elseif ($tx->status === 'partially_returned')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Partial</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Open</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                               class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                                View Tx →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($recentTransactions->hasPages())
                        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700">
                            {{ $recentTransactions->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
