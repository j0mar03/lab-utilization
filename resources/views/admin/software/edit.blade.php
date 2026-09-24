<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.software.index') }}"
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                ← Back to Software
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Software: {{ $software->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700">

                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>✏️</span>
                            <span>Edit Software Suite</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Update software information, description, category, and target departments.
                        </p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $software->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $software->is_active ? '🟢 Active' : '⚪ Disabled' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.software.update', $software) }}" class="space-y-5"
                      x-data="{
                          selectedIcon: '{{ old('icon', $software->icon ?: '💻') }}',
                          selectedCategory: '{{ old('category', $software->category) }}',
                          isActive: {{ old('is_active', $software->is_active) ? 'true' : 'false' }}
                      }">
                    @csrf
                    @method('PUT')

                    {{-- Software Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Software Title / Suite Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $software->name) }}" required autofocus
                               placeholder="e.g. Blender 3D, Proteus 8, Wireshark"
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm px-3 py-2">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Short Name & Icon (2 Columns) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Short Name --}}
                        <div>
                            <label for="short" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Short / Display Name <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                            </label>
                            <input type="text" id="short" name="short" value="{{ old('short', $software->short) }}"
                                   placeholder="e.g. Blender, Wireshark, MATLAB"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm px-3 py-2">
                            <p class="mt-1 text-[11px] text-gray-400">Used for compact badge displays on cards.</p>
                            @error('short')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Icon / Emoji Selector --}}
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Icon / Emoji
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="text" id="icon" name="icon" x-model="selectedIcon"
                                       class="w-16 text-center text-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 py-1.5">
                                <div class="flex items-center gap-1 flex-wrap text-base">
                                    @foreach ($icons as $emoji => $label)
                                        <button type="button" @click="selectedIcon = '{{ $emoji }}'"
                                                title="{{ $label }}"
                                                class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-base transition">
                                            {{ $emoji }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @error('icon')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Category & Sort Order (2 Columns) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Category / Domain
                            </label>
                            <select id="category" name="category" x-model="selectedCategory"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm px-3 py-2">
                                <option value="">— Select Category —</option>
                                @foreach ($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}" {{ old('category', $software->category) === $catKey ? 'selected' : '' }}>
                                        {{ $catLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Sort Order
                            </label>
                            <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $software->sort_order) }}" min="0" max="9999"
                                   placeholder="e.g. 10"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 text-sm px-3 py-2">
                            <p class="mt-1 text-[11px] text-gray-400">Lower numbers appear first.</p>
                            @error('sort_order')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Included Tools / Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Included Applications / Tools / Modules
                        </label>
                        <textarea id="description" name="description" rows="2"
                                  placeholder="e.g. Photoshop, Illustrator, Premiere Pro"
                                  class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm focus:ring-purple-500 focus:border-purple-500">{{ old('description', $software->description) }}</textarea>
                        <p class="mt-1 text-xs text-gray-400">Helps students and instructors identify which sub-tools fall under this suite.</p>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Target Departments --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Target Departments <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                        </label>
                        @php
                            $savedDepts = (array) ($software->departments ?? []);
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-200 dark:border-gray-700">
                            @foreach ($departments as $deptKey => $deptFullName)
                                <label class="flex items-center gap-2 cursor-pointer text-xs">
                                    <input type="checkbox" name="departments[]" value="{{ $deptKey }}"
                                           {{ in_array($deptKey, old('departments', $savedDepts)) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="font-bold text-gray-800 dark:text-gray-200">[{{ $deptKey }}]</span>
                                    <span class="text-gray-500 dark:text-gray-400 truncate">{{ $deptFullName }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Leave all unchecked if this software applies across all departments.</p>
                        @error('departments')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Active Toggle --}}
                    <div class="p-3.5 rounded-xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="isActive"
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    Active (Visible in checkout forms & QR scanner)
                                </span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    When disabled, this software remains in historical reports but will not be shown in active checkout checklists.
                                </p>
                            </div>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button"
                                onclick="if(confirm('Are you sure you want to remove software suite \'{{ addslashes($software->name) }}\'? Historical reports will be preserved.')) document.getElementById('delete-software-form').submit();"
                                class="text-xs text-red-500 hover:text-red-700 font-medium">
                            🗑️ Delete Software
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.software.index') }}"
                               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                                Cancel
                            </a>
                            <x-primary-button class="!bg-purple-600 hover:!bg-purple-700">
                                Update Software
                            </x-primary-button>
                        </div>
                    </div>

                </form>

                {{-- Hidden Delete Form --}}
                <form id="delete-software-form" method="POST" action="{{ route('admin.software.destroy', $software) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
