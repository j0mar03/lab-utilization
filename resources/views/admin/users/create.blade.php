<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                ➕ Add New User
            </h2>
            <a href="{{ route('admin.users.index') }}"
               class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                ← Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <x-input-label for="name" value="Full Name *" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      :value="old('name')" required autofocus placeholder="e.g., Prof. Juan Dela Cruz" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Email Address (Login) *" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                      :value="old('email')" required placeholder="e.g., jdelacruz@pup.edu.ph" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>

                    {{-- Role Selection --}}
                    <div>
                        <x-input-label for="role" value="User Role *" />
                        <select id="role" name="role" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-sm">
                            <option value="">Select a Role...</option>
                            <option value="student_assistant" {{ old('role') === 'student_assistant' ? 'selected' : '' }}>
                                🎓 Student Assistant (Can view checkouts, assist returns, view history)
                            </option>
                            <option value="lab_head" {{ old('role') === 'lab_head' ? 'selected' : '' }}>
                                👑 Lab Head / Admin (Full access to all settings, tools, users, and reports)
                            </option>
                            <option value="faculty" {{ old('role') === 'faculty' ? 'selected' : '' }}>
                                👨‍🏫 Faculty (Standard borrower / faculty member)
                            </option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('role')" />
                    </div>

                    {{-- Password --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="password" value="Password *" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                          required placeholder="At least 8 characters" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" value="Confirm Password *" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                          class="mt-1 block w-full" required placeholder="Repeat password" />
                            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
                        </div>
                    </div>

                    {{-- Telegram Chat ID (Optional) --}}
                    <div>
                        <x-input-label for="telegram_chat_id" value="Telegram Chat ID (Optional)" />
                        <x-text-input id="telegram_chat_id" name="telegram_chat_id" type="text" class="mt-1 block w-full"
                                      :value="old('telegram_chat_id')" placeholder="e.g., 123456789" />
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Used to send direct notifications for overdue returns and checkout alerts.
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('telegram_chat_id')" />
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.users.index') }}"
                           class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                            Cancel
                        </a>
                        <x-primary-button>
                            Save User
                        </x-primary-button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
