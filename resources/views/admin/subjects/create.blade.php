<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.subjects.index') }}"
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                ← Back to Subjects
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Add New Subject
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700">

                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span>📚</span>
                        <span>Curriculum Course Details</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Add a course offering so students and instructors can record room and equipment transactions under this subject.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-5">
                    @csrf

                    {{-- Course Code --}}
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Course / Subject Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}" required autofocus
                               placeholder="e.g. CPET 201, INTE 205, COEN 301"
                               class="w-full font-mono uppercase border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            The official catalog course code according to the program curriculum.
                        </p>
                        @error('code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Course Name / Description --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Course Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               placeholder="e.g. Data Structures and Algorithms, Digital Electronics Laboratory"
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Offering Department <span class="text-red-500">*</span>
                        </label>
                        <select id="department" name="department" required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            <option value="">— Select Department —</option>
                            @foreach ($departments as $deptName => $deptCode)
                                <option value="{{ $deptName }}" {{ old('department') === $deptName ? 'selected' : '' }}>
                                    [{{ $deptCode }}] {{ $deptName }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Year Level --}}
                    <div>
                        <label for="year_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Curriculum Year Level <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                        </label>
                        <select id="year_level" name="year_level"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            <option value="">— Not specific / All Years —</option>
                            @foreach ($yearLevels as $yl)
                                <option value="{{ $yl }}" {{ old('year_level') === $yl ? 'selected' : '' }}>
                                    {{ $yl }}
                                </option>
                            @endforeach
                        </select>
                        @error('year_level')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Active Status Checkbox --}}
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Course Offering</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Only active subjects appear in transaction creation forms and QR code checkouts.
                                </p>
                            </div>
                        </label>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.subjects.index') }}"
                           class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Save Subject
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
