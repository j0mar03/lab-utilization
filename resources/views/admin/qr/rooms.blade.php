<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes — Rooms</title>
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
            <span class="font-bold">QR Code Sheet — All Rooms</span>
            <span class="text-gray-400 text-sm ml-3">{{ $rooms->count() }} rooms</span>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.qr.tools') }}"
               class="text-sm bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded transition">
                Switch to Tools →
            </a>
            <button onclick="window.print()"
                    class="text-sm bg-green-600 hover:bg-green-700 px-4 py-2 rounded transition">
                🖨️ Print / Save PDF
            </button>
            <a href="{{ route('admin.tools.index') }}"
               class="text-sm text-gray-400 hover:text-white px-3 py-2">← Back</a>
        </div>
    </div>

    {{-- Instructions (no-print) --}}
    <div class="no-print max-w-4xl mx-auto px-6 py-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
            <strong>How to use:</strong> Press "Print / Save PDF" → print on paper →
            cut each QR block and post it on the corresponding room door.
            Faculty scan the QR with their phone camera to check out the room.
        </div>
    </div>

    {{-- QR Grid --}}
    <div class="max-w-4xl mx-auto px-6 pb-10">

        {{-- LAB rooms --}}
        @php $labRooms = $rooms->filter(fn($r) => str_starts_with($r->name, 'LAB')); @endphp
        @if ($labRooms->isNotEmpty())
            <h2 class="no-print text-lg font-bold text-gray-700 mb-4 mt-6">Laboratory Rooms</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                @foreach ($labRooms as $room)
                    <div class="bg-white border-2 border-blue-200 rounded-xl p-4 text-center shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">LAB ROOM</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.2 bg-blue-50 text-blue-700 border border-blue-200 rounded">
                                {{ $room->departmentShort() }}
                            </span>
                        </div>
                        <div class="text-xl font-black text-gray-900 mb-3">{{ $room->name }}</div>
                        <div class="flex justify-center mb-3">
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)
                                ->style('round')
                                ->color(30, 64, 175)
                                ->generate(route('scan.room', $room->id)) !!}
                        </div>
                        @if ($room->location)
                            <div class="text-xs text-gray-400">{{ $room->location }}</div>
                        @endif
                        <div class="text-xs text-gray-300 mt-1 break-all">
                            /scan/room/{{ $room->id }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- LEC rooms --}}
        @php $lecRooms = $rooms->filter(fn($r) => str_starts_with($r->name, 'LEC')); @endphp
        @if ($lecRooms->isNotEmpty())
            <h2 class="no-print text-lg font-bold text-gray-700 mb-4 mt-6">Lecture Rooms</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($lecRooms as $room)
                    <div class="bg-white border-2 border-gray-200 rounded-xl p-4 text-center shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">LECTURE</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.2 bg-gray-100 text-gray-700 border border-gray-300 rounded">
                                {{ $room->departmentShort() }}
                            </span>
                        </div>
                        <div class="text-xl font-black text-gray-900 mb-3">{{ $room->name }}</div>
                        <div class="flex justify-center mb-3">
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)
                                ->style('round')
                                ->color(75, 85, 99)
                                ->generate(route('scan.room', $room->id)) !!}
                        </div>
                        @if ($room->location)
                            <div class="text-xs text-gray-400">{{ $room->location }}</div>
                        @endif
                        <div class="text-xs text-gray-300 mt-1 break-all">
                            /scan/room/{{ $room->id }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

</body>
</html>
