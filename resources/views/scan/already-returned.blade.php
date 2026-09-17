<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Already Returned — #{{ $transaction->id }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-sm w-full text-center">
        <p class="text-5xl mb-4">ℹ️</p>
        <h2 class="text-xl font-bold text-gray-800 mb-2">Already Returned</h2>
        <p class="text-gray-500 text-sm mb-6">
            Transaction #{{ $transaction->id }} was already closed at
            {{ $transaction->returned_at?->format('M d, g:i A') }}.
        </p>
        @if ($transaction->room)
            <a href="{{ route('scan.room', $transaction->room_id) }}"
               class="inline-block bg-blue-600 text-white px-6 py-3 rounded-xl font-semibold text-sm">
                ← Back to {{ $transaction->room->name }}
            </a>
        @endif
    </div>
</body>
</html>
