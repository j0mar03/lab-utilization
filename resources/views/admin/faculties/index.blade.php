<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    <span>👨‍🏫</span>
                    <span>Faculty Management</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Maintain official list of instructors, departments, and institutional emails for laboratory borrower selection.
                </p>
            </div>
            <a href="{{ route('admin.faculties.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                + Add Faculty
            </a>
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

            {{-- Summary KPI Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Faculty</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $counts['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Active</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $counts['active'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">DECET</p>
                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $counts['decet'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">DOMIT</p>
                    <p class="text-xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $counts['domit'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">DEMET</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $counts['demet'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Inactive</p>
                    <p class="text-xl font-bold text-gray-500 dark:text-gray-400 mt-1">{{ $counts['inactive'] }}</p>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <form method="GET" action="{{ route('admin.faculties.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Search Name / Email</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="e.g. Ruiz, Santos, @pup.edu.ph..."
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="w-64">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Department</label>
                        <select name="department" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>
                                    {{ \App\Models\Faculty::DEPARTMENTS[$dept] ?? $dept }} — {{ Str::limit($dept, 30) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-36">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Status</option>
                            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'department', 'status']))
                            <a href="{{ route('admin.faculties.index') }}"
                               class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Faculty Records Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3 text-left w-12">#</th>
                                <th class="px-5 py-3 text-left">Faculty Member</th>
                                <th class="px-5 py-3 text-left">Department</th>
                                <th class="px-4 py-3 text-center">Gender</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse ($faculties as $faculty)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                    <td class="px-5 py-3.5 text-xs text-gray-400">
                                        {{ $faculty->id }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <span>{{ $faculty->gender === 'F' ? '👩‍🏫' : '👨‍🏫' }}</span>
                                            <span>{{ $faculty->name }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            ✉️ {{ $faculty->email }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @php
                                            $short = $faculty->departmentShort();
                                            $badgeClass = match($short) {
                                                'DECET' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200',
                                                'DOMIT' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200',
                                                'DEMET' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200',
                                                'DCRET' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $badgeClass }}">
                                            {{ $short }}
                                        </span>
                                        <div class="text-[11px] text-gray-400 truncate max-w-xs mt-0.5">
                                            {{ $faculty->department }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 text-center text-xs font-medium text-gray-600 dark:text-gray-300">
                                        {{ $faculty->gender ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if ($faculty->is_active)
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap space-x-2">
                                        <a href="{{ route('admin.faculties.edit', $faculty) }}"
                                           class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            ✏️ Edit
                                        </a>

                                        <form method="POST" action="{{ route('admin.faculties.destroy', $faculty) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to remove {{ addslashes($faculty->name) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 ml-2">
                                                🗑️ Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-gray-400 dark:text-gray-500">
                                        No faculty members found matching your search criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($faculties->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $faculties->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
