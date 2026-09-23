<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.rooms.index') }}"
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                ← Back to Rooms
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Add New Room
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700">

                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span>🏫</span>
                        <span>Room / Laboratory Details</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Add a new room or laboratory to the inventory so faculty, staff, and students can check it out and scan QR codes.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.rooms.store') }}" class="space-y-5"
                      x-data="{
                          hasWifi: {{ old('has_wifi', false) ? 'true' : 'false' }},
                          roomName: '{{ old('name', '') }}',
                          roomType: '{{ old('room_type', '') }}',
                          setPreset(prefix, type) {
                              if (!this.roomName || this.roomName === 'LAB ' || this.roomName === 'LEC ' || this.roomName === 'Office') {
                                  this.roomName = prefix;
                              } else if (!this.roomName.startsWith(prefix.trim())) {
                                  this.roomName = prefix;
                              }
                              if (type) this.roomType = type;
                          },
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

                    {{-- Room Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Room Name / Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" x-model="roomName" value="{{ old('name') }}" required autofocus
                               placeholder="e.g. LAB 104, LAB MECH-1, LEC 201, DOMIT Office"
                               class="w-full uppercase border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        <div class="mt-1.5 flex items-center gap-2 flex-wrap text-xs">
                            <span class="text-gray-400">Quick Presets:</span>
                            <button type="button" @click="setPreset('LAB ', 'computer_lab')" class="px-2 py-0.5 rounded bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 hover:bg-purple-100 transition">
                                💻 Computer Lab
                            </button>
                            <button type="button" @click="setPreset('LAB ', 'engineering_lab')" class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 transition">
                                ⚙️ Mechanical / Eng Lab
                            </button>
                            <button type="button" @click="setPreset('LEC ', 'lecture')" class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition">
                                📖 Lecture Room
                            </button>
                            <button type="button" @click="setPreset('Office', 'office')" class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition">
                                🏢 Office
                            </button>
                        </div>
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
                                <option value="{{ $key }}" {{ old('room_type') === $key ? 'selected' : '' }}>
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
                                <option value="{{ $deptName }}" {{ old('department') === $deptName ? 'selected' : '' }}>
                                    [{{ $deptCode }}] {{ $deptName }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Associates this room with a department for filtered schedules and utilization analytics.
                        </p>
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
                            <input type="text" id="location" name="location" value="{{ old('location') }}"
                                   placeholder="e.g. 1st Floor, 2nd Floor, Wing A"
                                   list="location_presets"
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
                            <input type="number" id="capacity" name="capacity" value="{{ old('capacity') }}" min="1" max="500"
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
                                      class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('wifi_notes') }}</textarea>
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
                        <input type="url" id="manual_url" name="manual_url" value="{{ old('manual_url') }}"
                               placeholder="https://drive.google.com/... or link to policy document"
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        @error('manual_url')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.rooms.index') }}"
                           class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                            Cancel
                        </a>
                        <x-primary-button>
                            + Create Room
                        </x-primary-button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
