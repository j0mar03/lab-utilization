<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tools.index') }}"
               class="text-gray-500 hover:text-gray-700 text-sm">
                ← Back to Tools
            </a>
            <span class="text-gray-300">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Tool: {{ $tool->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Availability Status Card --}}
            @php
                $borrowed  = $tool->borrowed_quantity;
                $available = $tool->available_quantity;
            @endphp
            <div class="bg-white shadow-sm rounded-lg p-4">
                <p class="text-sm font-medium text-gray-600 mb-3">Current Availability</p>
                <div class="flex gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $tool->total_quantity }}</div>
                        <div class="text-xs text-gray-500">Total</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $borrowed > 0 ? 'text-orange-500' : 'text-gray-400' }}">{{ $borrowed }}</div>
                        <div class="text-xs text-gray-500">Borrowed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $available > 0 ? 'text-green-500' : 'text-red-500' }}">{{ $available }}</div>
                        <div class="text-xs text-gray-500">Available</div>
                    </div>
                </div>
            </div>

            {{-- Edit Form --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('admin.tools.update', $tool) }}"
                      x-data="{ newCat: false }">
                    @csrf
                    @method('PUT')

                    {{-- Tool Name --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tool Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $tool->name) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Category <span class="text-red-500">*</span>
                        </label>

                        @if ($categories->isNotEmpty())
                            <div class="flex gap-2 items-center mb-2">
                                <div class="flex-1" x-show="!newCat">
                                    <select name="category"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}" {{ old('category', $tool->category) === $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" @click="newCat = !newCat"
                                        class="px-3 py-2 text-sm border border-dashed border-gray-400 rounded-md text-gray-600 hover:border-blue-400 hover:text-blue-600 whitespace-nowrap">
                                    <span x-text="newCat ? '← Use existing' : '+ New category'"></span>
                                </button>
                            </div>
                        @endif

                        <div x-show="newCat || {{ $categories->isEmpty() ? 'true' : 'false' }}">
                            <input type="text" name="new_category" value="{{ old('new_category') }}"
                                   placeholder="Type new category name..."
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>

                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Department <span class="text-gray-500 text-xs font-normal">(Tool Inventory Department)</span>
                        </label>
                        <select name="department"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">— General / Shared / All Departments —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ old('department', $tool->department) === $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Assign to DOMIT, DCEET, or DEMET to organize inventory and filter checkouts by department.
                        </p>
                        @error('department')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Description <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <textarea name="description" rows="2"
                                  class="w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $tool->description) }}</textarea>
                    </div>

                    {{-- Total Quantity --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Total Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_quantity"
                               value="{{ old('total_quantity', $tool->total_quantity) }}"
                               min="{{ $borrowed }}" max="999" required
                               class="w-32 border-gray-300 rounded-md shadow-sm">
                        @if ($borrowed > 0)
                            <p class="mt-1 text-xs text-orange-500">
                                ⚠️ {{ $borrowed }} unit(s) currently borrowed — minimum quantity is {{ $borrowed }}.
                            </p>
                        @endif
                        @error('total_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Home Room --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Home Room <span class="text-gray-400 font-normal">(where this tool is normally stored)</span>
                        </label>
                        <select name="room_id"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">— None (shared / floating) —</option>
                            @php
                                $offices = $rooms->filter(fn($r) => $r->isOffice());
                                $otherRooms = $rooms->filter(fn($r) => !$r->isOffice());
                            @endphp

                            @if ($offices->isNotEmpty())
                                <optgroup label="🏢 Laboratory Offices (Tool Storage / Custodian)">
                                    @foreach ($offices as $room)
                                        <option value="{{ $room->id }}"
                                                {{ old('room_id', $tool->room_id) == $room->id ? 'selected' : '' }}>
                                            🏢 {{ $room->name }} ({{ $room->departmentShort() }} - {{ $room->location }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @foreach ($otherRooms->groupBy(fn($r) => $r->department ?: 'General / Shared') as $dept => $roomList)
                                <optgroup label="🏫 {{ $dept }}">
                                    @foreach ($roomList as $room)
                                        <option value="{{ $room->id }}"
                                                {{ old('room_id', $tool->room_id) == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }} ({{ $room->location ?? 'Campus' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Select <strong>Laboratory Office 109A</strong> or <strong>Laboratory Office 203</strong> for tools kept in custodian offices, or select a specific lab.
                        </p>
                    </div>

                    {{-- Active Toggle --}}
                    <div class="mb-6 flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                               {{ old('is_active', $tool->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-sm text-gray-700">
                            Active (available for checkout)
                        </label>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition">
                            Save Changes
                        </button>
                        <a href="{{ route('admin.tools.index') }}"
                           class="px-6 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
