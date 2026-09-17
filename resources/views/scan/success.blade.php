<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Checkout Confirmed — #{{ $transaction->id }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen">

    <div class="bg-green-600 text-white px-4 py-4 flex items-center gap-3">
        <span class="text-2xl">✅</span>
        <div>
            <div class="font-bold text-lg">Checkout Recorded!</div>
            <div class="text-green-200 text-sm">Transaction #{{ $transaction->id }}</div>
        </div>
    </div>

    <div class="px-4 py-6 max-w-lg mx-auto">

        {{-- Summary card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-5">
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Name</span>
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
                        <span class="font-semibold text-gray-900">
                            {{ $transaction->tool->name }}
                            @if ($transaction->quantity > 1)(×{{ $transaction->quantity }})@endif
                        </span>
                    </div>
                @endif
                @if ($transaction->subject)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Subject</span>
                        <span class="font-semibold text-gray-900">{{ $transaction->subject }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Time</span>
                    <span class="text-gray-700">{{ $transaction->checked_out_at->format('M d, Y g:i A') }}</span>
                </div>
            </div>
        </div>

        {{-- Transaction ID highlight --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-5 text-center">
            <p class="text-sm text-blue-600 mb-1">📋 Your Transaction ID</p>
            <p class="text-4xl font-black text-blue-700">#{{ $transaction->id }}</p>
            <p class="text-xs text-blue-500 mt-2">
                Screenshot this or remember it for returning.
            </p>
        </div>

        {{-- Return QR code --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-5 text-center">
            <p class="text-sm font-semibold text-gray-700 mb-3">
                📷 Return QR Code
            </p>
            <div class="flex justify-center mb-3">
                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(180)
                    ->style('round')
                    ->color(220, 38, 38)
                    ->generate(route('scan.return', $transaction->id)) !!}
            </div>
            <p class="text-xs text-gray-500">
                Scan this when returning to close the transaction.<br>
                Or tap the button below.
            </p>
        </div>

        {{-- Return button --}}
        <a href="{{ route('scan.return', $transaction->id) }}"
           class="block w-full bg-orange-500 hover:bg-orange-600 text-white font-bold text-center text-lg py-4 rounded-xl mb-3 transition-colors">
            ↩️ Go to Return Page
        </a>

        {{-- Wi-Fi info --}}
        @if ($transaction->room && $transaction->room->has_wifi && $transaction->room->wifi_notes)
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200 mb-4">
                <p class="text-sm font-semibold text-blue-800 mb-1">📶 Wi-Fi for {{ $transaction->room->name }}</p>
                <p class="text-sm text-blue-700">{{ $transaction->room->wifi_notes }}</p>
            </div>
        @endif

        {{-- Lab manual link --}}
        @if ($transaction->room && $transaction->room->manual_url)
            <a href="{{ $transaction->room->manual_url }}" target="_blank"
               class="block w-full text-center text-sm text-blue-600 hover:underline py-2">
                📖 View Lab Manual / Room Info →
            </a>
        @endif

        <p class="text-center text-xs text-gray-400 mt-6">PUP-ITECH Lab Utilization System</p>
    </div>

</body>
</html>
