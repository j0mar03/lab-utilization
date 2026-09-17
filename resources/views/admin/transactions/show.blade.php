<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.transactions.index') }}"
               class="text-gray-500 hover:text-gray-700 text-sm">
                ← Transactions
            </a>
            <span class="text-gray-300">/</span>
            <h2 class="font-semibold text-xl text-gray-800">
                Transaction #{{ $transaction->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- Status Banner --}}
            @if ($transaction->status === 'overdue')
                <div class="bg-red-50 border border-red-300 rounded-xl p-4 flex items-center gap-3">
                    <span class="text-2xl">🚨</span>
                    <div>
                        <p class="font-bold text-red-800">This transaction is OVERDUE</p>
                        <p class="text-sm text-red-600">Was due {{ $transaction->expected_return_at->diffForHumans() }}</p>
                    </div>
                    @if ($transaction->items->isEmpty())
                        <form method="POST" action="{{ route('admin.transactions.return', $transaction) }}" class="ml-auto"
                              onsubmit="return confirm('Mark this overdue transaction as returned?');">
                            @csrf
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                ✓ Mark Returned
                            </button>
                        </form>
                    @else
                        <a href="#return-section"
                           class="ml-auto bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            📦 Process Return ↓
                        </a>
                    @endif
                </div>
            @elseif ($transaction->status === 'partially_returned')
                <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-center gap-3">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <p class="font-bold text-amber-800">Partially Returned ({{ $transaction->total_quantity_returned }} / {{ $transaction->total_quantity_borrowed }} items returned)</p>
                        <p class="text-xs text-amber-700">Remaining items are still actively checked out to {{ $transaction->borrower_name }}.</p>
                    </div>
                    <a href="#return-section"
                       class="ml-auto bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                        📦 Return Remaining ↓
                    </a>
                </div>
            @elseif ($transaction->status === 'open')
                <div class="bg-yellow-50 border border-yellow-300 rounded-xl p-4 flex items-center gap-3">
                    <span class="text-2xl">🟡</span>
                    <div>
                        <p class="font-semibold text-yellow-800">Currently checked out</p>
                        @if ($transaction->expected_return_at)
                            <p class="text-xs text-yellow-700">Due: {{ $transaction->expected_return_at->format('g:i A') }} ({{ $transaction->expected_return_at->diffForHumans() }})</p>
                        @endif
                    </div>
                    @if ($transaction->items->isEmpty())
                        <form method="POST" action="{{ route('admin.transactions.return', $transaction) }}" class="ml-auto"
                              onsubmit="return confirm('Mark this transaction as returned?');">
                            @csrf
                            <button type="submit"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                ✓ Mark Returned
                            </button>
                        </form>
                    @else
                        <a href="#return-section"
                           class="ml-auto bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            📦 Check In Tools ↓
                        </a>
                    @endif
                </div>
            @else
                <div class="bg-green-50 border border-green-300 rounded-xl p-4 flex items-center gap-3">
                    <span class="text-2xl">✅</span>
                    <div>
                        <p class="font-semibold text-green-800">Returned successfully</p>
                        @if ($transaction->returned_at)
                            <p class="text-xs text-green-700">Checked in on {{ $transaction->returned_at->format('M d, Y g:i A') }}</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Multi-tool Items Breakdown Card --}}
            @if ($transaction->items->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="return-section">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                <span>🔧 Borrowed Tools & Return Status</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 font-normal">
                                    {{ $transaction->items->count() }} item line(s)
                                </span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Breakdown of individual tools, borrowed quantities, and returns.
                            </p>
                        </div>
                        <div class="text-right text-xs">
                            <span class="text-gray-500">Progress:</span>
                            <strong class="text-gray-900 font-semibold">
                                {{ $transaction->total_quantity_returned }} / {{ $transaction->total_quantity_borrowed }} returned
                            </strong>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase">
                                <tr>
                                    <th class="px-5 py-3 text-left">Tool / Equipment</th>
                                    <th class="px-4 py-3 text-center">Borrowed</th>
                                    <th class="px-4 py-3 text-center">Returned</th>
                                    <th class="px-4 py-3 text-center">Remaining</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Returned At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($transaction->items as $item)
                                    <tr>
                                        <td class="px-5 py-3.5">
                                            <div class="font-medium text-gray-900">{{ $item->tool?->name ?? 'Tool #' . $item->tool_id }}</div>
                                            <div class="text-xs text-gray-400">{{ $item->tool?->category ?? 'Equipment' }}</div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-semibold text-gray-800">
                                            {{ $item->quantity_borrowed }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-semibold text-emerald-600">
                                            {{ $item->quantity_returned }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center font-bold {{ $item->remaining_quantity > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                            {{ $item->remaining_quantity }}
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            @if ($item->status === 'returned')
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">Returned</span>
                                            @elseif ($item->status === 'partially_returned')
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Partial</span>
                                            @else
                                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Borrowed</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-right text-xs text-gray-500">
                                            {{ $item->returned_at ? $item->returned_at->format('M d, g:i A') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Return Form for Tools with Remaining Items --}}
                    @if ($transaction->status !== 'returned' && $transaction->items->some(fn($i) => $i->remaining_quantity > 0))
                        <div class="p-5 bg-gray-50/80 border-t border-gray-100">
                            <h4 class="font-semibold text-sm text-gray-900 mb-1 flex items-center gap-2">
                                <span>📥 Check In Tools (Full or Partial Return)</span>
                            </h4>
                            <p class="text-xs text-gray-500 mb-4">
                                Specify how many units are being returned right now. Unreturned items will remain borrowed under this transaction.
                            </p>

                            <form method="POST" action="{{ route('admin.transactions.return', $transaction) }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach ($transaction->items->filter(fn($i) => $i->remaining_quantity > 0) as $item)
                                        <div class="p-3 bg-white rounded-lg border border-gray-200 shadow-sm flex items-center justify-between gap-3">
                                            <div>
                                                <span class="font-medium text-sm text-gray-900 block truncate max-w-[180px]">
                                                    {{ $item->tool?->name ?? 'Tool' }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    Borrowed: {{ $item->quantity_borrowed }} | Remaining: <strong class="text-amber-600">{{ $item->remaining_quantity }}</strong>
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <label class="text-xs text-gray-500">Return:</label>
                                                <input type="number"
                                                       name="return_items[{{ $item->id }}]"
                                                       value="{{ $item->remaining_quantity }}"
                                                       min="0"
                                                       max="{{ $item->remaining_quantity }}"
                                                       class="w-20 border-gray-300 rounded-md text-sm text-center py-1 font-semibold focus:border-blue-500 focus:ring-blue-500">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">
                                        Condition Notes upon Return (Optional)
                                    </label>
                                    <input type="text" name="return_notes"
                                           placeholder="e.g., All returned in good condition; or 1 unit kept for extension."
                                           class="w-full border-gray-300 rounded-lg text-sm px-3 py-2">
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <button type="submit"
                                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                                        ✓ Confirm Tool Return
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Details Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">

                @php
                    $toolDisplay = '—';
                    if ($transaction->items->isNotEmpty()) {
                        $toolDisplay = $transaction->items->map(fn($i) => ($i->tool?->name ?? 'Tool') . " (×{$i->quantity_borrowed})")->join(', ');
                    } elseif ($transaction->tool) {
                        $toolDisplay = $transaction->tool->name;
                    }

                    $qtyDisplay = '—';
                    if ($transaction->items->isNotEmpty()) {
                        $qtyDisplay = "{$transaction->total_quantity_borrowed} total ({$transaction->total_quantity_returned} returned, {$transaction->remaining_quantity} remaining)";
                    } elseif ($transaction->tool) {
                        $qtyDisplay = $transaction->quantity;
                    }

                    $rows = [
                        ['Borrower Name',    $transaction->borrower_name],
                        ['Borrower Email',   $transaction->borrower_email ?? '—'],
                        ['Subject',          $transaction->subject ?? '—'],
                        ['Room',             $transaction->room?->name ?? '—'],
                        ['Tool(s)',          $toolDisplay],
                        ['Quantity',         $qtyDisplay],
                        ['Checked Out At',   $transaction->checked_out_at->format('M d, Y g:i A')],
                        ['Expected Return',  $transaction->expected_return_at?->format('M d, Y g:i A') ?? 'Not specified'],
                        ['Returned At',      $transaction->returned_at?->format('M d, Y g:i A') ?? '—'],
                        ['Duration',         $transaction->returned_at
                                                ? $transaction->checked_out_at->diff($transaction->returned_at)->format('%hh %im')
                                                : $transaction->checked_out_at->diffForHumans(null, true) . ' (ongoing)'],
                        ['Status',           ucfirst(str_replace('_', ' ', $transaction->status))],
                        ['Source',           match($transaction->source) {
                                                'google_form' => '📝 Google Form',
                                                'qr_scan'     => '📷 QR Scan',
                                                default       => '💻 Dashboard',
                                             }],
                        ['Logged by',        $transaction->user?->name ?? 'System'],
                        ['Notes',            $transaction->notes ?? '—'],
                    ];
                @endphp

                @foreach ($rows as [$label, $value])
                    <div class="flex px-5 py-3">
                        <dt class="w-40 text-sm text-gray-500 shrink-0">{{ $label }}</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $value }}</dd>
                    </div>
                @endforeach

            </div>

            {{-- Notification Logs --}}
            @if ($transaction->notificationLogs->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 text-sm">
                            📨 Notifications Sent ({{ $transaction->notificationLogs->count() }})
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($transaction->notificationLogs as $log)
                            <div class="px-5 py-3 flex items-center gap-4 text-sm">
                                <span class="text-gray-400 w-28 shrink-0 text-xs">
                                    {{ $log->sent_at->format('g:i A') }}
                                </span>
                                <span class="capitalize text-gray-700">{{ $log->channel }}</span>
                                <span class="{{ $log->success ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $log->success ? '✅ Sent' : '❌ Failed' }}
                                </span>
                                @if ($log->error_message)
                                    <span class="text-xs text-red-400 truncate">{{ $log->error_message }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
