<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔧 Tool Inventory Management
            </h2>
            <a href="{{ route('admin.tools.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition">
                + Add Tool
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('delete'))
                <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3">
                    ⚠️ {{ $errors->first('delete') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="bg-white rounded-lg shadow-sm p-4">
                <form method="GET" action="{{ route('admin.tools.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Tool name..."
                               class="border-gray-300 rounded-md text-sm px-3 py-2 w-48">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Department</label>
                        <select name="department"
                                class="border-gray-300 rounded-md text-sm px-3 py-2 max-w-[240px]">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Category</label>
                        <select name="category"
                                class="border-gray-300 rounded-md text-sm px-3 py-2">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status"
                                class="border-gray-300 rounded-md text-sm px-3 py-2">
                            <option value="">All</option>
                            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-md transition">
                        Filter
                    </button>
                    <a href="{{ route('admin.tools.index') }}"
                       class="px-4 py-2 text-sm text-gray-600 hover:underline">
                        Reset
                    </a>
                </form>
            </div>

            {{-- Tools Table --}}
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tool Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Home Room</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Qty</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Borrowed</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Available</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($tools as $tool)
                            @php
                                $borrowed  = $tool->borrowed_count ?? 0;
                                $available = max(0, $tool->total_quantity - $borrowed);
                            @endphp
                            <tr class="hover:bg-gray-50 {{ !$tool->is_active ? 'opacity-60' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $tool->name }}</div>
                                    @if ($tool->description)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ Str::limit($tool->description, 60) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($tool->department)
                                        @php
                                            $deptCode = $tool->departmentShort();
                                            $badgeCls = match($deptCode) {
                                                'DOMIT' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                'DCEET' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'DEMET' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                default => 'bg-gray-100 text-gray-800 border-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold border {{ $badgeCls }}" title="{{ $tool->department }}">
                                            {{ $deptCode }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">General</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $tool->category }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $tool->room?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                    {{ $tool->total_quantity }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm {{ $borrowed > 0 ? 'text-orange-600 font-semibold' : 'text-gray-400' }}">
                                    {{ $borrowed }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-bold {{ $available > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $available }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($tool->is_active)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.tools.edit', $tool) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm mr-3">Edit</a>
                                    <form method="POST" action="{{ route('admin.tools.destroy', $tool) }}"
                                          class="inline"
                                          onsubmit="return confirm('Delete {{ addslashes($tool->name) }}? This cannot be undone (transaction history is preserved).')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    No tools found.
                                    <a href="{{ route('admin.tools.create') }}" class="text-blue-600 hover:underline ml-1">Add your first tool →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if ($tools->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $tools->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
