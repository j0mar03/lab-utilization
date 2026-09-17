<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tools.index') }}"
               class="text-gray-500 hover:text-gray-700 text-sm">
                ← Back to Tools
            </a>
            <span class="text-gray-300">/</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add New Tool
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form method="POST" action="{{ route('admin.tools.store') }}"
                      x-data="{ newCat: {{ $categories->isEmpty() ? 'true' : (old('new_category') ? 'true' : 'false') }} }">
                    @csrf

                    {{-- Tool Name --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tool Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="e.g. HDMI Cable #1, Lab 104 Key, Projector Remote"
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
                                        <option value="">— Select existing category —</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                                {{ $cat }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="button" @click="newCat = !newCat"
                                        class="px-3 py-2 text-sm border border-dashed border-gray-400 rounded-md text-gray-600 hover:border-blue-400 hover:text-blue-600 whitespace-nowrap">
                                    <span x-text="newCat ? '← Pick from existing' : '+ Add new category'"></span>
                                </button>
                            </div>
                        @endif

                        {{-- New category text input --}}
                        <div x-show="newCat || {{ $categories->isEmpty() ? 'true' : 'false' }}">
                            <input type="text" name="new_category" value="{{ old('new_category', old('category')) }}"
                                   placeholder="e.g. Cables, Keys, Adapters, Testing Tools..."
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <p class="mt-1 text-xs text-gray-500">
                                Enter a descriptive category name for grouping this tool in the system.
                            </p>
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
                                <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>
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
                                  placeholder="Additional notes for admin reference (condition, serial number, etc.)"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Total Quantity --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Total Quantity <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="total_quantity" value="{{ old('total_quantity', 1) }}"
                               min="1" max="999" required
                               class="w-32 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            How many physical units of this tool exist in the lab.
                            (e.g. if you have 5 HDMI cables, enter 5)
                        </p>
                        @error('total_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Home Room (optional) --}}
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Home Room <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <select name="room_id"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">— None (shared / floating) —</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Assign a room if this tool belongs to a specific room (e.g. a room key).
                        </p>
                        @error('room_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Active Toggle --}}
                    <div class="mb-6 flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="is_active"
                               {{ old('is_active', '1') ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-sm text-gray-700">
                            Active (available for checkout)
                        </label>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition">
                            Add Tool
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
