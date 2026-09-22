<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.faculties.index') }}"
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                ← Back to Faculty List
            </a>
            <span class="text-gray-300 dark:text-gray-600">/</span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit Faculty: {{ $faculty->name }}
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
                            <span>Edit Faculty Details</span>
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Update profile, official department assignment, or institutional email.
                        </p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $faculty->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $faculty->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.faculties.update', $faculty) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Faculty Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $faculty->name) }}" required autofocus
                               placeholder="e.g. Engr. Juan Dela Cruz, Prof. Maria Santos"
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Institutional / PUP Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $faculty->email) }}" required
                               placeholder="e.g. jdelacruz@pup.edu.ph"
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <select id="department" name="department" required
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            <option value="">— Select Department —</option>
                            @foreach ($departments as $deptName => $deptCode)
                                <option value="{{ $deptName }}" {{ old('department', $faculty->department) === $deptName ? 'selected' : '' }}>
                                    [{{ $deptCode }}] {{ $deptName }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Gender <span class="text-gray-400 text-xs font-normal">(Optional)</span>
                        </label>
                        <select id="gender" name="gender"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm px-3 py-2">
                            <option value="">— Prefer not to specify —</option>
                            <option value="M" {{ old('gender', $faculty->gender) === 'M' ? 'selected' : '' }}>Male (M)</option>
                            <option value="F" {{ old('gender', $faculty->gender) === 'F' ? 'selected' : '' }}>Female (F)</option>
                            <option value="Other" {{ old('gender', $faculty->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Active Status Checkbox --}}
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $faculty->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:ring-blue-500">
                            <div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Status</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Uncheck if this instructor is on leave, retired, or inactive.
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
                                onclick="if (confirm('Are you sure you want to permanently delete this faculty member?')) { document.getElementById('delete-faculty-form').submit(); }"
                                class="text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                            🗑️ Delete Faculty
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.faculties.index') }}"
                               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                                Update Faculty
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Hidden Delete Form --}}
                <form id="delete-faculty-form" method="POST" action="{{ route('admin.faculties.destroy', $faculty) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
