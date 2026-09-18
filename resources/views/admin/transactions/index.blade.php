<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📋 Transaction History
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.transactions.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    + New Transaction
                </a>
                <a href="{{ route('admin.transactions.export', request()->query()) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                    ⬇️ Export CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

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

            {{-- ── Filter Bar ─────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <form method="GET" action="{{ route('admin.transactions.index') }}"
                      class="flex flex-wrap gap-3 items-end">

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Search Name</label>
                        <input type="text" name="borrower" value="{{ request('borrower') }}"
                               placeholder="Faculty name..."
                               class="border-gray-300 rounded-lg text-sm px-3 py-2 w-44">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status"
                                class="border-gray-300 rounded-lg text-sm px-3 py-2">
                            <option value="">All Statuses</option>
                            <option value="open"               {{ request('status') === 'open'               ? 'selected' : '' }}>Open</option>
                            <option value="partially_returned" {{ request('status') === 'partially_returned' ? 'selected' : '' }}>Partially Returned</option>
                            <option value="returned"           {{ request('status') === 'returned'           ? 'selected' : '' }}>Returned</option>
                            <option value="overdue"            {{ request('status') === 'overdue'            ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Room</label>
                        <select name="room_id"
                                class="border-gray-300 rounded-lg text-sm px-3 py-2 w-36">
                            <option value="">All Rooms</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tool</label>
                        <select name="tool_id"
                                class="border-gray-300 rounded-lg text-sm px-3 py-2 w-44">
                            <option value="">All Tools</option>
                            @foreach ($tools as $tool)
                                <option value="{{ $tool->id }}" {{ request('tool_id') == $tool->id ? 'selected' : '' }}>
                                    {{ $tool->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="border-gray-300 rounded-lg text-sm px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="border-gray-300 rounded-lg text-sm px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Source</label>
                        <select name="source"
                                class="border-gray-300 rounded-lg text-sm px-3 py-2">
                            <option value="">All Sources</option>
                            <option value="google_form" {{ request('source') === 'google_form' ? 'selected' : '' }}>Google Form</option>
                            <option value="qr_scan"     {{ request('source') === 'qr_scan'     ? 'selected' : '' }}>QR Scan</option>
                            <option value="dashboard"   {{ request('source') === 'dashboard'   ? 'selected' : '' }}>Dashboard</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.transactions.index') }}"
                           class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── Result Summary ──────────────────────────────────────────── --}}
            <div class="flex gap-4 text-sm">
                <span class="text-gray-500">
                    Showing <strong class="text-gray-800">{{ $transactions->total() }}</strong> records
                </span>
                @if (request()->hasAny(['borrower','status','room_id','tool_id','date_from','date_to','source']))
                    <span class="text-blue-500 text-xs self-center">(filtered)</span>
                @endif
            </div>

            {{-- ── Transactions Table ──────────────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-12">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Faculty</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Checked Out</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Returned</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($transactions as $tx)
                                <tr class="hover:bg-gray-50 {{ $tx->status === 'overdue' ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3 text-xs text-gray-400">#{{ $tx->id }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        {{ $tx->borrower_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $tx->subjectDescription() }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 max-w-[200px]">
                                        <div class="truncate">{{ $tx->subject ?? '—' }}</div>
                                        @if ($tx->hasSoftwareUtilized())
                                            <div class="flex flex-wrap gap-1 mt-0.5">
                                                @foreach ((array) $tx->software_utilized as $sw)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                                        💻 {{ $sw }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                        {{ $tx->checked_out_at->format('M d, g:i A') }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                        {{ $tx->returned_at ? $tx->returned_at->format('g:i A') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        @if ($tx->returned_at)
                                            {{ $tx->checked_out_at->diff($tx->returned_at)->format('%hh %im') }}
                                        @elseif ($tx->isOverdue())
                                            <span class="text-red-500">{{ $tx->checked_out_at->diffForHumans(null, true) }}</span>
                                        @else
                                            {{ $tx->checked_out_at->diffForHumans(null, true) }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-gray-400">
                                            @if ($tx->source === 'google_form') 📝
                                            @elseif ($tx->source === 'qr_scan') 📷
                                            @else 💻
                                            @endif
                                            {{ $tx->source === 'google_form' ? 'Form' : ($tx->source === 'qr_scan' ? 'QR' : 'Manual') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($tx->status === 'open')
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Open</span>
                                        @elseif ($tx->status === 'partially_returned')
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-medium" title="{{ $tx->total_quantity_returned }}/{{ $tx->total_quantity_borrowed }} items returned">
                                                ⚡ Partial ({{ $tx->total_quantity_returned }}/{{ $tx->total_quantity_borrowed }})
                                            </span>
                                        @elseif ($tx->status === 'returned')
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Returned</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                                        <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                           class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">Details</a>
                                        @if ($tx->status !== 'returned')
                                            @if ($tx->items->isNotEmpty())
                                                <a href="{{ route('admin.transactions.show', $tx->id) }}#return-section"
                                                   class="text-xs font-medium bg-amber-100 hover:bg-amber-200 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 px-2 py-1 rounded transition">
                                                    📦 Return Tools
                                                </a>
                                            @else
                                                <form method="POST" action="{{ route('admin.transactions.return', $tx) }}"
                                                      class="inline"
                                                      onsubmit="return confirm('Mark transaction #{{ $tx->id }} ({{ $tx->borrower_name }}) as returned?');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="text-xs font-medium bg-emerald-100 hover:bg-emerald-200 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 px-2 py-1 rounded transition">
                                                        ✓ Return
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                        No transactions match the current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($transactions->hasPages())
                    <div class="px-5 py-3 border-t border-gray-100">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
