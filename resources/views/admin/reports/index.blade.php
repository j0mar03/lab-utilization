<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📈 Utilization Reports
            </h2>
            <a href="{{ route('admin.transactions.export') }}"
               class="text-sm bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">
                ⬇️ Export All CSV
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ── Summary Stats Row ──────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <div class="text-3xl font-black text-blue-600">{{ number_format($totalTransactions) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Transactions</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <div class="text-3xl font-black text-green-600">{{ number_format($totalReturned) }}</div>
                    <div class="text-sm text-gray-500 mt-1">Completed Returns</div>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 text-center">
                    <div class="text-3xl font-black text-indigo-600">{{ $avgDurationHours }}h</div>
                    <div class="text-sm text-gray-500 mt-1">Avg Room Checkout Duration</div>
                </div>
            </div>

            {{-- ── Row 1: Daily activity + Day of Week ────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        📅 Checkouts — Last 14 Days
                    </h3>
                    <canvas id="dailyChart" height="200"></canvas>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        📆 Checkouts by Day of Week
                    </h3>
                    <canvas id="dowChart" height="200"></canvas>
                </div>

            </div>

            {{-- ── Row 2: Top rooms + Top tools ───────────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        🏫 Most Used Rooms
                    </h3>
                    @if ($topRoomData->sum() === 0)
                        <p class="text-gray-400 text-sm text-center py-10">No room data yet.</p>
                    @else
                        <canvas id="topRoomsChart" height="220"></canvas>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        🔧 Most Borrowed Tools
                    </h3>
                    @if ($topToolData->sum() === 0)
                        <p class="text-gray-400 text-sm text-center py-10">No tool data yet.</p>
                    @else
                        <canvas id="topToolsChart" height="220"></canvas>
                    @endif
                </div>

            </div>

            {{-- ── Row 3: Room vs Tool split + Avg duration ───────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        🍩 Checkout Type Breakdown
                    </h3>
                    <div class="flex justify-center">
                        <div style="max-width: 260px; width: 100%;">
                            <canvas id="typeChart" height="260"></canvas>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">
                        ⏱️ Avg Checkout Duration by Room (hours)
                    </h3>
                    @if ($avgRoomData->sum() === 0)
                        <p class="text-gray-400 text-sm text-center py-10">Not enough return data yet.</p>
                    @else
                        <canvas id="avgDurationChart" height="220"></canvas>
                    @endif
                </div>

            </div>

            {{-- ── Data Privacy Note ───────────────────────────────────────── --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm text-gray-500">
                <strong>📊 Research Note:</strong> This data is suitable for system-evaluation papers on lab utilization patterns.
                Aggregated charts contain no personal identifiers. For raw data export with borrower names, use the
                <a href="{{ route('admin.transactions.export') }}" class="text-blue-600 hover:underline">CSV export</a>
                and handle in accordance with RA 10173 (Data Privacy Act).
            </div>

        </div>
    </div>

    {{-- Chart.js loaded from CDN — no npm dependency, works on shared hosting --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // ── Shared color palette ──────────────────────────────────────────
        const blueShades = [
            '#2563EB','#3B82F6','#60A5FA','#93C5FD','#BFDBFE',
            '#1D4ED8','#1E40AF','#1E3A8A',
        ];
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9CA3AF' : '#6B7280';
        const gridColor = isDark ? '#374151' : '#F3F4F6';

        // ── Chart 1: Daily checkouts ──────────────────────────────────────
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: @json($dailyLabels),
                datasets: [{
                    label: 'Checkouts',
                    data: @json($dailyData),
                    backgroundColor: '#3B82F6',
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } },
                },
            },
        });

        // ── Chart 2: Day of week ──────────────────────────────────────────
        new Chart(document.getElementById('dowChart'), {
            type: 'bar',
            data: {
                labels: @json($dowLabels),
                datasets: [{
                    label: 'Checkouts',
                    data: @json($dowValues),
                    backgroundColor: '#8B5CF6',
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } },
                },
            },
        });

        // ── Chart 3: Top rooms ────────────────────────────────────────────
        @if ($topRoomData->sum() > 0)
        new Chart(document.getElementById('topRoomsChart'), {
            type: 'bar',
            data: {
                labels: @json($topRoomLabels),
                datasets: [{
                    label: 'Checkouts',
                    data: @json($topRoomData),
                    backgroundColor: blueShades,
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } },
                },
            },
        });
        @endif

        // ── Chart 4: Top tools ────────────────────────────────────────────
        @if ($topToolData->sum() > 0)
        new Chart(document.getElementById('topToolsChart'), {
            type: 'bar',
            data: {
                labels: @json($topToolLabels),
                datasets: [{
                    label: 'Borrows',
                    data: @json($topToolData),
                    backgroundColor: '#10B981',
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } },
                },
            },
        });
        @endif

        // ── Chart 5: Doughnut — room vs tool ─────────────────────────────
        new Chart(document.getElementById('typeChart'), {
            type: 'doughnut',
            data: {
                labels: ['Room Only', 'Tool Only', 'Room + Tool'],
                datasets: [{
                    data: [{{ $roomOnlyCount }}, {{ $toolOnlyCount }}, {{ $bothCount }}],
                    backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, boxWidth: 12 },
                    },
                },
            },
        });

        // ── Chart 6: Avg duration by room ─────────────────────────────────
        @if ($avgRoomData->sum() > 0)
        new Chart(document.getElementById('avgDurationChart'), {
            type: 'bar',
            data: {
                labels: @json($avgRoomLabels),
                datasets: [{
                    label: 'Avg hours',
                    data: @json($avgRoomData),
                    backgroundColor: '#F59E0B',
                    borderRadius: 4,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { display: false } },
                },
            },
        });
        @endif
    </script>

</x-app-layout>
