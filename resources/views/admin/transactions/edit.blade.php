<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.transactions.show', $transaction) }}"
                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm">
                    ← Transaction #{{ $transaction->id }}
                </a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    ✏️ Edit Transaction #{{ $transaction->id }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.transactions.show', $transaction) }}"
                   class="px-3 py-1.5 text-xs text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    Cancel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
             borrowerName: '{{ addslashes(old('borrower_name', $transaction->borrower_name)) }}',
             borrowerEmail: '{{ addslashes(old('borrower_email', $transaction->borrower_email ?? '')) }}',
             selectedDepartment: '{{ addslashes(old('department', $transaction->department ?? '')) }}',
             deptFilter: '{{ addslashes(old('department', $transaction->department ?? '')) }}',
             subjectVal: '{{ addslashes(old('subject', $transaction->subject ?? '')) }}',
             subjectsByDept: {{ Js::from($subjects->groupBy('department')->map(fn($list) => $list->map(fn($s) => ['code' => $s->code, 'name' => $s->name, 'full' => $s->code . ' - ' . $s->name]))) }},
             selectedRoomId: '{{ old('room_id', $transaction->room_id ?? '') }}',
             computerLabIds: {{ Js::from($rooms->filter(fn($r) => $r->isComputerLab())->pluck('id')->map(fn($id) => (int)$id)->values()) }},
             get isSelectedRoomComputerLab() {
                 return Boolean(this.selectedRoomId && this.computerLabIds.includes(parseInt(this.selectedRoomId)));
             },

             onFacultySelect(event) {
                 const opt = event.target.selectedOptions[0];
                 if (!opt || !opt.value) return;
                 if (opt.value === '__custom__') {
                     this.$nextTick(() => document.getElementById('borrower_name')?.focus());
                     return;
                 }
                 this.borrowerName = opt.dataset.name || opt.value;
                 this.borrowerEmail = opt.dataset.email || '';
                 if (opt.dataset.department) {
                     this.selectedDepartment = opt.dataset.department;
                     this.deptFilter = opt.dataset.department;
                 }
             },

             onSubjectSelect(event) {
                 const val = event.target.value;
                 if (val && val !== '__custom__') {
                     this.subjectVal = val;
                 } else if (val === '__custom__') {
                     this.$nextTick(() => document.getElementById('subject_input')?.focus());
                 }
             },

             onRoomSelect(event) {
                 const opt = event.target.selectedOptions[0];
                 this.selectedRoomId = event.target.value;
                 if (opt && opt.dataset.department && !this.selectedDepartment) {
                     this.selectedDepartment = opt.dataset.department;
                 }
                 if (!this.isSelectedRoomComputerLab) {
                     document.querySelectorAll('.software-checkbox').forEach(function(cb) { cb.checked = false; });
                     const custom = document.getElementById('software_custom');
                     if (custom) custom.value = '';
                 }
             }
         }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Informative Help Callout --}}
            <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl p-4 flex items-start gap-3">
                <span class="text-2xl mt-0.5">ℹ️</span>
                <div class="text-sm">
                    <p class="font-semibold text-blue-900 dark:text-blue-200">
                        Correction Mode for Transaction #{{ $transaction->id }}
                    </p>
                    <p class="text-blue-700 dark:text-blue-300 text-xs mt-1">
                        Use this form to correct any data inputted in error by a student or instructor, such as selecting the wrong room, typing an incorrect course/subject, adjusting timestamps, or updating software usage.
                    </p>
                </div>
            </div>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-300 rounded-xl p-4 text-sm">
                    <p class="font-bold mb-1">Please fix the following issues:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Main Form Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base flex items-center gap-2">
                            @if ($transaction->isRoom())
                                <span>🏫 Room Utilization Details</span>
                            @else
                                <span>🔧 Tool Borrowing Details</span>
                            @endif
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Original Source: <span class="capitalize font-medium">{{ str_replace('_', ' ', $transaction->source) }}</span>
                            • Logged: {{ $transaction->created_at->format('M d, Y h:i A') }}
                        </p>
                    </div>

                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                        {{ $transaction->status === 'returned' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' :
                          ($transaction->status === 'open' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' :
                          ($transaction->status === 'partially_returned' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300')) }}">
                        Status: {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.transactions.update', $transaction) }}"
                      @submit="if (isSelectedRoomComputerLab) {
                          const checked = Array.from(document.querySelectorAll('.software-checkbox:checked'));
                          const custom = document.getElementById('software_custom')?.value?.trim();
                          if (checked.length === 0 && !custom) {
                              alert('⚠️ Computer Laboratory sessions require at least one software to be selected or specified.');
                              $event.preventDefault();
                              return false;
                          }
                      }"
                      class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    @if ($transaction->isRoom())
                        {{-- ── ROOM SELECTION (PRIMARY FIX FOR WRONG ROOM) ───────────── --}}
                        <div class="bg-amber-50/70 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/60 rounded-xl p-4">
                            <label for="room_id" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                                <span>🚪 Laboratory / Lecture Room</span>
                                <span class="text-red-500">*</span>
                                <span class="text-xs font-normal text-amber-700 dark:text-amber-300 ml-1">
                                    (Currently: <strong>{{ $transaction->room?->name ?? 'None' }}</strong>)
                                </span>
                            </label>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                If the student selected the wrong room, select the correct laboratory room here:
                            </p>

                            <select name="room_id" id="room_id" required x-model="selectedRoomId" @change="onRoomSelect($event)"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-semibold">
                                <option value="">— Select Room —</option>
                                @php
                                    $currentRoomId = old('room_id', $transaction->room_id);
                                    $groupedRooms = $rooms->groupBy(function($r) {
                                        return $r->department ?: 'Other / General Rooms';
                                    });
                                @endphp

                                @foreach ($groupedRooms as $deptName => $deptRooms)
                                    <optgroup label="{{ $deptName }}">
                                        @foreach ($deptRooms as $room)
                                            <option value="{{ $room->id }}"
                                                    data-department="{{ $room->department }}"
                                                    {{ $currentRoomId == $room->id ? 'selected' : '' }}>
                                                {{ $room->name }}
                                                @if ($room->isComputerLab())
                                                    [💻 Computer Lab]
                                                @elseif ($room->isEngineeringLab())
                                                    [⚙️ Engineering Lab]
                                                @elseif ($room->isLecture())
                                                    [📖 Lecture]
                                                @else
                                                    [🏢 Office]
                                                @endif
                                                ({{ $room->departmentShort() }})
                                                @if ($transaction->room_id == $room->id) ★ (Current) @endif
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('room_id')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    @else
                        {{-- ── TOOL SELECTION ────────────────────────────────────────── --}}
                        <div class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl p-4">
                            <label for="tool_id" class="block text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">
                                🔧 Tool / Equipment <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="md:col-span-2">
                                    <select name="tool_id" id="tool_id" required
                                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        @foreach ($tools as $tool)
                                            <option value="{{ $tool->id }}" {{ old('tool_id', $transaction->tool_id) == $tool->id ? 'selected' : '' }}>
                                                {{ $tool->name }} ({{ $tool->departmentShort() }} - {{ $tool->category }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="number" name="quantity" min="1" required
                                           value="{{ old('quantity', $transaction->quantity) }}"
                                           placeholder="Qty"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm text-sm">
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- ── BORROWER & DEPARTMENT SECTION ────────────────────────── --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Borrower Name --}}
                        <div>
                            <label for="borrower_name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Borrower / Faculty Name <span class="text-red-500">*</span>
                            </label>

                            {{-- Optional Faculty Quick-picker --}}
                            <div class="mb-1.5">
                                <select @change="onFacultySelect($event)"
                                        class="w-full text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md py-1 px-2">
                                    <option value="">— Quick Select Registered Faculty (Optional) —</option>
                                    @foreach ($faculties as $faculty)
                                        <option value="{{ $faculty->name }}"
                                                data-name="{{ $faculty->name }}"
                                                data-email="{{ $faculty->email }}"
                                                data-department="{{ $faculty->department }}">
                                            {{ $faculty->name }} ({{ $faculty->departmentShort() }})
                                        </option>
                                    @endforeach
                                    <option value="__custom__">✏️ Custom / Student (Type below)</option>
                                </select>
                            </div>

                            <input type="text" name="borrower_name" id="borrower_name" required
                                   x-model="borrowerName"
                                   placeholder="e.g., Prof. Juan Dela Cruz"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            @error('borrower_name')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Borrower Email --}}
                        <div>
                            <label for="borrower_email" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Borrower Email (Optional)
                            </label>
                            <input type="email" name="borrower_email" id="borrower_email"
                                   x-model="borrowerEmail"
                                   placeholder="e.g., faculty@pup.edu.ph"
                                   class="w-full mt-7 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            @error('borrower_email')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ── DEPARTMENT & SUBJECT SECTION ─────────────────────────── --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Department --}}
                        <div>
                            <label for="department" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Department Affiliation
                            </label>
                            <select name="department" id="department" x-model="selectedDepartment"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm text-sm">
                                <option value="">— Auto-detect based on Room/Subject/Faculty —</option>
                                <option value="Department of Computer and Electronics Engineering Technology">DECET (Computer & Electronics Eng'g Tech)</option>
                                <option value="Department of Office Management and Information Technology">DOMIT (Office Mgt & Info Tech)</option>
                                <option value="Department of Electrical and Mechanical Engineering Technology">DEMET (Electrical & Mech Eng'g Tech)</option>
                                <option value="Department of Civil and Railway Engineering Technology">DCRET (Civil & Railway Eng'g Tech)</option>
                                <option value="College of Science">College of Science</option>
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Controls which department this transaction is counted under in audit reports.</p>
                        </div>

                        {{-- Subject / Purpose --}}
                        <div>
                            <label for="subject_input" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                Course Subject / Purpose <span class="text-red-500">*</span>
                            </label>

                            <div class="mb-1.5">
                                <select @change="onSubjectSelect($event)"
                                        class="w-full text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md py-1 px-2">
                                    <option value="">— Pre-listed Subjects (Optional) —</option>
                                    @foreach ($subjects->groupBy('department') as $dept => $sList)
                                        <optgroup label="{{ $dept }}">
                                            @foreach ($sList as $s)
                                                <option value="{{ $s->code }} - {{ $s->name }}">{{ $s->code }} - {{ $s->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                    <option value="__custom__">✏️ Custom Subject (Type below)</option>
                                </select>
                            </div>

                            <input type="text" name="subject" id="subject_input" required
                                   x-model="subjectVal"
                                   placeholder="e.g., CPET 201 - 2D Animation"
                                   class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            @error('subject')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @if ($transaction->isRoom())
                        {{-- ── SOFTWARE UTILIZATION CHECKBOXES & CUSTOM (COMPUTER LABS ONLY) ─────────────── --}}
                        @php
                            $currentSoftware = (array) old('software_utilized', $transaction->software_utilized ?? []);
                            $catalogKeys = array_keys($softwareCatalog);
                            $catalogNames = array_column($softwareCatalog, 'name');
                            $customSoftwareList = array_diff($currentSoftware, array_merge($catalogKeys, $catalogNames));
                            $customSoftwareStr = implode(', ', $customSoftwareList);
                        @endphp
                        <div x-show="isSelectedRoomComputerLab" x-cloak class="border border-purple-200 dark:border-purple-800/50 bg-purple-50/40 dark:bg-purple-950/20 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-sm font-semibold text-purple-900 dark:text-purple-300">
                                        💻 Software Utilized in this Session <span class="text-red-500">*</span>
                                    </label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Required for computer laboratories: select all software titles used during this class session.
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-200 dark:bg-purple-900 text-purple-800 dark:text-purple-300">
                                    Required for Computer Lab
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                @foreach ($softwareCatalog as $key => $meta)
                                    @php $checked = in_array($key, $currentSoftware) || in_array($meta['name'], $currentSoftware); @endphp
                                    <label class="flex items-center gap-2.5 p-2 rounded-lg border text-xs cursor-pointer transition
                                        {{ $checked ? 'bg-purple-100/80 border-purple-300 dark:bg-purple-900/40 dark:border-purple-700 text-purple-900 dark:text-purple-200' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-purple-300' }}">
                                        <input type="checkbox" name="software_utilized[]" value="{{ $meta['name'] }}"
                                               :disabled="!isSelectedRoomComputerLab"
                                               {{ $checked ? 'checked' : '' }}
                                               class="software-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500 h-4 w-4">
                                        <span class="font-medium truncate">{{ $meta['icon'] }} {{ $meta['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="pt-1">
                                <label for="software_custom" class="block text-xs font-medium text-purple-800 dark:text-purple-300 mb-1">
                                    Other / Specific Software Titles (Comma-separated)
                                </label>
                                <input type="text" name="software_custom" id="software_custom"
                                       :disabled="!isSelectedRoomComputerLab"
                                       value="{{ old('software_custom', $customSoftwareStr) }}"
                                       placeholder="e.g. Adobe Animate, Packet Tracer, Blender"
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-lg text-xs py-1.5 px-3">
                            </div>
                            @error('software_utilized')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    @if ($transaction->items->isNotEmpty())
                        <div class="border border-amber-200 dark:border-amber-800/50 bg-amber-50/40 dark:bg-amber-950/20 rounded-xl p-4 space-y-2">
                            <label class="block text-sm font-semibold text-amber-900 dark:text-amber-300">
                                📦 Borrowed Tools & Accessories ({{ $transaction->items->count() }})
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Equipment and accessories checked out for this transaction:
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                                @foreach ($transaction->items as $item)
                                    <div class="p-2.5 rounded-lg bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-700 flex items-center justify-between text-xs">
                                        <div>
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $item->tool?->name ?? 'Tool' }}</span>
                                            <span class="text-gray-500 dark:text-gray-400 block text-[11px]">{{ $item->tool?->category ?? 'General' }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-bold text-amber-700 dark:text-amber-400">×{{ $item->quantity_borrowed }}</span>
                                            <span class="block text-[10px] {{ $item->status === 'returned' ? 'text-green-600 font-semibold' : 'text-amber-600' }}">
                                                {{ $item->status === 'returned' ? '✓ Returned' : '⏳ Checked Out' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ── TIMESTAMPS & STATUS ──────────────────────────────────── --}}
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                            ⏱️ Timestamps & Transaction Status
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            {{-- Checked Out At (Time In) --}}
                            <div>
                                <label for="checked_out_at" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Time In (Checked Out) <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="checked_out_at" id="checked_out_at" required
                                       value="{{ old('checked_out_at', $transaction->checked_out_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm text-xs">
                                @error('checked_out_at')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Expected Return At --}}
                            <div>
                                <label for="expected_return_at" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Expected Due Time
                                </label>
                                <input type="datetime-local" name="expected_return_at" id="expected_return_at"
                                       value="{{ old('expected_return_at', $transaction->expected_return_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm text-xs">
                            </div>

                            {{-- Returned At (Time Out) --}}
                            <div>
                                <label for="returned_at" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Time Out (Returned At)
                                </label>
                                <input type="datetime-local" name="returned_at" id="returned_at"
                                       value="{{ old('returned_at', $transaction->returned_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm text-xs">
                                <p class="text-[10px] text-gray-400 mt-0.5">Leave blank if still ongoing.</p>
                            </div>

                            {{-- Status --}}
                            <div>
                                <label for="status" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm text-xs font-semibold">
                                    <option value="open"               {{ old('status', $transaction->status) === 'open' ? 'selected' : '' }}>🟡 Open (Active)</option>
                                    <option value="returned"           {{ old('status', $transaction->status) === 'returned' ? 'selected' : '' }}>✅ Returned (Completed)</option>
                                    <option value="partially_returned" {{ old('status', $transaction->status) === 'partially_returned' ? 'selected' : '' }}>⚡ Partially Returned</option>
                                    <option value="overdue"            {{ old('status', $transaction->status) === 'overdue' ? 'selected' : '' }}>🚨 Overdue</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ── NOTES SECTION ───────────────────────────────────────── --}}
                    <div>
                        <label for="notes" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Notes / Reason for Correction (Optional)
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  placeholder="e.g., Corrected room from LAB 204 to LAB 105 as requested by student Ruiz due to accidental scan."
                                  class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm text-sm">{{ old('notes', $transaction->notes) }}</textarea>
                    </div>

                    {{-- ── FORM ACTIONS ────────────────────────────────────────── --}}
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <button type="button"
                                    onclick="if(confirm('Are you sure you want to permanently delete transaction #{{ $transaction->id }}? This action cannot be undone.')) { document.getElementById('delete-transaction-form').submit(); }"
                                    class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 hover:underline">
                                🗑️ Delete Transaction
                            </button>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.transactions.show', $transaction) }}"
                               class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                                💾 Save Changes
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Hidden Delete Form --}}
                <form id="delete-transaction-form" method="POST" action="{{ route('admin.transactions.destroy', $transaction) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
