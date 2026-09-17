<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Codes — Tools</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
        @page { margin: 1cm; }
    </style>
</head>
<body class="bg-gray-100">

    {{-- Print toolbar --}}
    <div class="no-print bg-gray-800 text-white px-6 py-3 flex items-center justify-between sticky top-0 z-10">
        <div>
            <span class="font-bold">QR Code Sheet — Tools</span>
            <span class="text-gray-400 text-sm ml-3">{{ $tools->count() }} tools</span>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.qr.rooms') }}"
               class="text-sm bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded transition">
                Switch to Rooms →
            </a>
            <button onclick="window.print()"
                    class="text-sm bg-green-600 hover:bg-green-700 px-4 py-2 rounded transition">
                🖨️ Print / Save PDF
            </button>
            <a href="{{ route('admin.tools.index') }}"
               class="text-sm text-gray-400 hover:text-white px-3 py-2">← Back</a>
        </div>
    </div>

    <div class="no-print max-w-4xl mx-auto px-6 py-4">
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-sm text-indigo-800">
            <strong>How to use:</strong> Print → cut each QR block → tape it to the physical tool.
            When faculty borrow a tool, they scan its QR code and fill in their name.
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 pb-10">

        {{-- Group by category --}}
        @php $byCategory = $tools->groupBy('category'); @endphp

        @foreach ($byCategory as $category => $categoryTools)
            <h2 class="no-print text-lg font-bold text-gray-700 mb-4 mt-6">{{ $category }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                @foreach ($categoryTools as $tool)
                    <div class="bg-white border-2 border-indigo-200 rounded-xl p-4 text-center shadow-sm">
                        <div class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">
                            {{ $tool->category }}
                        </div>
                        <div class="text-sm font-black text-gray-900 mb-1 leading-tight">{{ $tool->name }}</div>
                        @if ($tool->total_quantity > 1)
                            <div class="text-xs text-gray-400 mb-2">Qty: {{ $tool->total_quantity }}</div>
                        @else
                            <div class="mb-2"></div>
                        @endif
                        <div class="flex justify-center mb-3">
                            {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)
                                ->style('round')
                                ->color(79, 70, 229)
                                ->generate(route('scan.tool', $tool->id)) !!}
                        </div>
                        @if ($tool->room)
                            <div class="text-xs text-gray-400">{{ $tool->room->name }}</div>
                        @endif
                        <div class="text-xs text-gray-300 mt-1 break-all">
                            /scan/tool/{{ $tool->id }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        @if ($tools->isEmpty())
            <div class="text-center py-20 text-gray-500">
                <p class="text-4xl mb-4">📦</p>
                <p>No active tools found.</p>
                <a href="{{ route('admin.tools.create') }}" class="text-indigo-600 hover:underline">Add tools first →</a>
            </div>
        @endif

    </div>

</body>
</html>
