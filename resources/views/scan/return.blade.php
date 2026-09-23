<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Return — #{{ $transaction->id }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="bg-orange-600 text-white px-4 py-4 flex items-center gap-3">
        <span class="text-2xl">↩️</span>
        <div>
            <div class="font-bold text-lg">Return Item</div>
            <div class="text-orange-200 text-sm">Transaction #{{ $transaction->id }}</div>
        </div>
    </div>

    <div class="px-4 py-6 max-w-lg mx-auto">

        {{-- What's being returned --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-5">
            <p class="text-sm text-gray-500 mb-3">Confirming return of:</p>

            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Borrower</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $transaction->borrower_name }}</span>
                </div>
                @if ($transaction->room)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Room</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $transaction->room->name }}</span>
                    </div>
                @endif
                @if ($transaction->tool)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Equipment / Tool</span>
                        <span class="text-sm font-semibold text-gray-900">
                            {{ $transaction->tool->name }}
                            @if ($transaction->quantity > 1) (×{{ $transaction->quantity }}) @endif
                        </span>
                    </div>
                @endif
                @if ($transaction->items->isNotEmpty())
                    <div class="pt-2 border-t border-gray-100">
                        <span class="text-xs font-bold text-orange-700 uppercase tracking-wide block mb-1.5">
                            📦 Tools, Keys & Accessories Returning Together:
                        </span>
                        <ul class="space-y-1 pl-1">
                            @foreach ($transaction->items as $item)
                                <li class="text-sm text-gray-800 flex items-center justify-between">
                                    <span class="flex items-center gap-1.5">
                                        <span>{{ str_contains(strtolower($item->tool?->name ?? ''), 'key') ? '🔑' : '🔌' }}</span>
                                        <span>{{ $item->tool?->name ?? 'Tool' }}</span>
                                    </span>
                                    <span class="font-bold text-orange-600">×{{ $item->remaining_quantity > 0 ? $item->remaining_quantity : $item->quantity_borrowed }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($transaction->subject)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Subject</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $transaction->subject }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Checked out</span>
                    <span class="text-sm text-gray-700">{{ $transaction->checked_out_at->format('M d, g:i A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Duration</span>
                    <span class="text-sm text-gray-700">{{ $transaction->checked_out_at->diffForHumans(null, true) }}</span>
                </div>
            </div>
        </div>

        {{-- Confirm button --}}
        <form method="POST" action="{{ route('scan.return.process', $transaction->id) }}">
            @csrf
            <button type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-bold text-lg py-4 rounded-xl transition-colors mb-3">
                ✅ Confirm Return
            </button>
        </form>

        <a href="{{ $transaction->room_id ? route('scan.room', $transaction->room_id) : route('scan.tool', $transaction->tool_id) }}"
           class="block text-center text-sm text-gray-400 mt-2">
            Cancel
        </a>

        <p class="text-center text-xs text-gray-400 mt-6">PUP-ITECH Lab Utilization System</p>
    </div>

</body>
</html>
