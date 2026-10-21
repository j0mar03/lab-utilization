<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes — Room Door Feedback Posters</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .page-break { page-break-after: always; }
        }
        @page { margin: 1cm; }
    </style>
</head>
<body class="bg-gray-100">

    {{-- Print toolbar (hidden on print) --}}
    <div class="no-print bg-gray-800 text-white px-6 py-3 flex items-center justify-between sticky top-0 z-10">
        <div>
            <span class="font-bold">⭐ Room Door Feedback Posters — All Rooms</span>
            <span class="text-gray-400 text-sm ml-3">{{ $rooms->count() }} rooms</span>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.qr.rooms') }}" class="text-sm bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded transition">
                ← Checkout QR Sheet
            </a>
            <button onclick="window.print()" class="text-sm bg-green-600 hover:bg-green-700 px-4 py-2 rounded transition font-bold">
                🖨️ Print / Save PDF
            </button>
            <a href="{{ route('admin.feedbacks.index') }}" class="text-sm text-gray-400 hover:text-white px-3 py-2">
                ← Feedback Dashboard
            </a>
        </div>
    </div>

    {{-- Instructions (no-print) --}}
    <div class="no-print max-w-4xl mx-auto px-6 py-4">
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
            <strong>📋 Door Feedback Poster:</strong> Print this sheet and tape each block at the back of the room door. Students and faculty scan this QR code with their smartphones to submit ratings on room cleanliness, safety, and equipment readiness.
        </div>
    </div>

    {{-- QR Grid --}}
    <div class="max-w-4xl mx-auto px-6 pb-10">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            @foreach ($rooms as $room)
                <div class="bg-white border-2 border-red-200 rounded-2xl p-5 text-center shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute top-0 left-0 right-0 h-2 bg-red-800"></div>

                    <div>
                        <div class="flex items-center justify-between mb-1 mt-1">
                            <span class="text-[9px] font-bold text-red-800 uppercase tracking-widest">PUP-ITECH SURVEY</span>
                            <span class="text-[9px] font-semibold px-1.5 py-0.2 bg-red-50 text-red-700 border border-red-200 rounded">
                                {{ $room->departmentShort() }}
                            </span>
                        </div>

                        <div class="text-2xl font-black text-gray-900 mt-2">{{ $room->name }}</div>
                        <p class="text-[11px] text-gray-500 font-medium mb-3">Facility Satisfaction & Quality Feedback</p>

                        <div class="flex justify-center mb-3">
                            <div class="p-2 border border-gray-200 rounded-xl bg-gray-50">
                                {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(130)
                                    ->style('round')
                                    ->color(128, 0, 0)
                                    ->generate(route('feedback.room', $room->id)) !!}
                            </div>
                        </div>

                        <p class="text-xs font-bold text-gray-800 leading-snug">
                            📷 Scan with phone camera to evaluate your room experience!
                        </p>
                        <p class="text-[10px] text-gray-400 mt-1">
                            Rates cleanliness, safety, TV/projector, and staff assistance.
                        </p>
                    </div>

                    <div class="mt-4 pt-2 border-t border-gray-100 text-[9px] text-gray-400">
                        {{ url('/feedback/room/' . $room->id) }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</body>
</html>
