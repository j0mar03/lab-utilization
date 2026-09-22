<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    <span>🏫</span>
                    <span>Room Management</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Manage classrooms, computer laboratories, engineering workshops, and administrative offices.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.qr.rooms') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                    <span>📱 Room QR Codes</span>
                </a>
                <a href="{{ route('admin.rooms.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition">
                    <span>+ Add Room</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 rounded-xl px-4 py-3 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 rounded-xl px-4 py-3 text-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- KPI Metrics Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Rooms</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $counts['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">Computer Labs</p>
                    <p class="text-xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $counts['computer_labs'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Engineering Labs</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $counts['engineering_labs'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Lecture Rooms</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $counts['lectures'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">Offices</p>
                    <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $counts['offices'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-red-600 dark:text-red-400 font-medium">Occupied Now</p>
                    <p class="text-xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $counts['occupied'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Available</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $counts['available'] }}</p>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <form method="GET" action="{{ route('admin.rooms.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Search Room / Location</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="e.g. 104, 201, 2nd Floor, LAB..."
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="w-56">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Department</label>
                        <select name="department" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>
                                    {{ \App\Models\Room::DEPARTMENTS[$dept] ?? $dept }} — {{ Str::limit($dept, 25) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-44">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Room Type</label>
                        <select name="type" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Types</option>
                            <option value="computer_lab" {{ request('type') === 'computer_lab' ? 'selected' : '' }}>💻 Computer Lab</option>
                            <option value="engineering_lab" {{ request('type') === 'engineering_lab' ? 'selected' : '' }}>⚙️ Engineering Lab</option>
                            <option value="lecture" {{ request('type') === 'lecture' ? 'selected' : '' }}>📖 Lecture Room</option>
                            <option value="office" {{ request('type') === 'office' ? 'selected' : '' }}>🏢 Office</option>
                        </select>
                    </div>

                    <div class="w-36">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Status</option>
                            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>🟢 Available</option>
                            <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>🔴 Occupied</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-gray-900 dark:bg-gray-100 hover:bg-gray-800 dark:hover:bg-white text-white dark:text-gray-900 rounded-lg text-sm font-semibold transition shadow-xs">
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'department', 'type', 'status', 'has_wifi']))
                            <a href="{{ route('admin.rooms.index') }}"
                               class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Rooms Data Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-750 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5 text-left">Room Name & Type</th>
                                <th class="px-5 py-3.5 text-left">Department</th>
                                <th class="px-5 py-3.5 text-left">Location / Floor</th>
                                <th class="px-5 py-3.5 text-center">Capacity</th>
                                <th class="px-5 py-3.5 text-center">Wi-Fi</th>
                                <th class="px-5 py-3.5 text-left">Live Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse ($rooms as $room)
                                @php
                                    $curr = $room->currentTransaction();
                                    $isOcc = $room->isOccupied();
                                    $isStale = $curr?->isStale() ?? false;
                                @endphp
                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-750/50 transition">
                                    {{-- Room Name & Type --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-xl">
                                                @if ($room->isComputerLab()) 💻
                                                @elseif ($room->isEngineeringLab()) ⚙️
                                                @elseif ($room->isOffice()) 🏢
                                                @else 📖
                                                @endif
                                            </span>
                                            <div>
                                                <a href="{{ route('admin.rooms.show', $room) }}"
                                                   class="font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                                    {{ $room->name }}
                                                </a>
                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                    <span class="text-[11px] px-1.5 py-0.2 rounded font-medium {{ $room->isComputerLab() ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-800 dark:text-purple-300' : ($room->isEngineeringLab() ? 'bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-300' : ($room->isOffice() ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-300' : 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300')) }}">
                                                        {{ $room->roomType() }}
                                                    </span>
                                                    @if ($room->manual_url)
                                                        <a href="{{ $room->manual_url }}" target="_blank" class="text-[10px] text-blue-500 hover:underline">
                                                            Manual ↗
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Department --}}
                                    <td class="px-5 py-4">
                                        @if ($room->department)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                                {{ $room->departmentShort() }}
                                            </span>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-[220px]" title="{{ $room->department }}">
                                                {{ $room->department }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">General / Shared</span>
                                        @endif
                                    </td>

                                    {{-- Location --}}
                                    <td class="px-5 py-4 text-xs text-gray-700 dark:text-gray-300">
                                        {{ $room->location ?: '—' }}
                                    </td>

                                    {{-- Capacity --}}
                                    <td class="px-5 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $room->capacity ? $room->capacity . ' seats' : '—' }}
                                    </td>

                                    {{-- Wi-Fi --}}
                                    <td class="px-5 py-4 text-center">
                                        @if ($room->has_wifi)
                                            <span class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-semibold" title="{{ $room->wifi_notes ?: 'Wi-Fi available' }}">
                                                📶 Yes
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">No</span>
                                        @endif
                                    </td>

                                    {{-- Live Status --}}
                                    <td class="px-5 py-4">
                                        @if ($isOcc)
                                            <div>
                                                @if ($isStale)
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 dark:text-amber-400">
                                                        ⚠️ Unclosed Past Session
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-red-600 dark:text-red-400">
                                                        🔴 In Use
                                                    </span>
                                                @endif
                                                <div class="text-[11px] text-gray-600 dark:text-gray-400 truncate max-w-[160px]" title="{{ $curr?->borrower_name }}">
                                                    👤 {{ $curr?->borrower_name }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                                🟢 Available
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.qr.room', $room) }}"
                                               title="View & Print QR Badge"
                                               class="p-1.5 text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                📱
                                            </a>
                                            <a href="{{ route('admin.rooms.edit', $room) }}"
                                               class="px-2.5 py-1 text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                                                  onsubmit="return confirm('Are you sure you want to remove {{ $room->name }}? This can be undone by database administrators.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        {{ $isOcc ? 'disabled' : '' }}
                                                        title="{{ $isOcc ? 'Cannot delete an occupied room' : 'Remove Room' }}"
                                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg transition {{ $isOcc ? 'text-gray-300 dark:text-gray-600 cursor-not-allowed' : 'text-red-600 hover:text-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 cursor-pointer' }}">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                                        <span class="text-3xl block mb-2">🏫</span>
                                        <p class="font-medium">No rooms found matching your search filters.</p>
                                        <a href="{{ route('admin.rooms.create') }}" class="mt-2 inline-block text-xs text-blue-600 hover:underline">
                                            + Add a new room
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($rooms->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $rooms->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
