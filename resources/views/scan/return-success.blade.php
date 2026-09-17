<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Returned — #{{ $transaction->id }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="bg-green-600 text-white px-4 py-4 flex items-center gap-3">
        <span class="text-2xl">✅</span>
        <div>
            <div class="font-bold text-lg">Item Returned!</div>
            <div class="text-green-200 text-sm">Transaction #{{ $transaction->id }} closed</div>
        </div>
    </div>

    <div class="px-4 py-6 max-w-lg mx-auto">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-5">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Returned by</span>
                    <span class="font-semibold text-gray-900">{{ $transaction->borrower_name }}</span>
                </div>
                @if ($transaction->room)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Room</span>
                        <span class="font-semibold text-gray-900">{{ $transaction->room->name }}</span>
                    </div>
                @endif
                @if ($transaction->tool)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Tool</span>
                        <span class="font-semibold text-gray-900">{{ $transaction->tool->name }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Checked out</span>
                    <span class="text-gray-700">{{ $transaction->checked_out_at->format('g:i A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Returned</span>
                    <span class="text-gray-700">{{ $transaction->returned_at->format('g:i A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Duration</span>
                    <span class="font-medium text-gray-800">
                        {{ $transaction->checked_out_at->diff($transaction->returned_at)->format('%hh %im') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="text-center py-4">
            <p class="text-5xl mb-3">🎉</p>
            <p class="text-gray-600">Thank you! The item has been marked as returned.</p>
        </div>

        <p class="text-center text-xs text-gray-400 mt-8">PUP-ITECH Lab Utilization System</p>
    </div>

</body>
</html>
