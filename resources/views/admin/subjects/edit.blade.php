<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.subjects.index') }}"
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                ← Back to Subjects
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Subject: {{ $subject->code }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700">

                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <span>✏️</span>
                            <span>Edit Course Information</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Modify course title, code, offering department, or active status.
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $subject->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $subject->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.subjects.update', $subject) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Course Code --}}
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Course / Subject Code <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="code" name="code" value="{{ old('code', $subject->code) }}" required autofocus
                               placeholder="e.g. CPET 201, INTE 205"
                               class="w-full font-mono uppercase border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Course Name / Description --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Course Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $subject->name) }}" required
                               placeholder="e.g. Data Structures and Algorithms"
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
                                <option value="{{ $deptName }}" {{ old('department', $subject->department) === $deptName ? 'selected' : '' }}>
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
                                <option value="{{ $yl }}" {{ old('year_level', $subject->year_level) === $yl ? 'selected' : '' }}>
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
                                   {{ old('is_active', $subject->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Course Offering</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Uncheck to archive this subject without deleting past transaction records.
                                </p>
                            </div>
                        </label>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button"
                                onclick="if (confirm('Are you sure you want to permanently delete subject {{ addslashes($subject->code) }}?')) { document.getElementById('delete-subject-form').submit(); }"
                                class="text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            🗑️ Delete Subject
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.subjects.index') }}"
                               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                                Update Subject
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Hidden Delete Form --}}
                <form id="delete-subject-form" method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
