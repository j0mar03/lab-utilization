<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                    <span>💻</span>
                    <span>Software & Application Catalog</span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Manage software suites and applications required for Computer Laboratory sessions, curriculum delivery, and OPCR utilization reports.
                </p>
            </div>
            <a href="{{ route('admin.software.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <span>+</span>
                <span>Add Software</span>
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

            {{-- Summary Metric Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Software Suites</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $counts['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Active in Checkouts</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $counts['active'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Disabled / Inactive</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $counts['inactive'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">Categories Tracked</p>
                    <p class="text-xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $counts['categories'] }}</p>
                </div>
            </div>

            {{-- Filter Bar --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <form method="GET" action="{{ route('admin.software.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Search Software</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="e.g. AutoCAD, Python, Adobe, Cisco, VS Code..."
                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div class="w-56">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Category</label>
                        <select name="category" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Categories</option>
                            @foreach ($categories as $catKey => $catLabel)
                                <option value="{{ $catKey }}" {{ request('category') === $catKey ? 'selected' : '' }}>
                                    {{ $catLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-44">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Department</label>
                        <select name="department" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Departments</option>
                            @foreach ($departments as $deptKey => $deptName)
                                <option value="{{ $deptKey }}" {{ request('department') === $deptKey ? 'selected' : '' }}>
                                    {{ $deptKey }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-36">
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 shadow-sm">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled Only</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Filter
                        </button>
                        @if (request()->hasAny(['search', 'category', 'department', 'status']))
                            <a href="{{ route('admin.software.index') }}"
                               class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 text-sm rounded-lg transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-750 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="px-5 py-3.5">Software Suite / Title</th>
                                <th class="px-4 py-3.5">Category</th>
                                <th class="px-4 py-3.5">Included Tools / Description</th>
                                <th class="px-4 py-3.5">Target Depts</th>
                                <th class="px-3 py-3.5 text-center">Order</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-5 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($softwareList as $sw)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750/50 transition {{ ! $sw->is_active ? 'opacity-60 bg-gray-50/30 dark:bg-gray-800/40' : '' }}">
                                    {{-- Name & Icon --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <span class="text-2xl p-2 rounded-lg bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800/60 leading-none">
                                                {{ $sw->icon ?: '💻' }}
                                            </span>
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                    <span>{{ $sw->name }}</span>
                                                    @if ($sw->short && $sw->short !== $sw->name)
                                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-mono">
                                                            {{ $sw->short }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-[11px] text-gray-400 dark:text-gray-500">
                                                    ID #{{ $sw->id }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Category --}}
                                    <td class="px-4 py-4">
                                        @if ($sw->category)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                {{ $categories[$sw->category] ?? $sw->category }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>

                                    {{-- Description / Included Tools --}}
                                    <td class="px-4 py-4 max-w-xs">
                                        <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2" title="{{ $sw->description }}">
                                            {{ $sw->description ?: '—' }}
                                        </p>
                                    </td>

                                    {{-- Target Departments --}}
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-1 flex-wrap">
                                            @if (!empty($sw->departments))
                                                @foreach ($sw->departments as $dept)
                                                    <span class="text-[10px] px-1.5 py-0.2 rounded font-mono font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                                        {{ $dept }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-[11px] text-gray-400 italic">All Depts</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Sort Order --}}
                                    <td class="px-3 py-4 text-center font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ $sw->sort_order }}
                                    </td>

                                    {{-- Status with 1-click toggle form --}}
                                    <td class="px-4 py-4 text-center">
                                        <form method="POST" action="{{ route('admin.software.toggle', $sw) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    title="Click to {{ $sw->is_active ? 'Disable' : 'Enable' }}"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold transition {{ $sw->is_active ? 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-gray-100 hover:bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $sw->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                                <span>{{ $sw->is_active ? 'Active' : 'Disabled' }}</span>
                                            </button>
                                        </form>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.software.edit', $sw) }}"
                                               class="p-1.5 text-gray-500 hover:text-purple-600 dark:text-gray-400 dark:hover:text-purple-400 transition"
                                               title="Edit Software">
                                                ✏️
                                            </a>
                                            <form method="POST" action="{{ route('admin.software.destroy', $sw) }}"
                                                  onsubmit="return confirm('Are you sure you want to remove software suite \'{{ addslashes($sw->name) }}\'? Historical transaction reports will remain preserved.')"
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition"
                                                        title="Delete Software">
                                                    🗑️
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <span class="text-3xl">💻</span>
                                            <p class="font-medium text-sm">No software entries found matching your criteria.</p>
                                            <p class="text-xs text-gray-400">Try adjusting your filters or click "+ Add Software" above.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($softwareList->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $softwareList->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
