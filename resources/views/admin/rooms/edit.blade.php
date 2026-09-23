<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.rooms.index') }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                    ← Back to Rooms
                </a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edit Room: {{ $room->name }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.qr.room', $room) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition">
                    <span>📱 QR Badge</span>
                </a>
                <a href="{{ route('admin.rooms.show', $room) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-semibold rounded-lg transition">
                    <span>View Room →</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Room Edit Form --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700">

                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>✏️</span>
                            <span>Update Room Information</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Modify name, department alignment, floor, capacity, or Wi-Fi credentials.
                        </p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $room->isOccupied() ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                        {{ $room->isOccupied() ? '🔴 In Use' : '🟢 Available' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.rooms.update', $room) }}" class="space-y-5"
                      x-data="{
                          hasWifi: {{ old('has_wifi', $room->has_wifi) ? 'true' : 'false' }},
                          roomName: '{{ old('name', $room->name) }}',
                          roomType: '{{ old('room_type', $room->room_type ?? '') }}',
                          get computedType() {
                              if (this.roomType && this.roomType !== 'general') return this.roomType;
                              const n = this.roomName.trim().toUpperCase();
                              if (['LAB 104', 'LAB 105', 'LAB 109C', 'LAB 203', 'LAB 204', 'LAB 205'].includes(n)) return 'computer_lab';
                              if (['LAB 109', 'LAB 109B', 'LAB 208'].includes(n)) return 'engineering_lab';
                              if (n.startsWith('LEC')) return 'lecture';
                              if (n.includes('OFFICE')) return 'office';
                              return this.roomType || 'general';
                          }
                      }">
                    @csrf
                    @method('PUT')

                    {{-- Room Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Room Name / Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" x-model="roomName" required autofocus
                               class="w-full uppercase border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Room Classification / Type --}}
                    <div>
                        <label for="room_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Room Classification <span class="text-gray-400 text-xs font-normal">(Controls software tracking)</span>
                        </label>
                        <select id="room_type" name="room_type" x-model="roomType"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            <option value="">— Auto-detect from Room Name —</option>
                            @foreach ($roomTypes as $key => $label)
                                <option value="{{ $key }}" {{ old('room_type', $room->room_type) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Dynamic Software Tracking Explanation --}}
                        <div class="mt-2 text-xs rounded-lg p-3 transition"
                             :class="{
                                 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800': computedType === 'computer_lab',
                                 'bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800': computedType === 'engineering_lab',
                                 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800': computedType === 'lecture',
                                 'bg-gray-50 text-gray-700 dark:bg-gray-750 dark:text-gray-300 border border-gray-200 dark:border-gray-700': computedType === 'office' || computedType === 'general'
                             }">
                            <template x-if="computedType === 'computer_lab'">
                                <div class="flex items-start gap-2">
                                    <span class="text-base leading-none">💻</span>
                                    <div>
                                        <strong class="font-semibold">Software Tracking ENABLED:</strong>
                                        <p class="mt-0.5 opacity-90">Borrowers checking into this room will be prompted to record software utilized (AutoCAD, Dev C++, Packet Tracer, etc.).</p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="computedType === 'engineering_lab'">
                                <div class="flex items-start gap-2">
                                    <span class="text-base leading-none">⚙️</span>
                                    <div>
                                        <strong class="font-semibold">Mechanical / Engineering Lab (Software Tracking DISABLED):</strong>
                                        <p class="mt-0.5 opacity-90">Mechanical, electrical, and fabrication labs focus on physical apparatus and tool transactions. Software tracking will not be asked.</p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="computedType === 'lecture'">
                                <div class="flex items-start gap-2">
                                    <span class="text-base leading-none">📖</span>
                                    <div>
                                        <strong class="font-semibold">Lecture Classroom:</strong>
                                        <p class="mt-0.5 opacity-90">Software tracking is disabled. Room checkouts track subject, instructor, and schedule occupancy.</p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="computedType === 'office'">
                                <div class="flex items-start gap-2">
                                    <span class="text-base leading-none">🏢</span>
                                    <div>
                                        <strong class="font-semibold">Office / Administrative Room:</strong>
                                        <p class="mt-0.5 opacity-90">Designated for administrative or faculty staff use. Software tracking disabled.</p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="computedType === 'general'">
                                <div class="flex items-start gap-2">
                                    <span class="text-base leading-none">🏫</span>
                                    <div>
                                        <strong class="font-semibold">General / Shared Space:</strong>
                                        <p class="mt-0.5 opacity-90">Standard checkout rules apply.</p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        @error('room_type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Assigned Department <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                        </label>
                        <select id="department" name="department"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            <option value="">— General / Shared Room —</option>
                            @foreach ($departments as $deptName => $deptCode)
                                <option value="{{ $deptName }}" {{ old('department', $room->department) === $deptName ? 'selected' : '' }}>
                                    [{{ $deptCode }}] {{ $deptName }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Location & Capacity (2 Columns) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Location / Floor --}}
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Location / Floor
                            </label>
                            <input type="text" id="location" name="location" value="{{ old('location', $room->location) }}"
                                   list="location_presets"
                                   placeholder="e.g. 1st Floor, 2nd Floor"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            <datalist id="location_presets">
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc }}">
                                @endforeach
                            </datalist>
                            @error('location')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Capacity --}}
                        <div>
                            <label for="capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Seating Capacity <span class="text-gray-400 text-xs font-normal">(Students/Seats)</span>
                            </label>
                            <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" min="1" max="500"
                                   placeholder="e.g. 40"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            @error('capacity')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Wi-Fi Availability Toggle --}}
                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-750/50 border border-gray-200 dark:border-gray-700 space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="has_wifi" value="1" x-model="hasWifi"
                                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                <span>📶 Wi-Fi Access Available in Room</span>
                            </span>
                        </label>

                        <div x-show="hasWifi" x-transition>
                            <label for="wifi_notes" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Wi-Fi Notes / SSID / Access Instructions (Optional)
                            </label>
                            <textarea id="wifi_notes" name="wifi_notes" rows="2"
                                      placeholder="e.g. SSID: PUP_ITECH_LAB, Password available at SA counter"
                                      class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('wifi_notes', $room->wifi_notes) }}</textarea>
                        </div>
                        @error('wifi_notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Documentation / Manual URL --}}
                    <div>
                        <label for="manual_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Lab Manual / Guidelines URL <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                        </label>
                        <input type="url" id="manual_url" name="manual_url" value="{{ old('manual_url', $room->manual_url) }}"
                               placeholder="https://drive.google.com/..."
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        @error('manual_url')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                        @if (! $room->isOccupied())
                            <button type="button"
                                    onclick="if(confirm('Are you sure you want to remove this room? This action can be reversed by an administrator.')) document.getElementById('delete-room-form').submit();"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">
                                🗑️ Remove Room
                            </button>
                        @else
                            <span class="text-xs text-gray-400">Cannot delete while room is occupied</span>
                        @endif

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.rooms.index') }}"
                               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                                Cancel
                            </a>
                            <x-primary-button>
                                Save Changes
                            </x-primary-button>
                        </div>
                    </div>

                </form>

                <form id="delete-room-form" method="POST" action="{{ route('admin.rooms.destroy', $room) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
