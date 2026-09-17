<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                👥 User Management
            </h2>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition">
                + Add User
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-300 rounded-lg px-4 py-3 text-sm">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 rounded-lg px-4 py-3 text-sm">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            {{-- Role Overview Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <a href="{{ route('admin.users.index') }}"
                   class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ !request('role') ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 dark:border-gray-700' }} hover:shadow transition">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">All Users</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $roleCounts['all'] }}</div>
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'lab_head']) }}"
                   class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('role') === 'lab_head' ? 'border-purple-500 ring-2 ring-purple-200' : 'border-gray-200 dark:border-gray-700' }} hover:shadow transition">
                    <div class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Lab Head (Admin)</div>
                    <div class="mt-1 text-2xl font-bold text-purple-700 dark:text-purple-300">{{ $roleCounts['lab_head'] }}</div>
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'student_assistant']) }}"
                   class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('role') === 'student_assistant' ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-gray-200 dark:border-gray-700' }} hover:shadow transition">
                    <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Assistants</div>
                    <div class="mt-1 text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ $roleCounts['student_assistant'] }}</div>
                </a>
                <a href="{{ route('admin.users.index', ['role' => 'faculty']) }}"
                   class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('role') === 'faculty' ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-gray-200 dark:border-gray-700' }} hover:shadow transition">
                    <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Faculty</div>
                    <div class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $roleCounts['faculty'] }}</div>
                </a>
            </div>

            {{-- Filters & Search --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Name or email..."
                               class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2 w-64 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                        <select name="role"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg text-sm px-3 py-2">
                            <option value="">All Roles</option>
                            <option value="lab_head" {{ request('role') === 'lab_head' ? 'selected' : '' }}>Lab Head (Admin)</option>
                            <option value="student_assistant" {{ request('role') === 'student_assistant' ? 'selected' : '' }}>Student Assistant</option>
                            <option value="faculty" {{ request('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg text-sm font-medium transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}"
                           class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            {{-- Users Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Telegram Chat ID</th>
                            <th class="px-6 py-3">Created</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            {{ $user->name }}
                                            @if ($user->id === auth()->id())
                                                <span class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded px-1.5 py-0.5 ml-1">You</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    @if ($user->role === 'lab_head')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                            👑 Lab Head (Admin)
                                        </span>
                                    @elseif ($user->role === 'student_assistant')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">
                                            🎓 Student Assistant
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                            👨‍🏫 Faculty
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    @if ($user->telegram_chat_id)
                                        <span class="font-mono text-gray-700 dark:text-gray-300">💬 {{ $user->telegram_chat_id }}</span>
                                    @else
                                        <span class="text-gray-400 italic">None</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $user->created_at?->format('M d, Y') ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs font-medium">
                                        Edit
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete user \'{{ $user->name }}\'? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xs font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    No users found matching the filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($users->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
